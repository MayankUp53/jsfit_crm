<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RemoveMemoAndActiveFlgFromMtStatuses extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('mt_statuses');
        $table->removeColumn('item_memo')
              ->removeColumn('active_flg')
              ->update();
    }
}
