<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MtPhonelistsTable
 */
class MtPhonelistsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mt_phonelists');
        $this->setDisplayField('item_name');
        $this->setPrimaryKey('id');

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
        // item_name
        $validator
            ->scalar('item_name')
            ->maxLength('item_name', 200)
            ->notEmptyString('item_name', '名称は必須です。');

        // phone (NO RESTRICTION - only required)
        $validator
            ->scalar('phone')
            ->notEmptyString('phone', '電話番号は必須です。');

        // item_memo
        $validator
            ->scalar('item_memo')
            ->allowEmptyString('item_memo');

        // active_flg
        $validator
            ->integer('active_flg')
            ->notEmptyString('active_flg', '有効無効を選択してください。');

        // customer_cd (if used in import)
        $validator
            ->scalar('customer_cd')
            ->allowEmptyString('customer_cd');

        // disp_cd (if used in import)
        $validator
            ->integer('disp_cd')
            ->allowEmptyString('disp_cd');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // Unique phone only
        $rules->add(
            $rules->isUnique(['phone']),
            ['errorField' => 'phone', 'message' => '電話番号が重複しています。']
        );

        return $rules;
    }
}