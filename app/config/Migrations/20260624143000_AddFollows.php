<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddFollows extends AbstractMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        if ($this->hasTable('follows')) {
            return;
        }

        $this->table('follows')
            ->addColumn('follower_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('following_id', 'integer', [
                'null' => false,
            ])
            ->addTimestamps('created', 'modified')
            ->addIndex(['follower_id'])
            ->addIndex(['following_id'])
            ->addIndex(['follower_id', 'following_id'], ['unique' => true])
            ->create();
    }
}
