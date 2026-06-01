<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Client;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;

use function Cake\Error\debug;

/**
 * Homes Controller (landing dashboard)
 *
 * Migrated from CakePHP 2.x keeping functional parity.
 */
class HomesController extends AppController
{
    protected $Callbacks;
    protected $CoreSettings;
    protected $MtCids;
    protected $MtPhonelists;
    protected $Users;
    protected $MtSendSmsCids;
    protected $MtSmsTexts;
    protected $Customers;
    protected $SmsHistories;
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('App.CallbackNotification');

        // Tables referenced across actions.
        $this->Callbacks = $this->fetchTable('Callbacks');
        $this->CoreSettings = $this->fetchTable('CoreSettings');
        $this->MtCids = $this->fetchTable('MtCids');
        $this->MtPhonelists = $this->fetchTable('MtPhonelists');
        $this->MtCids = $this->fetchTable('MtCids');
        $this->Users = $this->fetchTable('Users');
        $this->MtSendSmsCids = $this->fetchTable('MtSendSmsCids');
        $this->MtSmsTexts = $this->fetchTable('MtSmsTexts');
        $this->Customers = $this->fetchTable('Customers');
        $this->SmsHistories = $this->fetchTable('SmsHistories');
    }

    public function beforeFilter(EventInterface $event): ?ResponseInterface
    {
        parent::beforeFilter($event);
        // Landing page is public after login; rely on Authentication component to guard.
        return null;
    }

    public function index()
    {
        // Upcoming callbacks
        $callbacks = $this->Callbacks
            ->find()
            ->where(['deleted IS' => null])
            ->orderBy(['callback_datetime' => 'DESC'])
            ->all();

        $core = $this->CoreSettings->find()
            ->where(['deleted IS' => null])
            ->first();

        $voicelogUrl = $core?->voicelog_url;
        $ccid = $core?->ccid;
        $limit = (int)($core?->limit_value ?? 50);
        $callbackNoticeTime = (int)($core?->callback_notice_time ?? 0);

        $session = $this->request->getSession();
        $userId = $this->Authentication->getIdentity()?->getIdentifier();
        $campaignId = $session->read('campaign');

        $dateFrom = date('Y-m-d', strtotime('-2 month'));
        $dateTo = date('Y-m-d');

        $response = $this->callCallHistoryApi(
            (string)$ccid,
            (string)$campaignId,
            $limit,
            (string)$userId,
            $dateFrom,
            $dateTo
        );

        // // Paginate call history (array from API)
        // $page = (int)$this->request->getQuery('page', 1);
        // $perPage = (int)($core?->limit ?? 50);
        // $response = is_array($response) ? $response : [];
        // $total = count($response);
        // $offset = max(0, ($page - 1) * $perPage);
        // $pagedCalls = array_slice($response, $offset, $perPage);
        // $pageCount = (int)ceil($total / $perPage);

        $warning = $this->CallbackNotification->getNumOfWarning();
        $callbackNoticeTimeString = '+' . $callbackNoticeTime . ' minute';

        $this->set(compact(
            'callbacks',
            'response',
          //  'pagedCalls',
         //   'page',
          //  'pageCount',
          //  'total',
            'voicelogUrl',
            'callbackNoticeTimeString',
            'userId',
            'warning',
            'limit',
            'campaignId'
        ));

        $this->setListValue();
    }

    public function play(?string $id = null, ?string $crtObjectId = null)
    {
        $core = $this->CoreSettings->find()
            ->where(['deleted IS' => null])
            ->first();

        $url = $core?->voicelog_url;
        if (!$url) {
            throw new NotFoundException('録音再生URLが設定されていません。');
        }

        $this->set(compact('url', 'id', 'crtObjectId'));
        $this->viewBuilder()->disableAutoLayout();
    }

    public function phoneList()
    {
        $mtPhonelists = $this->MtPhonelists
            ->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtPhonelists'));
        $this->viewBuilder()->disableAutoLayout();
        
     //   $this->setListValue();
    }

    public function smsTextList()
    {
        $mtSmsTexts = $this->MtSmsTexts
            ->find()
            ->where([
                'deleted IS' => null,
                'active_flg' => 1,
            ])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtSmsTexts'));
        $this->viewBuilder()->disableAutoLayout();
    }

    public function storeList()
    {
        $mtPhonelists = $this->MtPhonelists
            ->find()
            ->select(['item_name'])
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();


        $this->set(compact('mtPhonelists'));
        $this->viewBuilder()->disableAutoLayout();
        $this->setListValue();
    }


