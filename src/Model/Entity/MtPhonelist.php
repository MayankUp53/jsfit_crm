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
         '*' => true,
    'id' => false
    ];
}
