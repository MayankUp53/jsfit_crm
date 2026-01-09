<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Exception\PersistenceFailedException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
         $users = $this->Users->find()
            ->where(['Users.deleted IS' => null])
            ->orderBy(['Users.updated' => 'DESC', 'Users.created' => 'DESC']);

        // $this->paginate = [
        //     'limit' => 10
        // ];

        // $users = $this->paginate($query);

    $this->set(compact('users', ));
    $this->setListValue();
}



    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->find()
            ->where(['Users.id' => $id, 'Users.deleted IS' => null])
            ->firstOrFail();

        $this->set(compact('user'));
        $this->setListValue();
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['created'] = $now;
            $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('ユーザの登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('ユーザの登録に失敗しました。もう一度操作してください。'));
        }

        $warning = $this->CallbackNotification->getNumOfWarning();
        $this->set(compact('user', 'warning'));
        $this->setListValue();
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        // Handle back button
        if ($this->request->is('post') && $this->request->getData('back')) {
            return $this->redirect(['action' => 'view', $id]);
        }

        // Handle delete button
        if ($this->request->is('post') && $this->request->getData('delete')) {
            return $this->redirect(['action' => 'delete', $id]);
        }

        $user = $this->Users->find()
            ->where(['Users.id' => $id, 'Users.deleted IS' => null])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('ユーザの更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('ユーザの更新に失敗しました。もう一度操作してください。'));
        }

        $warning = $this->CallbackNotification->getNumOfWarning();
        $this->set(compact('user', 'warning'));
        $this->setListValue();
    }

    /**
     * Delete method (logical delete)
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);

        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        $user->deleted = $now;
        $user->deleted_user_id = $currentUser ? $currentUser->id : 0;

        if ($this->Users->save($user, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('ユーザの削除が完了しました。'));
        } else {
            $this->Flash->error(__('ユーザの削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Login method
     *
     * Mirrors legacy behavior: plain password allowed via LegacyPasswordHasher.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? '/homes';
            return $this->redirect($target);
        }

        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();
            if (!$result->isValid()) {
                $this->Flash->error(__('ユーザ名またはパスワードが誤っています。再度入力してください。'));
            }
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect(['action' => 'login']);
    }

    /**
     * SSO method (Single Sign-On)
     *
     * @param string|null $campaign Campaign ID
     * @param string|null $userid User ID
     * @param string|null $sessionId Session ID
     * @return \Cake\Http\Response|null|void
     */
    public function sso(?string $campaign = null, ?string $userid = null, ?string $sessionId = null)
    {
        if (empty($campaign) || empty($userid) || empty($sessionId)) {
            $this->Flash->error(__('必須のSSOパラメータが不足しています。再度操作してください。'));
            return $this->redirect(['action' => 'login']);
        }

        $user = $this->Users->find()
            ->where(['Users.loginid' => $userid, 'Users.deleted IS' => null])
            ->first();

        if (!$user) {
            $this->Flash->error(__('ユーザ情報が取得できませんでした。'));
            return $this->redirect(['action' => 'login']);
        }

        // Trust the SSO caller and attach the identity directly.
        $this->Authentication->logout();
        $this->Authentication->setIdentity($user);

        // Persist session context used by HomesController.
        $session = $this->request->getSession();
        $session->write([
            'campaign' => $campaign,
            'sessionId' => $sessionId,
        ]);

        return $this->redirect(['controller' => 'Homes', 'action' => 'index']);
    }

    /**
     * Set list values for views
     *
     * @return void
     */
    protected function setListValue(): void
    {
        // 権限
        $authOpt = ['0' => '一般', '9' => 'SV', '99' => '管理者'];

        // 作業者
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['Users.deleted IS' => null])
            ->orderBy(['Users.disp_cd' => 'ASC'])
            ->toArray();

        $this->set(compact('authOpt', 'opeOpt'));
    }

    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event): ?\Cake\Http\Response
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['sso', 'login']);
        return null;
    }
}
