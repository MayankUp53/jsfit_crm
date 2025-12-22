<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Contacts Controller now uses fixed field layouts (no custom field metadata).
 */
class ContactsController extends AppController
{
    protected $Customers;
    protected $Users;
    protected $MtStatuses;
    protected $Callbacks;
    protected $CoreSettings;
    protected $MtQueues;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');

        $this->Customers = $this->fetchTable('Customers');
        $this->Users = $this->fetchTable('Users');
        $this->MtStatuses = $this->fetchTable('MtStatuses');
        $this->Callbacks = $this->fetchTable('Callbacks');
        $this->CoreSettings = $this->fetchTable('CoreSettings');
        $this->MtQueues = $this->fetchTable('MtQueues');
    }

    public function clearSession()
    {
        $session = $this->request->getSession();
        $session->delete('Contacts.search');

        return $this->redirect(['action' => 'index']);
    }

    public function index()
    {
        $this->setListValue();
        $session = $this->request->getSession();

        if ($this->request->is('post')) {
            if ($this->request->getData('searching')) {
                $session->write('Contacts.search', $this->request->getData('Search', []));
                return $this->redirect(['action' => 'index']);
            }
            if ($this->request->getData('clear')) {
                return $this->clearSession();
            }
            if ($this->request->getData('add')) {
                return $this->redirect(['action' => 'add']);
            }
        }

        $searchData = $session->read('Contacts.search') ?? [];
        $searchData += $this->contactSearchDefaults();

        $query = $this->Contacts->find()
            ->contain(['Customers'])
            ->where(['Contacts.deleted IS' => null]);

        if ($searchData['customer_name'] !== '') {
            $query->where(['Customers.name LIKE' => '%' . $searchData['customer_name'] . '%']);
        }
        if ($searchData['customer_phone'] !== '') {
            $query->where([
                'OR' => [
                    'Customers.phone LIKE' => '%' . $searchData['customer_phone'] . '%',
                    'Customers.tel LIKE' => '%' . $searchData['customer_phone'] . '%',
                ],
            ]);
        }
        if ($searchData['customer_email'] !== '') {
            $query->where(['Customers.email LIKE' => '%' . $searchData['customer_email'] . '%']);
        }
        if ($searchData['customer_id'] !== '' && is_numeric($searchData['customer_id'])) {
            $query->where(['Customers.id' => (int)$searchData['customer_id']]);
        }
        if ($searchData['contact_id'] !== '' && is_numeric($searchData['contact_id'])) {
            $query->where(['Contacts.id' => (int)$searchData['contact_id']]);
        }
        if ($searchData['status'] !== '') {
            $query->where(['Contacts.status' => $searchData['status']]);
        }
        if ($searchData['queue_name'] !== '') {
            $query->where(['Contacts.queue_name' => $searchData['queue_name']]);
        }
        if ($searchData['created_user_id'] !== '') {
            $query->where(['Contacts.created_user_id' => $searchData['created_user_id']]);
        }
        if ($searchData['is_done'] !== '') {
            $query->where(['Contacts.is_done' => $searchData['is_done']]);
        }
        if ($searchData['phone'] !== '') {
            $query->where(['Contacts.phone LIKE' => '%' . $searchData['phone'] . '%']);
        }
        if ($searchData['summary'] !== '') {
            $query->where(['Contacts.summary LIKE' => '%' . $searchData['summary'] . '%']);
        }
        if ($searchData['remark'] !== '') {
            $query->where(['Contacts.remark LIKE' => '%' . $searchData['remark'] . '%']);
        }
        if ($searchData['created_from'] !== '') {
            $query->where(['Contacts.created >=' => $searchData['created_from']]);
        }
        if ($searchData['created_to'] !== '') {
            $end = $searchData['created_to'];
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                $end .= ' 23:59:59';
            }
            $query->where(['Contacts.created <=' => $end]);
        }
        if ($searchData['callback_from'] !== '') {
            $query->where(['Contacts.callback_datetime >=' => $searchData['callback_from']]);
        }
        if ($searchData['callback_to'] !== '') {
            $end = $searchData['callback_to'];
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                $end .= ' 23:59:59';
            }
            $query->where(['Contacts.callback_datetime <=' => $end]);
        }

        $query->orderBy(['Contacts.created' => 'DESC']);

        if ($this->request->is('post') && $this->request->getData('download')) {
            $results = $query->all();
            return $this->csv($results->toArray());
        }

        $this->paginate = ['limit' => 50];
        $contacts = $this->paginate($query);
        $this->set(compact('contacts', 'searchData'));
    }

    public function csv($results = null)
    {
        $filename = rawurlencode('対応履歴情報_' . date('Y-m-d H:i:s') . '.csv');
        $columns = $this->contactExportColumns();
        $this->setListValue();

        $this->set(compact('filename', 'columns', 'results'));
        $this->viewBuilder()->setLayout('ajax');
        $this->render('download');
    }

    public function view(?string $id = null)
    {
        $contact = $this->Contacts->find()
            ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
            ->contain(['Customers'])
            ->firstOrFail();

        $customer = $contact->customer;
        $contacts = $this->Contacts->find()
            ->where(['Contacts.customer_id' => $contact->customer_id, 'Contacts.deleted IS' => null])
            ->orderBy(['Contacts.updated' => 'DESC'])
            ->toArray();

        $this->set(compact('contact', 'customer', 'contacts'));
        $this->setListValue();
    }

    public function add(?string $customer_id = null)
    {
        if (empty($customer_id) || !$this->Customers->exists(['id' => $customer_id])) {
            $this->Flash->error(__('顧客を選択してから対応履歴を登録してください。'));
            return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
        }

        $this->setListValue();
        $contact = $this->Contacts->newEmptyEntity();
        $session = $this->request->getSession();
        $customer = $this->Customers->get($customer_id);

        if ($this->request->is('post')) {
            $data = $this->request->getData('Contact') ?: $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['created'] = $now;
            $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['queue_name'] = $session->read('queueName') ?: ($data['queue_name'] ?? null);
            $data['customer_id'] = $customer_id;

            $data = $this->normalizeContactPayload($data);
            $contact = $this->Contacts->patchEntity($contact, $data);

            if ($this->Contacts->save($contact)) {
                $lastId = $contact->id;

                $cbResult = '';
                if (!empty($data['phone']) && !empty($data['callback_datetime'])) {
                    $cbResult = $this->callbackAdd((int)$customer_id, $data);
                }

                $dncResult = $this->callDncApi((int)$customer_id, $data['status'] ?? null);

                $cbMsg = $cbResult === '1' ? '再コール予約の登録に失敗しました。' : '';
                $class = $cbResult === '1' || ($dncResult['result'] ?? 0) === -1 ? 'flash_failure' : 'flash_success';

                $this->Flash->set(__('対応履歴情報の登録が完了しました。' . $cbMsg . ($dncResult['message'] ?? '')), ['element' => 'default', 'params' => ['class' => $class]]);
                $session->delete('queueName');
                return $this->redirect(['action' => 'view', $lastId]);
            }

            $errorMsg = $this->formatEntityErrors($contact);
            $this->Flash->error(__('対応履歴情報の登録に失敗しました。もう一度操作してください。 {0}', $errorMsg));
        }

        $contacts = $this->Contacts->find()
            ->where(['Contacts.customer_id' => $customer_id, 'Contacts.deleted IS' => null])
            ->orderBy(['Contacts.updated' => 'DESC'])
            ->toArray();

        $this->set(compact('contact', 'contacts', 'customer'));
    }

    public function edit(?string $id = null)
    {
        if ($this->request->is('post') && $this->request->getData('delete')) {
            return $this->redirect(['action' => 'delete', $id]);
        }

        if ($this->request->is('post') && $this->request->getData('add')) {
            $customerId = $this->request->getData('Contact.customer_id') ?? $this->request->getData('customer_id');
            return $this->redirect(['action' => 'add', $customerId]);
        }

        $contact = $this->Contacts->find()
            ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
            ->contain(['Customers'])
            ->firstOrFail();
        $customer = $contact->customer;

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData('Contact') ?: $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;
            $data = $this->normalizeContactPayload($data);
            $contact = $this->Contacts->patchEntity($contact, $data);

            if ($this->Contacts->save($contact)) {
                if (!empty($data['phone']) && !empty($data['callback_datetime'])) {
                    $this->callbackEdit($contact->customer_id, (int)$contact->id, $data);
                }

                $this->callDncApi($contact->customer_id, $data['status'] ?? null);

                $this->Flash->success(__('対応履歴情報の更新が完了しました。'));
                return $this->redirect(['action' => 'view', $id]);
            }

            $errorMsg = $this->formatEntityErrors($contact);
            $this->Flash->error(__('対応履歴情報の更新に失敗しました。もう一度操作してください。 {0}', $errorMsg));
        }

        $this->set(compact('contact', 'customer'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $contact = $this->Contacts->get($id);

        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        $contact->deleted = $now;
        $contact->deleted_user_id = $currentUser ? $currentUser->id : 0;

        if ($this->Contacts->save($contact, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('対応履歴情報の削除が完了しました。'));
        } else {
            $this->Flash->error(__('対応履歴情報の削除に失敗しました。もう一度操作してください。'));
        }

        $customerId = $contact->customer_id;
        return $this->redirect(['action' => 'add', $customerId]);
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $activeOpt = ['0' => '無効', '1' => '有効'];
        $sexOpt = ['1' => '男性', '2' => '女性'];

        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['Users.auth' => '9'])
            ->orderBy(['Users.deleted' => 'ASC', 'Users.disp_cd' => 'ASC'])
            ->toArray();

        $allUsersOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->orderBy(['Users.deleted' => 'ASC', 'Users.disp_cd' => 'ASC'])
            ->toArray();

        $statusOpt = $this->MtStatuses->find('list', keyField: 'id', valueField: 'item_name')
            ->where(['MtStatuses.active_flg' => 1])
            ->orderBy(['MtStatuses.disp_cd' => 'ASC'])
            ->toArray();

        $callbackStatusOpt = ['0' => '未', '1' => '完了', '2' => '中止', '3' => '破棄', '4' => '登録誤り', '99' => 'その他'];

        $queueOpt = $this->MtQueues->find('list', keyField: 'id', valueField: 'item_name')
            ->where(['MtQueues.active_flg' => 1])
            ->orderBy(['MtQueues.disp_cd' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'activeOpt', 'sexOpt', 'opeOpt', 'allUsersOpt', 'statusOpt', 'callbackStatusOpt', 'queueOpt'));
    }

    protected function contactSearchDefaults(): array
    {
        return [
            'customer_name' => '',
            'customer_phone' => '',
            'customer_email' => '',
            'customer_id' => '',
            'contact_id' => '',
            'status' => '',
            'queue_name' => '',
            'created_user_id' => '',
            'is_done' => '',
            'phone' => '',
            'summary' => '',
            'remark' => '',
            'created_from' => '',
            'created_to' => '',
            'callback_from' => '',
            'callback_to' => '',
        ];
    }

    protected function contactExportColumns(): array
    {
        return [
            'id' => '履歴ID',
            'customer_id' => '顧客ID',
            'customer.name' => '顧客氏名',
            'customer.phone' => '顧客電話',
            'status' => 'ステータス',
            'summary' => '概要',
            'detail' => '対応内容',
            'remark' => '備考',
            'phone' => '架電番号',
            'queue_name' => 'キュー',
            'callback_datetime' => '再コール日時',
            'is_done' => '再コール状態',
            'created' => '登録日',
            'created_user_id' => '登録者',
            'updated' => '更新日',
            'updated_user_id' => '更新者',
        ];
    }

    protected function normalizeContactPayload(array $data): array
    {
        foreach (['summary', 'detail', 'remark', 'phone', 'queue_name', 'callback_datetime'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (!array_key_exists('is_done', $data) || $data['is_done'] === '') {
            $data['is_done'] = 0;
        }

        return $data;
    }

    protected function callbackAdd(int $customerId, array $data): string
    {
        if (empty($data['callback_datetime']) || empty($data['phone'])) {
            return '';
        }

        $callback = $this->Callbacks->newEmptyEntity();
        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;

        $payload = [
            'customer_id' => $customerId,
            'callback_datetime' => $data['callback_datetime'],
            'phone' => $data['phone'],
            'remark' => $data['remark'] ?? null,
            'is_done' => 0,
            'created' => $now,
            'created_user_id' => $userId,
            'updated' => $now,
            'updated_user_id' => $userId,
        ];

        $callback = $this->Callbacks->patchEntity($callback, $payload);
        return $this->Callbacks->save($callback) ? '0' : '1';
    }

    protected function callbackEdit(int $customerId, int $callbackId, array $data): void
    {
        $callback = $this->Callbacks->find()
            ->where(['id' => $callbackId])
            ->first();

        if (!$callback) {
            $this->callbackAdd($customerId, $data);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;

        $payload = [
            'customer_id' => $customerId,
            'phone' => $data['phone'] ?? null,
            'callback_datetime' => $data['callback_datetime'] ?? null,
            'is_done' => $data['is_done'] ?? 0,
            'remark' => $data['remark'] ?? null,
            'updated' => $now,
            'updated_user_id' => $userId,
        ];

        $callback = $this->Callbacks->patchEntity($callback, $payload);
        $this->Callbacks->save($callback);
    }

    protected function callDncApi(int $customerId, mixed $status): array
    {
        return ['result' => 0, 'message' => ''];
    }
}
