<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

use function Cake\Error\debug;
use function Cake\Error\debug as ErrorDebug;

/**
 * Customers Controller
 *
 * @property \App\Model\Table\CustomersTable $Customers
 * @property \App\Model\Table\ContactsTable $Contacts
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\MtStatusesTable $MtStatuses
 */
class CustomersController extends AppController
{
    protected $Contacts;
    protected $Users;
    protected $MtStatuses;
    protected $MtCids;
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');

        // Load additional tables
        $this->Contacts = $this->fetchTable('Contacts');
        $this->Users = $this->fetchTable('Users');
        $this->MtStatuses = $this->fetchTable('MtStatuses');
        $this->MtCids = $this->fetchTable('MtCids');
    }

    /**
     * Clear session method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function clearSession()
    {
        $session = $this->request->getSession();
        $session->delete('Customers.search');

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function index()
    {
        $this->setListValue();
        $session = $this->request->getSession();

        // Handle POST actions (search, clear, add)
        if ($response = $this->handleCustomerPost($session)) {
            return $response;
        }

        //  Get search data from session and defaults
        $searchData = $this->getCustomerSearchData($session);

        // Build query
        $query = $this->buildCustomerQuery($searchData);

        //  CSV download
        if ($this->handleCustomerCsv($query)) {
            return;
        }

        //  Pagination
        $this->paginate = ['limit' => 50];
        $customers = $this->paginate($query);

        // Set variables for view
        $this->set(compact('customers', 'searchData'));
    }

    // ====================
    // Handle POST actions
    // ====================
    private function handleCustomerPost($session)
    {
        if (!$this->request->is('post')) return null;

        if ($this->request->getData('searching')) {
            $session->write('Customers.search', $this->request->getData('Search', []));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->getData('clear') && method_exists($this, 'clearSession')) {
            return $this->clearSession();
        }

        if ($this->request->getData('add')) {
            return $this->redirect(['action' => 'add']);
        }

        return null;
    }

    // ====================
    //  Get search data with defaults
    // ====================
    private function getCustomerSearchData($session): array
    {
        $searchData = $session->read('Customers.search') ?? [];
        return $searchData + $this->customerSearchDefaults();
    }

    // ====================
    //  Build query with filters
    // ====================
private function buildCustomerQuery(array $searchData)
{
    $query = $this->Customers->find()
        ->where(['Customers.deleted IS' => null]);

    // Filters
    if (!empty($searchData['name'])) {
        $query->where(['Customers.name LIKE' => '%' . trim($searchData['name']) . '%']);
    }

if (!empty($searchData['phone'])) {


    $phone = str_replace(['-', ' '], '', trim($searchData['phone']));

    $expr = "REPLACE(REPLACE(Customers.phone, '-', ''), ' ', '') LIKE :phone OR 
             REPLACE(REPLACE(Customers.tel, '-', ''), ' ', '') LIKE :phone";

    $query->where([$expr])
          ->bind(':phone', "%$phone%", 'string');

}

    if (!empty($searchData['email'])) {
        $query->where(['LOWER(Customers.email) LIKE' => '%' . strtolower(trim($searchData['email'])) . '%']);
    }

    if (!empty($searchData['fax'])) {
        $query->where(['Customers.fax LIKE' => '%' . trim($searchData['fax']) . '%']);
    }

    if (!empty($searchData['client_cd']) && is_numeric($searchData['client_cd'])) {
        $query->where(['Customers.client_cd' => (int)$searchData['client_cd']]);
    }

    if (!empty($searchData['company_name'])) {
        $query->where(['Customers.company_name LIKE' => '%' . trim($searchData['company_name']) . '%']);
    }

    if (!empty($searchData['type_of_number'])) {
        $query->where(['Customers.type_of_number IN' => $searchData['type_of_number']]);
    }

    // Created date range
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

    // Updated date range
    if (!empty($searchData['updated_from'])) {
        $query->where(['Customers.updated >=' => $searchData['updated_from']]);
    }
    if (!empty($searchData['updated_to'])) {
        $endUpdated = $searchData['updated_to'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endUpdated)) {
            $endUpdated .= ' 23:59:59';
        }
        $query->where(['Customers.updated <=' => $endUpdated]);
    }

    return $query->orderBy(['Customers.client_cd' => 'DESC']);
}



    // ====================
    //  Handle CSV download
    // ====================
    private function handleCustomerCsv($query): ?\Cake\Http\Response
    {
        if (!$this->request->is('post') || !$this->request->getData('download')) {
            return null;
        }

        $results = $query->all();
        if (method_exists($this, 'csv')) {
            return $this->csv($results->toArray());
        }

        return null;
    }

    /**
     * CSV export method
     *
     * @param \Cake\Datasource\ResultSetInterface $results Results to export
     * @return \Cake\Http\Response|null|void
     */
    public function csv($results = null)
    {
        $filename = rawurlencode('顧客情報_' . date('Y-m-d H:i:s') . '.csv');
        $columns = $this->customerExportColumns();
        $sexLabels = $this->sexOptions();

        $this->set(compact('filename', 'columns', 'results', 'sexLabels'));
        $this->viewBuilder()->setLayout('ajax');
        $this->render('download');
    }

    /**
     * Jump method - search by phone number
     *
     * @param string|null $phone Phone number
     * @return \Cake\Http\Response|null|void
     */
    public function jump(?string $phone = null)
    {
        if (empty($phone)) {
            return $this->redirect(['action' => 'index']);
        }

        $this->setListValue();

        // Search by phone number
        $results = $this->Customers->find()
            ->where([
                'Customers.deleted IS' => null,
                'OR' => [
                    'Customers.tel_only' => $phone,
                    'Customers.phone_only' => $phone,
                    'Customers.tel' => $phone,
                    'Customers.phone' => $phone,
                ],
            ])
            ->toArray();

        $this->set('phone', $phone);

        // If no results, auto-add
        if (count($results) == 0) {
            $this->autoAdd($phone);
            $results = $this->Customers->find()
                ->where([
                    'Customers.deleted IS' => null,
                    'OR' => [
                        'Customers.tel_only' => $phone,
                        'Customers.phone_only' => $phone,
                        'Customers.tel' => $phone,
                        'Customers.phone' => $phone,
                    ],
                ])
                ->toArray();
        }

        // If one result, redirect to contact add
        if (count($results) == 1) {
            $id = $results[0]->id;
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
        }

        // If multiple results, show search results
        if (count($results) > 1) {
            $query = $this->Customers->find()
                ->where([
                    'Customers.deleted IS' => null,
                    'OR' => [
                        'Customers.tel_only LIKE' => "%{$phone}%",
                        'Customers.phone_only LIKE' => "%{$phone}%",
                        'Customers.tel LIKE' => "%{$phone}%",
                        'Customers.phone LIKE' => "%{$phone}%",
                    ],
                ]);

            $this->paginate = ['limit' => 50];
            $customers = $this->paginate($query);
            $this->set(compact('customers'));
            $this->render('index');
            return;
        }

        // No results
        $this->Flash->error(__('顧客情報が見つかりませんでした。電話番号：' . $phone));
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Auto-add customer by phone number
     *
     * @param string|null $phone Phone number
     * @return int Customer ID
     */
    protected function autoAdd(?string $phone = null): int
    {
        $latestId = 0;
        if (empty($phone)) {
            return $latestId;
        }

        $customer = $this->Customers->newEmptyEntity();
        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        // Generate disp_no with milliseconds
        $a = explode('.', (string)microtime(true));
        $dispNo = date('ymdHis') . substr($a[1] ?? '000', 0, 3);

        $customer->created = $now;
        $customer->created_user_id = $currentUser ? $currentUser->id : 0;
        $customer->updated = $now;
        $customer->updated_user_id = $currentUser ? $currentUser->id : 0;
        $customer->disp_no = $dispNo;
        $customer->client_cd = 'ameyo_' . bin2hex(random_bytes(8));
        $customer->tel = $phone;
        $customer->zip_cd = '';
        $customer->addr1 = '';

        if ($this->Customers->save($customer)) {
            $latestId = $customer->id;
        }

        return $latestId;
    }

    /**
     * Ameyo method
     *
     * @param string|null $campaign Campaign ID
     * @param string|null $sessionId Session ID
     * @param string|null $queueId Queue ID
     * @param string|null $queueName Queue name
     * @param string|null $phone Phone number
     * @param string|null $dstPhone Destination phone
     * @param string|null $customerId Customer ID
     * @return \Cake\Http\Response|null|void
     */
    public function ameyo(
        ?string $campaign = null,
        ?string $sessionId = null,
        ?string $queueId = null,
        ?string $queueName = null,
        ?string $phone = null,
        ?string $dstPhone = null,
        ?string $customerId = null,
        ?string $crtObjectId = null
    ) {
        $session = $this->request->getSession();

       // Clear session
        $session->delete('campaign');
        $session->delete('sessionId');
        $session->delete('queueId');
        $session->delete('queueName');
        $session->delete('phone');
        $session->delete('dstPhone');
        $session->delete('customerId');
        $session->delete('crtObjectId');

        // Set session values
        $session->write([
            'campaign' => $campaign,
            'sessionId' => $sessionId,
            'queueId' => $queueId,
            'queueName' => $queueName,
            'phone' => $phone,
            'dstPhone' => $dstPhone,
            'customerId' => $customerId,
            'crtObjectId' => $crtObjectId,
        ]);

        // Handle unknown/anonymous phone numbers
        if ($phone == 'unknown' || $phone == 'anonymous') {
            $id = $this->autoAdd($phone);
            $this->Flash->success(__('新規顧客（非通知の電話番号）の自動登録を行いました。:' . $id));
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
        }
 $phone = str_replace(['-', ' ', '+'], '', trim($phone));

$expr = "
    REPLACE(REPLACE(REPLACE(Customers.tel_only, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.phone_only, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.tel, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.phone, '-', ''), ' ', ''), '+', '') LIKE :phone
";

$results = $this->Customers->find()
    ->where([
        'Customers.deleted IS' => null,
        $expr
    ])
    ->bind(':phone', "%$phone%", 'string')
    ->toArray();

        // If phone doesn't exist, auto-add
        if (count($results) == 0) {
            $id = $this->autoAdd($phone);
            $this->Flash->success(__('新規顧客（未登録の電話番号）の自動登録を行いました。:' . $id));
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
        }

        if (empty($phone)) {
            return $this->redirect(['action' => 'index']);
        }

        $this->setListValue();
        $this->set('phone', $phone);

        // If one result, redirect to contact add
        if (count($results) == 1) {
            $id = $results[0]->id;
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
        }

        // If multiple results, show search results
        if (count($results) > 1) {


 $query = $this->Customers->find()
    ->where([
        'Customers.deleted IS' => null,
        $expr
    ])
    ->bind(':phone', "%$phone%", 'string');

            $this->paginate = ['limit' => 50];
            $customers = $this->paginate($query);
            $this->set(compact('customers'));
            $this->render('index');
            return;
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * View method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $customer = $this->Customers->find()
            ->where(['Customers.id' => $id, 'Customers.deleted IS' => null])
            ->firstOrFail();

        // Get related contacts
        $contacts = $this->Contacts->find()
            ->where([
                'Contacts.customer_id' => $id,
                'Contacts.deleted IS' => null,
            ])
            ->orderBy(['Contacts.updated' => 'DESC'])
            ->toArray();

        $this->set(compact('customer', 'contacts'));
        $this->setListValue();
    }


    public function add()
    {
        $customer = $this->Customers->newEmptyEntity();

        if ($this->request->is('post')) {

            // 🔙 Back / Cancel button
            if ($this->request->getData('back')) {
                return $this->redirect(['action' => 'index']);
            }

            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            // Audit fields
            $data['created'] = $now;
            $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;


            $customer = $this->Customers->patchEntity($customer, $data);

            if ($this->Customers->save($customer)) {
                $this->Flash->success(__('顧客情報の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }

            $errorMsg = $this->formatEntityErrors($customer);
            $this->Flash->error(__('顧客情報の登録に失敗しました。 {0}', $errorMsg));
        }

        $this->set(compact('customer'));
        $this->setListValue();
    }


    /**
     * Edit method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
public function edit(?string $id = null)
{
    // Fetch customer
    $customer = $this->Customers->find()
        ->where(['Customers.id' => $id, 'Customers.deleted IS' => null])
        ->firstOrFail();

    if ($this->request->is(['post', 'patch', 'put'])) {

        // CANCEL button
        if ($this->request->getData('back')) {
            return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
        }

        // DELETE button
        if ($this->request->getData('delete')) {
            return $this->redirect(['action' => 'delete', $customer->id]);
        }

        // UPDATE button
        if ($this->request->getData('add')) {

            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            // ===== Custom Email Validation =====
            if (!empty($data['email'])) {

                // Split emails by ; or ,
                $emails = preg_split('/[;,]+/', $data['email']);
                $invalidEmails = [];

                foreach ($emails as $email) {
                    $email = trim($email);

                    // Only validate non-empty emails
                    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalidEmails[] = [
                            'email' => $email,
                            'reason' => 'メール形式が正しくありません。例: example@domain.com'
                        ];
                    }
                }

                if (!empty($invalidEmails)) {
                    $messages = [];
                    foreach ($invalidEmails as $err) {
                        $messages[] = "{$err['email']} → {$err['reason']}";
                    }

                    $this->Flash->error(
                        __('メールアドレスのエラーがあります。複数メールの場合は ; または , で区切ってください。') . "<br>" . implode("<br>", $messages)
                    );
                    $this->set(compact('customer'));
                    return;
                }
            }
            // ===== End Email Validation =====

            // Patch and save
            $customer = $this->Customers->patchEntity($customer, $data);

            if ($this->Customers->save($customer)) {
                $this->Flash->success(__('顧客情報の更新が完了しました。'));
                return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
            }

            $errorMsg = $this->formatEntityErrors($customer);
            $this->Flash->error(__('顧客情報の更新に失敗しました。 {0}', $errorMsg));
        }
    }

    $this->set(compact('customer'));
}
    /**
     * Delete method (logical delete)
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {

        $this->request->allowMethod(['post', 'delete', 'get']);
        $customer = $this->Customers->find()
            ->where(['Customers.id' => $id])
            ->firstOrFail();

        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        $customer->deleted = $now;
        $customer->deleted_user_id = $currentUser ? $currentUser->id : 0;

        if ($this->Customers->save($customer, ['fields' => ['deleted', 'deleted_user_id']])) {
            // Delete related contacts (logical delete)
            $affected = $this->Contacts->updateAll(
                [
                    'deleted' => $now,
                    'deleted_user_id' => $currentUser ? $currentUser->id : 0,
                ],
                ['customer_id' => $id]
            );

            $this->Flash->success(__('顧客情報と' . $affected . '件の対応履歴の削除が完了しました。'));
        } else {
            $this->Flash->error(__('顧客情報の削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $activeOpt = ['0' => '無効', '1' => '有効'];
        $sexOpt = $this->sexOptions();

        // Operators (users)
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['Users.deleted IS' => null])
            ->orderBy(['Users.disp_cd' => 'ASC'])
            ->toArray();

        // All users (including deleted)
        $allUsersOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->orderBy(['Users.deleted' => 'ASC', 'Users.disp_cd' => 'ASC'])
            ->toArray();

        // Status options (removed active_flg filter)
        $statusOpt = $this->MtStatuses->find('list', keyField: 'id', valueField: 'item_name')
            ->orderBy(['MtStatuses.disp_cd' => 'ASC'])
            ->toArray();

 $session = $this->request->getSession();

        $cidOpt = $this->MtCids
            ->find('list', keyField: 'cid', valueField: 'item_name')
            ->where([
                'deleted IS' => null,
               'campaign_id IS' => $session->read('campaign'),
                     // 'campaign_id IS' => 1,
            ])
            ->orderBy(['disp_cd' => 'ASC'])
            ->toArray();

      //  $cidOpt =[1=>'着信専用','2'=>'発信専用','3'=>'着信・発信両用'];

        $this->set(compact('warning', 'activeOpt', 'sexOpt', 'opeOpt', 'allUsersOpt', 'statusOpt', 'cidOpt'));
    }

    protected function customerSearchDefaults(): array
    {
        return [
            'client_cd' => '',
            'company_name' => '',
            'name'      => '',
            'type_of_number' => '',
            'tel'       => '',
            'email'     => '',
            'fax'       => '',
            'created'   => '',
        ];
    }

    protected function customerExportColumns(): array

    {
        return [
            'client_cd' => '得意先CD',
            'company_name' => '会社・支店名',
            'name'      => '氏名',
            'type_of_number' => '番号種別',
            'tel'       => '携帯番号',
            'email'     => 'メール',
            'fax'       => 'FAX',
            'created'   => '登録日',
            'updated'   => '更新日',
        ];
    }

    protected function normalizeCustomerPayload(array $data, bool $isCreate = true): array
    {
        if (empty($data['disp_no'])) {
            $data['disp_no'] = $this->generateDisplayNumber();
        }

        $data['zip_cd'] = $data['zip_cd'] ?? '';
        $data['addr1'] = $data['addr1'] ?? '';

        foreach (['addr2', 'addr3', 'name', 'name_kj', 'name_kn', 'email', 'tel_only', 'tel', 'phone_only', 'phone', 'fax', 'remark', 'ex1', 'ex2', 'ex3'] as $field) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    protected function generateDisplayNumber(): string
    {
        $a = explode('.', (string)microtime(true));
        return date('ymdHis') . str_pad(substr($a[1] ?? '000', 0, 3), 3, '0');
    }

    protected function sexOptions(): array
    {
        return ['1' => '男性', '2' => '女性'];
    }
}
