<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddUniqueConstraintToClientCd extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('customers');
        $duplicates = $this->fetchAll(
            "SELECT client_cd FROM customers GROUP BY client_cd HAVING COUNT(*) > 1 LIMIT 1"
        );
        if (!empty($duplicates)) {
            return;
        }
        $table->addIndex(['client_cd'], ['unique' => true, 'name' => 'unique_client_cd'])
              ->update();
    }
}
