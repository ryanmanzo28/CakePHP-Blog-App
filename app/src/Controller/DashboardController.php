<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
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

        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;
        $isAdmin = $identityEntity instanceof User && $identityEntity->isAdmin();
        $currentUserId = $identityEntity instanceof User ? (int)$identityEntity->id : null;

        /** @var \App\Model\Table\ArticlesTable $Articles */
        $Articles = $this->fetchTable('Articles');

        if ($isAdmin) {
            /** @var \App\Model\Table\UsersTable $Users */
            $Users = $this->fetchTable('Users');

            $articleCount = $Articles->find()->count();
            $userCount    = $Users->find()->count();
            $publishedCount = $Articles->find()->where(['published' => true])->count();

            // Five most-recently created articles for the activity feed.
            $recentArticles = $Articles->find()
                ->orderDesc('Articles.created')
                ->limit(5)
                ->all();

            $recentUsers = $Users->find()
                ->orderDesc('Users.created')
                ->limit(5)
                ->all();

            $this->set(compact(
                'articleCount',
                'userCount',
                'publishedCount',
                'recentArticles',
                'recentUsers'
            ));
        } else {
            $feedArticles = $Articles->find()
                ->where(['Articles.published' => true])
                ->orderDesc('Articles.created')
                ->limit(30)
                ->all();

            $myRecentArticles = [];
            if ($currentUserId !== null) {
                $myRecentArticles = $Articles->find()
                    ->where(['Articles.user_id' => $currentUserId])
                    ->orderDesc('Articles.created')
                    ->limit(6)
                    ->all();
            }

            $this->set(compact('feedArticles', 'myRecentArticles'));
        }

        $this->set(compact('identity', 'isAdmin', 'currentUserId'));
    }
}
