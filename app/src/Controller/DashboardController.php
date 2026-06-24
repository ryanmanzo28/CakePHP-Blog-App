<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Service\ContentModerationService;
use Cake\Http\Exception\ForbiddenException;
use Cake\Event\EventInterface;

/**
 * Dashboard Controller
 *
 * Landing page for authenticated users. Passes summary counts and the
 * current user identity to the view.
 */
class DashboardController extends AppController
{
    private ContentModerationService $moderationService;

    public function initialize(): void
    {
        parent::initialize();
        $this->moderationService = new ContentModerationService();
    }

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

        $feedArticles = $Articles->find()
            ->where(['Articles.published' => true])
            ->orderDesc('Articles.created')
            ->limit(40)
            ->all();

        if ($Articles->getSchema()->hasColumn('silenced')) {
            $feedArticles = $Articles->find()
                ->where([
                    'Articles.published' => true,
                    'Articles.silenced' => false,
                ])
                ->orderDesc('Articles.created')
                ->limit(40)
                ->all();
        }

        $myRecentArticles = [];
        if ($currentUserId !== null) {
            $myRecentArticles = $Articles->find()
                ->where(['Articles.user_id' => $currentUserId])
                ->orderDesc('Articles.created')
                ->limit(6)
                ->all();
        }

        $this->set(compact('feedArticles', 'myRecentArticles'));

        $this->set(compact('identity', 'isAdmin', 'currentUserId'));
    }

    /**
     * Admin dashboard — restricted to admin users only.
     *
     * @return void
     */
    public function admin(): void
    {
        $this->Authorization->skipAuthorization();
        $this->render('admin');

        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;
        $isAdmin = $identityEntity instanceof User && $identityEntity->isAdmin();
        $currentUserId = $identityEntity instanceof User ? (int)$identityEntity->id : null;

        if (!$isAdmin) {
            throw new ForbiddenException('Only admins can access the admin dashboard.');
        }

        /** @var \App\Model\Table\ArticlesTable $Articles */
        $Articles = $this->fetchTable('Articles');
        /** @var \App\Model\Table\UsersTable $Users */
        $Users = $this->fetchTable('Users');

        $articleCount = $Articles->find()->count();
        $userCount = $Users->find()->count();
        $publishedCount = $Articles->find()->where(['published' => true])->count();
        $draftCount = $Articles->find()->where(['published' => false])->count();

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');
        $newUsersLast7Days = $Users->find()
            ->where(['Users.created >=' => $sevenDaysAgo])
            ->count();
        $newArticlesLast7Days = $Articles->find()
            ->where(['Articles.created >=' => $sevenDaysAgo])
            ->count();

        $recentArticles = $Articles->find()
            ->orderDesc('Articles.created')
            ->limit(5)
            ->all();

        $recentDrafts = $Articles->find()
            ->where(['Articles.published' => false])
            ->orderDesc('Articles.modified')
            ->limit(5)
            ->all();

        $recentUsers = $Users->find()
            ->orderDesc('Users.created')
            ->limit(5)
            ->all();

        $moderationReasonByArticleId = [];
        foreach ($recentArticles as $article) {
            $match = $this->moderationService->detectMatches($article);
            if (!empty($match['keywords'])) {
                $moderationReasonByArticleId[(int)$article->id] = $match['keywords'];
            }
        }
        foreach ($recentDrafts as $article) {
            $match = $this->moderationService->detectMatches($article);
            if (!empty($match['keywords'])) {
                $moderationReasonByArticleId[(int)$article->id] = $match['keywords'];
            }
        }

        /** @var \App\Model\Table\ModerationFiltersTable $ModerationFilters */
        $ModerationFilters = $this->fetchTable('ModerationFilters');
        $moderationFilters = $ModerationFilters->find()
            ->orderDesc('ModerationFilters.modified')
            ->all();

        $this->set(compact(
            'articleCount',
            'userCount',
            'publishedCount',
            'draftCount',
            'newUsersLast7Days',
            'newArticlesLast7Days',
            'recentArticles',
            'recentDrafts',
            'recentUsers',
            'moderationFilters',
            'moderationReasonByArticleId',
            'identity',
            'isAdmin',
            'currentUserId'
        ));
    }

    /**
     * Admin: create a moderation keyword filter.
     *
     * @return \Cake\Http\Response|null
     */
    public function addFilter()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        /** @var \App\Model\Table\ModerationFiltersTable $ModerationFilters */
        $ModerationFilters = $this->fetchTable('ModerationFilters');
        $filter = $ModerationFilters->newEmptyEntity();

        $data = $this->request->getData();
        $data['active'] = true;
        $hasAction = !empty($data['action_delete']) || !empty($data['action_silence']) || !empty($data['action_ban']);
        if (!$hasAction) {
            $data['action_silence'] = true;
        }

        $filter = $ModerationFilters->patchEntity($filter, $data);
        if ($ModerationFilters->save($filter)) {
            $this->Flash->success(__('Moderation filter added.'));
        } else {
            $this->Flash->error(__('Could not add moderation filter.'));
        }

        return $this->redirect(['action' => 'admin']);
    }

    /**
     * Admin: toggle filter active/inactive.
     *
     * @param string|null $id Filter id.
     * @return \Cake\Http\Response|null
     */
    public function toggleFilter($id = null)
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        /** @var \App\Model\Table\ModerationFiltersTable $ModerationFilters */
        $ModerationFilters = $this->fetchTable('ModerationFilters');
        $filter = $ModerationFilters->get($id);
        $filter->active = !(bool)$filter->active;

        if ($ModerationFilters->save($filter)) {
            $this->Flash->success(__('Filter status updated.'));
        } else {
            $this->Flash->error(__('Unable to update filter status.'));
        }

        return $this->redirect(['action' => 'admin']);
    }

    /**
     * Admin: delete a moderation filter.
     *
     * @param string|null $id Filter id.
     * @return \Cake\Http\Response|null
     */
    public function deleteFilter($id = null)
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post', 'delete']);
        $this->requireAdmin();

        /** @var \App\Model\Table\ModerationFiltersTable $ModerationFilters */
        $ModerationFilters = $this->fetchTable('ModerationFilters');
        $filter = $ModerationFilters->get($id);

        if ($ModerationFilters->delete($filter)) {
            $this->Flash->success(__('Filter deleted.'));
        } else {
            $this->Flash->error(__('Could not delete filter.'));
        }

        return $this->redirect(['action' => 'admin']);
    }

    /**
     * Admin: re-run moderation filters for all articles.
     *
     * @return \Cake\Http\Response|null
     */
    public function reprocessFilters()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        $summary = $this->moderationService->reprocessAllArticles();
        $this->Flash->success(__('Reprocessed {0} posts: {1} deleted, {2} silenced, {3} accounts banned.',
            $summary['reviewed'],
            $summary['deleted'],
            $summary['silenced'],
            $summary['banned']
        ));

        return $this->redirect(['action' => 'admin']);
    }

    /**
     * Ensure current identity is an admin.
     *
     * @return void
     */
    private function requireAdmin(): void
    {
        $identity = $this->Authentication->getIdentity();
        $entity = $identity ? $identity->getOriginalData() : null;

        if (!$entity instanceof User || !$entity->isAdmin()) {
            throw new ForbiddenException('Only admins can perform this action.');
        }
    }
}
