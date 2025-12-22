<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddClientColumnsToCustomers extends AbstractMigration
{
    public function change(): void
    {
        // Modify existing table 'customers'
        $table = $this->table('customers');

        $table->addColumn('client_cd', 'string', [
                'limit' => 50,
                'null' => true, 
                'default' => ''
            ])
            ->addColumn('company_name', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null
            ])
            ->addColumn('type_of_number', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null
            ])
            ->update();
    }
}
