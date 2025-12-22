<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CoreSetting Entity
 */
class CoreSetting extends Entity
{
    protected array $_accessible = [
        'ccid' => true,
        'host_name' => true,
        'dnc_lead_id' => true,
        'voicelog_url' => true,
        'dnc_api_url' => true,
        'ch_api_url' => true,
        'dial_api_url' => true,
        'ameyo_alert_enabled' => true,
        'callback_notice_time' => true,
        'memo' => true,
        'created' => true,
        'created_user_id' => true,
        'updated' => true,
        'updated_user_id' => true,
        'deleted' => true,
        'deleted_user_id' => true,
    ];
}
