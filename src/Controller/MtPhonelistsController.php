<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * MtPhonelists Controller
 *
 * Legacy phone book management.
 */
class MtPhonelistsController extends AppController
{
    protected $MtPhonelists;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');

        $this->MtPhonelists = $this->fetchTable('MtPhonelists');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $mtPhonelists = $this->MtPhonelists->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtPhonelists'));
        $this->setListValue();
    }

    public function add()
    {
        $entry = $this->MtPhonelists->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            // Ensure unique disp_cd (idx_mt_phonelists_disp)
            $maxDisp = $this->MtPhonelists->find()
                ->select(['max_disp' => $this->MtPhonelists->find()->func()->max('disp_cd')])
                ->first();
            $nextDisp = (int)($maxDisp?->max_disp ?? 0) + 1;
            $data['disp_cd'] = $nextDisp;

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $entry = $this->MtPhonelists->patchEntity($entry, $data);
            if ($this->MtPhonelists->save($entry)) {
                $this->Flash->success(__('電話帳データの登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('電話帳データの登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $entry = $this->MtPhonelists
            ->find()
            ->where([
                'id' => $id,
                'deleted IS' => null,
            ])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtPhonelists->patchEntity($entry, $data);
            if ($this->MtPhonelists->save($entry)) {
                $this->Flash->success(__('電話帳データの更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('電話帳データの更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $entry = $this->MtPhonelists
            ->find()
            ->where([
                'id' => $id,
                'deleted IS' => null,
            ])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtPhonelists->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('電話帳データの削除が完了しました。'));
        } else {
            $this->Flash->error(__('電話帳データの削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

public function sortupdate()
{
    // Get POST data
    $idString = $this->request->getData('ids');
    if (!$idString) return $this->redirect(['action' => 'index']);

    $sortArray = explode('-', $idString);
    $idx = 1;

    foreach ($sortArray as $itemId) {
        if (!$itemId) continue;

        $entry = $this->MtPhonelists->get($itemId); // simpler than find()->first()
        if ($entry) {
            $entry->disp_cd = $idx++;
            $this->MtPhonelists->save($entry);
        }
    }

    // Return JSON if AJAX
    if ($this->request->is('ajax')) {
        $this->set('success', true);
        $this->viewBuilder()->setOption('serialize', ['success']);
        return;
    }

    $this->Flash->success('表示順序を更新しました。');
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
