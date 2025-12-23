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

    // Fetch contacts with associated customer data
    $query = $this->Contacts->find()
        ->contain(['Customers'])
        ->where(['Contacts.deleted IS' => null]);

    // --- Filter by Customer fields ---
    if (!empty($searchData['client_cd']) && is_numeric($searchData['client_cd'])) {
        $query->where(['Customers.client_cd' => (int)$searchData['client_cd']]);
    }
    if (!empty($searchData['company_name'])) {
        $query->where(['Customers.company_name LIKE' => '%' . $searchData['company_name'] . '%']);
    }
    if (!empty($searchData['name'])) {
        $query->where(['Customers.name LIKE' => '%' . $searchData['name'] . '%']);
    }
    if (!empty($searchData['type_of_number'])) {
        $query->where(['Customers.type_of_number LIKE' => '%' . $searchData['type_of_number'] . '%']);
    }
    if (!empty($searchData['phone'])) {
        $query->where([
            'OR' => [
                'Customers.phone LIKE' => '%' . $searchData['phone'] . '%',
                'Customers.tel LIKE' => '%' . $searchData['phone'] . '%',
            ],
        ]);
    }
    if (!empty($searchData['created_from'])) {
        $query->where(['Customers.created >=' => $searchData['created_from']]);
    }
    if (!empty($searchData['created_to'])) {
        $end = $searchData['created_to'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end .= ' 23:59:59';
        }
        $query->where(['Customers.created <=' => $end]);
    }

    // --- Filter by Contact fields ---
    if (!empty($searchData['summary'])) {
        $query->where(['Contacts.summary LIKE' => '%' . $searchData['summary'] . '%']);
    }
    if (!empty($searchData['status'])) {
        $query->where(['Contacts.status' => $searchData['status']]);
    }
    if (!empty($searchData['created_user_id'])) {
        $query->where(['Contacts.created_user_id' => $searchData['created_user_id']]);
    }

    // Order by contact creation date
    $query->order(['Contacts.created' => 'DESC']);

    // CSV download
    if ($this->request->is('post') && $this->request->getData('download')) {
        $results = $query->all();

        // Add op_incharge field for CSV
        foreach ($results as $contact) {
            $user = $this->Users->find()
                ->select(['name'])
                ->where(['id' => $contact->updated_user_id])
                ->first();
            $contact->op_incharge = $user ? $user->name : '-';
        }

        return $this->csv($results->toArray());
    }

    // Pagination
    $this->paginate = ['limit' => 50];
    $contacts = $this->paginate($query);

    // Add op_incharge field for each contact
    foreach ($contacts as $contact) {
        $user = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contact->updated_user_id])
            ->first();
        $contact->op_incharge = $user ? $user->user_name : '-';
    }

    $this->set(compact('contacts', 'searchData'));
}


//     public function index()
// {
//     $this->setListValue();
//     $session = $this->request->getSession();

//     if ($this->request->is('post')) {
//         if ($this->request->getData('searching')) {
//             $session->write('Contacts.search', $this->request->getData('Search', []));
//             return $this->redirect(['action' => 'index']);
//         }
//         if ($this->request->getData('clear')) {
//             return $this->clearSession();
//         }
//         if ($this->request->getData('add')) {
//             return $this->redirect(['action' => 'add']);
//         }
//     }

//     $searchData = $session->read('Contacts.search') ?? [];
//     $searchData += $this->contactSearchDefaults();

//     // Fetch contacts with associated customer data
//     $query = $this->Contacts->find()
//         ->contain(['Customers'])
//         ->where(['Contacts.deleted IS' => null]);

//     // --- Filter by Customer fields ---
//     if (!empty($searchData['client_cd']) && is_numeric($searchData['client_cd'])) {
//         $query->where(['Customers.client_cd' => (int)$searchData['client_cd']]);
//     }
//     if (!empty($searchData['company_name'])) {
//         $query->where(['Customers.company_name LIKE' => '%' . $searchData['company_name'] . '%']);
//     }
//     if (!empty($searchData['name'])) {
//         $query->where(['Customers.name LIKE' => '%' . $searchData['name'] . '%']);
//     }
//     if (!empty($searchData['type_of_number'])) {
//         $query->where(['Customers.type_of_number LIKE' => '%' . $searchData['type_of_number'] . '%']);
//     }
//     if (!empty($searchData['phone'])) {
//         $query->where([
//             'OR' => [
//                 'Customers.phone LIKE' => '%' . $searchData['phone'] . '%',
//                 'Customers.tel LIKE' => '%' . $searchData['phone'] . '%',
//             ],
//         ]);
//     }
//     if (!empty($searchData['created_from'])) {
//         $query->where(['Customers.created >=' => $searchData['created_from']]);
//     }
//     if (!empty($searchData['created_to'])) {
//         $end = $searchData['created_to'];
//         if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
//             $end .= ' 23:59:59';
//         }
//         $query->where(['Customers.created <=' => $end]);
//     }

