<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MtStatuses Model
 */
class MtStatusesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('mt_statuses');
        $this->setDisplayField('item_name');
        $this->setPrimaryKey('id');
    }

 public function validationDefault(Validator $validator): Validator
{
    $validator
        ->scalar('item_name')
        ->maxLength('item_name', 200)
        ->requirePresence('item_name', 'create')
        ->notEmptyString('item_name', 'Name is required');

    $validator
        ->integer('disp_cd')
        ->allowEmptyString('disp_cd'); // optional if you allow blank

    return $validator;
}

}

