<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class FixTagTableTimestampDefaults extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $this->execute('UPDATE tags SET created = NOW() WHERE created IS NULL');
        $this->execute('UPDATE tags SET modified = NOW() WHERE modified IS NULL');

        if ($this->hasTable('articles_tags')) {
            $this->execute('UPDATE articles_tags SET created = NOW() WHERE created IS NULL');
            $this->execute('UPDATE articles_tags SET modified = NOW() WHERE modified IS NULL');
        }

        $this->execute('ALTER TABLE tags MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->execute('ALTER TABLE tags MODIFY modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        if ($this->hasTable('articles_tags')) {
            $this->execute('ALTER TABLE articles_tags MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            $this->execute('ALTER TABLE articles_tags MODIFY modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        }
    }
}
