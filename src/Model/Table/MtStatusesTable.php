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
            ->notEmptyString('item_name', '名称は必須です。入力してください。');

        $validator
            ->integer('active_flg')
            ->requirePresence('active_flg', 'create')
            ->notEmptyString('active_flg', '有効無効は必須です。選択してください。');

        return $validator;
    }
}

