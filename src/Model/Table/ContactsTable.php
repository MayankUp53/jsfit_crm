<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Contacts Model
 *
 * @property \App\Model\Table\CustomersTable&\Cake\ORM\Association\BelongsTo $Customers
 *
 * @method \App\Model\Entity\Contact newEmptyEntity()
 * @method \App\Model\Entity\Contact newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Contact> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Contact get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Contact findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Contact patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Contact> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Contact|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Contact saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Contact>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Contact> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Contact>|false deleteMany(iterable $entities, bool $breakOnFailure = false)
 * @method iterable<\App\Model\Entity\Contact> deleteManyOrFail(iterable $entities, bool $breakOnFailure = false)
 */
class ContactsTable extends Table
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

        $this->setTable('contacts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER',
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
            ->integer('customer_id')
            ->requirePresence('customer_id', 'create')
            ->notEmptyString('customer_id');

             $validator
        ->allowEmptyString('summary');

    $validator
        ->requirePresence('status', 'create')
        ->notEmptyString('status', 'ステータスを選択してください。');


    $validator
        ->allowEmptyString('remark');

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
        $rules->add($rules->existsIn('customer_id', 'Customers'), ['errorField' => 'customer_id']);

        return $rules;
    }

    /**
     * Build search query from conditions
     *
     * @param array $conditions Search conditions
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function buildSearchQuery(array $conditions = []): SelectQuery
    {
        $query = $this->find()
            ->contain(['Customers'])
            ->where(['Contacts.deleted IS' => null]);

        if (!empty($conditions['s_customer_phone'])) {
            $phone = $conditions['s_customer_phone'];
            $query->where(['Customers.phone LIKE' => '%' . $phone . '%']);
        }

        if (!empty($conditions['s_customer_name'])) {
            $query->where(['Customers.name LIKE' => '%' . $conditions['s_customer_name'] . '%']);
        }

        if (!empty($conditions['s_customer_email'])) {
            $query->where(['Customers.email LIKE' => '%' . $conditions['s_customer_email'] . '%']);
        }

        if (!empty($conditions['s_customer_id'])) {
            $query->where(['Customers.id' => $conditions['s_customer_id']]);
        }

        // Created user search
        if (!empty($conditions['s_created_user_id']) && is_array($conditions['s_created_user_id'])) {
            $query->where(['Contacts.created_user_id IN' => $conditions['s_created_user_id']]);
        }

        // Status search
        if (!empty($conditions['s_status']) && is_array($conditions['s_status'])) {
            $query->where(['Contacts.status IN' => $conditions['s_status']]);
        }

        // Date from create
        if (!empty($conditions['s_date_from_create'])) {
            $query->where(['Contacts.created >=' => $conditions['s_date_from_create']]);
        }

        // Date to create
        if (!empty($conditions['s_date_to_create'])) {
            $query->where(['Contacts.created <=' => $conditions['s_date_to_create'] . ' 23:59:59']);
        }

        // Date from update
        if (!empty($conditions['s_date_from_update'])) {
            $query->where(['Contacts.updated >=' => $conditions['s_date_from_update']]);
        }

        // Date to update
        if (!empty($conditions['s_date_to_update'])) {
            $query->where(['Contacts.updated <=' => $conditions['s_date_to_update'] . ' 23:59:59']);
        }

        $query->orderBy(['Contacts.updated' => 'DESC', 'Contacts.created' => 'DESC']);

        return $query;
    }
}
