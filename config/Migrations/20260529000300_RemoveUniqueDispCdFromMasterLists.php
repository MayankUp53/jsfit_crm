<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveUniqueDispCdFromMasterLists extends AbstractMigration
{
    public function up(): void
    {
        $this->removeIndexIfExists('mt_cids', 'idx_mt_cids_campaign_disp');
        $this->removeIndexIfExists('mt_sms_texts', 'uq_mt_sms_texts_campaign_disp');
    }

    public function down(): void
    {
        $mtCids = $this->table('mt_cids');
        if (!$mtCids->hasIndexByName('idx_mt_cids_campaign_disp')) {
            $mtCids
                ->addIndex(['campaign_id', 'disp_cd'], [
                    'unique' => true,
                    'name' => 'idx_mt_cids_campaign_disp',
                ])
                ->update();
        }

        $mtSmsTexts = $this->table('mt_sms_texts');
        if (!$mtSmsTexts->hasIndexByName('uq_mt_sms_texts_campaign_disp')) {
            $mtSmsTexts
                ->addIndex(['campaign_id', 'disp_cd'], [
                    'unique' => true,
                    'name' => 'uq_mt_sms_texts_campaign_disp',
                ])
                ->update();
        }
    }

    protected function removeIndexIfExists(string $tableName, string $indexName): void
    {
        $table = $this->table($tableName);

        if ($table->hasIndexByName($indexName)) {
            $table->removeIndexByName($indexName)->update();
        }
    }
}
