<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomersController;
use App\Model\Entity\User;
use App\Model\Table\CustomersTable;
use App\View\AppView;
use Authentication\Identity;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\Table;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

/** Exercises the migrated customer flow without touching the application database. */
class CustomersFlowTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        ConnectionManager::setConfig('customers_flow', ['className' => Connection::class, 'driver' => Sqlite::class, 'database' => ':memory:']);
        $this->connection = ConnectionManager::get('customers_flow');
        $fields = [
            'client_cd', 'client_branch_code', 'client_name', 'client_branch_name', 'addr1', 'addr2', 'addr3',
            'tel', 'tel_only', 'tel2', 'fax', 'section_name', 'sales_person', 'deadline',
            'incharge1_name', 'incharge1_phone', 'incharge2_name', 'incharge2_phone',
            'incharge3_name', 'incharge3_phone', 'incharge4_name', 'incharge4_phone',
            'notes1', 'notes2', 'notes3', 'notes4',
        ];
        $columns = implode(', ', array_map(static fn($field) => $field . ' TEXT', $fields));
        $this->connection->execute('CREATE TABLE customers (id INTEGER PRIMARY KEY AUTOINCREMENT, client_code INTEGER, ' . $columns . ', created DATETIME, updated DATETIME, deleted DATETIME, created_user_id INTEGER, updated_user_id INTEGER, deleted_user_id INTEGER, UNIQUE(client_code, client_branch_code))');
        $this->connection->execute('CREATE TABLE contacts (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER, deleted DATETIME, deleted_user_id INTEGER)');
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, user_name TEXT)');
        $this->connection->insert('users', ['id' => 7, 'user_name' => 'Operator']);
        Router::reload();
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $routes): void {
            $routes->setRouteClass(DashedRoute::class);
            $routes->fallbacks();
        });
    }

    protected function tearDown(): void
    {
        $this->connection->getDriver()->disconnect();
        ConnectionManager::drop('customers_flow');
        parent::tearDown();
    }

    private function controller(string $action, string $method = 'GET', array $data = [], string $auth = '9'): CustomerFlowHarness
    {
        $request = (new ServerRequest(['environment' => ['REQUEST_METHOD' => $method], 'session' => new Session()]))
            ->withParsedBody($data)
            ->withAttribute('params', ['controller' => 'Customers', 'action' => $action, 'plugin' => null, '_ext' => null, 'pass' => []])
            ->withAttribute('identity', new Identity(new User(['id' => 7, 'auth' => $auth])));
        Router::setRequest($request);
        $controller = new CustomerFlowHarness($request, 'Customers');
        $controller->auth = $auth;
        return $controller;
    }

    private function addCustomer(array $data = []): int
    {
        $controller = $this->controller('add', 'POST', $data + ['client_code' => '123456', 'client_branch_code' => '001', 'client_name' => 'Customer', 'tel' => '03-1234-5678', 'tel2' => '090-1234-5678', 'add' => '1']);
        $response = $controller->add();
        self::assertNotNull($response, json_encode($controller->viewBuilder()->getVar('customer')?->getErrors()));
        self::assertStringContainsString('/customers', $response->getHeaderLine('Location'));
        return (int)$this->connection->execute('SELECT MAX(id) AS id FROM customers')->fetch('assoc')['id'];
    }

    public function testAddEditAndCancelNavigation(): void
    {
        $id = $this->addCustomer(['notes1' => 'Note', 'incharge4_phone' => '040', 'deleted' => '2020-01-01']);
        $customer = $this->connection->execute('SELECT * FROM customers WHERE id = ?', [$id])->fetch('assoc');
        self::assertSame('123456', $customer['client_cd']);
        self::assertSame('0312345678', $customer['tel_only']);
        self::assertSame('040', $customer['incharge4_phone']);
        self::assertSame('Note', $customer['notes1']);
        self::assertNull($customer['deleted']);
        self::assertEquals(7, $customer['created_user_id']);
        // Separate branches may share the same customer code.
        self::assertGreaterThan($id, $this->addCustomer(['client_branch_code' => '002']));

        $controller = $this->controller('edit', 'PATCH', ['client_code' => '123456', 'client_branch_code' => '001', 'tel' => '050-0000-0000', 'notes4' => 'Updated', 'add' => '1']);
        self::assertStringContainsString('/contacts/add/' . $id, $controller->edit((string)$id)->getHeaderLine('Location'));
        $customer = $this->connection->execute('SELECT * FROM customers WHERE id = ?', [$id])->fetch('assoc');
        self::assertSame('05000000000', $customer['tel_only']);
        self::assertSame('Updated', $customer['notes4']);
        self::assertSame('Customer', $customer['client_name']);
        $cancel = $this->controller('edit', 'POST', ['back' => '1', 'tel' => '']);
        self::assertStringContainsString('/contacts/add/' . $id, $cancel->edit((string)$id)->getHeaderLine('Location'));
        $cancel = $this->controller('add', 'POST', ['back' => '1']);
        self::assertStringContainsString('/customers', $cancel->add()->getHeaderLine('Location'));
        self::assertSame(2, $this->controller('index')->Customers->find()->count());
    }

    public function testInvalidInputKeepsSubmittedValues(): void
    {
        $controller = $this->controller('add', 'POST', ['client_code' => '', 'tel' => '', 'notes2' => 'Keep me', 'add' => '1']);
        self::assertNull($controller->add());
        $entity = $controller->viewBuilder()->getVar('customer');
        self::assertNotEmpty($entity->getError('client_code'));
        self::assertNotEmpty($entity->getError('tel'));
        self::assertSame('Keep me', $entity->notes2);
        self::assertSame(0, $controller->Customers->find()->count());
    }

    public function testSearchAndIndexLayout(): void
    {
        $id = $this->addCustomer(['addr2' => 'Tokyo', 'sales_person' => 'Sales']);
        $controller = $this->controller('index');
        $method = new \ReflectionMethod(CustomersController::class, 'buildCustomerQuery');
        self::assertSame(1, $method->invoke($controller, ['tel_only' => '090-1234-5678'])->count());
        self::assertSame(0, $method->invoke($controller, ['tel_only' => '090'])->count());
        self::assertSame(1, $method->invoke($controller, ['addr1' => 'Tokyo', 'sales_person' => 'Sales', 'client_code' => '234'])->count());
        self::assertSame(0, $method->invoke($controller, ['client_branch_name' => 'missing'])->count());
        $controller->index();
        $view = new AppView($controller->getRequest());
        $view->set($controller->viewBuilder()->getVars());
        $html = $view->render('Customers/index', false);
        self::assertStringContainsString('/contacts/add/' . $id, $html);
        self::assertStringNotContainsString('>操作<', $html);
        self::assertStringNotContainsString('callModal', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($dom);
        self::assertSame(15, $xpath->query('//table[@id="dt"]/thead/tr/td')->length);
        self::assertSame(15, $xpath->query('//table[@id="dt"]/tbody/tr[1]/td')->length);
    }

    public function testEditDeleteSoftDeletesOnlyLinkedHistory(): void
    {
        $id = $this->addCustomer();
        $otherId = $this->addCustomer(['client_branch_code' => '002']);
        foreach ([$id, $id, $otherId] as $customerId) {
            $this->connection->insert('contacts', ['customer_id' => $customerId]);
        }
        $controller = $this->controller('edit', 'PATCH', ['delete' => '1']);
        self::assertStringContainsString('/customers', $controller->edit((string)$id)->getHeaderLine('Location'));
        $customer = $controller->Customers->get($id);
        self::assertNotNull($customer->deleted);
        self::assertSame(7, $customer->deleted_user_id);
        $contacts = $this->connection->execute('SELECT * FROM contacts ORDER BY id')->fetchAll('assoc');
        self::assertSame($contacts[0]['deleted'], $contacts[1]['deleted']);
        self::assertNotNull($contacts[0]['deleted']);
        self::assertEquals(7, $contacts[0]['deleted_user_id']);
        self::assertNull($contacts[2]['deleted']);
        self::assertNull($controller->Customers->get($otherId)->deleted);
        $method = new \ReflectionMethod(CustomersController::class, 'buildCustomerQuery');
        self::assertSame(1, $method->invoke($controller, [])->count());
    }

    public function testSearchPersistenceClearAndExport(): void
    {
        $this->addCustomer();
        $controller = $this->controller('index', 'POST', ['searching' => '1', 'Search' => ['client_name' => 'Customer']]);
        $controller->index();
        self::assertSame(['client_name' => 'Customer'], $controller->getRequest()->getSession()->read('Customers.search'));
        $controller->clearSession();
        self::assertNull($controller->getRequest()->getSession()->read('Customers.search'));

        $controller = $this->controller('index', 'POST', ['download' => '1']);
        $controller->index();
        self::assertCount(1, $controller->exportRows);
        self::assertEquals('123456', $controller->exportRows[0]['client_cd']);
        self::assertSame('Operator', $controller->exportRows[0]['created_user']);
        self::assertNull($controller->viewBuilder()->getVar('customers'));
    }

    public function testDeleteRollsBackWhenLinkedHistoryUpdateFails(): void
    {
        $id = $this->addCustomer();
        $this->connection->insert('contacts', ['customer_id' => $id]);
        $this->connection->execute("CREATE TRIGGER reject_delete BEFORE UPDATE ON contacts BEGIN SELECT RAISE(ABORT, 'Test failure'); END");
        try {
            $this->controller('delete', 'POST')->delete((string)$id);
            self::fail('Expected the update failure');
        } catch (\Cake\Database\Exception\QueryException $exception) {
            self::assertStringContainsString('Test failure', $exception->getMessage());
        }
        self::assertNull($this->controller('index')->Customers->get($id)->deleted);
    }

    public function testDeleteRejectsReadOnlyUser(): void
    {
        $id = $this->addCustomer();
        $this->expectException(ForbiddenException::class);
        $this->controller('delete', 'POST', [], '0')->delete((string)$id);
    }

    public function testDeleteRejectsGetRequest(): void
    {
        $id = $this->addCustomer();
        $this->expectException(MethodNotAllowedException::class);
        $this->controller('delete')->delete((string)$id);
    }

    public function testIncomingAutoCreateFailureShowsErrorInsteadOfContactZero(): void
    {
        $this->connection->execute('ALTER TABLE customers ADD COLUMN phone TEXT');
        $this->connection->execute('ALTER TABLE customers ADD COLUMN phone_only TEXT');

        foreach (['unknown', 'anonymous', '0312345678'] as $phone) {
            $controller = $this->controller('ameyo');
            $controller->getRequest()->getSession()->delete('Flash.flash');
            $customers = $this->getMockBuilder(CustomersTable::class)
                ->setConstructorArgs([['connection' => $this->connection, 'alias' => 'Customers']])
                ->onlyMethods(['save'])
                ->getMock();
            $customers->expects(self::once())->method('save')->willReturn(false);
            $controller->Customers = $customers;

            $response = $controller->ameyo('1', 'session', null, null, $phone);

            self::assertSame('/customers', parse_url($response->getHeaderLine('Location'), PHP_URL_PATH));
            $messages = $controller->getRequest()->getSession()->read('Flash.flash');
            self::assertCount(1, $messages);
            self::assertSame('flash/error', $messages[0]['element']);
            self::assertSame(0, (int)$this->connection->execute('SELECT COUNT(*) AS total FROM customers')->fetch('assoc')['total']);
        }
    }

    public function testAddAndEditFormsMatchOriginalFields(): void
    {
        $id = $this->addCustomer();
        foreach (['add', 'edit'] as $action) {
            foreach (['0', '9'] as $auth) {
                $controller = $this->controller($action, 'GET', [], $auth);
                $action === 'edit' ? $controller->edit((string)$id) : $controller->add();
                $view = new AppView($controller->getRequest());
                $view->set($controller->viewBuilder()->getVars());
                $html = $view->render('Customers/' . $action, false);
                foreach (['client_code', 'client_branch_code', 'client_name', 'client_branch_name', 'incharge4_name', 'incharge4_phone', 'addr3', 'deadline', 'notes4', 'tel', 'tel2', 'back', 'add'] as $field) {
                    self::assertStringContainsString('name="' . $field . '"', $html);
                }
                self::assertSame($action === 'edit' && $auth === '9', str_contains($html, 'name="delete"'));
                self::assertStringContainsString($action === 'edit' ? '#ff8c00' : '#4169E1', $html);
                self::assertStringNotContainsString('name="email"', $html);
                self::assertStringNotContainsString('name="client_cd"', $html);
            }
        }
    }
}

class CustomerFlowHarness extends CustomersController
{
    public CustomersTable $Customers;
    public string $auth = '9';
    public array $exportRows = [];

    public function initialize(): void
    {
        $connection = ConnectionManager::get('customers_flow');
        $this->Customers = new CustomersTable(['connection' => $connection, 'alias' => 'Customers']);
        $this->Contacts = new Table(['connection' => $connection, 'table' => 'contacts']);
        $this->Users = new Table(['connection' => $connection, 'table' => 'users']);
        $this->loadComponent('Flash');
    }

    protected function getCurrentUser(): ?User
    {
        return new User(['id' => 7, 'auth' => $this->auth]);
    }

    protected function setListValue(): void
    {
    }

    public function csv($results = null)
    {
        $this->exportRows = $results;
    }
}
