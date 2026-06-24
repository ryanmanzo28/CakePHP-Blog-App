<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class FollowsTable extends Table
{
    /**
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('follows');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('FollowerUsers', [
            'className' => 'Users',
            'foreignKey' => 'follower_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('FollowingUsers', [
            'className' => 'Users',
            'foreignKey' => 'following_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('follower_id')
            ->requirePresence('follower_id', 'create')
            ->notEmptyString('follower_id');

        $validator
            ->nonNegativeInteger('following_id')
            ->requirePresence('following_id', 'create')
            ->notEmptyString('following_id');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules The rules object.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['follower_id'], 'FollowerUsers'));
        $rules->add($rules->existsIn(['following_id'], 'FollowingUsers'));
        $rules->add($rules->isUnique(['follower_id', 'following_id'], __('You already follow this user.')));

        return $rules;
    }
}
