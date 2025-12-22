<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('user_name');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('disp_cd')
            ->maxLength('disp_cd', 20)
            ->requirePresence('disp_cd', 'create')
            ->notEmptyString('disp_cd');

        $validator
            ->scalar('user_name')
            ->maxLength('user_name', 100)
            ->requirePresence('user_name', 'create')
            ->notEmptyString('user_name');

        $validator
            ->integer('auth')
            ->allowEmptyString('auth');

        $validator
            ->scalar('loginid')
            ->maxLength('loginid', 100)
            ->allowEmptyString('loginid');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        $validator
            ->scalar('remark1')
            ->maxLength('remark1', 200)
            ->allowEmptyString('remark1');

        $validator
            ->scalar('remark2')
            ->maxLength('remark2', 200)
            ->allowEmptyString('remark2');

        $validator
            ->scalar('remark3')
            ->maxLength('remark3', 200)
            ->allowEmptyString('remark3');

        $validator
            ->dateTime('created')
            ->allowEmptyDateTime('created');

        $validator
            ->integer('created_user_id')
            ->requirePresence('created_user_id', 'create')
            ->notEmptyString('created_user_id');

        $validator
            ->dateTime('updated')
            ->allowEmptyDateTime('updated');

        $validator
            ->integer('updated_user_id')
            ->allowEmptyString('updated_user_id');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->integer('deleted_user_id')
            ->allowEmptyString('deleted_user_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }

    /**
     * Custom finder for authentication - excludes deleted users
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findAuth(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where(['Users.deleted IS' => null]);
    }
}

