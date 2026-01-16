<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;

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
            $this->Flash->error(__('電話帳データの登録に失敗しました。もう一度操作してください。 または表示順序がすでに存在する'));
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



    public function updateOrder()
    {
        $this->request->allowMethod(['post']);

        try {
            // Read JSON payload from request body
            $data = json_decode($this->request->getBody()->getContents(), true);
            Log::debug('AJAX payload: ' . json_encode($data));

            if (empty($data['orderData'])) {
                throw new \Exception('No orderData received');
            }

            $mtPhonelistsTable = $this->getTableLocator()->get('MtPhonelists');

            // STEP 1: temporary negative disp_cd to avoid unique conflicts
            $entities = [];
            foreach ($data['orderData'] as $index => $item) {
                $entity = $mtPhonelistsTable->get($item['id']);
                $entity->disp_cd = - ($index + 1);
                $entities[] = $entity;
            }
            $mtPhonelistsTable->saveMany($entities, ['atomic' => true]);

            // STEP 2: assign final sequential numbers
            $entities = [];
            foreach ($data['orderData'] as $index => $item) {
                $entity = $mtPhonelistsTable->get($item['id']);
                $entity->disp_cd = $index + 1;
                $entities[] = $entity;
            }
            if (!$mtPhonelistsTable->saveMany($entities, ['atomic' => true])) {
                $errors = [];
                foreach ($entities as $e) {
                    $errors[$e->id] = $e->getErrors();
                }
                Log::error('SaveMany failed: ' . json_encode($errors));
                throw new \Exception('SaveMany failed, check logs');
            }

            // Success response
            $this->set(['message' => 'Order updated successfully']);
            $this->viewBuilder()->setOption('serialize', ['message']);
        } catch (\Exception $e) {
            Log::error('updateOrder failed: ' . $e->getMessage());

            $this->response = $this->response->withStatus(500);
            $this->set([
                'message' => 'Error saving order',
                'error' => $e->getMessage()
            ]);
            $this->viewBuilder()->setOption('serialize', ['message', 'error']);
        }
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
