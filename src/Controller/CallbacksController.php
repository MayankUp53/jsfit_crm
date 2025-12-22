<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Callbacks Controller
 *
 * Manages callback (再コール) reservations.
 */
class CallbacksController extends AppController
{
    protected $Callbacks;
    protected $Users;
    protected $Customers;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->Callbacks = $this->fetchTable('Callbacks');
        $this->Users = $this->fetchTable('Users');
        $this->Customers = $this->fetchTable('Customers');
    }

    public function index()
    {
        $callbacks = $this->Callbacks->find()
            ->where(['deleted IS' => null])
            ->orderBy(['callback_datetime' => 'DESC'])
            ->all();

        $this->set(compact('callbacks'));
        $this->setListValue();
    }

    public function add()
    {
        $callback = $this->Callbacks->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;
            $data['is_done'] = $data['is_done'] ?? 0;

            $callback = $this->Callbacks->patchEntity($callback, $data);
            if ($this->Callbacks->save($callback)) {
                $this->Flash->success(__('再コール予約の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('再コール予約の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('callback'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $callback = $this->Callbacks->get($id, ['conditions' => ['deleted IS' => null]]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $callback = $this->Callbacks->patchEntity($callback, $data);
            if ($this->Callbacks->save($callback)) {
                $this->Flash->success(__('再コール予約の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('再コール予約の更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('callback'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $callback = $this->Callbacks->get($id);

        $callback->deleted = date('Y-m-d H:i:s');
        $callback->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->Callbacks->save($callback, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('再コール予約の削除が完了しました。'));
        } else {
            $this->Flash->error(__('再コール予約の削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $callbackStatusOpt = ['0' => '未', '1' => '完了', '2' => '中止', '3' => '破棄', '4' => '登録誤り', '99' => 'その他'];
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();
        $customers = $this->Customers->find('list', keyField: 'id', valueField: 'name_kj')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'callbackStatusOpt', 'opeOpt', 'customers'));
    }
}
