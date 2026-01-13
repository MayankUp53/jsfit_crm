<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMemoToCustomers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('customers');
        $table->addColumn('memo', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 255,
            'comment' => 'Memo field for customer'
        ])
        ->update();
    }
}
