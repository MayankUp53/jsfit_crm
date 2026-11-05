<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameLimitColumnInCoreSettings extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('core_settings');
        if ($table->hasColumn('limit') && !$table->hasColumn('limit_value')) {
            $table->renameColumn('limit', 'limit_value')->update();
        }
    }
}
