<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RestoreCoreColumns extends AbstractMigration
{
    public function up(): void
    {
        $this->restoreCustomers();
        $this->restoreContacts();
    }

    protected function restoreCustomers(): void
    {
        if (!$this->hasTable('customers')) {
            return;
        }

        $table = $this->table('customers');
        $modified = false;

        if (!$table->hasColumn('type_of_number')) {
            $table->addColumn('type_of_number', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'company_branch_name',
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

        if (!$table->hasColumn('ex2')) {
            $table->addColumn('ex2', 'string', [
                'limit' => 1000,
                'null' => true,
                'after' => 'ex1',
            ]);
            $modified = true;
        }

        if ($modified) {
            $table->update();
        }

        // Copy values from legacy custom columns if they exist
        if ($this->hasTable('customers')) {
            if ($this->table('customers')->hasColumn('cf_type_of_number')) {
                $this->execute('UPDATE customers SET type_of_number = COALESCE(type_of_number, cf_type_of_number) WHERE cf_type_of_number IS NOT NULL');
            }
            if ($this->table('customers')->hasColumn('cf_fax')) {
                $this->execute('UPDATE customers SET fax = COALESCE(fax, cf_fax) WHERE cf_fax IS NOT NULL');
            }
        }

        // Drop duplicated cf_* columns and matching custom_field entries
        $table = $this->table('customers');
        $dropModified = false;
        foreach (['cf_type_of_number', 'cf_cf_type_of_number', 'cf_fax'] as $col) {
            if ($table->hasColumn($col)) {
                $table->removeColumn($col);
                $dropModified = true;
            }
        }
        if ($dropModified) {
            $table->update();
        }

        $this->execute("DELETE FROM custom_fields WHERE name IN ('cf_type_of_number','cf_cf_type_of_number','cf_fax')");
    }

    protected function restoreContacts(): void
    {
        if (!$this->hasTable('contacts')) {
            return;
        }

        $table = $this->table('contacts');
        $modified = false;

        if (!$table->hasColumn('summary')) {
            $table->addColumn('summary', 'string', [
                'limit' => 1000,
                'null' => true,
                'after' => 'status',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('detail')) {
            $table->addColumn('detail', 'text', [
                'null' => true,
                'after' => 'summary',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('remark')) {
            $table->addColumn('remark', 'text', [
                'null' => true,
                'after' => 'detail',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('callback_datetime')) {
            $table->addColumn('callback_datetime', 'datetime', [
                'null' => true,
                'after' => 'phone',
            ]);
            $modified = true;
        }

        if (!$table->hasColumn('is_done')) {
            $table->addColumn('is_done', 'integer', [
                'null' => true,
                'after' => 'callback_datetime',
            ]);
            $modified = true;
        }

        if ($modified) {
            $table->update();
        }
    }

    public function down(): void
    {
        // Intentionally left empty to avoid dropping restored data.
    }
}
