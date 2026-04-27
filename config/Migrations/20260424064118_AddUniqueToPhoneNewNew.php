<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddUniqueToPhoneNewNew extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('mt_phonelists');

        $table
            ->addIndex(['phone'], [
                'unique' => true,
                'name' => 'UNIQUE_PHONE'
            ])
            ->update();
    }
}