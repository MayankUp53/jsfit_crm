<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Customers Model
 *
 * @method \App\Model\Entity\Customer newEmptyEntity()
 * @method \App\Model\Entity\Customer newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Customer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Customer get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Customer findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Customer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Customer[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Customer|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Customer saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Customer[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Customer[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Customer[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Customer[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 */
class CustomersTable extends Table
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

        $this->setTable('customers');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
           $this->hasMany('Contacts', [
        'foreignKey' => 'customer_id', 
        'dependent' => true,           
    ]);

        // Keep legacy created/updated columns in sync.
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'updated' => 'always',
                ],
            ],
        ]);
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name'); // name is optional

        $validator
            ->scalar('phone')
            ->maxLength('phone', 50)
            ->allowEmptyString('phone');

        $validator
    ->scalar('email')
    ->maxLength('email', 1000)
    ->allowEmptyString('email'); // keep this

        $validator
            ->scalar('fax')
            ->maxLength('fax', 255)
            ->allowEmptyString('fax');

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

        $validator
            ->scalar('client_cd')
            ->maxLength('client_cd', 255)
            ->requirePresence('client_cd', 'create')
            ->notEmptyString('client_cd')
            ->add('client_cd', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'この client_cd は既に存在します'
            ]);

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
     * Build query with search conditions
     * 
     * @param array|null $conditions Search conditions
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function buildSearchQuery(?array $conditions = null): SelectQuery
    {
        $query = $this->find()
            ->where(['deleted IS' => null]);

        if (empty($conditions)) {
            return $query;
        }

        if (!empty($conditions['s_name'])) {
            $query->where(['name LIKE' => '%' . $conditions['s_name'] . '%']);
        }

        if (!empty($conditions['s_phone'])) {
            $query->where(['phone LIKE' => '%' . $conditions['s_phone'] . '%']);
        }

        if (!empty($conditions['s_email'])) {
            $query->where(['email LIKE' => '%' . $conditions['s_email'] . '%']);
        }

        if (!empty($conditions['s_fax'])) {
            $query->where(['fax LIKE' => '%' . $conditions['s_fax'] . '%']);
        }

        if (!empty($conditions['s_id'])) {
            $query->where(['id' => $conditions['s_id']]);
        }

        return $query;
    }
}
