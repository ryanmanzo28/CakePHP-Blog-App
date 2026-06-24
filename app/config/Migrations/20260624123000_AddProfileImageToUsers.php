<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddProfileImageToUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('users');
        if (!$table->hasColumn('profile_image')) {
            $table->addColumn('profile_image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
                'after' => 'role',
            ]);
            $table->update();
        }
    }
}
