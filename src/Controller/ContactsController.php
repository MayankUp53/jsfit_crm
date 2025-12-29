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
    protected $MtOverview;

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
         $this->MtOverview = $this->fetchTable('MtOverview');
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

    /* =========================
     * Handle POST actions
     * ========================= */
    if ($this->request->is('post')) {

        // Search
        if ($this->request->getData('searching')) {
            $session->write('Contacts.search', $this->request->getData('Search', []));
            return $this->redirect(['action' => 'index']);
        }

        // Clear
        if ($this->request->getData('clear')) {
            return $this->clearSession();
        }

        // Add
        if ($this->request->getData('add')) {
            return $this->redirect(['action' => 'add']);
        }
    }

    /* =========================
     * Read search data
     * ========================= */
    $searchData = $session->read('Contacts.search') ?? [];
    $searchData += $this->contactSearchDefaults();

    /* =========================
     * Base Query
     * ========================= */
    $query = $this->Contacts->find()
        ->contain(['Customers'])
        ->where(['Contacts.deleted IS' => null]);

    /* =========================
     * Customer-based filters
     * ========================= */

    // Client CD
    if (!empty($searchData['client_cd']) && is_numeric($searchData['client_cd'])) {
        $query->where(['Customers.client_cd' => (int)$searchData['client_cd']]);
    }

    // Company name
    if (!empty($searchData['company_name'])) {
        $query->where([
            'Customers.company_name LIKE' => '%' . trim($searchData['company_name']) . '%'
        ]);
    }

    // Customer name
    if (!empty($searchData['name'])) {
        $query->where([
            'Customers.name LIKE' => '%' . trim($searchData['name']) . '%'
        ]);
    }

    // Type of number
    if (!empty($searchData['type_of_number'])) {
        $query->where([
            'Customers.type_of_number LIKE' => '%' . trim($searchData['type_of_number']) . '%'
        ]);
    }

    // Phone / Tel
    if (!empty($searchData['phone'])) {
        $query->where([
            'OR' => [
                'Customers.phone LIKE' => '%' . trim($searchData['phone']) . '%',
                'Customers.tel LIKE'   => '%' . trim($searchData['phone']) . '%',
            ],
        ]);
    }

    // ✅ Email (FIXED)
    if (!empty($searchData['email'])) {
        $email = trim($searchData['email']);
        $query->where([
            'LOWER(Customers.email) LIKE' => '%' . strtolower($email) . '%'
        ]);
    }

    // ✅ Fax (ADDED)
    if (!empty($searchData['fax'])) {
        $query->where([
            'Customers.fax LIKE' => '%' . trim($searchData['fax']) . '%'
        ]);
    }

    // Created date from
    if (!empty($searchData['created_from'])) {
        $query->where(['Customers.created >=' => $searchData['created_from']]);
    }

    // Created date to
    if (!empty($searchData['created_to'])) {
        $end = $searchData['created_to'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end .= ' 23:59:59';
        }
        $query->where(['Customers.created <=' => $end]);
    }

    /* =========================
     * Contact-based filters
     * ========================= */

    // Summary
    if (!empty($searchData['summary'])) {
        $query->where([
            'Contacts.summary LIKE' => '%' . trim($searchData['summary']) . '%'
        ]);
    }

    // Status
    if (!empty($searchData['status'])) {
        $query->where(['Contacts.status' => $searchData['status']]);
    }

    // Created user
    if (!empty($searchData['created_user_id'])) {
        $query->where(['Contacts.created_user_id' => $searchData['created_user_id']]);
    }

    /* =========================
     * Order
     * ========================= */
    $query->orderBy(['Contacts.created' => 'DESC']);
    /* =========================
     * CSV Download
     * ========================= */
if ($this->request->is('post') && $this->request->getData('download')) {
    $results = $query->all();

    $exportData = [];

    foreach ($results as $contact) {
        // Get OP name
        $user = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contact->updated_user_id])
            ->first();

        $contact->op_incharge = $user ? $user->user_name : '-';

        // Map data manually for CSV
        $exportData[] = [
            'id' => $contact->id,
            'client_cd' => $contact->customer->client_cd ?? '',
            'company_name' => $contact->customer->company_name ?? '',
            'customer_name' => $contact->customer->name ?? '',
            'customer_tel' => $contact->customer->tel ?? '',
            'customer_email' => $contact->customer->email ?? '',
            'customer_fax' => $contact->customer->fax ?? '',
            'summary' => $contact->summary,
            'status' => $contact->status,
            'remark' => $contact->remark,
            'op_incharge' => $contact->op_incharge,
            'created' => $contact->created ? $contact->created->format('Y-m-d H:i') : '',
            'updated' => $contact->updated ? $contact->updated->format('Y-m-d H:i') : '',
        ];
    }

    return $this->csv($exportData);
}



    $this->paginate = ['limit' => 50];
    $contacts = $this->paginate($query);

    foreach ($contacts as $contact) {
        $user = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contact->updated_user_id])
            ->first();

        $contact->op_incharge = $user ? $user->user_name : '-';
    }

    $this->set(compact('contacts', 'searchData'));
}

public function csv(array $results = [])
{
    $filename = rawurlencode('対応履歴情報_' . date('Y-m-d H:i:s') . '.csv');
    
    // Use column mapping for CSV headers
    $columns = [
        'id' => '履歴ID',
        'client_cd' => '得意先CD',
        'company_name' => '会社・支店名',
        'customer_name' => '顧客氏名',
        'customer_tel' => '顧客電話',
        'customer_email' => '顧客メール',
        'customer_fax' => 'FAX',
        'summary' => '概要',
        'status' => 'ステータス',
        'remark' => '対応内容',
        'op_incharge' => '担当OP',
        'created' => '登録日',
        'updated' => '更新日',
    ];

    $this->set(compact('filename', 'columns', 'results'));
    $this->viewBuilder()->setLayout('ajax');
    $this->render('download');
}





