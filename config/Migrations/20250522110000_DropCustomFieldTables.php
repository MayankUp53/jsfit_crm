<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DropCustomFieldTables extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('custom_fields')) {
            $this->table('custom_fields')->drop()->save();
        }
        if ($this->hasTable('custom_entities')) {
            $this->table('custom_entities')->drop()->save();
        }
    }

    public function down(): void
    {
        // Intentionally left blank; custom field infrastructure has been removed.
    }
}
