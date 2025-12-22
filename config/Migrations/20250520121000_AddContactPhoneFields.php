<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddContactPhoneFields extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('contacts')) {
            $table = $this->table('contacts');
            if (!$table->hasColumn('phone')) {
                $table->addColumn('phone', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => '電話番号（再コール用）',
                ]);
            }
            if (!$table->hasColumn('is_done')) {
                $table->addColumn('is_done', 'integer', [
                    'limit' => 4,
                    'null' => true,
                    'default' => 0,
                    'comment' => 'コールバックステータス',
                ]);
            }
            $table->update();
        }
    }
}
