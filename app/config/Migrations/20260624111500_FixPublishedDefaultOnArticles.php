<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class FixPublishedDefaultOnArticles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('articles');

        if ($table->hasColumn('published')) {
            // Backfill any legacy nulls before enforcing the default.
            $this->execute('UPDATE articles SET published = 0 WHERE published IS NULL');

            $table->changeColumn('published', 'boolean', [
                'default' => false,
                'null' => false,
            ]);
            $table->update();
        }
    }
}
