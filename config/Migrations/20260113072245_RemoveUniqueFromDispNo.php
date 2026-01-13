<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveUniqueFromDispNo extends AbstractMigration
{
    public function change(): void
    {
        // Drop the UNIQUE index on disp_no
        $table = $this->table('customers');
        $table->removeIndex(['disp_no'])->update();
    }
}
