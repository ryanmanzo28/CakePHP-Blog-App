<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class LikesTable extends Table
{
    /**
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('likes');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->nonNegativeInteger('article_id')
            ->requirePresence('article_id', 'create')
            ->notEmptyString('article_id');

        $validator
            ->nonNegativeInteger('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules The rules object.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['article_id'], 'Articles'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->isUnique(['article_id', 'user_id'], __('You already liked this post.')));

        return $rules;
    }
}
