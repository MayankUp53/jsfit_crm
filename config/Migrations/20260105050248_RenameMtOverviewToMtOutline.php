<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameMtOverviewToMtOutline extends AbstractMigration
{
    public function change(): void
    {
        // Rename table
        $this->table('mt_overview')
             ->rename('mt_outline')
             ->update();
    }
}
