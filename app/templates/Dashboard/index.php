<?php
/**
 * Dashboard view.
 *
 * Variables available:
 *   $identity         (\Authorization\IdentityInterface|null)
 *   $currentUserId    (int|null)
 *   $feedArticles     (iterable)
 *   $myRecentArticles (iterable)
 *
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Dashboard'));
$identity = $identity ?? null;
$currentUserId = $currentUserId ?? null;
$feedArticles = $feedArticles ?? [];
$myRecentArticles = $myRecentArticles ?? [];
?>
<div class="dashboard-shell">
<div class="dashboard">

    <header class="dashboard__hero dashboard__hero--feed">
        <div class="dashboard__hero-copy">
            <h2><?= __('Your Feed') ?></h2>
            <p><?= __('Discover recent posts and publish your own updates.') ?></p>
        </div>
        <?php if ($identity) : ?>
            <span class="dashboard__badge"><?= __('Signed in as {0}', h($identity->get('email'))) ?></span>
        <?php endif; ?>
    </header>

    <section class="dashboard__actions" aria-label="<?= __('Create and manage') ?>">
        <?= $this->Html->link(__('Create Blog Post'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__action-btn']) ?>
        <?= $this->Html->link(__('Browse All Articles'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'dashboard__action-btn']) ?>
        <?= $this->Html->link(__('Settings'), ['controller' => 'Settings', 'action' => 'index'], ['class' => 'dashboard__action-btn']) ?>
    </section>

    <div class="dashboard-feed">
        <section class="dashboard-feed__main" aria-label="<?= __('Article feed') ?>">
            <?php if (empty($feedArticles) || count($feedArticles) === 0) : ?>
                <article class="dashboard-feed__card">
                    <p class="dashboard__empty"><?= __('No published articles yet.') ?></p>
                </article>
            <?php else : ?>
                <?php foreach ($feedArticles as $article) : ?>
                    <?php
                    $body = trim((string)$article->body);
                    $sentences = preg_split('/(?<=[.!?])\s+/', $body) ?: [];
                    $excerpt = implode(' ', array_slice($sentences, 0, 4));
                    if ($excerpt === '') {
                        $excerpt = mb_substr($body, 0, 260);
                    }
                    ?>
                    <article class="dashboard-feed__card">
                        <header class="dashboard-feed__card-head">
                            <?= $this->Html->link(
                                h($article->title),
                                ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                ['class' => 'dashboard-feed__title']
                            ) ?>
                            <span class="dashboard__list-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                        </header>

                        <p class="dashboard-feed__excerpt">
                            <?= h($excerpt) ?>
                            <?php if (mb_strlen($body) > mb_strlen($excerpt)) : ?>
                                ...
                            <?php endif; ?>
                        </p>

                        <div class="dashboard-feed__actions">
                            <?= $this->Html->link(__('Read more'), ['controller' => 'Articles', 'action' => 'view', $article->slug], ['class' => 'dashboard__card-link']) ?>
                            <?php if (!empty($currentUserId) && (int)$article->user_id === (int)$currentUserId) : ?>
                                <?= $this->Html->link(__('Edit my post'), ['controller' => 'Articles', 'action' => 'edit', $article->slug], ['class' => 'dashboard__card-link']) ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="dashboard-feed__side" aria-label="<?= __('Your recent posts') ?>">
            <section class="dashboard__panel">
                <div class="dashboard__panel-head">
                    <h3><?= __('Your Recent Posts') ?></h3>
                    <?= $this->Html->link(__('New'), ['controller' => 'Articles', 'action' => 'add']) ?>
                </div>

                <?php if (empty($myRecentArticles) || count($myRecentArticles) === 0) : ?>
                    <p class="dashboard__empty"><?= __('You have not posted yet.') ?></p>
                <?php else : ?>
                    <ul class="dashboard__list">
                        <?php foreach ($myRecentArticles as $article) : ?>
                            <li class="dashboard__list-item">
                                <div class="dashboard__list-main">
                                    <?= $this->Html->link(
                                        h($article->title),
                                        ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                        ['class' => 'dashboard__list-title']
                                    ) ?>
                                    <span class="dashboard__list-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                                </div>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->slug], ['class' => 'dashboard__card-link']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </aside>
    </div>

</div>
</div>
