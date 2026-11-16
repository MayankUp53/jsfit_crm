<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use PHPUnit\Framework\TestCase;

class UsersLoginidTest extends TestCase
{
    private Connection $connection;
    private UsersTable $users;

    protected function setUp(): void
    {
        parent::setUp();
        ConnectionManager::setConfig('users_loginid', [
            'className' => Connection::class, 'driver' => Sqlite::class, 'database' => ':memory:',
        ]);
        $this->connection = ConnectionManager::get('users_loginid');
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, disp_cd TEXT, user_name TEXT, loginid TEXT, created_user_id INTEGER, deleted DATETIME)');
        $this->users = new UsersTable(['connection' => $this->connection, 'alias' => 'Users']);
        $this->connection->insert('users', ['id' => 1, 'disp_cd' => '1', 'user_name' => 'Same Name', 'loginid' => 'operator', 'created_user_id' => 1]);
    }

    protected function tearDown(): void
    {
        $this->connection->getDriver()->disconnect();
        ConnectionManager::drop('users_loginid');
        parent::tearDown();
    }

    private function newUser(string $loginid)
    {
        return $this->users->newEntity([
            'disp_cd' => '2', 'user_name' => 'Same Name', 'loginid' => $loginid, 'created_user_id' => 1,
        ]);
    }

    public function testCreateRejectsActiveDuplicate(): void
    {
        $user = $this->newUser('operator');
        self::assertFalse($this->users->save($user));
        self::assertNotEmpty($user->getError('loginid'));
        self::assertSame(1, $this->users->find()->count());
    }

    public function testEditAllowsOwnLoginidButRejectsAnotherActiveLoginid(): void
    {
        $user = $this->users->get(1);
        $user->user_name = 'Updated Name';
        self::assertNotFalse($this->users->save($user));

        $other = $this->newUser('other');
        self::assertNotFalse($this->users->save($other));
        $other->loginid = 'operator';
        self::assertFalse($this->users->save($other));
        self::assertNotEmpty($other->getError('loginid'));
        self::assertSame('other', $this->users->get($other->id)->loginid);
    }

    public function testSoftDeletedLoginidCanBeReusedWithoutChangingOldUser(): void
    {
        $old = $this->users->get(1);
        $old->deleted = '2026-11-16 10:00:00';
        self::assertNotFalse($this->users->save($old));
        $new = $this->newUser('operator');
        self::assertNotFalse($this->users->save($new));
        self::assertNotEquals($old->id, $new->id);
        self::assertNotNull($this->users->get(1)->deleted);
        self::assertSame($new->id, $this->users->find('auth')->where(['loginid' => 'operator'])->firstOrFail()->id);
    }

    public function testRestoreRejectsReusedLoginid(): void
    {
        $this->connection->update('users', ['deleted' => '2026-11-16 10:00:00'], ['id' => 1]);
        self::assertNotFalse($this->users->save($this->newUser('operator')));
        $old = $this->users->get(1);
        $old->deleted = null;
        self::assertFalse($this->users->save($old));
        self::assertNotEmpty($old->getError('loginid'));
        self::assertNotNull($this->users->get(1)->deleted);
    }

    public function testDuplicateDisplayNameIsAllowed(): void
    {
        self::assertNotFalse($this->users->save($this->newUser('other')));
        self::assertSame(2, $this->users->find()->where(['user_name' => 'Same Name'])->count());
    }

    public function testLoginidIsRequiredOnCreateAndCannotBeClearedOnEdit(): void
    {
        $missing = $this->users->newEntity(['disp_cd' => '2', 'user_name' => 'Name', 'created_user_id' => 1]);
        self::assertNotEmpty($missing->getError('loginid'));
        $blank = $this->newUser('');
        self::assertFalse($this->users->save($blank));
        self::assertNotEmpty($blank->getError('loginid'));
        $edited = $this->users->patchEntity($this->users->get(1), ['loginid' => '']);
        self::assertFalse($this->users->save($edited));
        self::assertNotEmpty($edited->getError('loginid'));
    }
}
