<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ContactsController;
use App\Model\Entity\Contact;
use App\Model\Entity\User;
use Authentication\Identity;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\Table;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

class ContactsUpdateFailureTest extends TestCase
{
    public function testInvalidUpdateKeepsDetailPageHistoryAndSubmittedValues(): void
    {
        ConnectionManager::setConfig('contact_update_failure', [
            'className' => Connection::class,
            'driver' => Sqlite::class,
            'database' => ':memory:',
        ]);
        $connection = ConnectionManager::get('contact_update_failure');
        try {
            $connection->execute('CREATE TABLE customers (id INTEGER PRIMARY KEY, client_name TEXT)');
            $connection->execute('CREATE TABLE contacts (id INTEGER PRIMARY KEY, customer_id INTEGER, summary TEXT, detail TEXT, remark TEXT, status TEXT, queue_name TEXT, updated_user_id INTEGER, updated DATETIME, deleted DATETIME)');
            $connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, user_name TEXT)');
            $connection->insert('customers', ['id' => 1, 'client_name' => 'Existing customer']);
            $connection->insert('users', ['id' => 7, 'user_name' => 'Operator']);
            $connection->insert('contacts', ['id' => 10, 'customer_id' => 1, 'summary' => 'Saved history', 'status' => '1', 'updated_user_id' => 7]);

            Router::reload();
            Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $routes): void {
                $routes->setRouteClass(DashedRoute::class);
                $routes->fallbacks();
            });
            $request = (new ServerRequest(['environment' => ['REQUEST_METHOD' => 'POST'], 'session' => new Session()]))
                ->withParsedBody(['update' => '1', 'summary' => 'Unsaved summary', 'detail' => 'Unsaved detail', 'remark' => 'Unsaved memo', 'status' => ''])
                ->withAttribute('params', ['controller' => 'Contacts', 'action' => 'edit', 'plugin' => null, '_ext' => null, 'pass' => ['10']])
                ->withAttribute('identity', new Identity(new User(['id' => 7, 'auth' => '9'])));
            Router::setRequest($request);
            $controller = new ContactUpdateFailureHarness($request, 'Contacts');
            $response = $controller->edit('10');

            self::assertSame(200, $response->getStatusCode());
            $body = (string)$response->getBody();
            foreach (['Existing customer', 'Saved history', 'Unsaved summary', 'Unsaved detail', 'Unsaved memo', '履歴一覧', 'customer-details'] as $text) {
                self::assertStringContainsString($text, $body);
            }
            self::assertStringContainsString('/contacts/edit/10', $body);
            self::assertNotEmpty($controller->viewBuilder()->getVar('contact')->getErrors());
            self::assertNotEmpty($request->getSession()->read('Flash.flash'));
            self::assertSame('Saved history', $connection->execute('SELECT summary FROM contacts WHERE id = 10')->fetch('assoc')['summary']);
        } finally {
            $connection->getDriver()->disconnect();
            ConnectionManager::drop('contact_update_failure');
            Router::reload();
        }
    }
}

class ContactUpdateFailureHarness extends ContactsController
{
    public function initialize(): void
    {
        $connection = ConnectionManager::get('contact_update_failure');
        $this->Customers = new Table(['connection' => $connection, 'table' => 'customers', 'alias' => 'Customers']);
        $this->Contacts = new Table(['connection' => $connection, 'table' => 'contacts', 'alias' => 'Contacts', 'entityClass' => Contact::class]);
        $this->Contacts->belongsTo('Customers', ['targetTable' => $this->Customers, 'foreignKey' => 'customer_id']);
        $this->Contacts->getValidator()->notEmptyString('status', 'Select a status.');
        $this->Users = new Table(['connection' => $connection, 'table' => 'users', 'alias' => 'Users']);
        $this->loadComponent('Flash');
        $this->viewBuilder()->disableAutoLayout();
    }

    protected function getCurrentUser(): ?User
    {
        return new User(['id' => 7, 'auth' => '9']);
    }

    protected function setListValue(): void
    {
        $this->set(['allUsersOpt' => [7 => 'Operator'], 'statusOpt' => [1 => 'Open'], 'queueOpt' => []]);
    }
}
