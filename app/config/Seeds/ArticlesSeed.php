<?php
declare(strict_types=1);

// config/Seeds/ArticlesSeed.php
use Migrations\AbstractSeed;

class ArticlesSeed extends AbstractSeed
{
    public function run(): void
    {
        $existing = $this->fetchRow("SELECT id FROM articles WHERE slug = 'first-post' LIMIT 1");
        if (!empty($existing)) {
            return;
        }

        $data = [
            [
                'user_id' => 1,
                'title' => 'First Post',
                'slug' => 'first-post',
                'body' => 'This is the first post.',
                'published' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('articles');
        $table->insert($data)->save();
    }
}