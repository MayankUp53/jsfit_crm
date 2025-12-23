<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddOverviewAndResponseDetailToContacts extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contacts');

        // Add new columns
        $table->addColumn('overview', 'text', [
            'default' => null,
            'null' => true,
            'comment' => 'Overview of the contact'
        ])
        ->addColumn('response_detail', 'text', [
            'default' => null,
            'null' => true,
            'comment' => 'Detailed response'
        ])
        // Change type of status column
        ->changeColumn('status', 'string', [
            'limit' => 50,
            'default' => null,
            'null' => true,
            'comment' => 'Status as string'
        ])
        ->update();
    }
}
