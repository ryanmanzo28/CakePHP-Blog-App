<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Like extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected $_accessible = [
        'article_id' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'article' => true,
        'user' => true,
    ];
}
