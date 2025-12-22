<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMissingCustomerContactColumns extends AbstractMigration
{
    public function up(): void
    {
        // Customers table: add name/email/fax columns if missing.
        if ($this->hasTable('customers')) {
            $customers = $this->table('customers');
            $modified = false;

            if (!$customers->hasColumn('name')) {
                $customers->addColumn('name', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'default' => null,
                    'after' => 'addr3',
                    'comment' => '氏名(固定列)',
                ]);
                $modified = true;
            }
            if (!$customers->hasColumn('email')) {
                $customers->addColumn('email', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'default' => null,
                    'after' => 'name_kn',
                    'comment' => 'メールアドレス',
                ]);
                $modified = true;
            }
            if (!$customers->hasColumn('fax')) {
                $customers->addColumn('fax', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'default' => null,
                    'after' => 'phone',
                    'comment' => 'FAX番号',
                ]);
                $modified = true;
            }

            if ($modified) {
                $customers->update();
                $this->execute('UPDATE customers SET name = COALESCE(name, name_kj)');
            }
        }

        // Contacts table: add phone/is_done columns if missing.
        if ($this->hasTable('contacts')) {
            $contacts = $this->table('contacts');
            $modified = false;
            if (!$contacts->hasColumn('phone')) {
                $contacts->addColumn('phone', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'after' => 'detail',
                    'comment' => '連絡電話番号',
                ]);
                $modified = true;
            }
            if (!$contacts->hasColumn('is_done')) {
                $contacts->addColumn('is_done', 'integer', [
                    'limit' => 4,
                    'null' => true,
                    'default' => 0,
                    'after' => 'callback_datetime',
                    'comment' => '再コール完了フラグ',
                ]);
                $modified = true;
            }
            if ($modified) {
                $contacts->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('customers')) {
            $customers = $this->table('customers');
            foreach (['fax', 'email', 'name'] as $column) {
                if ($customers->hasColumn($column)) {
                    $customers->removeColumn($column);
                }
            }
            $customers->update();
        }

        if ($this->hasTable('contacts')) {
            $contacts = $this->table('contacts');
            foreach (['is_done', 'phone'] as $column) {
                if ($contacts->hasColumn($column)) {
                    $contacts->removeColumn($column);
                }
            }
            $contacts->update();
        }
    }
}
