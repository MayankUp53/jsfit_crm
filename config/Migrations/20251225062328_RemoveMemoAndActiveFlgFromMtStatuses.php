<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RemoveMemoAndActiveFlgFromMtStatuses extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('mt_statuses');
        $modified = false;
        $hasActiveFlg = $table->hasColumn('active_flg');

        if (!$table->hasColumn('active') && $table->hasColumn('active_flg')) {
            $table->addColumn('active', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Status is active (1) or inactive (0)',
            ]);
            $modified = true;
        }
        if ($modified) {
            $table->update();
        }
        if ($hasActiveFlg && $table->hasColumn('active')) {
            $this->execute('UPDATE mt_statuses SET active = COALESCE(active_flg, 0)');
        }

        $modified = false;
        if ($table->hasColumn('item_memo')) {
            $table->removeColumn('item_memo');
            $modified = true;
        }
        if ($table->hasColumn('active_flg')) {
            $table->removeColumn('active_flg');
            $modified = true;
        }
        if ($modified) {
            $table->update();
        }
    }
}
