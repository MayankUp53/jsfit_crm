<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ModifyEmailColumnInCustomers extends AbstractMigration
{
    public function change(): void
    {
        // Change the 'email' column to VARCHAR(1000) and allow NULL
        $table = $this->table('customers');
        $table->changeColumn('email', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '複数メールを ; 区切りで格納'
        ])->update();
    }
}