<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MtStatuses Controller
 *
 * Status master maintenance.
 */
class MtStatusesController extends AppController
{
    protected $MtStatuses;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->MtStatuses = $this->fetchTable('MtStatuses');
        $this->Users = $this->fetchTable('Users');
    }

public function index()
{
    // Fetch statuses
    $mtStatuses = $this->MtStatuses->find()
        ->where(['deleted IS' => null])
        ->orderBy(['disp_cd' => 'ASC'])
        ->all()
        ->toArray(); // convert entities to array

    // Fetch users once (id => user_name)
    $users = $this->Users->find('list', [
        'keyField' => 'id',
        'valueField' => 'user_name'
    ])
    ->where(['deleted IS' => null])
    ->toArray();

    // Add created/updated user names to each status
    foreach ($mtStatuses as $key => $row) {
        $mtStatuses[$key]['created_user_name'] = $users[$row['created_user_id']] ?? '-';
        $mtStatuses[$key]['updated_user_name'] = $users[$row['updated_user_id']] ?? '-';
    }

    $this->set(compact('mtStatuses'));
}




public function add()
{
    $entry = $this->MtStatuses->newEmptyEntity();

    if ($this->request->is('post')) {
        $data = $this->request->getData();

        // Automatically set display order if not provided
        if (empty($data['disp_cd'])) {
            $maxDisp = $this->MtStatuses->find()
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

        $entry = $this->MtStatuses->patchEntity($entry, $data);

        if ($this->MtStatuses->save($entry)) {
            $this->Flash->success(__('ステータスの登録が完了しました。'));
            return $this->redirect(['action' => 'index']);
        }

        $errorMsg = $this->formatEntityErrors($entry);
        $this->Flash->error(__('ステータスの登録に失敗しました。入力内容を確認してください。 {0}', $errorMsg));
    }

    $this->set(compact('entry'));
    $this->setListValue();
}



public function edit(?string $id = null)
{
    // Fetch the entry
    $entry = $this->MtOverview->find()
        ->where(['MtOverview.id' => $id, 'deleted IS' => null])
        ->firstOrFail();

    if ($this->request->is(['patch', 'post', 'put'])) {
        $data = $this->request->getData();

        // Only patch relevant fields
        $data = [
            'item_name' => $data['item_name'] ?? $entry->item_name,
            'disp_cd'   => $data['disp_cd'] ?? $entry->disp_cd,
            'updated'   => date('Y-m-d H:i:s'),
            'updated_user_id' => $this->getCurrentUser()?->id ?? 0
        ];

        $entry = $this->MtOverview->patchEntity($entry, $data);

        if ($this->MtOverview->save($entry)) {
            $this->Flash->success(__('概要の更新が完了しました。'));
            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->error(__('概要の更新に失敗しました。もう一度操作してください。'));
    }

    $this->set(compact('entry'));
    $this->setListValue();
}



    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entry = $this->MtStatuses->find()
            ->where(['id' => $id])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtStatuses->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('ステータスの削除が完了しました。'));
        } else {
            $this->Flash->error(__('ステータスの削除に失敗しました。もう一度操作してください。'));
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
            $entry = $this->MtStatuses->find()->where(['id' => $itemId])->first();
            if ($entry) {
                $entry->disp_cd = $idx++;
                $this->MtStatuses->save($entry);
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
