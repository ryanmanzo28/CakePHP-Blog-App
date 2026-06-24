<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Dashboard Controller
 *
 * Landing page for authenticated users. Passes summary counts and the
 * current user identity to the view.
 */
class DashboardController extends AppController
{
    /**
     * Dashboard is for logged-in users only.
     *
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([]);
    }

    /**
     * Index — main dashboard view.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->skipAuthorization();

        /** @var \App\Model\Table\ArticlesTable $Articles */
        $Articles = $this->fetchTable('Articles');
        /** @var \App\Model\Table\UsersTable $Users */
        $Users = $this->fetchTable('Users');

        $articleCount = $Articles->find()->count();
        $userCount    = $Users->find()->count();

        // Five most-recently created articles for the activity feed.
        $recentArticles = $Articles->find()
            ->orderDesc('Articles.created')
            ->limit(5)
            ->all();

        $identity = $this->Authentication->getIdentity();

        $this->set(compact('articleCount', 'userCount', 'recentArticles', 'identity'));
    }
}
