<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveUniqueDispCdFromMtPhonelists extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('mt_phonelists')) {
            return;
        }
        $table = $this->table('mt_phonelists');
        if ($table->hasIndex(['disp_cd'])) {
            $table->removeIndex(['disp_cd'])->update();
        }
    }
}