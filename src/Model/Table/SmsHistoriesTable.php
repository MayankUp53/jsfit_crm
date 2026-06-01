<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SmsHistoriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sms_histories');
        $this->setDisplayField('phone');
        $this->setPrimaryKey('id');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'updated' => 'always',
                ],
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('customer_id')
            ->requirePresence('customer_id', 'create')
            ->notEmptyString('customer_id');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 50)
            ->notEmptyString('phone');

        $validator
            ->scalar('send_sms_number')
            ->maxLength('send_sms_number', 200)
            ->notEmptyString('send_sms_number');

        $validator
            ->scalar('sms_text')
            ->notEmptyString('sms_text');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->inList('status', ['success', 'failed'])
            ->notEmptyString('status');

        $validator
            ->integer('created_user_id')
            ->notEmptyString('created_user_id');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('customer_id', 'Customers'), ['errorField' => 'customer_id']);

        return $rules;
    }
}