public function sendSmsApi()
{
    if (!$this->request->is('post')) {
        return $this->redirect(['action' => 'index']);
    }

    $core = $this->CoreSettings->find()
        ->where(['deleted IS' => null])
        ->first();

    $smsCid = $this->request->getData('sms_cid');
    $smsTel = (string)$this->request->getData('sms_tel');
    $smsText = trim((string)($this->request->getData('sms_text') ?? ''));

    $smsNumbers = preg_split('/[\s,;]+/', $smsTel, -1, PREG_SPLIT_NO_EMPTY);
    $smsNumbers = array_map(
        static fn($tel) => preg_replace('/[^0-9]/', '', (string)$tel),
        $smsNumbers ?: []
    );
    $smsNumbers = array_values(array_unique(array_filter($smsNumbers)));

    if (empty($smsCid) || empty($smsNumbers) || $smsText === '') {
        $this->Flash->error(__('必須情報が不足しています。'));
        return $this->redirect(['action' => 'index']);
    }

    $url = $core?->sendSMS_api_url;
    if (!$url) {
        $this->Flash->error(__('SMS API URLが設定されていません。'));
        return $this->redirect(['action' => 'index']);
    }

    $client = new Client();
    $successCount = 0;
    $failedNumbers = [];
    $failureMessages = [];

    foreach ($smsNumbers as $smsNumber) {
        $customerId = $this->findOrCreateSmsCustomer($smsNumber);

        $query = http_build_query([
            'srcPhone'       => $smsNumber,
            'sendSmsNumber'  => $smsCid,
            'smsText'        => $smsText,
        ], '', '&', PHP_QUERY_RFC3986);

        $finalUrl = rtrim($url, '?&') . '?' . $query;
        Log::debug('SMS API URL: ' . $finalUrl);

        try {
            $response = $client->get($finalUrl);
            $statusCode = $response->getStatusCode();
            $body = (string)$response->getBody();
            $json = json_decode($body, true);
        } catch (\Throwable $e) {
            Log::error('SMS APIエラー (' . $smsNumber . '): ' . $e->getMessage());
            $this->saveSmsHistory($customerId, $smsNumber, (string)$smsCid, $smsText, 'failed');
            $failedNumbers[] = $smsNumber;
            $failureMessages[] = $smsNumber . ': SMS APIから応答がありませんでした。';
            continue;
        }

        $isSuccess = $this->isSmsApiSuccess($statusCode, $json, $body);
        $apiMessage = $this->getSmsApiMessage($json, $body);

        if ($isSuccess) {
            $this->saveSmsHistory($customerId, $smsNumber, (string)$smsCid, $smsText, 'success');
            $successCount++;
        } else {
            Log::error('SMS送信失敗レスポンス (' . $smsNumber . ') HTTP ' . $statusCode . ': ' . $apiMessage . ' | ' . $body);
            $this->saveSmsHistory($customerId, $smsNumber, (string)$smsCid, $smsText, 'failed');
            $failedNumbers[] = $smsNumber;
            $failureMessages[] = $smsNumber . ': ' . $apiMessage;
        }
    }

    if ($successCount > 0) {
        $this->Flash->success(__('SMS送信が完了しました。成功: {0}件', $successCount));
    }
    if (!empty($failedNumbers)) {
        $this->Flash->error(__('SMS送信に失敗しました。失敗: {0}件 ({1})', count($failedNumbers), implode(' / ', $failureMessages)));
    }

    return $this->redirect(['action' => 'index']);
}

    protected function isSmsApiSuccess(int $statusCode, mixed $json, string $body): bool
    {
        if (!is_array($json)) {
            return false;
        }

        $apiStatus = strtolower((string)($json['status'] ?? ''));
        if ($apiStatus !== '') {
            return $apiStatus === 'success';
        }

        $apiHttpStatus = (int)($json['http_status'] ?? $statusCode);
        return $apiHttpStatus >= 200 && $apiHttpStatus < 300;
    }

    protected function getSmsApiMessage(mixed $json, string $body): string
    {
        if (is_array($json)) {
            $message = trim((string)($json['message'] ?? ''));
            if ($message !== '') {
                return $message;
            }

            $httpStatus = (int)($json['http_status'] ?? 0);
            if ($httpStatus >= 400 && $httpStatus < 500) {
                return 'SMS APIのリクエストエラーです。';
            }
            if ($httpStatus >= 500) {
                return 'SMS APIのサーバエラーです。';
            }
            if ($httpStatus === 0) {
                return 'SMS APIから応答がありませんでした。';
            }
        }

        return trim($body) !== '' ? trim($body) : 'SMS送信に失敗しました。';
    }
    protected function findOrCreateSmsCustomer(string $phone): int
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        $expr = "
            REPLACE(REPLACE(REPLACE(Customers.tel_only, '-', ''), ' ', ''), '+', '') = :phone OR
            REPLACE(REPLACE(REPLACE(Customers.phone_only, '-', ''), ' ', ''), '+', '') = :phone OR
            REPLACE(REPLACE(REPLACE(Customers.tel, '-', ''), ' ', ''), '+', '') = :phone OR
            REPLACE(REPLACE(REPLACE(Customers.phone, '-', ''), ' ', ''), '+', '') = :phone
        ";

        $customer = $this->Customers->find()
            ->where([
                'Customers.deleted IS' => null,
                $expr,
            ])
            ->bind(':phone', $cleanPhone, 'string')
            ->first();

        if ($customer) {
            return (int)$customer->id;
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;
        $a = explode('.', (string)microtime(true));
        $dispNo = date('ymdHis') . substr($a[1] ?? '000', 0, 3);

        $customer = $this->Customers->newEmptyEntity();
        $customer = $this->Customers->patchEntity($customer, [
            'disp_no' => $dispNo,
            'client_cd' => 'sms_' . bin2hex(random_bytes(8)),
            'tel' => $cleanPhone,
            'tel_only' => $cleanPhone,
            'zip_cd' => '',
            'addr1' => '',
            'created' => $now,
            'created_user_id' => $userId,
            'updated' => $now,
            'updated_user_id' => $userId,
        ]);

        if ($this->Customers->save($customer)) {
            return (int)$customer->id;
        }

        Log::error('SMS送信用の顧客自動登録に失敗しました: ' . json_encode($customer->getErrors()));
        return 0;
    }

    protected function saveSmsHistory(int $customerId, string $phone, string $sendSmsNumber, string $smsText, string $status): void
    {
        if ($customerId <= 0) {
            Log::error('顧客IDが空のためSMS履歴登録をスキップしました。電話番号: ' . $phone);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;

        $history = $this->SmsHistories->newEmptyEntity();
        $history = $this->SmsHistories->patchEntity($history, [
            'customer_id' => $customerId,
            'phone' => $phone,
            'send_sms_number' => $sendSmsNumber,
            'sms_text' => $smsText,
            'status' => $status,
            'created' => $now,
            'created_user_id' => $userId,
            'updated' => $now,
            'updated_user_id' => $userId,
        ]);

        if (!$this->SmsHistories->save($history)) {
            Log::error('SMS履歴の保存に失敗しました: ' . json_encode($history->getErrors()));
        }
    }

public function manualDialApi()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'index']);
        }

        $session = $this->request->getSession();
        $core = $this->CoreSettings->find()
            ->where(['deleted IS' => null])
            ->first();

        $cmdreqId = 0;
        $campaignId = $session->read('campaign');
        $sessionId = $session->read('sessionId');
        $callerId = $this->request->getData('cid');
        $tel = $this->request->getData('tel');