public function view(?string $id = null)
{
    if (empty($id)) {
        $this->Flash->error(__('無効なIDです。'));
        return $this->redirect(['action' => 'index']);
    }

    // Fetch the main contact and its associated customer
    $contact = $this->Contacts->find()
        ->where(['Contacts.id' => $id, 'Contacts.deleted IS' => null])
        ->contain(['Customers'])
        ->first();

    if (!$contact) {
        $this->Flash->error(__('指定された対応履歴が存在しません。'));
        return $this->redirect(['action' => 'index']);
    }

    // Convenience variable for the customer
    $customer = $contact->customer;

    // Fetch all contacts for the same customer (excluding deleted)
    $contacts = $this->Contacts->find()
        ->where([
            'Contacts.customer_id' => $contact->customer_id,
            'Contacts.deleted IS' => null
        ])
        ->orderBy(['Contacts.updated' => 'DESC'])
        ->contain(['Customers']) // optional, for customer info in list
        ->all();

    // Add dynamic field 'op_incharge' like in index/add methods
    foreach ($contacts as $contactItem) {
        $user = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contactItem->updated_user_id])
            ->first();

        $contactItem->op_incharge = $user ? $user->user_name : '-';
    }

    // Also add op_incharge for main contact
    $mainUser = $this->Users->find()
        ->select(['user_name'])
        ->where(['id' => $contact->updated_user_id])
        ->first();

    $contact->op_incharge = $mainUser ? $mainUser->user_name : '-';

    $this->set(compact('contact', 'customer', 'contacts'));
    $this->setListValue(); // keep for select options if needed in view
}

 public function add(?string $customer_id = null)
{
    // Check if customer ID is provided and exists
    if (empty($customer_id) || !$this->Customers->exists(['id' => $customer_id])) {
        $this->Flash->error(__('顧客を選択してから対応履歴を登録してください。'));
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }

    // Handle Back button
    if ($this->request->is('post') && $this->request->getData('back')) {
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }

    $contact = $this->Contacts->newEmptyEntity();
    $customer = $this->Customers->get($customer_id);
    $currentUser = $this->getCurrentUser();

         $statusOpt = $this->MtStatuses
    ->find('list', keyField: 'item_name', valueField: 'item_name')
    ->orderBy(['disp_cd' => 'ASC'])
    ->toArray();

     $overviewOpt = $this->MtOverview
    ->find('list', keyField: 'item_name', valueField: 'item_name')
    ->orderBy(['disp_cd' => 'ASC'])
    ->toArray();

    // Handle form submission
    if ($this->request->is('post')) {
        $data = $this->request->getData('Contact') ?: $this->request->getData();
        $now = date('Y-m-d H:i:s');

        // Set default fields
        $data['customer_id'] = $customer_id;
        $data['created'] = $now;
        $data['updated'] = $now;
        $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
        $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

        $contact = $this->Contacts->patchEntity($contact, $data);

        if ($this->Contacts->save($contact)) {
            
            $this->Flash->success(__('対応履歴情報の登録が完了しました。'));
            return $this->redirect(['action' => 'view', $contact->id]);
        }

        $this->Flash->error(__('対応履歴情報の登録に失敗しました。'));
    }

    // Fetch contacts for this customer
    $contacts = $this->Contacts->find()
        ->where([
            'Contacts.customer_id' => $customer_id,
            'Contacts.deleted IS' => null
        ])
        ->contain(['Customers'])
        ->orderBy(['Contacts.created' => 'DESC'])
        ->all();

    // Convert updated_user_id to user_name
    foreach ($contacts as $contactItem) {
        $user = $this->Users->find()
            ->select(['user_name'])
            ->where(['id' => $contactItem->updated_user_id])
            ->first();
        $contactItem->op_incharge = $user ? $user->user_name : '-';
    }

    $this->set(compact('contact', 'contacts', 'customer', 'statusOpt', 'overviewOpt'));
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


      $statusOpt = $this->MtStatuses
    ->find('list', keyField: 'item_name', valueField: 'item_name')
    ->orderBy(['disp_cd' => 'ASC'])
    ->toArray();

     $overviewOpt = $this->MtOverview
    ->find('list', keyField: 'item_name', valueField: 'item_name')
    ->orderBy(['disp_cd' => 'ASC'])
    ->toArray();
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
            // Optional callback handling
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

    $this->set(compact('contact', 'customer', 'statusOpt', 'overviewOpt'));
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
  


    $callbackStatusOpt = ['0' => '未', '1' => '完了', '2' => '中止', '3' => '破棄', '4' => '登録誤り', '99' => 'その他'];

    $queueOpt = $this->MtQueues->find('list', keyField: 'id', valueField: 'item_name')
        ->where(['MtQueues.active_flg' => 1]) // keep this if active_flg still exists in MtQueues
        ->orderBy(['MtQueues.disp_cd' => 'ASC'])
        ->toArray();

    $this->set(compact('warning', 'activeOpt', 'sexOpt', 'opeOpt', 'allUsersOpt', 'callbackStatusOpt', 'queueOpt',));
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
        'client_cd' => '得意先CD',
        'company_name' => '会社・支店名',
        'customer_name' => '顧客氏名',
        'customer_tel' => '顧客電話',
        'summary' => '概要',
        'status' => 'ステータス',
        'remark' => '対応内容',
        'op_incharge' => '担当OP',
        'created' => '登録日',
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
