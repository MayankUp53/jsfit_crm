<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCustomerNewFields extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('customers')) {
            $table = $this->table('customers');
            if (!$table->hasColumn('client_cd')) {
                $table->addColumn('client_cd', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Client CD',
                ]);
            }
            if (!$table->hasColumn('company_branch_name')) {
                $table->addColumn('company_branch_name', 'string', [
                    'limit' => 200,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Company / branch name',
                ]);
            }
            if (!$table->hasColumn('type_of_number')) {
                $table->addColumn('type_of_number', 'string', [
                    'limit' => 100,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Type of number',
                ]);
            }
            if (!$table->hasColumn('fax')) {
                $table->addColumn('fax', 'string', [
                    'limit' => 50,
                    'null' => true,
                    'default' => null,
                    'comment' => 'FAX',
                ]);
            }
            if (!$table->hasColumn('email')) {
                $table->addColumn('email', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Email address',
                ]);
            }
            $table->update();
        }
    }
}
