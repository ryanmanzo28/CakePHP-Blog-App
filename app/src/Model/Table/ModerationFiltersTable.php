<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ModerationFiltersTable extends Table
{
    /**
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('moderation_filters');
        $this->setDisplayField('keyword');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('keyword')
            ->maxLength('keyword', 120)
            ->requirePresence('keyword', 'create')
            ->notEmptyString('keyword', __('Keyword is required.'));

        $validator
            ->boolean('action_delete')
            ->notEmptyString('action_delete');

        $validator
            ->boolean('action_silence')
            ->notEmptyString('action_silence');

        $validator
            ->boolean('action_ban')
            ->notEmptyString('action_ban');

        $validator
            ->boolean('active')
            ->notEmptyString('active');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules The rules object.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['keyword']), [
            'errorField' => 'keyword',
            'message' => __('A filter for this keyword already exists.'),
        ]);

        return $rules;
    }

    /**
     * @param \Cake\Event\EventInterface $event Event.
     * @param \Cake\Datasource\EntityInterface $entity Entity being saved.
     * @return void
     */
    public function beforeSave(EventInterface $event, $entity): void
    {
        // Defensive timestamp fallback in case behavior resolution is bypassed.
        $now = FrozenTime::now();
        if ($entity->isNew() && !$entity->get('created')) {
            $entity->set('created', $now);
        }
        if (!$entity->get('modified')) {
            $entity->set('modified', $now);
        }
    }
}
