<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameLimitColumnInCoreSettings extends AbstractMigration
{
    public function change(): void
    {
        // Rename column `limit` to `limit_value` in `core_settings` table
        $table = $this->table('core_settings');
        $table->renameColumn('limit', 'limit_value')
              ->update();
    }
}
