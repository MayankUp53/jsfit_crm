<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateInitialTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        if (!$this->hasTable('users')) {
            // Users table
            $users = $this->table('users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $users->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'ユーザID',
            ])
            ->addColumn('disp_cd', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_name', 'string', [
                'limit' => 100,
                'null' => false,
                'comment' => '氏名',
            ])
            ->addColumn('auth', 'integer', [
                'limit' => 1,
                'null' => true,
                'default' => null,
                'comment' => '権限',
            ])
            ->addColumn('loginid', 'string', [
                'limit' => 100,
                'null' => true,
                'default' => null,
                'comment' => 'ログインＩＤ',
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
                'comment' => 'パスワード',
            ])
            ->addColumn('remark1', 'string', [
                'limit' => 200,
                'null' => true,
                'default' => null,
                'comment' => '備考１',
            ])
            ->addColumn('remark2', 'string', [
                'limit' => 200,
                'null' => true,
                'default' => null,
                'comment' => '備考２',
            ])
            ->addColumn('remark3', 'string', [
                'limit' => 200,
                'null' => true,
                'default' => null,
                'comment' => '備考３',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('created_user_id', 'integer', [
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('updated', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('updated_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('deleted', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('customers')) {
            // Customers table
            $customers = $this->table('customers', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $customers->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 4,
                'null' => false,
                'comment' => '顧客ID',
            ])
        ->addColumn('disp_no', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '表示Ｎｏ',
        ])
        ->addColumn('zip_cd', 'string', [
            'limit' => 8,
            'null' => false,
            'comment' => '郵便番号',
        ])
        ->addColumn('addr1', 'string', [
            'limit' => 100,
            'null' => false,
            'comment' => '住所１',
        ])
        ->addColumn('addr2', 'string', [
            'limit' => 200,
            'null' => true,
            'default' => null,
            'comment' => '住所２',
        ])
        ->addColumn('addr3', 'string', [
            'limit' => 200,
            'null' => true,
            'default' => null,
            'comment' => '住所３',
        ])
        ->addColumn('name_kj', 'string', [
            'limit' => 100,
            'null' => true,
            'default' => null,
            'comment' => '氏名',
        ])
        ->addColumn('name_kn', 'string', [
            'limit' => 100,
            'null' => true,
            'default' => null,
            'comment' => 'フリガナ',
        ])
        ->addColumn('tel_only', 'string', [
            'limit' => 20,
            'null' => true,
            'default' => null,
            'comment' => '携帯番号ハイフン無',
        ])
        ->addColumn('tel', 'string', [
            'limit' => 20,
            'null' => true,
            'default' => null,
            'comment' => '携帯番号',
        ])
        ->addColumn('phone_only', 'string', [
            'limit' => 20,
            'null' => true,
            'default' => null,
            'comment' => '電話番号ハイフン無',
        ])
        ->addColumn('phone', 'string', [
            'limit' => 20,
            'null' => true,
            'default' => null,
            'comment' => '電話番号',
        ])
        ->addColumn('birth', 'date', [
            'null' => true,
            'default' => null,
            'comment' => '生年月日',
        ])
        ->addColumn('sex', 'string', [
            'limit' => 100,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('remark', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('ex1', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('ex2', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('ex3', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
        ])
            ->addIndex(['disp_no'], ['unique' => true, 'name' => 'disp_idx'])
            ->create();
        }

        if (!$this->hasTable('contacts')) {
            // Contacts table
            $contacts = $this->table('contacts', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $contacts->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 4,
                'null' => false,
                'comment' => '履歴ID',
            ])
        ->addColumn('customer_id', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('status', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('summary', 'string', [
            'limit' => 1000,
            'null' => false,
            'comment' => '概要',
        ])
        ->addColumn('detail', 'string', [
            'limit' => 1000,
            'null' => false,
            'comment' => '対応内容',
        ])
        ->addColumn('remark', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => 'メモ',
        ])
        ->addColumn('callback_datetime', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
            ->addColumn('queue_name', 'string', [
                'limit' => 100,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('callbacks')) {
            // Callbacks table
            $callbacks = $this->table('callbacks', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $callbacks->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('customer_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('callback_datetime', 'datetime', [
            'null' => false,
        ])
        ->addColumn('phone', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '電話番号',
        ])
        ->addColumn('remark', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('is_done', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => 0,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('core_settings')) {
            // Core Settings table
            $coreSettings = $this->table('core_settings', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $coreSettings->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('ccid', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('host_name', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => 'ホスト名',
        ])
        ->addColumn('dnc_lead_id', 'integer', [
            'limit' => 11,
            'null' => false,
            'comment' => 'DNCデフォルトリードID',
        ])
        ->addColumn('voicelog_url', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '通録ファイルURL',
        ])
        ->addColumn('dnc_api_url', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => 'DNCAPI_URL',
        ])
        ->addColumn('ch_api_url', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => 'コール履歴API_URL',
        ])
        ->addColumn('dial_api_url', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => 'ManualDialAPI_URL',
        ])
        ->addColumn('ameyo_alert_enabled', 'integer', [
            'limit' => 1,
            'null' => false,
            'comment' => 'Ameyoパラメータ取得確認アラート',
        ])
        ->addColumn('callback_notice_time', 'integer', [
            'limit' => 1,
            'null' => false,
            'comment' => 'コールバック通知時間（分）',
        ])
        ->addColumn('memo', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
            ->addColumn('limit', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('mt_cids')) {
            // MT CIDs table
            $mtCids = $this->table('mt_cids', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $mtCids->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('disp_cd', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('campaign_id', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('item_name', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '名称',
        ])
        ->addColumn('cid', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '電話番号ハイフン無し',
        ])
        ->addColumn('item_memo', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('active_flg', 'integer', [
            'limit' => 1,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('mt_phonelists')) {
            // MT Phonelists table
            $mtPhonelists = $this->table('mt_phonelists', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $mtPhonelists->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('disp_cd', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('item_name', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '名称',
        ])
        ->addColumn('phone', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '電話番号ハイフン無し',
        ])
        ->addColumn('item_memo', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('active_flg', 'integer', [
            'limit' => 1,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('mt_queues')) {
            // MT Queues table
            $mtQueues = $this->table('mt_queues', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $mtQueues->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('disp_cd', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('item_name', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '名称',
        ])
        ->addColumn('item_memo', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('active_flg', 'integer', [
            'limit' => 1,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }

        if (!$this->hasTable('mt_statuses')) {
            // MT Statuses table
            $mtStatuses = $this->table('mt_statuses', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $mtStatuses->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'null' => false,
                'comment' => 'シーケンス',
            ])
        ->addColumn('disp_cd', 'integer', [
            'limit' => 11,
            'null' => false,
        ])
        ->addColumn('item_name', 'string', [
            'limit' => 200,
            'null' => false,
            'comment' => '名称',
        ])
        ->addColumn('item_memo', 'string', [
            'limit' => 1000,
            'null' => true,
            'default' => null,
            'comment' => '備考',
        ])
        ->addColumn('active_flg', 'integer', [
            'limit' => 1,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('created_user_id', 'integer', [
            'limit' => 4,
            'null' => false,
        ])
        ->addColumn('updated', 'datetime', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('updated_user_id', 'integer', [
            'limit' => 4,
            'null' => true,
            'default' => null,
        ])
        ->addColumn('deleted', 'datetime', [
            'null' => true,
            'default' => null,
        ])
            ->addColumn('deleted_user_id', 'integer', [
                'limit' => 4,
                'null' => true,
                'default' => null,
            ])
            ->create();
        }
    }
}
