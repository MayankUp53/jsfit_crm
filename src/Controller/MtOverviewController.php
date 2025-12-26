<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class MtOverviewController extends AppController{
  protected $MtOverview ;
    protected $Users;

    public function initialize(): void
    {
        parent::initialize();
        $this->Users = $this->fetchTable('Users');
        $this->MtOverview = $this->fetchTable('MtOverview');
    }

    public function index()
    {
        $mtOverview = $this->MtOverview->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        // Load user names for created/updated
        $users = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->toArray();

        foreach ($mtOverview as $row) {
            $row->created_user_name = $users[$row->created_user_id] ?? '-';
            $row->updated_user_name = $users[$row->updated_user_id] ?? '-';
        }

        $this->set(compact('mtOverview'));
    }

  public function add()
{
    $entry = $this->MtOverview->newEmptyEntity();

    if ($this->request->is('post')) {
        $data = $this->request->getData();


        if (empty($data['disp_cd'])) {
            $maxDisp = $this->MtOverview->find()
                ->select(['max_disp' => 'MAX(disp_cd)'])
                ->first()
                ->max_disp ?? 0;
            $data['disp_cd'] = $maxDisp + 1;
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;

        $data['created'] = $now;
        $data['created_user_id'] = $userId;
        $data['updated'] = $now;
        $data['updated_user_id'] = $userId;

        $entry = $this->MtOverview->patchEntity($entry, $data);

        if ($this->MtOverview->save($entry)) {
            $this->Flash->success(__('概要の登録が完了しました。'));
            return $this->redirect(['action' => 'index']);
        }

        $errorMsg = $this->formatEntityErrors($entry);
        $this->Flash->error(__('概要の登録に失敗しました。入力内容を確認してください。 {0}', $errorMsg));
    }

    $this->set(compact('entry'));
     $this->setListValue();
   
}

    public function edit($id = null)
    {
        $entry = $this->MtOverview->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtOverview->patchEntity($entry, $data);
            if ($this->MtOverview->save($entry)) {
                $this->Flash->success(__('Overview updated successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Failed to update overview.'));
        }
        $this->set(compact('entry'));
         $this->setListValue();
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entry = $this->MtOverview->get($id);
        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtOverview->save($entry)) {
            $this->Flash->success(__('Overview deleted successfully.'));
        } else {
            $this->Flash->error(__('Failed to delete overview.'));
        }
        return $this->redirect(['action' => 'index']);
    }

      protected function setListValue(): void
    {
        // $warning = $this->CallbackNotification->getNumOfWarning();
        // $activeOpt = ['0' => '無効', '1' => '有効'];
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact( 'opeOpt'));
    }
}
