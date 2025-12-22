<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Callbacks Model
 */
class CallbacksTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('callbacks');
        $this->setDisplayField('phone');
        $this->setPrimaryKey('id');
        
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }
}

