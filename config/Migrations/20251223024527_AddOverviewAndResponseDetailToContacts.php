<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddOverviewAndResponseDetailToContacts extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contacts');
        if (!$table->hasColumn('overview')) {
            $table->addColumn('overview', 'text', ['default' => null, 'null' => true, 'comment' => 'Overview of the contact']);
        }
        if (!$table->hasColumn('response_detail')) {
            $table->addColumn('response_detail', 'text', ['default' => null, 'null' => true, 'comment' => 'Detailed response']);
        }
        foreach (['summary', 'detail', 'remark', 'status', 'queue_name'] as $name) {
            if (!$table->hasColumn($name)) {
                $table->addColumn($name, 'string', ['limit' => 255, 'default' => null, 'null' => true]);
            }
        }
        $table->update();
    }
}
