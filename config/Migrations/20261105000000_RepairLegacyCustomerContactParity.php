<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RepairLegacyCustomerContactParity extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('customers')) {
            $table = $this->table('customers');
            $columns = [
                'client_cd' => ['integer', ['limit' => 11]],
                'client_name' => ['string', ['limit' => 255]],
                'company_name' => ['string', ['limit' => 255]],
                'client_branch_name' => ['string', ['limit' => 255]],
                'company_branch_name' => ['string', ['limit' => 255]],
                'client_branch_code' => ['string', ['limit' => 50]],
                'zip_cd' => ['string', ['limit' => 8]],
                'addr1' => ['string', ['limit' => 255]],
                'addr2' => ['string', ['limit' => 255]],
                'addr3' => ['string', ['limit' => 255]],
                'tel' => ['string', ['limit' => 50]],
                'tel_only' => ['string', ['limit' => 50]],
                'tel2' => ['string', ['limit' => 50]],
                'tel3' => ['string', ['limit' => 50]],
                'incharge1_name' => ['string', ['limit' => 255]],
                'incharge1_phone' => ['string', ['limit' => 50]],
                'incharge2_name' => ['string', ['limit' => 255]],
                'incharge2_phone' => ['string', ['limit' => 50]],
                'incharge3_name' => ['string', ['limit' => 255]],
                'incharge3_phone' => ['string', ['limit' => 50]],
                'incharge4_name' => ['string', ['limit' => 255]],
                'incharge4_phone' => ['string', ['limit' => 50]],
                'notes1' => ['text', []],
                'notes2' => ['text', []],
                'notes3' => ['text', []],
                'notes4' => ['text', []],
                'section_name' => ['string', ['limit' => 255]],
                'sales_person' => ['string', ['limit' => 255]],
                'deadline' => ['string', ['limit' => 50]],
                'type_of_number' => ['string', ['limit' => 50]],
            ];
            $modified = false;
            foreach ($columns as $name => [$type, $extra]) {
                if (!$table->hasColumn($name)) {
                    $table->addColumn($name, $type, $extra + ['null' => true, 'default' => null]);
                    $modified = true;
                }
            }
            if ($modified) {
                $table->update();
            }
            if ($table->hasColumn('client_code')) {
                $this->execute("UPDATE customers SET client_cd=COALESCE(client_cd,client_code) WHERE client_cd IS NULL AND client_code IS NOT NULL");
            }
            if ($table->hasColumn('name')) {
                $this->execute("UPDATE customers SET client_name=COALESCE(client_name,name) WHERE client_name IS NULL");
            }
            $this->execute("UPDATE customers SET company_name=COALESCE(company_name,client_branch_name,client_name,name) WHERE company_name IS NULL");
            $this->execute("UPDATE customers SET tel=COALESCE(tel,tel_only,phone) WHERE tel IS NULL");
            $this->execute("UPDATE customers SET tel_only=COALESCE(tel_only,tel,phone) WHERE tel_only IS NULL");
        }

        if ($this->hasTable('contacts')) {
            $table = $this->table('contacts');
            $columns = [
                'phone' => ['string', 200],
                'summary' => ['text', null],
                'detail' => ['text', null],
                'remark' => ['text', null],
                'status' => ['string', 50],
                'queue_name' => ['string', 100],
                'callback_datetime' => ['datetime', null],
                'is_done' => ['integer', 4],
                'response_detail' => ['text', null],
            ];
            $modified = false;
            foreach ($columns as $name => [$type, $limit]) {
                if ($table->hasColumn($name)) {
                    continue;
                }
                $options = ['null' => true, 'default' => null];
                if ($limit !== null) {
                    $options['limit'] = $limit;
                }
                if ($name === 'is_done') {
                    $options['default'] = 0;
                }
                $table->addColumn($name, $type, $options);
                $modified = true;
            }
            if ($modified) {
                $table->update();
            }
        }
    }
}
