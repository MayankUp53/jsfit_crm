<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $exists = $this->hasTable('users');
        if (!$exists) {
            $this->table('users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('id', 'integer', [
                    'autoIncrement' => true,
                    'limit' => 11,
                    'null' => false,
                    'comment' => 'ユーザID',
                ])
                ->addColumn('disp_cd', 'string', [
                    'limit' => 20,
                    'null' => false,
                ])
                ->addColumn('user_name', 'string', [
                    'limit' => 100,
                    'null' => false,
                    'comment' => '氏名',
                ])
                ->addColumn('auth', 'integer', [
                    'limit' => 1,
                    'null' => true,
                    'default' => null,
                    'comment' => '権限',
                ])
                ->addColumn('loginid', 'string', [
                    'limit' => 100,
                    'null' => true,
                    'default' => null,
                    'comment' => 'ログインＩＤ',
                ])
                ->addColumn('password', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'default' => null,
                    'comment' => 'パスワード',
                ])
                ->addColumn('remark1', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => '備考１',
                ])
                ->addColumn('remark2', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => '備考２',
                ])
                ->addColumn('remark3', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => '備考３',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'default' => null,
                ])
                ->addColumn('created_user_id', 'integer', [
                    'limit' => 4,
                    'null' => false,
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'default' => null,
                ])
                ->addColumn('updated_user_id', 'integer', [
                    'limit' => 4,
                    'null' => true,
                    'default' => null,
                ])
                ->addColumn('deleted', 'datetime', [
                    'null' => true,
                    'default' => null,
                ])
                ->addColumn('deleted_user_id', 'integer', [
                    'limit' => 4,
                    'null' => true,
                    'default' => null,
                ])
                ->create();
        }

        $row = $this->fetchRow('SELECT id FROM users LIMIT 1');
        if (!$row) {
            $password = password_hash('Admin@123', PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');
            $this->execute(sprintf(
                "INSERT INTO users (disp_cd, user_name, auth, loginid, password, remark1, remark2, remark3, created, created_user_id, updated, updated_user_id, deleted, deleted_user_id) VALUES ('%s','%s',%d,'%s','%s',NULL,NULL,NULL,'%s',%d,'%s',NULL,NULL,NULL)",
                '0001',
                'System Admin',
                99,
                'admin',
                $password,
                $now,
                0,
                $now
            ));
        }
    }
}
