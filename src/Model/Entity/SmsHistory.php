<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SmsHistory extends Entity
{
    protected array $_accessible = [
        'customer_id' => true,
        'phone' => true,
        'send_sms_number' => true,
        'sms_text' => true,
        'status' => true,
        'created' => true,
        'created_user_id' => true,
        'updated' => true,
        'updated_user_id' => true,
        'deleted' => true,
        'deleted_user_id' => true,
    ];
}
