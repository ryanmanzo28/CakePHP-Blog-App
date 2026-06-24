<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Notification extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected $_accessible = [
        'user_id' => true,
        'actor_user_id' => true,
        'article_id' => true,
        'type' => true,
        'message' => true,
        'is_read' => true,
        'created' => true,
        'modified' => true,
        'article' => true,
        'user' => true,
        'actor' => true,
    ];
}
