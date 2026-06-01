<?php
declare(strict_types=1);

namespace App\Controller;

class MtSmsTextsController extends AppController
{
    protected $MtSmsTexts;
    protected $Users;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('App.CallbackNotification');

        $this->MtSmsTexts = $this->fetchTable('MtSmsTexts');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $mtSmsTexts = $this->MtSmsTexts->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtSmsTexts'));
        $this->setListValue();
    }

    public function add()
    {
        $entry = $this->MtSmsTexts->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            if (empty($data['disp_cd'])) {
                $latest = $this->MtSmsTexts->find()
                    ->select(['disp_cd'])
                    ->orderBy(['disp_cd' => 'DESC'])
                    ->first();

                $data['disp_cd'] = $latest ? ((int)$latest->disp_cd + 1) : 1;
            }

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $entry = $this->MtSmsTexts->patchEntity($entry, $data);

            if ($this->MtSmsTexts->save($entry)) {
                $this->Flash->success(__('SMS文面の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('SMS文面の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $entry = $this->MtSmsTexts->find()
            ->where(['id' => $id, 'deleted IS' => null])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtSmsTexts->patchEntity($entry, $data);

            if ($this->MtSmsTexts->save($entry)) {
                $this->Flash->success(__('SMS文面の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('SMS文面の更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $entry = $this->MtSmsTexts->find()
            ->where(['id' => $id, 'deleted IS' => null])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtSmsTexts->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('SMS文面の削除が完了しました。'));
        } else {
            $this->Flash->error(__('SMS文面の削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
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
