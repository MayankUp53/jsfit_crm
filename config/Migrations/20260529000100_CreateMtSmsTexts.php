<?php
use Migrations\AbstractMigration;

class CreateMtSmsTexts extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('mt_sms_texts', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'integer', [
                'identity' => true,
                'null' => false,
            ])
            ->addColumn('disp_cd', 'integer', [
                'null' => false,
            ])
            ->addColumn('campaign_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('item_name', 'string', [
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('sms_text', 'text', [
                'null' => false,
            ])
            ->addColumn('item_memo', 'string', [
                'limit' => 1000,
                'null' => true,
            ])
            ->addColumn('active_flg', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
            ])
            ->addColumn('created_user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('updated', 'datetime', [
                'null' => true,
            ])
            ->addColumn('updated_user_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('deleted', 'datetime', [
                'null' => true,
            ])
            ->addColumn('deleted_user_id', 'integer', [
                'null' => true,
            ])
            ->addIndex(['campaign_id'], ['name' => 'idx_mt_sms_texts_campaign_id'])
            ->addIndex(['campaign_id', 'disp_cd'], ['unique' => true, 'name' => 'uq_mt_sms_texts_campaign_disp'])
            ->create();
    }
}
