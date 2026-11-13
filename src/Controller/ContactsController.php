<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\Error\debug;
use function Cake\Error\debug as ErrorDebug;
use function Cake\Error\debug as CakeErrorDebug;
use function Cake\Error\debug as DebugAlias;


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
    protected $MtPhonelists;
    protected $MtOutline;

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
        $this->MtPhonelists = $this->fetchTable('MtPhonelists');
        $this->MtOutline = $this->fetchTable('MtOutline');
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

        // Handle POST actions first
        if ($response = $this->handleIndexPost()) {
            return $response;
        }

        $searchData = $this->getSearchData();
        $query = $this->buildBaseQuery();
        $this->applySearchFilters($query, $searchData);

        // CSV download
        if ($this->handleCsvDownload($query)) {
            return;
        }

        $this->paginate = ['limit' => 50];
        $contacts = $this->paginate($query);

        $this->attachOpIncharge($contacts);

        $this->set(compact('contacts', 'searchData'));
    }

    private function handleIndexPost()
    {
        $session = $this->request->getSession();

        if (!$this->request->is('post')) {
            return null;
        }

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

        return null;
    }

    private function getSearchData(): array
    {
        $session = $this->request->getSession();
        $searchData = $session->read('Contacts.search') ?? [];

        return $searchData + $this->contactSearchDefaults();
    }

    private function buildBaseQuery()
    {
        return $this->Contacts->find()
            ->contain(['Customers'])
            ->where(['Contacts.deleted IS' => null])
            ->orderBy([
                'Contacts.updated' => 'DESC',
                'Contacts.created' => 'ASC',
            ]);
    }

    private function applySearchFilters($query, array $searchData): void
    {
        // =========================
        // Customer filters
        // =========================
        if (!empty($searchData['client_cd']) && is_numeric($searchData['client_cd'])) {
            $query->where(['Customers.client_cd' => (int)$searchData['client_cd']]);
        }

        if (!empty($searchData['company_name'])) {
            $query->where([
                'Customers.company_name LIKE' => '%' . trim($searchData['company_name']) . '%'
            ]);
        }

        if (!empty($searchData['name'])) {
            $query->where([
                'Customers.name LIKE' => '%' . trim($searchData['name']) . '%'
            ]);
        }

        $phone = $searchData['tel'] ?? $searchData['phone'] ?? '';
        if (!empty($phone)) {
            $phone = str_replace(['-', ' ', '－'], '', trim((string)$phone));
            $expr = "REPLACE(REPLACE(REPLACE(Customers.tel, '-', ''), ' ', ''), '－', '') LIKE :phone OR
                     REPLACE(REPLACE(REPLACE(Customers.tel2, '-', ''), ' ', ''), '－', '') LIKE :phone OR
                     REPLACE(REPLACE(REPLACE(Customers.tel_only, '-', ''), ' ', ''), '－', '') LIKE :phone";
            $query->where([$expr])->bind(':phone', "%{$phone}%", 'string');
        }
        if (!empty($searchData['client_name'])) {
            $query->where(['Customers.client_name LIKE' => '%' . trim((string)$searchData['client_name']) . '%']);
        }
        if (!empty($searchData['client_branch_name'])) {
            $query->where(['Customers.client_branch_name LIKE' => '%' . trim((string)$searchData['client_branch_name']) . '%']);
        }
        if (!empty($searchData['addr'])) {
            $query->where([
                'OR' => [
                    'Customers.addr1 LIKE' => '%' . trim((string)$searchData['addr']) . '%',
                    'Customers.addr2 LIKE' => '%' . trim((string)$searchData['addr']) . '%',
                    'Customers.addr3 LIKE' => '%' . trim((string)$searchData['addr']) . '%',
                    'Customers.zip_cd LIKE' => '%' . trim((string)$searchData['addr']) . '%',
                ],
            ]);
        }
        if (!empty($searchData['detail'])) {
            $query->where([
                'OR' => [
                    'Contacts.detail LIKE' => '%' . trim((string)$searchData['detail']) . '%',
                    'Contacts.remark LIKE' => '%' . trim((string)$searchData['detail']) . '%',
                ],
            ]);
        }
        

        if (!empty($searchData['email'])) {
            $query->where([
                'LOWER(Customers.email) LIKE' => '%' . strtolower(trim($searchData['email'])) . '%'
            ]);
        }

        if (!empty($searchData['fax'])) {
            $query->where([
                'Customers.fax LIKE' => '%' . trim($searchData['fax']) . '%'
            ]);
        }

        $opUsers = $searchData['created_user_id'] ?? $searchData['op_incharge'] ?? [];
        if (!empty($opUsers)) {
            $userIds = is_array($opUsers)
                ? array_map('intval', $opUsers)
                : [(int)$opUsers];
            $query->where(['Contacts.created_user_id IN' => array_filter($userIds)]);
        }



        // =========================
        // Contact filters
        // =========================

        // Summary (legacy text match)
        if (!empty($searchData['summary'])) {
            $query->where(['Contacts.summary LIKE' => '%' . trim((string)$searchData['summary']) . '%']);
        }

        // Status (multi-select)
        if (!empty($searchData['status'])) {
            $statuses = is_array($searchData['status']) ? $searchData['status'] : [$searchData['status']];
            $query->where(['Contacts.status IN' => array_values(array_filter($statuses))]);
        }


        if (!empty($searchData['created_from'])) {
            $query->where([
                'Contacts.created >=' => $searchData['created_from']
            ]);
        }

        if (!empty($searchData['created_to'])) {
            $end = $searchData['created_to'];

            // If only date is provided, include the full day
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                $end .= ' 23:59:59';
            }

            $query->where([
                'Contacts.created <=' => $end
            ]);
        }



        if (!empty($searchData['updated_from'])) {
            $query->where([
                'Contacts.updated >=' => $searchData['updated_from']
            ]);
        }

        if (!empty($searchData['updated_to'])) {
            $endUpdated = $searchData['updated_to'];

            // If only date is provided, include the full day
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endUpdated)) {
                $endUpdated .= ' 23:59:59';
            }

            $query->where([
                'Contacts.updated <=' => $endUpdated
            ]);
        }
    }

    private function handleCsvDownload($query): bool
    {
        if (!$this->request->is('post') || !$this->request->getData('download')) {
            return false;
        }

        $results = $query->all();
        $exportData = [];

        foreach ($results as $contact) {
            $user = $this->Users->find()
                ->select(['user_name'])
                ->where(['id' => $contact->updated_user_id])
                ->first();
            $createdUser = $this->Users->find()
                ->select(['user_name'])
                ->where(['id' => $contact->created_user_id])
                ->first();
            $updatedUser = $user;
            $status = $this->MtStatuses->find()
                ->select(['item_name'])
                ->where(['id' => $contact->status])
                ->first();
            $customer = $contact->customer;

            $exportData[] = [
                'id'          => $contact->id,
                'client_cd'   => $customer->client_cd ?? $customer->client_code ?? '',
                'client_branch_code' => $customer->client_branch_code ?? '',
                'company_name' => $customer->client_name ?? $customer->company_name ?? '',
                'client_branch_name' => $customer->client_branch_name ?? $customer->company_branch_name ?? '',
                'customer_name' => $customer->name ?? '',
                'customer_email' => $customer->email ?? '',
                'customer_tel' => $customer->tel ?? '',
                'tel2' => $customer->tel2 ?? '',
                'customer_fax' => $customer->fax ?? '',
                'remark'      => $contact->remark,
                'detail'      => $contact->detail ?? $contact->remark ?? '',
                'summary'     => $contact->summary,
                'queue_name'  => $contact->queue_name ?? '',
                'status'      => $status?->item_name ?? $contact->status,
                'addr1'       => $customer->addr1 ?? '',
                'addr2'       => $customer->addr2 ?? '',
                'section_name' => $customer->section_name ?? '',
                'sales_person' => $customer->sales_person ?? '',
                'deadline'    => $customer->deadline ?? '',
                'created'      => $contact->created ? $contact->created->format('Y-m-d H:i:s') : '',
                'updated'      => $contact->updated ? $contact->updated->format('Y-m-d H:i:s') : '',
                'op_incharge' => $user ? $user->user_name : '-',
                'created_user' => $createdUser ? $createdUser->user_name : '-',
                'updated_user' => $updatedUser ? $updatedUser->user_name : '-',
                'created_date' => $contact->created ? $contact->created->format('Y-m-d') : '',
                'created_time' => $contact->created ? $contact->created->format('H:i:s') : '',
                'updated_date' => $contact->updated ? $contact->updated->format('Y-m-d') : '',
                'updated_time' => $contact->updated ? $contact->updated->format('H:i:s') : '',
            ];
        }

        $this->csv($exportData);
        return true;
    }

    private function attachOpIncharge($contacts): void
    {
        foreach ($contacts as $contact) {
            $user = $this->Users->find()
                ->select(['user_name'])
                ->where(['id' => $contact->created_user_id ?: $contact->updated_user_id])
                ->first();

            $contact->op_incharge = $user ? $user->user_name : '-';
        }
    }

    public function csv(array $results = [])
    {
        $filename = rawurlencode('対応履歴情報_' . date('Y-m-d H:i:s') . '.csv');

        $columns = $this->contactExportColumns();

        $this->set(compact('filename', 'columns', 'results'));
        $this->viewBuilder()->setLayout('ajax');
        $this->render('download');
    }




    public function add(?string $customer_id = null)
    {
        // 1️⃣ Validate customer exists
        if (empty($customer_id) || !$this->Customers->exists(['id' => $customer_id])) {
            $this->Flash->error(__('顧客を選択してから対応履歴を登録してください。'));
            return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
        }

        $contact = $this->Contacts->newEmptyEntity();
        $customer = $this->Customers->get($customer_id);
        $currentUser = $this->getCurrentUser();
        $sessionQueueName = (string)($this->request->getSession()->read('queueName') ?? '');
        if ($sessionQueueName !== '') {
            $contact->queue_name = $sessionQueueName;
        }

        // 2️⃣ Handle Cancel FIRST
        if ($this->request->is('post') && $this->request->getData('back')) {
            // ⚡ Immediately redirect, skip everything else
            return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
        }

        // 3️⃣ Handle normal submit (登録)
        if ($this->request->is('post')) {

            $data = $this->request->getData('Contact') ?: $this->request->getData();

            // Force crt_object_id from session
            $sessionCrt = (string)($this->request->getSession()->read('crtObjectId') ?? '');
            if ($sessionCrt !== '') {
                $data['crt_object_id'] = $sessionCrt;
            }
            if ($sessionQueueName !== '' && empty($data['queue_name'])) {
                $data['queue_name'] = $sessionQueueName;
            }

            $now = date('Y-m-d H:i:s');

            $data['customer_id']      = $customer_id;
            $data['created']          = $now;
            $data['updated']          = $now;
            $data['created_user_id']  = $currentUser ? $currentUser->id : 0;
            $data['updated_user_id']  = $currentUser ? $currentUser->id : 0;
            if (!array_key_exists('detail', $data) && !empty($data['remark'])) {
                $data['detail'] = $data['remark'];
            }

            // 🔴 Only now patchEntity → validation runs
            $contact = $this->Contacts->patchEntity($contact, $data);

            if ($this->Contacts->save($contact)) {
                $this->request->getSession()->delete('queueName');
                $this->Flash->success(__('対応履歴情報の登録が完了しました。'));
                return $this->redirect(['action' => 'view', $contact->id]);
            }

            $this->Flash->error(__('対応履歴情報の登録に失敗しました。'));
        }

        // 4️⃣ Fetch contacts
        $contacts = $this->Contacts->find()
            ->where(['Contacts.customer_id' => $customer_id, 'Contacts.deleted IS' => null])
            ->contain(['Customers'])
            ->orderBy([
                'Contacts.updated' => 'DESC',
                'Contacts.id' => 'DESC',
            ])
            ->all();

        foreach ($contacts as $contactItem) {
            $user = $this->Users->find()
                ->select(['user_name'])
                ->where(['id' => $contactItem->updated_user_id])
                ->first();
            $contactItem->op_incharge = $user ? $user->user_name : '-';
        }

        $this->set(compact('contact', 'contacts', 'customer'));
        $this->setListValue();
    }


    public function view(?string $id = null)
    {

        // 1️⃣ Validate ID
        if (empty($id)) {
            $this->Flash->error(__('無効なIDです。'));
            return $this->redirect(['action' => 'index']);
        }

        // 2️⃣ Fetch main contact
        $contact = $this->Contacts->find()
            ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
            ->contain(['Customers'])
            ->first();

        if (!$contact) {
            $this->Flash->error(__('指定された対応履歴が存在しません。'));
            return $this->redirect(['action' => 'index']);
        }

        $currentUserId = $this->request->getAttribute('identity')->id;


        if ($this->request->is(['patch', 'put', 'post'])) {


            $data = $this->request->getData();

            if (!empty($data['update'])) {
                if (!array_key_exists('detail', $data) && !empty($data['remark'])) {
                    $data['detail'] = $data['remark'];
                }

                $contact = $this->Contacts->patchEntity($contact, $data);
                $contact->updated_user_id = $currentUserId;

                if ($this->Contacts->save($contact)) {
                    $this->Flash->success(__('対応履歴を更新しました。'));
                    return $this->redirect(['action' => 'view', $id]);
                }
                $this->Flash->error(__('更新に失敗しました。'));
            }

            if (!empty($data['back'])) {

                return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
            }



            if (!empty($data['add'])) {

                return $this->redirect(['action' => 'add', $contact->customer_id]);
            }


            if (!empty($data['delete'])) {

                // Soft delete: mark deleted timestamp
                $contact->deleted = date('Y-m-d H:i:s');
                $contact->updated_user_id = $currentUserId;

                if ($this->Contacts->save($contact)) {
                    $this->Flash->success(__('対応履歴を削除しました。'));
                    return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $contact->customer_id]);
                }
                $this->Flash->error(__('削除に失敗しました。'));
            }
        }


        $this->setContactDetailView($contact);
    }

    protected function setContactDetailView(\App\Model\Entity\Contact $contact): void
    {
        $customer = $contact->customer;

        $contacts = $this->Contacts->find()
            ->where(['Contacts.customer_id' => $contact->customer_id, 'Contacts.deleted IS' => null])
            ->orderBy([
                'Contacts.updated' => 'DESC',
                'Contacts.id' => 'DESC',
            ])
            ->contain(['Customers'])
            ->all();


        foreach ($contacts as $contactItem) {
            $user = $this->Users->find()
                ->select(['user_name'])
                ->where(['id' => $contactItem->updated_user_id])
                ->first();
            $contactItem->op_incharge = $user ? $user->user_name : '-';
        }


        $mainUser = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contact->updated_user_id])
            ->first();
        $contact->op_incharge = $mainUser ? $mainUser->user_name : '-';

        // Pass variables to view
        $this->set(compact('contact', 'customer', 'contacts'));
        $this->setListValue(); // for select options
    }



    public function edit(?string $id = null)
    {
        // Handle delete button
        if ($this->request->is('post') && $this->request->getData('delete')) {
            return $this->delete($id);
        }

        // Handle back button
        if ($this->request->is('post') && $this->request->getData('add')) {
            $customerId = $this->request->getData('customer_id')
                ?? $this->request->getData('Contact.customer_id');
            return $this->redirect(['action' => 'add', $customerId]);
        }

        // Fetch contact and related customer
        $contact = $this->Contacts->find()
            ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
            ->contain(['Customers'])
            ->firstOrFail();
        $customer = $contact->customer;

        // Handle update
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData('Contact') ?: $this->request->getData();

            // Force crt_object_id from session if present (more reliable than hidden input)
            $sessionCrt = (string)($this->request->getSession()->read('crtObjectId') ?? '');
            if ($sessionCrt !== '') {
                $data['crt_object_id'] = $sessionCrt;
            }

            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            // Patch entity directly without custom field normalization
            $contact = $this->Contacts->patchEntity($contact, $data);

            if ($this->Contacts->save($contact)) {
                // Create a new reservation, matching the legacy contact edit flow.
                if (!empty($data['phone']) && !empty($data['callback_datetime'])) {
                    $this->callbackAdd((int)$contact->customer_id, $data);
                }

                // Optional DNC API call
                $this->callDncApi($contact->customer_id, $data['status'] ?? null);

                $this->Flash->success(__('対応履歴情報の更新が完了しました。'));
                return $this->redirect(['action' => 'view', $id]);
            }

            $errorMsg = $this->formatEntityErrors($contact);
            $this->Flash->error(__('対応履歴情報の更新に失敗しました。もう一度操作してください。 {0}', $errorMsg));
            $this->setContactDetailView($contact);
            return $this->render('view');
        }

        $this->set(compact('contact', 'customer'));
        $this->setListValue();
    }




    public function delete(?string $id = null)
    {


        $this->request->allowMethod(['post', 'delete']);
        $contact = $this->Contacts->get($id);

        $currentUser = $this->getCurrentUser();
        $contact->deleted = date('Y-m-d H:i:s');
        $contact->deleted_user_id = $currentUser ? $currentUser->id : 0;

        if ($this->Contacts->save($contact, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('対応履歴を削除しました。'));
        } else {
            $this->Flash->error(__('削除に失敗しました。'));
        }

        return $this->redirect(['action' => 'add', $contact->customer_id]);
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

        $statusOpt = $this->MtStatuses
            ->find('list', keyField: 'id', valueField: 'item_name')
            ->where(['deleted IS' => null, 'active' => 1])
            ->orderBy(['disp_cd' => 'ASC'])
            ->toArray();

        $overviewOpt = $this->MtOutline
            ->find('list', keyField: 'item_name', valueField: 'item_name')->where(['deleted IS' => null, 'active' => 1])
            ->orderBy(['disp_cd' => 'ASC'])
            ->toArray();

        $callbackStatusOpt = ['0' => '未', '1' => '完了', '2' => '中止', '3' => '破棄', '4' => '登録誤り', '99' => 'その他'];

        $queueOpt = $this->MtQueues->find('list', keyField: 'item_name', valueField: 'item_name')
            ->where([
                'MtQueues.deleted IS' => null,
                'MtQueues.active_flg' => 1,
            ])
            ->orderBy(['MtQueues.disp_cd' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'activeOpt', 'sexOpt', 'opeOpt', 'allUsersOpt', 'callbackStatusOpt', 'queueOpt', 'statusOpt', 'overviewOpt'));
    }

    protected function contactSearchDefaults(): array
    {
        return [
            'customer_name' => '',
            'customer_phone' => '',
            'customer_email' => '',
            'customer_id' => '',
            'contact_id' => '',
            'client_name' => '',
            'client_branch_name' => '',
            'addr' => '',
            'detail' => '',
            'status' => '',
            'queue_name' => '',
            'created_user_id' => '',
            'is_done' => '',
            'phone' => '',
            'tel' => '',
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
            'client_cd' => '得意先CD',
            'client_branch_code' => '得意先支店CD',
            'id' => '履歴番号',
            'summary' => '概要',
            'queue_name' => 'キュー名',
            'detail' => '対応内容',
            'remark' => 'メモ',
            'status' => 'ステータス',
            'company_name' => '得意先名',
            'client_branch_name' => '得意先支店名',
            'customer_tel' => 'TEL',
            'tel2' => 'サブ電話番号1',
            'addr1' => '施主住所１',
            'addr2' => '施主住所２',
            'section_name' => '所属名',
            'sales_person' => '営業担当者名',
            'deadline' => '締日',
            'created_user' => '登録者',
            'created_date' => '登録日',
            'created_time' => '登録時間',
            'updated_user' => '更新者',
            'updated_date' => '更新日',
            'updated_time' => '更新時間',
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
