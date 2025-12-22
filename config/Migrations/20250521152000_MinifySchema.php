<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class MinifySchema extends AbstractMigration
{
    public function up(): void
    {
        $this->minifyCustomers();
        $this->minifyContacts();
    }

    protected function minifyCustomers(): void
    {
        if (!$this->hasTable('customers')) {
            return;
        }

        $table = $this->table('customers');
        $modified = false;

        if (!$table->hasColumn('name')) {
            $table->addColumn('name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'id',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('phone')) {
            $table->addColumn('phone', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'name',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('fax')) {
            $table->addColumn('fax', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'phone',
            ]);
            $modified = true;
        }

        if ($modified) {
            $table->update();
        }

        // Copy existing data into the new columns if missing
        $this->execute('UPDATE customers SET name = COALESCE(name)');
        $this->execute('UPDATE customers SET phone = COALESCE(phone, tel)');
        $this->execute('UPDATE customers SET fax = COALESCE(fax)');

        // Drop unused columns
        $dropColumns = [
            'zip_cd', 'addr1', 'addr2', 'addr3',
            'name_kj', 'name_kn',
            'tel_only', 'tel', 'phone_only',
            'birth', 'sex', 'remark',
            'ex1', 'ex2', 'ex3',
            'client_cd', 'company_branch_name',
            'type_of_number', 'disp_no',
            'cf_type_of_number', 'cf_cf_type_of_number', 'cf_fax', 'cf_remark',
        ];

        $modified = false;
        foreach ($dropColumns as $column) {
            if ($table->hasColumn($column)) {
                $table->removeColumn($column);
                $modified = true;
            }
        }
        if ($modified) {
            $table->update();
        }

        // Remove custom field definitions for dropped legacy columns
        $names = array_map(fn($col) => "'" . $col . "'", $dropColumns);
        if ($names) {
            $this->execute('DELETE FROM custom_fields WHERE name IN (' . implode(',', $names) . ')');
        }
    }

    protected function minifyContacts(): void
    {
        if (!$this->hasTable('contacts')) {
            return;
        }

        $table = $this->table('contacts');
        $dropColumns = [
            'status',
            'summary',
            'detail',
            'remark',
            'callback_datetime',
            'queue_name',
            'phone',
            'is_done',
        ];

        $modified = false;
        foreach ($dropColumns as $column) {
            if ($table->hasColumn($column)) {
                $table->removeColumn($column);
                $modified = true;
            }
        }

        if ($modified) {
            $table->update();
        }

        if ($dropColumns) {
            $names = array_map(fn($col) => "'" . $col . "'", $dropColumns);
            $this->execute('DELETE FROM custom_fields WHERE name IN (' . implode(',', $names) . ')');
        }
    }

    public function down(): void
    {
        // Not implemented intentionally
    }
}
