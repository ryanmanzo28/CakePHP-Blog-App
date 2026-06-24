<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ModerationFilter extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected $_accessible = [
        'keyword' => true,
        'action_delete' => true,
        'action_silence' => true,
        'action_ban' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];
}
