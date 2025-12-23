<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveOperationInchargeFromCustomersAndContacts extends AbstractMigration
{
    public function change(): void
    {
    

        // Remove from contacts
        $this->table('contacts')
            ->removeColumn('operation_incharge')
            ->update();
    }
}
