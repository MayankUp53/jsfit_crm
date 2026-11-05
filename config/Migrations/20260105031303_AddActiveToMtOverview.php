<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddActiveToMtOverview extends AbstractMigration
{
    public function change(): void
    {
        $tableName = $this->hasTable('mt_overview') ? 'mt_overview' : ($this->hasTable('mt_outline') ? 'mt_outline' : null);
        if ($tableName !== null) {
            $table = $this->table($tableName);
            if (!$table->hasColumn('active')) {
                $table->addColumn('active', 'integer', [
                    'default' => null,
                    'null' => true,
                    'after' => 'item_name',
                ])->update();
            }
        }
    }
}
