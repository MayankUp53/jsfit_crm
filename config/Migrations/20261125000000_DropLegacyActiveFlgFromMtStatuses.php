<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Migration\IrreversibleMigrationException;

class DropLegacyActiveFlgFromMtStatuses extends BaseMigration
{
    public function up(): void
    {
        if (!$this->hasTable('mt_statuses')) {
            return;
        }

        $table = $this->table('mt_statuses');
        if (!$table->hasColumn('active_flg')) {
            return;
        }

        if ($table->hasColumn('active')) {
            $table->removeColumn('active_flg')->update();
        } else {
            $table->renameColumn('active_flg', 'active')->update();
        }
    }

    public function down(): void
    {
        throw new IrreversibleMigrationException('The original mt_statuses.active_flg values cannot be restored.');
    }
}
