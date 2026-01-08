<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

final class AddCrtObjectIdToContacts extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contacts');

        if (!$table->hasColumn('crt_object_id')) {
            $table->addColumn('crt_object_id', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
                'comment' => 'Ameyo voice log crtObjectId',
                'after' => 'customer_id', // adjust if you prefer another position
            ]);
        }

        // Optional index (useful if you search/filter by this later)
        if (!$table->hasIndex(['crt_object_id'])) {
            $table->addIndex(['crt_object_id'], [
                'name' => 'idx_contacts_crt_object_id',
            ]);
        }

        $table->update();
    }
}