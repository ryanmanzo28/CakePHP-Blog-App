<?php
// src/Controller/ArticlesController.php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Service\ContentModerationService;
use Cake\Http\Response;

/**
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    private ContentModerationService $moderationService;

    public function initialize(): void
    {
        parent::initialize();
        $this->moderationService = new ContentModerationService();
    }

    public function index(): void
    {
        // View, index and tags actions are public methods
// and don't require authorization checks.
        $this->Authorization->skipAuthorization();
        $articles = $this->paginate($this->Articles);
        $this->set(compact('articles'));
    }

    public function view($slug = null): void
    {
        // View, index and tags actions are public methods
// and don't require authorization checks.
        $this->Authorization->skipAuthorization();
        // Update retrieving tags with contain()
        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();

        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;
        $currentUserId = $identityEntity instanceof User ? (int)$identityEntity->id : null;

        /** @var \Cake\ORM\Table $Likes */
        $Likes = $this->fetchTable('Likes');
        $likeCount = $Likes->find()
            ->where(['Likes.article_id' => (int)$article->id])
            ->count();

        $likedByCurrentUser = false;
        if ($currentUserId !== null) {
            $likedByCurrentUser = $Likes->find()
                ->where([
                    'Likes.article_id' => (int)$article->id,
                    'Likes.user_id' => $currentUserId,
                ])
                ->count() > 0;
        }

        $this->set(compact('article', 'likeCount', 'likedByCurrentUser', 'currentUserId'));
    }

    public function toggleLike($id = null)
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);

        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;
        if (!$identityEntity instanceof User) {
            $this->Flash->error(__('Please sign in to like posts.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $article = $this->Articles->get((int)$id);

        /** @var \Cake\ORM\Table $Likes */
        $Likes = $this->fetchTable('Likes');
        /** @var \Cake\ORM\Table $Notifications */
        $Notifications = $this->fetchTable('Notifications');

        $existingLike = $Likes->find()
            ->where([
                'Likes.article_id' => (int)$article->id,
                'Likes.user_id' => (int)$identityEntity->id,
            ])
            ->first();

        if ($existingLike) {
            $Likes->delete($existingLike);
            $this->Flash->success(__('Like removed.'));

            return $this->redirect($this->referer(['action' => 'view', $article->slug], true));
        }

        $like = $Likes->newEntity([
            'article_id' => (int)$article->id,
            'user_id' => (int)$identityEntity->id,
        ]);

        if ($Likes->save($like)) {
            if ((int)$article->user_id !== (int)$identityEntity->id) {
                $notification = $Notifications->newEntity([
                    'user_id' => (int)$article->user_id,
                    'actor_user_id' => (int)$identityEntity->id,
                    'article_id' => (int)$article->id,
                    'type' => 'like',
                    'message' => __('{0} liked your post "{1}".', $identityEntity->email, $article->title),
                    'is_read' => false,
                ]);
                $Notifications->save($notification);
            }

            $this->Flash->success(__('Post liked.'));
        } else {
            $this->Flash->error(__('Could not like this post.'));
        }

        return $this->redirect($this->referer(['action' => 'view', $article->slug], true));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        $this->Authorization->authorize($article);

        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            if ($article->published === null) {
                $article->published = false;
            }

            $article->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Articles->save($article)) {
                $result = $this->moderationService->moderateArticle($article);

                if (!empty($result['deleted'])) {
                    $this->Flash->warning(__('Your article matched moderation filters and was removed.'));

                    return $this->redirect(['action' => 'index']);
                }

                if ((bool)$article->published && (bool)!$article->silenced) {
                    $this->notifyFollowersOfNewPost($article);
                }

                $this->Flash->success(__('Your article has been saved.'));

                if (!empty($result['silenced'])) {
                    $this->Flash->warning(__('Your article is hidden from the main feed due to moderation filters.'));
                }
                if (!empty($result['bannedUser'])) {
                    $this->Flash->error(__('Your account has been restricted by moderation policy.'));
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $tags = $this->Articles->Tags->find('list')->all();
        $this->set(compact('article', 'tags'));
    }

    // in src/Controller/ArticlesController.php

public function edit($slug)
{
    $article = $this->Articles
        ->findBySlug($slug)
        ->contain('Tags') // load associated Tags
        ->firstOrFail();
    $this->Authorization->authorize($article);

    if ($this->request->is(['post', 'put'])) {
        $this->Articles->patchEntity($article, $this->request->getData(), [
            // Added: Disable modification of user_id.
            'accessibleFields' => ['user_id' => false],
        ]);

        if ($article->published === null) {
            $article->published = false;
        }

        if ($this->Articles->save($article)) {
                $result = $this->moderationService->moderateArticle($article);

                if (!empty($result['deleted'])) {
                    $this->Flash->warning(__('The article matched moderation filters and was removed.'));

                    return $this->redirect(['action' => 'index']);
                }

            $this->Flash->success(__('Your article has been updated.'));

                if (!empty($result['silenced'])) {
                    $this->Flash->warning(__('This article is hidden from the main feed due to moderation filters.'));
                }
                if (!empty($result['bannedUser'])) {
                    $this->Flash->error(__('Your account has been restricted by moderation policy.'));
                }

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__('Unable to update your article.'));
    }
    $tags = $this->Articles->Tags->find('list')->all();
    $this->set(compact('article', 'tags'));
}
    public function delete(?string $slug): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->Authorization->authorize($article);
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));

            return $this->redirect(['action' => 'index']);
        }

        return null;
    }

    public function tags(): void
    {
        // View, index and tags actions are public methods
// and don't require authorization checks.
        $this->Authorization->skipAuthorization();
        // The 'pass' key is provided by CakePHP and contains all
        // the passed URL path segments in the request.
        $tags = $this->request->getParam('pass');

        // Use the ArticlesTable to find tagged articles.
        $articles = $this->Articles->find('tagged', ['tags' => $tags])
            ->all();

        // Pass variables into the view template context.
        $this->set([
            'articles' => $articles,
            'tags' => $tags,
        ]);
    }

    /**
     * Notify followers when an author publishes a new post.
     *
     * @param \App\Model\Entity\Article $article Saved article.
     * @return void
     */
    private function notifyFollowersOfNewPost($article): void
    {
        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');
        /** @var \Cake\ORM\Table $Notifications */
        $Notifications = $this->fetchTable('Notifications');
        /** @var \App\Model\Table\UsersTable $Users */
        $Users = $this->fetchTable('Users');

        $author = $Users->get((int)$article->user_id);

        $followerRows = $Follows->find()
            ->select(['follower_id'])
            ->where(['Follows.following_id' => (int)$article->user_id])
            ->enableHydration(false)
            ->toArray();

        foreach ($followerRows as $row) {
            $followerId = (int)$row['follower_id'];
            if ($followerId === (int)$article->user_id) {
                continue;
            }

            $notification = $Notifications->newEntity([
                'user_id' => $followerId,
                'actor_user_id' => (int)$article->user_id,
                'article_id' => (int)$article->id,
                'type' => 'new_post',
                'message' => __('{0} posted a new article: "{1}".', $author->email, $article->title),
                'is_read' => false,
            ]);
            $Notifications->save($notification);
        }
    }
}