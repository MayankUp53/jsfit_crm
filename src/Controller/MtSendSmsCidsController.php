<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MtSendSmsCids Controller
 *
 * SMS outbound caller ID master.
 */
class MtSendSmsCidsController extends AppController
{
    protected $MtSendSmsCids;
    protected $Users;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('App.CallbackNotification');

        $this->MtSendSmsCids = $this->fetchTable('MtSendSmsCids');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $mtSendSmsCids = $this->MtSendSmsCids->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtSendSmsCids'));
        $this->setListValue();
    }

    public function add()
    {
        $entry = $this->MtSendSmsCids->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            if (empty($data['disp_cd'])) {
                $latest = $this->MtSendSmsCids->find()
                    ->select(['disp_cd'])
                    ->orderBy(['disp_cd' => 'DESC'])
                    ->first();

                $data['disp_cd'] = $latest ? ((int)$latest->disp_cd + 1) : 1;
            }

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $entry = $this->MtSendSmsCids->patchEntity($entry, $data);

            if ($this->MtSendSmsCids->save($entry)) {
                $this->Flash->success(__('SMS発信者番号の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('SMS発信者番号の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $entry = $this->MtSendSmsCids->find()
            ->where(['MtSendSmsCids.id' => $id, 'deleted IS' => null])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtSendSmsCids->patchEntity($entry, $data);

            if ($this->MtSendSmsCids->save($entry)) {
                $this->Flash->success(__('SMS発信者番号の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('SMS発信者番号の更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $entry = $this->MtSendSmsCids->find()
            ->where(['id' => $id])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtSendSmsCids->save($entry, [
            'fields' => ['deleted', 'deleted_user_id']
        ])) {
            $this->Flash->success(__('SMS発信者番号の削除が完了しました。'));
        } else {
            $this->Flash->error(__('SMS発信者番号の削除に失敗しました。もう一度操作してください。'));
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

            $entry = $this->MtSendSmsCids->find()
                ->where(['id' => $itemId])
                ->first();

            if ($entry) {
                $entry->disp_cd = $idx++;
                $this->MtSendSmsCids->save($entry);
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();

        $activeOpt = [
            '0' => '無効',
            '1' => '有効'
        ];

        $opeOpt = $this->Users->find('list', [
                'keyField' => 'id',
                'valueField' => 'user_name'
            ])
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'activeOpt', 'opeOpt'));
    }
}
