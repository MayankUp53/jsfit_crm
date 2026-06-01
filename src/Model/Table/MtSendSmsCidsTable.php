<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MtSendSmsCidsTable
 *
 * SMS CID master table.
 */
class MtSendSmsCidsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mt_sendSms_cid');
        $this->setDisplayField('item_name');
        $this->setPrimaryKey('id');

        // Timestamp behavior (legacy column names)
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
            ->integer('disp_cd')
            ->allowEmptyString('disp_cd');

        $validator
            ->integer('campaign_id')
            ->notEmptyString('campaign_id', 'キャンペーンIDは必須です。');

        $validator
            ->scalar('item_name')
            ->maxLength('item_name', 200)
            ->notEmptyString('item_name', '名称は必須です。');

        $validator
            ->scalar('cid')
            ->maxLength('cid', 200)
            ->notEmptyString('cid', 'CIDは必須です。');

        $validator
            ->scalar('item_memo')
            ->maxLength('item_memo', 1000)
            ->allowEmptyString('item_memo');

        $validator
            ->integer('active_flg')
            ->notEmptyString('active_flg', '有効無効を選択してください。');

        $validator
            ->integer('created_user_id')
            ->notEmptyString('created_user_id');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
