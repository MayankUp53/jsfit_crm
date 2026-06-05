<?php
declare(strict_types=1);

namespace App\Controller;

class SmsHistoriesController extends AppController
{
    protected $SmsHistories;
    protected $Users;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('App.CallbackNotification');

        $this->SmsHistories = $this->fetchTable('SmsHistories');
        $this->Users = $this->fetchTable('Users');
    }

    public function clearSession()
    {
        $this->request->getSession()->delete('SmsHistories.search');

        return $this->redirect(['action' => 'index']);
    }

    public function index()
    {
        $this->setListValue();

        if ($this->request->is('post')) {
            if ($this->request->getData('searching')) {
                $this->request->getSession()->write('SmsHistories.search', $this->request->getData('Search', []));
                return $this->redirect(['action' => 'index']);
            }

            if ($this->request->getData('clear')) {
                return $this->clearSession();
            }
        }

        $searchData = $this->request->getSession()->read('SmsHistories.search') ?? [];
        $searchData += $this->searchDefaults();

        $query = $this->SmsHistories->find()
            ->contain(['Customers'])
            ->where(['SmsHistories.deleted IS' => null])
            ->orderBy([
                'SmsHistories.created' => 'DESC',
                'SmsHistories.updated' => 'DESC',
                'SmsHistories.id' => 'DESC',
            ]);

        $this->applySearchFilters($query, $searchData);

        if ($this->handleCsvDownload($query)) {
            return;
        }

        $this->paginate = ['limit' => 50];
        $smsHistories = $this->paginate($query);

        $this->set(compact('smsHistories', 'searchData'));
    }

    private function handleCsvDownload($query): bool
    {
        if (!$this->request->is('post') || !$this->request->getData('download')) {
            return false;
        }

        $smsStatusOpt = [
            'success' => '成功',
            'failed' => '失敗',
        ];
        $exportData = [];

        foreach ($query->all() as $smsHistory) {
            $exportData[] = [
                'id' => $smsHistory->id,
                'client_cd' => $smsHistory->customer->client_cd ?? '',
                'company_name' => $smsHistory->customer->company_name ?? '',
                'customer_name' => $smsHistory->customer->name ?? '',
                'type_of_number' => $smsHistory->customer->type_of_number ?? '',
                'customer_tel' => $smsHistory->customer->tel ?? '',
                'customer_fax' => $smsHistory->customer->fax ?? '',
                'customer_email' => $smsHistory->customer->email ?? '',
                'phone' => $smsHistory->phone,
                'send_sms_number' => $smsHistory->send_sms_number,
                'sms_text' => $smsHistory->sms_text,
                'status' => $smsStatusOpt[$smsHistory->status] ?? $smsHistory->status,
                'created' => $smsHistory->created ? $smsHistory->created->format('Y-m-d H:i:s') : '',
                'updated' => $smsHistory->updated ? $smsHistory->updated->format('Y-m-d H:i:s') : '',
            ];
        }

        $this->csv($exportData);
        return true;
    }

    public function csv(array $results = []): void
    {
        $filename = rawurlencode('SMS送信履歴_' . date('Y-m-d H:i:s') . '.csv');
        $columns = [
            'id' => 'SMS履歴ID',
            'client_cd' => '得意先CD',
            'company_name' => '会社・支店名',
            'customer_name' => '氏名',
            'type_of_number' => '番号種別',
            'customer_tel' => 'TEL',
            'customer_fax' => 'FAX',
            'customer_email' => 'メール',
            'phone' => 'SMS送信先',
            'send_sms_number' => '送信元',
            'sms_text' => 'メッセージ',
            'status' => 'ステータス',
            'created' => '送信日',
            'updated' => '更新日',
        ];

        $this->set(compact('filename', 'columns', 'results'));
        $this->viewBuilder()->setLayout('ajax');
        $this->render('download');
    }

    public function view(?string $id = null)
    {
        $smsHistory = $this->SmsHistories->find()
            ->where([
                'SmsHistories.id' => $id,
                'SmsHistories.deleted IS' => null,
            ])
            ->contain(['Customers'])
            ->firstOrFail();

        $customerSmsHistories = $this->SmsHistories->find()
            ->where([
                'SmsHistories.customer_id' => $smsHistory->customer_id,
                'SmsHistories.deleted IS' => null,
            ])
            ->orderBy([
                'SmsHistories.created' => 'DESC',
                'SmsHistories.updated' => 'DESC',
                'SmsHistories.id' => 'DESC',
            ])
            ->all();

        $this->set(compact('smsHistory', 'customerSmsHistories'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $smsHistory = $this->SmsHistories->find()
            ->where([
                'SmsHistories.id' => $id,
                'SmsHistories.deleted IS' => null,
            ])
            ->firstOrFail();

        $smsHistory->deleted = date('Y-m-d H:i:s');
        $smsHistory->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->SmsHistories->save($smsHistory, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('SMS送信履歴の削除が完了しました。'));
        } else {
            $this->Flash->error(__('SMS送信履歴の削除に失敗しました。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    protected function applySearchFilters($query, array $searchData): void
    {
        if (!empty($searchData['company_name'])) {
            $query->where(['Customers.company_name LIKE' => '%' . trim($searchData['company_name']) . '%']);
        }

        if (!empty($searchData['name'])) {
            $query->where(['Customers.name LIKE' => '%' . trim($searchData['name']) . '%']);
        }

        if (!empty($searchData['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', (string)$searchData['phone']);
            $expr = "
                REPLACE(REPLACE(Customers.phone, '-', ''), ' ', '') LIKE :phone OR
                REPLACE(REPLACE(Customers.tel, '-', ''), ' ', '') LIKE :phone OR
                REPLACE(REPLACE(SmsHistories.phone, '-', ''), ' ', '') LIKE :phone
            ";
            $query->where([$expr])->bind(':phone', "%$phone%", 'string');
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

        if (!empty($searchData['type_of_number'])) {
            $types = is_array($searchData['type_of_number']) ? $searchData['type_of_number'] : [$searchData['type_of_number']];
            $query->where(['Customers.type_of_number IN' => $types]);
        }

        if (!empty($searchData['status'])) {
            $statuses = is_array($searchData['status']) ? $searchData['status'] : [$searchData['status']];
            $query->where(['SmsHistories.status IN' => $statuses]);
        }

        if (!empty($searchData['created_from'])) {
            $query->where(['SmsHistories.created >=' => $searchData['created_from']]);
        }

        if (!empty($searchData['created_to'])) {
            $end = $searchData['created_to'];
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                $end .= ' 23:59:59';
            }
            $query->where(['SmsHistories.created <=' => $end]);
        }
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $smsStatusOpt = [
            'success' => '成功',
            'failed' => '失敗',
        ];

        $allUsersOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->orderBy(['Users.deleted' => 'ASC', 'Users.disp_cd' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'smsStatusOpt', 'allUsersOpt'));
    }

    protected function searchDefaults(): array
    {
        return [
            'company_name' => '',
            'name' => '',
            'phone' => '',
            'email' => '',
            'fax' => '',
            'client_cd' => '',
            'type_of_number' => '',
            'status' => '',
            'created_from' => '',
            'created_to' => '',
        ];
    }
}
