<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveOperationInchargeFromCustomers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('customers');
        $table->removeColumn('operation_incharge');
        $table->update();
    }
}
