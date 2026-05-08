<?php

use Migrations\AbstractMigration;

class AddSendSmsApiUrlToCoreSettings extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('core_settings');

        $table->addColumn('sendSMS_api_url', 'string', [
            'limit' => 255,
            'null' => true,
            'default' => null,
            'after' => 'id'
        ]);

        $table->update();
    }
}