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
        $query = $this->Customers->find()->where(['Customers.deleted IS' => null]);
        foreach ([
            'client_code', 'client_branch_code', 'client_name', 'client_branch_name',
            'section_name', 'sales_person', 'incharge1_name', 'incharge1_phone',
            'incharge2_name', 'incharge2_phone',
        ] as $field) {
            $value = trim((string)($searchData[$field] ?? ''));
            if ($value !== '') {
                $query->where(
                    ['Customers.' . $field . ' LIKE' => '%' . $value . '%'],
                    ['Customers.' . $field => 'string']
                );
            }
        }
        $tel = trim((string)($searchData['tel_only'] ?? ''));
        if ($tel !== '') {
            $query->where(['OR' => ['Customers.tel' => $tel, 'Customers.tel2' => $tel]]);
        }
        $address = trim((string)($searchData['addr1'] ?? ''));
        if ($address !== '') {
            $query->where(['OR' => [
                'Customers.addr1 LIKE' => '%' . $address . '%',
                'Customers.addr2 LIKE' => '%' . $address . '%',
                'Customers.addr3 LIKE' => '%' . $address . '%',
            ]]);
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
        return $query->orderBy(['Customers.created' => 'DESC', 'Customers.id' => 'DESC']);
    }

    // ====================
    //  Handle CSV download
    // ====================
    private function handleCustomerCsv($query): bool
    {
        if (!$this->request->is('post') || !$this->request->getData('download')) {
            return false;
        }

        $results = $query->all();
        if (method_exists($this, 'csv')) {
            $exportRows = [];
            foreach ($results as $customer) {
                $createdUser = $this->Users->find()
                    ->select(['user_name'])
                    ->where(['id' => $customer->created_user_id])
                    ->first();
                $updatedUser = $this->Users->find()
                    ->select(['user_name'])
                    ->where(['id' => $customer->updated_user_id])
                    ->first();
                $exportRows[] = [
                    'client_cd' => $customer->client_code ?? $customer->client_cd ?? '',
                    'client_branch_code' => $customer->client_branch_code ?? '',
                    'client_name' => $customer->client_name ?? $customer->company_name ?? $customer->name ?? '',
                    'client_branch_name' => $customer->client_branch_name ?? $customer->company_branch_name ?? '',
                    'tel' => $customer->tel ?? '',
                    'tel2' => $customer->tel2 ?? '',
                    'addr1' => $customer->addr1 ?? '',
                    'addr2' => $customer->addr2 ?? '',
                    'addr3' => $customer->addr3 ?? '',
                    'section_name' => $customer->section_name ?? '',
                    'sales_person' => $customer->sales_person ?? '',
                    'deadline' => $customer->deadline ?? '',
                    'incharge1_name' => $customer->incharge1_name ?? '',
                    'incharge1_phone' => $customer->incharge1_phone ?? '',
                    'incharge2_name' => $customer->incharge2_name ?? '',
                    'incharge2_phone' => $customer->incharge2_phone ?? '',
                    'incharge3_name' => $customer->incharge3_name ?? '',
                    'incharge3_phone' => $customer->incharge3_phone ?? '',
                    'incharge4_name' => $customer->incharge4_name ?? '',
                    'incharge4_phone' => $customer->incharge4_phone ?? '',
                    'notes1' => $customer->notes1 ?? '',
                    'notes2' => $customer->notes2 ?? '',
                    'notes3' => $customer->notes3 ?? '',
                    'notes4' => $customer->notes4 ?? '',
                    'created_user' => $createdUser?->user_name ?? '',
                    'created' => $customer->created?->format('Y-m-d H:i:s') ?? '',
                    'updated_user' => $updatedUser?->user_name ?? '',
                    'updated' => $customer->updated?->format('Y-m-d H:i:s') ?? '',
                ];
            }
            $this->csv($exportRows);
            return true;
        }

        return false;
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
                    'Customers.tel2' => $phone,
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
                        'Customers.tel2' => $phone,
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
                        'Customers.tel2 LIKE' => "%{$phone}%",
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
            if ($id <= 0) {
                $this->Flash->error(__('顧客の自動登録に失敗しました。もう一度操作してください。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->success(__('新規顧客（非通知の電話番号）の自動登録を行いました。:' . $id));
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
        }
        $phone = str_replace(['-', ' ', '+'], '', trim((string)$phone));
        if ($phone === '') {
            return $this->redirect(['action' => 'index']);
        }

$expr = "
    REPLACE(REPLACE(REPLACE(Customers.tel_only, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.phone_only, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.tel, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.phone, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.tel2, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.incharge1_phone, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.incharge2_phone, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.incharge3_phone, '-', ''), ' ', ''), '+', '') LIKE :phone OR
    REPLACE(REPLACE(REPLACE(Customers.incharge4_phone, '-', ''), ' ', ''), '+', '') LIKE :phone
";

$results = $this->Customers->find()
    ->where([
        'Customers.deleted IS' => null,
        $expr
    ])
    ->bind(':phone', "%$phone", 'string')
    ->toArray();

        // If phone doesn't exist, auto-add
        if (count($results) == 0) {
            $id = $this->autoAdd($phone);
            if ($id <= 0) {
                $this->Flash->error(__('顧客の自動登録に失敗しました。もう一度操作してください。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->success(__('新規顧客（未登録の電話番号）の自動登録を行いました。:' . $id));
            return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $id]);
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
    ->bind(':phone', "%$phone", 'string');

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
            ->orderBy([
                'Contacts.created' => 'DESC',
                'Contacts.updated' => 'DESC',
                'Contacts.id' => 'DESC',
            ])
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

            $data = $this->customerFormData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            // Audit fields
            $data['created'] = $now;
            $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;


            $customer = $this->Customers->patchEntity($customer, $data, ['validate' => 'customerForm']);

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
        $customer = $this->Customers->find()
            ->where(['Customers.id' => $id, 'Customers.deleted IS' => null])
            ->firstOrFail();

        if ($this->request->is(['post', 'patch', 'put'])) {
            if ($this->request->getData('back')) {
                return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $customer->id]);
            }
            if ($this->request->getData('delete')) {
                return $this->delete((string)$customer->id);
            }

            $data = $this->customerFormData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;
            $customer = $this->Customers->patchEntity($customer, $data, ['validate' => 'customerForm']);
            if ($this->Customers->save($customer)) {
                $this->Flash->success(__('顧客情報の更新が完了しました。'));
                return $this->redirect(['controller' => 'Contacts', 'action' => 'add', $customer->id]);
            }
            $this->Flash->error(__('顧客情報の更新に失敗しました。 {0}', $this->formatEntityErrors($customer)));
        }

        $this->set(compact('customer'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'patch', 'put', 'delete']);
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || (string)$currentUser->auth === '0') {
            throw new \Cake\Http\Exception\ForbiddenException('顧客情報を削除する権限がありません。');
        }
        $customer = $this->Customers->find()
            ->where(['Customers.id' => $id, 'Customers.deleted IS' => null])
            ->firstOrFail();

        $now = date('Y-m-d H:i:s');
        $affected = 0;
        $saved = $this->Customers->getConnection()->transactional(function () use ($customer, $currentUser, $now, &$affected) {
            $customer->deleted = $now;
            $customer->deleted_user_id = $currentUser->id;
            if (!$this->Customers->save($customer)) {
                return false;
            }
            $affected = $this->Contacts->updateAll(
                ['deleted' => $now, 'deleted_user_id' => $currentUser->id],
                ['customer_id' => $customer->id]
            );
            return true;
        });

        if ($saved) {
            $this->Flash->success(__('顧客情報と{0}件の対応履歴の削除が完了しました。', $affected));
        } else {
            $this->Flash->error(__('顧客情報の削除に失敗しました。もう一度操作してください。'));
        }
        return $this->redirect(['action' => 'index']);
    }

    private function customerFormData(): array
    {
        $fields = [
            'client_code', 'client_branch_code', 'client_name', 'client_branch_name',
            'addr1', 'addr2', 'addr3', 'tel', 'fax', 'tel2', 'section_name', 'sales_person', 'deadline',
            'incharge1_name', 'incharge1_phone', 'incharge2_name', 'incharge2_phone',
            'incharge3_name', 'incharge3_phone', 'incharge4_name', 'incharge4_phone',
            'notes1', 'notes2', 'notes3', 'notes4',
        ];
        $data = array_intersect_key($this->request->getData(), array_flip($fields));
        if (array_key_exists('client_code', $data)) {
            $data['client_cd'] = (string)$data['client_code'];
        }
        if (array_key_exists('tel', $data)) {
            $data['tel_only'] = preg_replace('/[- －]+/u', '', (string)$data['tel']);
        }
        return $data;
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
            'tel_only' => '',
            'client_name' => '',
            'client_branch_name' => '',
            'addr1' => '',
            'client_code' => '',
            'client_branch_code' => '',
            'section_name' => '',
            'sales_person' => '',
            'incharge1_name' => '',
            'incharge1_phone' => '',
            'incharge2_name' => '',
            'incharge2_phone' => '',
            'email'     => '',
            'fax'       => '',
            'created'   => '',
        ];
    }

    protected function customerExportColumns(): array

    {
        return [
            'client_cd' => '得意先CD',
            'client_branch_code' => '得意先支店CD',
            'client_name' => '得意先名',
            'client_branch_name' => '得意先支店名',
            'tel' => 'TEL',
            'tel2' => 'サブ電話番号1',
            'addr1' => '住所1',
            'addr2' => '住所2',
            'addr3' => '住所3',
            'section_name' => '所属名',
            'sales_person' => '営業担当者名',
            'deadline' => '締日',
            'incharge1_name' => '顧客の担当者1',
            'incharge1_phone' => '顧客の担当者1電話',
            'incharge2_name' => '顧客の担当者2',
            'incharge2_phone' => '顧客の担当者2電話',
            'incharge3_name' => '顧客の担当者3',
            'incharge3_phone' => '顧客の担当者3電話',
            'incharge4_name' => '顧客の担当者4',
            'incharge4_phone' => '顧客の担当者4電話',
            'notes1' => '備考1',
            'notes2' => '備考2',
            'notes3' => '備考3',
            'notes4' => '備考4',
            'created_user' => '登録者',
            'created'   => '登録日',
            'updated_user' => '更新者',
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
