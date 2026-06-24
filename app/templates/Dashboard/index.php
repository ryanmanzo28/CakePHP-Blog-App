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
$likeCountByArticleId = $likeCountByArticleId ?? [];
$likedByCurrentUserByArticleId = $likedByCurrentUserByArticleId ?? [];
$notifications = $notifications ?? [];
$unreadNotificationCount = $unreadNotificationCount ?? 0;
$followedUserIds = $followedUserIds ?? [];
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
                    $readMins = max(1, (int)ceil(str_word_count(strip_tags($body)) / 200));
                    ?>
                    <article class="dashboard-feed__card">
                        <header class="dashboard-feed__card-head">
                            <?= $this->Html->link(
                                h($article->title),
                                ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                ['class' => 'dashboard-feed__title']
                            ) ?>
                            <div class="dashboard-feed__head-meta">
                                <span class="dashboard__list-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                                <span class="dashboard-feed__read-time"><?= __n('{0} min', '{0} min', $readMins, $readMins) ?></span>
                            </div>
                        </header>

                        <?php
                        $author = $article->user ?? null;
                        $authorId = $author ? (int)$author->id : (int)$article->user_id;
                        $authorEmail = $author ? (string)$author->email : __('Unknown author');
                        $isFollowingAuthor = in_array($authorId, $followedUserIds, true);
                        ?>
                        <div class="dashboard-feed__meta-row">
                            <span class="dashboard-feed__author"><?= __('by {0}', h($authorEmail)) ?></span>
                            <?php if (!empty($currentUserId) && $authorId !== (int)$currentUserId) : ?>
                                <?php if ($isFollowingAuthor) : ?>
                                    <?= $this->Form->postLink(
                                        __('Following'),
                                        ['controller' => 'Users', 'action' => 'unfollow', $authorId],
                                        ['class' => 'dashboard-feed__follow-btn is-following']
                                    ) ?>
                                <?php else : ?>
                                    <?= $this->Form->postLink(
                                        __('Follow'),
                                        ['controller' => 'Users', 'action' => 'follow', $authorId],
                                        ['class' => 'dashboard-feed__follow-btn']
                                    ) ?>
                                <?php endif; ?>
                            <?php elseif ($isFollowingAuthor) : ?>
                                <span class="dashboard-feed__priority-pill"><?= __('Followed') ?></span>
                            <?php endif; ?>
                        </div>

                        <p class="dashboard-feed__excerpt">
                            <?= h($excerpt) ?>
                            <?php if (mb_strlen($body) > mb_strlen($excerpt)) : ?>
                                ...
                            <?php endif; ?>
                        </p>

                        <div class="dashboard-feed__actions">
                            <?php
                            $articleId = (int)$article->id;
                            $likeCount = (int)($likeCountByArticleId[$articleId] ?? 0);
                            $isLiked = !empty($likedByCurrentUserByArticleId[$articleId]);
                            ?>
                            <span class="dashboard-feed__likes-count"><?= __n('{0} like', '{0} likes', $likeCount, $likeCount) ?></span>
                            <?php if (!empty($currentUserId)) : ?>
                                <?= $this->Form->postLink(
                                    $isLiked ? __('Unlike') : __('Like'),
                                    ['controller' => 'Articles', 'action' => 'toggleLike', $articleId],
                                    ['class' => 'dashboard-feed__like-btn' . ($isLiked ? ' is-liked' : '')]
                                ) ?>
                            <?php else : ?>
                                <?= $this->Html->link(__('Sign in to like'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'dashboard__card-link']) ?>
                            <?php endif; ?>
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
                    <h3><?= __('Notifications') ?></h3>
                    <?php if (!empty($unreadNotificationCount)) : ?>
                        <span class="dashboard__pill dashboard__pill--role"><?= __n('{0} unread', '{0} unread', $unreadNotificationCount, $unreadNotificationCount) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications) || count($notifications) === 0) : ?>
                    <p class="dashboard__empty"><?= __('No notifications yet.') ?></p>
                <?php else : ?>
                    <ul class="dashboard__list">
                        <?php foreach ($notifications as $notification) : ?>
                            <li class="dashboard__list-item">
                                <div class="dashboard__list-main">
                                    <?php if (!empty($notification->article_id) && !empty($notification->article->slug)) : ?>
                                        <?= $this->Html->link(
                                            h($notification->message),
                                            ['controller' => 'Articles', 'action' => 'view', $notification->article->slug],
                                            ['class' => 'dashboard__list-title']
                                        ) ?>
                                    <?php else : ?>
                                        <span class="dashboard__list-title"><?= h($notification->message) ?></span>
                                    <?php endif; ?>
                                    <span class="dashboard__list-meta"><?= h($notification->created->i18nFormat('MMM d, yyyy h:mm a')) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

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
