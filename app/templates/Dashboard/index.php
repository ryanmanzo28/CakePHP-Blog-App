<?php
/**
 * Dashboard view.
 *
 * Variables available:
 *   $articleCount   (int)
 *   $userCount      (int)
 *   $publishedCount (int)
 *   $recentArticles (iterable)
 *   $recentUsers    (iterable)
 *   $identity       (\Authorization\IdentityInterface|null)
 *
 * @var \App\View\AppView $this
 * @var int $articleCount
 * @var int $userCount
 * @var int $publishedCount
 * @var iterable $recentArticles
 * @var iterable $recentUsers
 */
$this->assign('title', __('Dashboard'));
?>
<div class="dashboard-shell">
<div class="dashboard">

    <header class="dashboard__hero">
        <div class="dashboard__hero-copy">
            <h2><?= __('Dashboard') ?></h2>
            <p><?= __('Everything important at a glance.') ?></p>
        </div>
        <?php if ($identity) : ?>
            <span class="dashboard__badge"><?= __('Signed in as {0}', h($identity->get('email'))) ?></span>
        <?php endif; ?>
    </header>

    <section class="dashboard__stats" aria-label="<?= __('Overview metrics') ?>">
        <article class="dashboard__card">
            <span class="dashboard__card-icon" aria-hidden="true">📰</span>
            <span class="dashboard__card-value"><?= $articleCount ?></span>
            <span class="dashboard__card-label"><?= __('Total Articles') ?></span>
            <?= $this->Html->link(__('View all'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </article>

        <article class="dashboard__card">
            <span class="dashboard__card-icon" aria-hidden="true">👥</span>
            <span class="dashboard__card-value"><?= $userCount ?></span>
            <span class="dashboard__card-label"><?= __('Total Users') ?></span>
            <?= $this->Html->link(__('Manage users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </article>

        <article class="dashboard__card">
            <span class="dashboard__card-icon" aria-hidden="true">✅</span>
            <span class="dashboard__card-value"><?= $publishedCount ?></span>
            <span class="dashboard__card-label"><?= __('Published Posts') ?></span>
            <?= $this->Html->link(__('Write new'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__card-link']) ?>
        </article>
    </section>

    <section class="dashboard__actions" aria-label="<?= __('Quick actions') ?>">
        <?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__action-btn']) ?>
        <?= $this->Html->link(__('Manage Users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dashboard__action-btn']) ?>
        <?= $this->Html->link(__('Open Settings'), ['controller' => 'Settings', 'action' => 'index'], ['class' => 'dashboard__action-btn']) ?>
    </section>

    <div class="dashboard__grid">
        <section class="dashboard__panel">
            <div class="dashboard__panel-head">
                <h3><?= __('Recent Articles') ?></h3>
                <?= $this->Html->link(__('See all'), ['controller' => 'Articles', 'action' => 'index']) ?>
            </div>

            <?php if (count($recentArticles) === 0) : ?>
                <p class="dashboard__empty"><?= __('No articles yet.') ?></p>
            <?php else : ?>
                <ul class="dashboard__list">
                    <?php foreach ($recentArticles as $article) : ?>
                        <li class="dashboard__list-item">
                            <div class="dashboard__list-main">
                                <?= $this->Html->link(
                                    h($article->title),
                                    ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                    ['class' => 'dashboard__list-title']
                                ) ?>
                                <span class="dashboard__list-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                            </div>
                            <span class="dashboard__pill"><?= $article->published ? __('Published') : __('Draft') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="dashboard__panel">
            <div class="dashboard__panel-head">
                <h3><?= __('Newest Users') ?></h3>
                <?= $this->Html->link(__('User list'), ['controller' => 'Users', 'action' => 'index']) ?>
            </div>

            <?php if (count($recentUsers) === 0) : ?>
                <p class="dashboard__empty"><?= __('No users found.') ?></p>
            <?php else : ?>
                <ul class="dashboard__list">
                    <?php foreach ($recentUsers as $user) : ?>
                        <li class="dashboard__list-item">
                            <div class="dashboard__list-main">
                                <span class="dashboard__list-title"><?= h($user->email) ?></span>
                                <span class="dashboard__list-meta"><?= h($user->created->i18nFormat('MMM d, yyyy')) ?></span>
                            </div>
                            <span class="dashboard__pill dashboard__pill--role"><?= h($user->role) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

</div>
</div>
