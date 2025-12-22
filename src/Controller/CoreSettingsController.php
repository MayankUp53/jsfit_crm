<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * CoreSettings Controller
 *
 * System-wide external API and integration settings.
 */
class CoreSettingsController extends AppController
{
    protected $CoreSettings;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->CoreSettings = $this->fetchTable('CoreSettings');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $coreSettings = $this->CoreSettings->find()
            ->where(['deleted IS' => null])
            ->all();

        $this->set(compact('coreSettings'));
        $this->setListValue();
    }

    public function add()
    {
        $setting = $this->CoreSettings->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $setting = $this->CoreSettings->patchEntity($setting, $data);
            if ($this->CoreSettings->save($setting)) {
                $this->Flash->success(__('設定の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('設定の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('setting'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $setting = $this->CoreSettings->find()
            ->where(['id' => $id, 'deleted IS' => null])
            ->firstOrFail();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $setting = $this->CoreSettings->patchEntity($setting, $data);
            if ($this->CoreSettings->save($setting)) {
                $this->Flash->success(__('設定の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('設定の更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('setting'));
        $this->setListValue();
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $activeOpt = ['0' => '無効', '1' => '有効'];
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'activeOpt', 'opeOpt'));
    }
}
