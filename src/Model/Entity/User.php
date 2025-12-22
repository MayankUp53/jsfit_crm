<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $disp_cd
 * @property string $user_name
 * @property int|null $auth
 * @property string|null $loginid
 * @property string|null $password
 * @property string|null $remark1
 * @property string|null $remark2
 * @property string|null $remark3
 * @property \Cake\I18n\DateTime|null $created
 * @property int $created_user_id
 * @property \Cake\I18n\DateTime|null $updated
 * @property int|null $updated_user_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property int|null $deleted_user_id
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'disp_cd' => true,
        'user_name' => true,
        'auth' => true,
        'loginid' => true,
        'password' => true,
        'remark1' => true,
        'remark2' => true,
        'remark3' => true,
        'created' => true,
        'created_user_id' => true,
        'updated' => true,
        'updated_user_id' => true,
        'deleted' => true,
        'deleted_user_id' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];
}

