<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Follow extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected $_accessible = [
        'follower_id' => true,
        'following_id' => true,
        'created' => true,
        'modified' => true,
        'follower' => true,
        'following' => true,
    ];
}
