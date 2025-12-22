<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Settings Controller
 *
 * Placeholder dashboard for miscellaneous settings.
 */
class SettingsController extends AppController
{      
      protected $Users ;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->Users = $this->fetchTable('Users');
    }

    public function index()
    {
        $this->setListValue();
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
