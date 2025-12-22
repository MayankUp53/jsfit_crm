<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MtProducts Controller
 *
 * Placeholder; legacy UI referenced from Settings.
 */
class MtProductsController extends AppController
{
    public function index()
    {
        $this->Flash->warning(__('MtProductsは未移植です (legacy deference)。'));
        return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
    }
}
