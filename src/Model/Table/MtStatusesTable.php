<?php
declare(strict_types=1);

namespace App\Model\Table;

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

        // Optional: add timestamp behavior if needed
        $this->addBehavior('Timestamp');
    }

    public function activeField(): string
    {
        return $this->getSchema()->hasColumn('active_flg') ? 'active_flg' : 'active';
    }

    public function validationDefault(Validator $validator): Validator
    {
        // 名称 (item_name)
        $validator
            ->scalar('item_name')
            ->maxLength('item_name', 200, '名称は200文字以内で入力してください。')
            ->requirePresence('item_name', 'create', '名称は必須です。')
            ->notEmptyString('item_name', '名称は必須です。');

        // 表示順 (disp_cd)
        $validator
            ->integer('disp_cd', '表示順は整数で入力してください。')
            ->allowEmptyString('disp_cd'); // optional if blank is allowed

        $activeField = $this->activeField();
        $validator
            ->integer($activeField, '状態は整数で入力してください。')
            ->requirePresence($activeField, 'create', '状態は必須です。')
            ->notEmptyString($activeField, '状態を選択してください。')
            ->inList($activeField, [0, 1], '状態は「有効」または「無効」を選択してください。');

        return $validator;
    }
}
