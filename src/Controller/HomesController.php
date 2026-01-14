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
        $limit = (int)($core?->limit ?? 50);
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

        // Paginate call history (array from API)
        $page = (int)$this->request->getQuery('page', 1);
        $perPage = 25;
        $response = is_array($response) ? $response : [];
        $total = count($response);
        $offset = max(0, ($page - 1) * $perPage);
        $pagedCalls = array_slice($response, $offset, $perPage);
        $pageCount = (int)ceil($total / $perPage);

        $warning = $this->CallbackNotification->getNumOfWarning();
        $callbackNoticeTimeString = '+' . $callbackNoticeTime . ' minute';

        $this->set(compact(
            'callbacks',
            'response',
            'pagedCalls',
            'page',
            'pageCount',
            'total',
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
            throw new NotFoundException('Voicelog URL is not configured.');
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
            Log::error('manualDialApi failed: ' . $e->getMessage());
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
            Log::error('callCallHistoryApi failed: ' . $e->getMessage());
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
                //'campaign_id IS' => $session->read('campaign'),
                     'campaign_id IS' => 1,
            ])
            ->orderBy(['disp_cd' => 'ASC'])
            ->toArray();
          
              

        $this->set([
            'activeOpt' => $activeOpt,
            'callbackStatusOpt' => $callbackStatusOpt,
            'opeOpt' => $operators,
            'cidOpt' => $cidOpt,

        ]);
    }
}
