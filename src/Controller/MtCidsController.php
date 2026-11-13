<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MtCids Controller
 *
 * Outbound caller ID master.
 */
class MtCidsController extends AppController
{
    protected $MtCids;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->MtCids = $this->fetchTable('MtCids');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $mtCids = $this->MtCids->find()
            ->where(['deleted IS' => null])
            ->orderBy(['disp_cd' => 'ASC'])
            ->all();

        $this->set(compact('mtCids'));
        $this->setListValue();
    }

    public function add()
    {
        $entry = $this->MtCids->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['active_flg'] = 1;
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()?->id ?? 0;

            if (empty($data['disp_cd'])) {
                $latest = $this->MtCids->find()
                    ->select(['disp_cd'])
                    ->orderBy(['disp_cd' => 'DESC'])
                    ->first();

                $data['disp_cd'] = $latest ? ((int)$latest->disp_cd + 1) : 1;
            }

            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $data['updated'] = $now;
            $data['updated_user_id'] = $userId;

            $entry = $this->MtCids->patchEntity($entry, $data);
            if ($this->MtCids->save($entry)) {
                $this->Flash->success(__('発信者番号の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('発信者番号の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function edit(?string $id = null)
    {
        $entry = $this->MtCids->find()
            ->where(['MtCids.id' => $id, 'deleted IS' => null])
            ->firstOrFail();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            unset($data['active_flg']);
            $data['updated'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

            $entry = $this->MtCids->patchEntity($entry, $data);
            if ($this->MtCids->save($entry)) {
                $this->Flash->success(__('発信者番号の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('発信者番号の更新に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('entry'));
        $this->setListValue();
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entry = $this->MtCids->find()
            ->where(['id' => $id])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtCids->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('発信者番号の削除が完了しました。'));
        } else {
            $this->Flash->error(__('発信者番号の削除に失敗しました。もう一度操作してください。'));
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
            $entry = $this->MtCids->find()->where(['id' => $itemId])->first();
            if ($entry) {
                $entry->disp_cd = $idx++;
                $this->MtCids->save($entry);
            }
        }
        return $this->redirect(['action' => 'index']);
    }

    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'opeOpt'));
    }
}
