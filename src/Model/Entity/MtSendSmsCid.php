<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MtSendSmsCid Entity
 *
 * SMS CID configuration entity.
 */
class MtSendSmsCid extends Entity
{
    /**
     * Mass assignable fields
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'disp_cd' => true,
        'campaign_id' => true,
        'item_name' => true,
        'cid' => true,
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