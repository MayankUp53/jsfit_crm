<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MtOutlineTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('mt_outline');
        $this->setPrimaryKey('id');
        $this->setDisplayField('item_name');
    }

public function validationDefault(Validator $validator): Validator
{
    // item_name validation
    $validator
        ->scalar('item_name')
        ->maxLength('item_name', 200, '名称は200文字以内で入力してください。')
        ->requirePresence('item_name', 'create')
        ->notEmptyString('item_name', '名称は必須です。');

    // disp_cd validation (optional)
    $validator
        ->integer('disp_cd', '表示順は整数で入力してください。')
        ->allowEmptyString('disp_cd');

    // active validation
    $validator
        ->integer('active', '状態は整数で入力してください。')
        ->requirePresence('active', 'create', '状態は必須です。')
        ->notEmptyString('active', '状態を選択してください。')
        ->inList('active', [0, 1], '状態は「有効」または「無効」を選択してください。');

    return $validator;
}

}
