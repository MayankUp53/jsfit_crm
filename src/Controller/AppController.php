<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Component\FlashComponent;
use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Psr\Http\Message\ResponseInterface;

/**
 * Base controller for the application.
 */
class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication', [
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login',
            ],
            'loginRedirect' => [
                'controller' => 'Homes',
                'action' => 'index',
            ],
            'logoutRedirect' => [
                'controller' => 'Users',
                'action' => 'login',
            ],
        ]);
    }

    public function beforeFilter(EventInterface $event): ?ResponseInterface
    {
        parent::beforeFilter($event);
        $this->response = $this->response->withDisabledCache();

        return null;
    }

    protected function getCurrentUser(): ?\App\Model\Entity\User
    {
        $identity = $this->Authentication->getIdentity();
        return $identity ? $identity->getOriginalData() : null;
    }

    /**
     * Only return the built-in search definitions now that dynamic fields are gone.
     */
    protected function getSearchFields(string $entityName): array
    {
        return $this->systemSearchFields($entityName);
    }

    protected function applySearchConditions(SelectQuery $query, array $fields, array $searchData): SelectQuery
    {
        foreach ($fields as $field) {
            $key = $field['key'];
            $column = $field['column'];
            $type = $field['type'] ?? 'text';
            $value = $searchData[$key] ?? null;

            if ($type === 'date_range') {
                if (!is_array($value)) {
                    continue;
                }
                $from = $value['from'] ?? null;
                $to = $value['to'] ?? null;
                if ($from !== null && $from !== '') {
                    $query->where([$column . ' >=' => $from]);
                }
                if ($to !== null && $to !== '') {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$to)) {
                        $to .= ' 23:59:59';
                    }
                    $query->where([$column . ' <=' => $to]);
                }
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            switch ($type) {
                case 'number':
                    if (is_numeric($value)) {
                        $query->where([$column => $value]);
                    }
                    break;
                case 'select':
                case 'date':
                    $query->where([$column => $value]);
                    break;
                default:
                    $query->where([$column . ' LIKE' => '%' . $value . '%']);
            }
        }

        return $query;
    }

    protected function systemSearchFields(string $entityName): array
    {
        $userOptions = $this->getUserOptions();

        if ($entityName === 'customers') {
            return [
                ['key' => 'sys_customer_name', 'label' => '氏名', 'column' => 'Customers.name', 'type' => 'text'],
                ['key' => 'sys_customer_phone', 'label' => '電話番号', 'column' => 'Customers.phone', 'type' => 'text'],
                ['key' => 'sys_customer_email', 'label' => 'メール', 'column' => 'Customers.email', 'type' => 'text'],
                ['key' => 'sys_customer_fax', 'label' => 'FAX', 'column' => 'Customers.fax', 'type' => 'text'],
                ['key' => 'sys_customer_id', 'label' => '顧客ID', 'column' => 'Customers.id', 'type' => 'number'],
                ['key' => 'sys_customer_created', 'label' => '登録日', 'column' => 'Customers.created', 'type' => 'date_range'],
                ['key' => 'sys_customer_updated', 'label' => '更新日', 'column' => 'Customers.updated', 'type' => 'date_range'],
                ['key' => 'sys_customer_created_user', 'label' => '登録者', 'column' => 'Customers.created_user_id', 'type' => 'select', 'options' => $userOptions],
                ['key' => 'sys_customer_updated_user', 'label' => '更新者', 'column' => 'Customers.updated_user_id', 'type' => 'select', 'options' => $userOptions],
            ];
        }

        if ($entityName === 'contacts') {
            return [
                ['key' => 'sys_contact_customer_name', 'label' => '顧客氏名', 'column' => 'Customers.name', 'type' => 'text'],
                ['key' => 'sys_contact_customer_phone', 'label' => '顧客電話', 'column' => 'Customers.phone', 'type' => 'text'],
                ['key' => 'sys_contact_customer_email', 'label' => '顧客メール', 'column' => 'Customers.email', 'type' => 'text'],
                ['key' => 'sys_contact_customer_id', 'label' => '顧客ID', 'column' => 'Customers.id', 'type' => 'number'],
                ['key' => 'sys_contact_id', 'label' => '履歴ID', 'column' => 'Contacts.id', 'type' => 'number'],
                ['key' => 'sys_contact_created', 'label' => '登録日', 'column' => 'Contacts.created', 'type' => 'date_range'],
                ['key' => 'sys_contact_updated', 'label' => '更新日', 'column' => 'Contacts.updated', 'type' => 'date_range'],
                ['key' => 'sys_contact_created_user', 'label' => '登録者', 'column' => 'Contacts.created_user_id', 'type' => 'select', 'options' => $userOptions],
                ['key' => 'sys_contact_updated_user', 'label' => '更新者', 'column' => 'Contacts.updated_user_id', 'type' => 'select', 'options' => $userOptions],
            ];
        }

        return [];
    }

    protected function getUserOptions(): array
    {
        try {
            return FactoryLocator::get('Table')->get('Users')
                ->find('list', keyField: 'id', valueField: 'user_name')
                ->where(['Users.deleted IS' => null])
                ->orderBy(['Users.disp_cd' => 'ASC'])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function formatEntityErrors(EntityInterface $entity): string
    {
        $errors = $entity->getErrors();
        if (empty($errors)) {
            return '';
        }

        $messages = [];
        $flatten = function ($items) use (&$flatten, &$messages) {
            foreach ($items as $item) {
                if (is_array($item)) {
                    $flatten($item);
                } else {
                    $messages[] = (string)$item;
                }
            }
        };
        $flatten($errors);

        return implode(' / ', array_unique($messages));
    }
}
