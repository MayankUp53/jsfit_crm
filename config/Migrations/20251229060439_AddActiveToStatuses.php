<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddActiveToStatuses extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mt_statuses');
        if (!$table->hasColumn('active')) {
            $table->addColumn('active', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Status is active (1) or inactive (0)',
            ]);
            $table->update();

            if ($table->hasColumn('active_flg')) {
                $this->execute('UPDATE mt_statuses SET active = COALESCE(active_flg, 0)');
            }
        }
    }
}