//     // --- Filter by Contact fields ---
//     if (!empty($searchData['summary'])) {
//         $query->where(['Contacts.summary LIKE' => '%' . $searchData['summary'] . '%']);
//     }
//     if (!empty($searchData['status'])) {
//         $query->where(['Contacts.status' => $searchData['status']]);
//     }
//     if (!empty($searchData['created_user_id'])) {
//         $query->where(['Contacts.created_user_id' => $searchData['created_user_id']]);
//     }

//     // Order by contact creation date
//     $query->orderBy(['Contacts.created' => 'DESC']);

//     // CSV download
//     if ($this->request->is('post') && $this->request->getData('download')) {
//         $results = $query->all();
//         return $this->csv($results->toArray());
//     }

//     $this->paginate = ['limit' => 50];
//     $contacts = $this->paginate($query);

//     $this->set(compact('contacts', 'searchData'));
// }



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
    // Fetch the main contact and its associated customer
    $contact = $this->Contacts->find()
        ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
        ->contain(['Customers'])
        ->firstOrFail();

    // Convenience variable for the customer
    $customer = $contact->customer;

    // Fetch all contacts for the same customer (excluding deleted)
    $contacts = $this->Contacts->find()
        ->where(['Contacts.customer_id' => $contact->customer_id, 'Contacts.deleted IS' => null])
        ->orderBy(['Contacts.updated' => 'DESC'])
        ->contain(['Customers']) // optional, if you need customer info in the list
        ->toArray();

    $this->set(compact('contact', 'customer', 'contacts'));
    $this->setListValue(); // keep for select options if needed in view
}


public function add(?string $customer_id = null)
{
    if (empty($customer_id) || !$this->Customers->exists(['id' => $customer_id])) {
        $this->Flash->error(__('顧客を選択してから対応履歴を登録してください。'));
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }

    // Back button
    if ($this->request->is('post') && $this->request->getData('back')) {
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }

    $contact  = $this->Contacts->newEmptyEntity();
    $customer = $this->Customers->get($customer_id);
    $currentUser = $this->getCurrentUser();

    if ($this->request->is('post')) {
        $data = $this->request->getData('Contact') ?: $this->request->getData();

        $now = date('Y-m-d H:i:s');

        $data['customer_id'] = $customer_id;
        $data['created'] = $now;
        $data['updated'] = $now;
        $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
        $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;
    

        $contact = $this->Contacts->patchEntity($contact, $data);

        if ($this->Contacts->save($contact)) {
            $this->Flash->success(__('対応履歴情報の登録が完了しました。'));
            return $this->redirect(['action' => 'view',    $contact->id]);
        }

        $this->Flash->error(__('対応履歴情報の登録に失敗しました。'));
    }

    
    $contacts = $this->Contacts->find()
        ->where([
            'Contacts.customer_id' => $customer_id,
            'Contacts.deleted IS' => null
        ])
        ->contain(['Customers'])
        ->order(['Contacts.created' => 'DESC'])
        ->all();

    $this->set(compact('contact', 'contacts', 'customer'));
}


 
public function edit(?string $id = null)
{
    // Handle delete button
    if ($this->request->is('post') && $this->request->getData('delete')) {
        return $this->redirect(['action' => 'delete', $id]);
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
        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        $data['updated'] = $now;
        $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;
       
    
    

        // Patch entity directly without custom field normalization
        $contact = $this->Contacts->patchEntity($contact, $data);

        if ($this->Contacts->save($contact)) {
            // Optional callback handling if needed
            if (!empty($data['phone']) && !empty($data['callback_datetime'])) {
                $this->callbackEdit($contact->customer_id, (int)$contact->id, $data);
            }

            // Optional DNC API call
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
