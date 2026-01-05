<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddActiveToMtOverview extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mt_overview');
        $table->addColumn('active', 'integer', [
            'default' => null, // no default
            'null' => true,
            'after' => 'item_name', // optional
        ])
        ->update();
    }
}
