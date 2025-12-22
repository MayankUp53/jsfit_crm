<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Contact Entity
 *
 * @property int $id
 * @property int $customer_id
 * @property int $status
 * @property string|null $summary
 * @property string|null $detail
 * @property string|null $remark
 * @property \Cake\I18n\DateTime|null $callback_datetime
 * @property string|null $queue_name
 * @property \Cake\I18n\DateTime|null $created
 * @property int $created_user_id
 * @property \Cake\I18n\DateTime|null $updated
 * @property int|null $updated_user_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property int|null $deleted_user_id
 *
 * @property \App\Model\Entity\Customer $customer
 */
class Contact extends Entity
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
        '*' => true,
        'id' => false
    ];
}
