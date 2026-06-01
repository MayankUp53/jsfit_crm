<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class MtSmsText extends Entity
{
    protected array $_accessible = [
        'disp_cd' => true,
        'campaign_id' => true,
        'item_name' => true,
        'sms_text' => true,
        'item_memo' => true,
        'active_flg' => true,
        'created' => true,
        'created_user_id' => true,
        'updated' => true,
        'updated_user_id' => true,
        'deleted' => true,
        'deleted_user_id' => true,
    ];
}
