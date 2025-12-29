<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddUniqueConstraintToClientCd extends AbstractMigration
{
    public function change(): void
    {
        // Add unique index on client_cd
        $table = $this->table('customers');
        $table->addIndex(['client_cd'], ['unique' => true, 'name' => 'unique_client_cd'])
              ->update();
    }
}
