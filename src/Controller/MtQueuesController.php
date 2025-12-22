<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MtQueues Controller
 *
 * Queue master maintenance.
 */
class MtQueuesController extends AppController
{
    protected $MtQueues;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->MtQueues = $this->fetchTable('MtQueues');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $mtQueues = $this->MtQueues->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtQueues'));
        $this->setListValue();
    }

    public function add()
    {
        $entry = $this->MtQueues->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $entry = $this->MtQueues->patchEntity($entry, $data);
            if ($this->MtQueues->save($entry)) {
                $this->Flash->success(__('キューの登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('キューの登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $entry = $this->MtQueues->get($id, ['conditions' => ['deleted IS' => null]]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtQueues->patchEntity($entry, $data);
            if ($this->MtQueues->save($entry)) {
                $this->Flash->success(__('キューの更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('キューの更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entry = $this->MtQueues->get($id);

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtQueues->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('キューの削除が完了しました。'));
        } else {
            $this->Flash->error(__('キューの削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function sortupdate(?string $id = null)
    {
        $sortArray = explode('-', (string)$id);
        $idx = 1;
        foreach ($sortArray as $itemId) {
            if (empty($itemId)) {
                continue;
            }
            $entry = $this->MtQueues->find()->where(['id' => $itemId])->first();
            if ($entry) {
                $entry->disp_cd = $idx++;
                $this->MtQueues->save($entry);
            }
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
