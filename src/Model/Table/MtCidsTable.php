<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MtCidsTable
 *
 * Outbound caller ID master.
 */
class MtCidsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mt_cids');
        $this->setDisplayField('item_name');
        $this->setPrimaryKey('id');

        // Keep created/updated in sync with legacy column names.
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
            ->notEmptyString('cid', '発信番号は必須です。');

        $validator
            ->allowEmptyString('item_memo');

        $validator
            ->integer('active_flg')
            ->notEmptyString('active_flg', '有効無効を選択してください。');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
