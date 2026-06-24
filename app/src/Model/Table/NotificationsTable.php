<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class NotificationsTable extends Table
{
    /**
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('notifications');
        $this->setPrimaryKey('id');
        $this->setDisplayField('message');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Actors', [
            'className' => 'Users',
            'foreignKey' => 'actor_user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'LEFT',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->nonNegativeInteger('actor_user_id')
            ->requirePresence('actor_user_id', 'create')
            ->notEmptyString('actor_user_id');

        $validator
            ->allowEmptyString('article_id')
            ->nonNegativeInteger('article_id');

        $validator
            ->scalar('type')
            ->maxLength('type', 50)
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('message')
            ->maxLength('message', 255)
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        $validator
            ->boolean('is_read')
            ->notEmptyString('is_read');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules The rules object.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['actor_user_id'], 'Actors'));

        return $rules;
    }
}
