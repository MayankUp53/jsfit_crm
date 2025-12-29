<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddActiveToStatuses extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mt_statuses');
        
        // Add 'active' column
        $table->addColumn('active', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Status is active (1) or inactive (0)'
        ]);
        
        $table->update();
    }
}
