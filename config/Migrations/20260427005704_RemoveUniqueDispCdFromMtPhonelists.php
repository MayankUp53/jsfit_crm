<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveUniqueDispCdFromMtPhonelists extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('mt_phonelists');

        // remove unique index
        $table->removeIndex(['disp_cd'])->update();
    }
}