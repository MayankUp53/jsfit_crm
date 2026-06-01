<?php
use Migrations\AbstractMigration;

class CreateSmsHistories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('sms_histories', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'integer', [
                'identity' => true,
                'null' => false,
            ])
            ->addColumn('customer_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('phone', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('send_sms_number', 'string', [
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('sms_text', 'text', [
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
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
            ->addIndex(['customer_id'], ['name' => 'idx_sms_histories_customer_id'])
            ->addIndex(['phone'], ['name' => 'idx_sms_histories_phone'])
            ->create();
    }
}