$tel = str_replace('-', '', $tel); // removes all dashes



        if (empty($campaignId) || empty($sessionId) || empty($callerId) || empty($tel)) {
            $this->Flash->error(__('必須の情報が不足しています。再度操作してください。'));
            return $this->redirect(['action' => 'index']);
        }

        $url = $core?->dial_api_url;
        if (!$url) {
            $this->Flash->error(__('ダイヤルAPIのURLが設定されていません。'));
            return $this->redirect(['action' => 'index']);
        }

        $payload = [
            'campaignId' => (string)$campaignId,
            'sessionId' => (string)$sessionId,
            'command-request-id' => (string)$cmdreqId,
            'phone' => (string)$tel,
            'shouldAddCustomer' => false,
            'additionalParams' => [
                'caller_id' => (string)$callerId,
            ],
        ];

        $client = new Client();
        try {
            // The legacy API expected JSON as a URL-encoded data param appended to the URL.
            $response = $client->get(
                $url . urlencode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
                [],
                ['headers' => ['Content-Type' => 'application/json']]
            );

            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            Log::error('手動発信APIの呼び出しに失敗しました: ' . $e->getMessage());
            $this->Flash->error(__('外部APIの呼び出しに失敗しました。'));
            return $this->redirect(['action' => 'index']);
        }

        if (!is_array($body) || ($body['reason'] ?? '') !== 'success') {
            $message = $body['reason'] ?? __('不明なエラーが発生しました。');
            if (strpos($message, '業務を行っていません') !== false) {
                $message = 'ステータスを利用可能に変更してください。';
            } elseif (strpos($message, '他のコールのために予約されています') !== false) {
                $message = '内線セッションエラー：電話機を確認し、再ログインしてください。';
            }
            $this->Flash->error($message);
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Call call history API (legacy HttpSocket equivalent).
     */
    protected function callCallHistoryApi(string $ccid, string $campaignId, int $limit, string $userId, string $dateFrom, string $dateTo): array
    {
        $core = $this->CoreSettings->find()
            ->where(['deleted IS' => null])
            ->first();

        $url = $core?->ch_api_url;
        if (!$url) {
            return [];
        }

        // Legacy implementation always queried all users and campaign_id=1.
        $userId = '';
        $campaignId = '1';

        $params = [
            'ccid' => $ccid,
            'campaign_id' => $campaignId,
            'limit' => $limit,
            'userid' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $client = new Client();
        try {
            $response = $client->get($url, $params);
            $body = json_decode((string)$response->getBody(), true);
            return is_array($body) ? $body : [];
        } catch (\Throwable $e) {
            Log::error('通話履歴APIの呼び出しに失敗しました: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Prepare shared option lists for the views.
     */
    protected function setListValue(): void
    {
        $activeOpt = ['0' => '無効', '1' => '有効'];
        $callbackStatusOpt = [
            '0' => '未',
            '1' => '完了',
            '2' => '中止',
            '3' => '破棄',
            '4' => '登録誤り',
            '99' => 'その他',
        ];

        $operators = $this->Users
            ->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
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

        $smsCidQuery = $this->MtSendSmsCids
            ->find('list', keyField: 'cid', valueField: 'item_name')
            ->where(['deleted IS' => null]);

        if ($session->read('campaign')) {
            $smsCidQuery->where(['campaign_id' => $session->read('campaign')]);
        }

        $smsCidOpt = $smsCidQuery
            ->orderBy(['disp_cd' => 'ASC'])
            ->toArray();
          
              

        $this->set([
            'activeOpt' => $activeOpt,
            'callbackStatusOpt' => $callbackStatusOpt,
            'opeOpt' => $operators,
            'cidOpt' => $cidOpt,
            'smsCidOpt' => $smsCidOpt,
        ]);
    }
}


