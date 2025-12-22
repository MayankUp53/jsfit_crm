<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MtPhonelist Entity
 *
 * Represents a phone book entry for quick dialing.
 */
class MtPhonelist extends Entity
{
    /**
     * Mass assignable fields.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'disp_cd' => true,
        'item_name' => true,
        'phone' => true,
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
