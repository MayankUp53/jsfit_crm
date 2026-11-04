<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddLegacyCustomerFields extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('customers')) {
            return;
        }

        $table = $this->table('customers');
        $columns = [
            'client_branch_code' => ['string', 50],
            'client_branch_name' => ['string', 255],
            'company_branch_name' => ['string', 255],
            'zip_cd' => ['string', 8],
            'addr1' => ['string', 255],
            'addr2' => ['string', 255],
            'addr3' => ['string', 255],
            'tel_only' => ['string', 20],
            'tel2' => ['string', 20],
            'tel3' => ['string', 20],
            'incharge1_name' => ['string', 255],
            'incharge1_phone' => ['string', 50],
            'incharge2_name' => ['string', 255],
            'incharge2_phone' => ['string', 50],
            'incharge3_name' => ['string', 255],
            'incharge3_phone' => ['string', 50],
            'incharge4_name' => ['string', 255],
            'incharge4_phone' => ['string', 50],
            'notes1' => ['text', null],
            'notes2' => ['text', null],
            'notes3' => ['text', null],
            'notes4' => ['text', null],
            'section_name' => ['string', 255],
            'sales_person' => ['string', 255],
            'deadline' => ['string', 50],
        ];

        $modified = false;
        foreach ($columns as $name => $definition) {
            if ($table->hasColumn($name)) {
                continue;
            }

            [$type, $limit] = $definition;
            $options = [
                'null' => true,
                'default' => null,
            ];
            if ($limit !== null) {
                $options['limit'] = $limit;
            }
            $table->addColumn($name, $type, $options);
            $modified = true;
        }

        if ($modified) {
            $table->update();
        }
    }
}
