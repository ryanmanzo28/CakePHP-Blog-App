<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Service\ContentModerationService;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
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

        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');

        $followedUserIds = [];
        if ($currentUserId !== null) {
            $followRows = $Follows->find()
                ->select(['following_id'])
                ->where(['Follows.follower_id' => $currentUserId])
                ->enableHydration(false)
                ->toArray();

            foreach ($followRows as $row) {
                $followedUserIds[] = (int)$row['following_id'];
            }
        }

        $baseFeedConditions = ['Articles.published' => true];
        if ($Articles->getSchema()->hasColumn('silenced')) {
            $baseFeedConditions['Articles.silenced'] = false;
        }

        $feedArticles = [];
        if (!empty($followedUserIds)) {
            $followedPosts = $Articles->find()
                ->where($baseFeedConditions + ['Articles.user_id IN' => $followedUserIds])
                ->contain(['Users'])
                ->orderDesc('Articles.created')
                ->limit(25)
                ->all()
                ->toArray();

            $otherPosts = $Articles->find()
                ->where($baseFeedConditions + ['Articles.user_id NOT IN' => $followedUserIds])
                ->contain(['Users'])
                ->orderDesc('Articles.created')
                ->limit(40)
                ->all()
                ->toArray();

            $feedArticles = array_slice(array_merge($followedPosts, $otherPosts), 0, 40);
        } else {
            $feedArticles = $Articles->find()
                ->where($baseFeedConditions)
                ->contain(['Users'])
                ->orderDesc('Articles.created')
                ->limit(40)
                ->all()
                ->toArray();
        }

        $myRecentArticles = [];
        if ($currentUserId !== null) {
            $myRecentArticles = $Articles->find()
                ->where(['Articles.user_id' => $currentUserId])
                ->orderDesc('Articles.created')
                ->limit(6)
                ->all()
                ->toArray();
        }

        $likeCountByArticleId = [];
        $likedByCurrentUserByArticleId = [];
        $notifications = [];
        $unreadNotificationCount = 0;

        $articleIds = array_map(static function ($article): int {
            return (int)$article->id;
        }, $feedArticles);

        if (!empty($articleIds)) {
            /** @var \Cake\ORM\Table $Likes */
            $Likes = $this->fetchTable('Likes');

            $likeRows = $Likes->find()
                ->select([
                    'article_id',
                    'like_count' => $Likes->find()->func()->count('*'),
                ])
                ->where(['Likes.article_id IN' => $articleIds])
                ->group(['Likes.article_id'])
                ->enableHydration(false)
                ->toArray();

            foreach ($likeRows as $row) {
                $likeCountByArticleId[(int)$row['article_id']] = (int)$row['like_count'];
            }

            if ($currentUserId !== null) {
                $likedRows = $Likes->find()
                    ->select(['article_id'])
                    ->where([
                        'Likes.article_id IN' => $articleIds,
                        'Likes.user_id' => $currentUserId,
                    ])
                    ->enableHydration(false)
                    ->toArray();

                foreach ($likedRows as $row) {
                    $likedByCurrentUserByArticleId[(int)$row['article_id']] = true;
                }
            }
        }

        if ($currentUserId !== null) {
            /** @var \Cake\ORM\Table $Notifications */
            $Notifications = $this->fetchTable('Notifications');

            $notifications = $Notifications->find()
                ->where(['Notifications.user_id' => $currentUserId])
                ->contain(['Articles'])
                ->orderDesc('Notifications.created')
                ->limit(8)
                ->all()
                ->toArray();

            $unreadNotificationCount = $Notifications->find()
                ->where([
                    'Notifications.user_id' => $currentUserId,
                    'Notifications.is_read' => false,
                ])
                ->count();
        }

        $this->set(compact(
            'feedArticles',
            'myRecentArticles',
            'likeCountByArticleId',
            'likedByCurrentUserByArticleId',
            'notifications',
            'unreadNotificationCount',
            'followedUserIds'
        ));

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
        $stats = $this->buildAdminStats($Articles, $Users);
        extract($stats);

        $recentArticles = $Articles->find()
            ->orderDesc('Articles.created')
            ->limit(5)
            ->all()
            ->toArray();

        // Fallback: if ORM returns empty but rows exist, hydrate recent records by IDs.
        if (count($recentArticles) === 0) {
            $recentArticleIds = $Articles->find()
                ->select(['id'])
                ->orderDesc('Articles.id')
                ->limit(5)
                ->enableHydration(false)
                ->toArray();

            if (!empty($recentArticleIds)) {
                $ids = array_map(static function (array $row): int {
                    return (int)$row['id'];
                }, $recentArticleIds);

                $recentArticles = $Articles->find()
                    ->where(['Articles.id IN' => $ids])
                    ->orderDesc('Articles.created')
                    ->all()
                    ->toArray();
            }
        }

        $recentDrafts = $Articles->find()
            ->where(function ($exp) {
                return $exp->or_([
                    'Articles.published' => false,
                    'Articles.published IS' => null,
                ]);
            })
            ->orderDesc('Articles.modified')
            ->limit(5)
            ->all()
            ->toArray();

        $recentUsers = $Users->find()
            ->orderDesc('Users.created')
            ->limit(5)
            ->all()
            ->toArray();

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
        $moderationFilterCount = $ModerationFilters->find()->count();
        $moderationFilters = $ModerationFilters->find()
            ->orderDesc('ModerationFilters.modified')
            ->all()
            ->toArray();

        if ($moderationFilterCount > 0 && count($moderationFilters) === 0) {
            $moderationFilters = $ModerationFilters->find()
                ->all()
                ->toArray();
        }

        // Fallback: if count still appears empty, verify through ORM count path.
        if ($moderationFilterCount === 0) {
            $moderationFilterCount = $ModerationFilters->find()->count();
            if ($moderationFilterCount > 0 && count($moderationFilters) === 0) {
                $moderationFilters = $ModerationFilters->find()
                    ->orderDesc('ModerationFilters.id')
                    ->limit(50)
                    ->all()
                    ->toArray();
            }
        }

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
            'moderationFilterCount',
            'moderationReasonByArticleId',
            'identity',
            'isAdmin',
            'currentUserId'
        ));
    }

    /**
     * Admin: lightweight stats payload for live dashboard updates.
     *
     * @return \Cake\Http\Response
     */
    public function adminStats(): Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['get']);
        $this->requireAdmin();

        /** @var \App\Model\Table\ArticlesTable $Articles */
        $Articles = $this->fetchTable('Articles');
        /** @var \App\Model\Table\UsersTable $Users */
        $Users = $this->fetchTable('Users');

        $payload = $this->buildAdminStats($Articles, $Users);

        return $this->response
            ->withType('application/json')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withStringBody((string)json_encode($payload));
    }

    /**
     * Build admin stats used by both HTML and JSON endpoints.
     *
     * @param \App\Model\Table\ArticlesTable $Articles Articles table.
     * @param \App\Model\Table\UsersTable $Users Users table.
     * @return array<string, int>
     */
    private function buildAdminStats($Articles, $Users): array
    {
        $articleCount = $Articles->find()->count();
        $userCount = $Users->find()->count();
        $publishedCount = $Articles->find()->where(['published' => true])->count();
        $draftCount = $Articles->find()
            ->where(function ($exp) {
                return $exp->or_([
                    'Articles.published' => false,
                    'Articles.published IS' => null,
                ]);
            })
            ->count();

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');
        $newUsersLast7Days = $Users->find()
            ->where(['Users.created >=' => $sevenDaysAgo])
            ->count();
        $newArticlesLast7Days = $Articles->find()
            ->where(['Articles.created >=' => $sevenDaysAgo])
            ->count();

        return compact(
            'articleCount',
            'userCount',
            'publishedCount',
            'draftCount',
            'newUsersLast7Days',
            'newArticlesLast7Days'
        );
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
