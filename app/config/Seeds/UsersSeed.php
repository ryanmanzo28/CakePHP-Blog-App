<?php
declare(strict_types=1);

// config/Seeds/UsersSeed.php
use Migrations\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    public function run(): void
    {
        $existing = $this->fetchRow("SELECT id FROM users WHERE email = 'cakephp@example.com' LIMIT 1");
        if (!empty($existing)) {
            return;
        }

        $data = [
            [
                'email' => 'cakephp@example.com',
                'password' => 'secret',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}