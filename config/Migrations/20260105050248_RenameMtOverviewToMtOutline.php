<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameMtOverviewToMtOutline extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('mt_overview') && !$this->hasTable('mt_outline')) {
            $this->table('mt_overview')->rename('mt_outline')->update();
        }
    }
}
