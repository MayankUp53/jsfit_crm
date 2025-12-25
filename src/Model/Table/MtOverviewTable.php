<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MtOverviewTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('mt_overview');
        $this->setPrimaryKey('id');
        $this->setDisplayField('item_name');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('item_name', 'create')
            ->notEmptyString('item_name', '名称は必須です。');

        return $validator;
    }
}
