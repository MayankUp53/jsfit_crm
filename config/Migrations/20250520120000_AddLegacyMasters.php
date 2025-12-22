<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Adds legacy master tables required by the migrated CakePHP 2.x features.
 */
class AddLegacyMasters extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('mt_cids')) {
            $this->table('mt_cids', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false])
                ->addColumn('disp_cd', 'integer', ['null' => false])
                ->addColumn('campaign_id', 'integer', ['null' => false])
                ->addColumn('item_name', 'string', ['limit' => 200, 'null' => false, 'comment' => '名称'])
                ->addColumn('cid', 'string', ['limit' => 200, 'null' => false, 'comment' => '発信番号（ハイフン無し）'])
                ->addColumn('item_memo', 'string', ['limit' => 1000, 'null' => true, 'default' => null])
                ->addColumn('active_flg', 'integer', ['limit' => 1, 'null' => false, 'default' => 0])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('created_user_id', 'integer', ['limit' => 4, 'null' => false])
                ->addColumn('updated', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('updated_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addColumn('deleted', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('deleted_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addIndex(['campaign_id', 'disp_cd'], ['unique' => true, 'name' => 'idx_mt_cids_campaign_disp'])
                ->create();
        }

        if (!$this->hasTable('mt_phonelists')) {
            $this->table('mt_phonelists', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false])
                ->addColumn('disp_cd', 'integer', ['null' => false])
                ->addColumn('item_name', 'string', ['limit' => 200, 'null' => false, 'comment' => '名称'])
                ->addColumn('phone', 'string', ['limit' => 200, 'null' => false, 'comment' => '電話番号（ハイフン無し）'])
                ->addColumn('item_memo', 'string', ['limit' => 1000, 'null' => true, 'default' => null])
                ->addColumn('active_flg', 'integer', ['limit' => 1, 'null' => false, 'default' => 0])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('created_user_id', 'integer', ['limit' => 4, 'null' => false])
                ->addColumn('updated', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('updated_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addColumn('deleted', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('deleted_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addIndex(['disp_cd'], ['unique' => true, 'name' => 'idx_mt_phonelists_disp'])
                ->create();
        }

        if (!$this->hasTable('mt_queues')) {
            $this->table('mt_queues', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false])
                ->addColumn('disp_cd', 'integer', ['null' => false])
                ->addColumn('item_name', 'string', ['limit' => 200, 'null' => false, 'comment' => '名称'])
                ->addColumn('item_memo', 'string', ['limit' => 1000, 'null' => true, 'default' => null])
                ->addColumn('active_flg', 'integer', ['limit' => 1, 'null' => false, 'default' => 0])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('created_user_id', 'integer', ['limit' => 4, 'null' => false])
                ->addColumn('updated', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('updated_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addColumn('deleted', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('deleted_user_id', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
                ->addIndex(['disp_cd'], ['unique' => true, 'name' => 'idx_mt_queues_disp'])
                ->create();
        }

    }
}
