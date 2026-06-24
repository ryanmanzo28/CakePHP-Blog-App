<?php
// src/Controller/ArticlesController.php
declare(strict_types=1);

namespace App\Controller;

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
        $this->set(compact('article'));
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

        // Changed: Set the user_id from the current user.
        $article->user_id = $this->request->getAttribute('identity')->getIdentifier();

        if ($this->Articles->save($article)) {
                $result = $this->moderationService->moderateArticle($article);

                if (!empty($result['deleted'])) {
                    $this->Flash->warning(__('Your article matched moderation filters and was removed.'));

                    return $this->redirect(['action' => 'index']);
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
}