<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddModerationFiltersAndSilenced extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $articles = $this->table('articles');
        if (!$articles->hasColumn('silenced')) {
            $articles->addColumn('silenced', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'published',
            ]);
            $articles->update();
        }

        if (!$this->hasTable('moderation_filters')) {
            $table = $this->table('moderation_filters');
            $table
                ->addColumn('keyword', 'string', [
                    'limit' => 120,
                    'null' => false,
                ])
                ->addColumn('action_delete', 'boolean', [
                    'default' => false,
                    'null' => false,
                ])
                ->addColumn('action_silence', 'boolean', [
                    'default' => true,
                    'null' => false,
                ])
                ->addColumn('action_ban', 'boolean', [
                    'default' => false,
                    'null' => false,
                ])
                ->addColumn('active', 'boolean', [
                    'default' => true,
                    'null' => false,
                ])
                ->addTimestamps('created', 'modified')
                ->addIndex(['keyword'])
                ->create();
        }
    }
}
