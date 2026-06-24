<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddLikesAndNotifications extends AbstractMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        if (!$this->hasTable('likes')) {
            $this->table('likes')
                ->addColumn('article_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                ])
                ->addTimestamps('created', 'modified')
                ->addIndex(['article_id'])
                ->addIndex(['user_id'])
                ->addIndex(['article_id', 'user_id'], ['unique' => true])
                ->create();
        }

        if (!$this->hasTable('notifications')) {
            $this->table('notifications')
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('actor_user_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('article_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('type', 'string', [
                    'limit' => 50,
                    'null' => false,
                ])
                ->addColumn('message', 'string', [
                    'limit' => 255,
                    'null' => false,
                ])
                ->addColumn('is_read', 'boolean', [
                    'default' => false,
                    'null' => false,
                ])
                ->addTimestamps('created', 'modified')
                ->addIndex(['user_id'])
                ->addIndex(['actor_user_id'])
                ->addIndex(['article_id'])
                ->addIndex(['is_read'])
                ->create();
        }
    }
}
