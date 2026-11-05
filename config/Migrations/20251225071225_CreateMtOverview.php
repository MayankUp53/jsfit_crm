<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateMtOverview extends BaseMigration
{
    public function change(): void
    {
        if (!$this->hasTable('mt_overview') && !$this->hasTable('mt_outline')) {
            $table = $this->table('mt_overview');
            $table->addColumn('item_name', 'string', ['limit' => 255, 'null' => false])
                  ->addColumn('disp_cd', 'integer', ['null' => true])
                  ->addColumn('created', 'datetime', ['null' => true])
                  ->addColumn('created_user_id', 'integer', ['null' => true])
                  ->addColumn('updated', 'datetime', ['null' => true])
                  ->addColumn('updated_user_id', 'integer', ['null' => true])
                  ->addColumn('deleted', 'datetime', ['null' => true])
                  ->addColumn('deleted_user_id', 'integer', ['null' => true])
                  ->create();
        }
    }
}
