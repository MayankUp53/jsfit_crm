<?php

use Migrations\AbstractMigration;

class AddSendSmsApiUrlToCoreSettings extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('core_settings');
        if ($table->hasColumn('sendSMS_api_url')) {
            $table->removeColumn('sendSMS_api_url')->update();
        }
    }
}