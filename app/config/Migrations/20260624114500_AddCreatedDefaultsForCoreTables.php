<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCreatedDefaultsForCoreTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        // Backfill legacy null timestamps first.
        $this->execute('UPDATE articles SET created = NOW() WHERE created IS NULL');
        $this->execute('UPDATE articles SET modified = NOW() WHERE modified IS NULL');
        $this->execute('UPDATE users SET created = NOW() WHERE created IS NULL');
        $this->execute('UPDATE users SET modified = NOW() WHERE modified IS NULL');

        if ($this->hasTable('moderation_filters')) {
            $this->execute('UPDATE moderation_filters SET created = NOW() WHERE created IS NULL');
            $this->execute('UPDATE moderation_filters SET modified = NOW() WHERE modified IS NULL');
        }

        // Set DB-level defaults to avoid insert failures when timestamps are omitted.
        $this->execute('ALTER TABLE articles MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->execute('ALTER TABLE articles MODIFY modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        $this->execute('ALTER TABLE users MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->execute('ALTER TABLE users MODIFY modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        if ($this->hasTable('moderation_filters')) {
            $this->execute('ALTER TABLE moderation_filters MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            $this->execute('ALTER TABLE moderation_filters MODIFY modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        }
    }
}
