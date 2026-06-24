<?php
/**
 * Admin dashboard view.
 *
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Admin Dashboard'));
$identity = $identity ?? null;
$articleCount = $articleCount ?? 0;
$userCount = $userCount ?? 0;
$publishedCount = $publishedCount ?? 0;
$draftCount = $draftCount ?? 0;
$newUsersLast7Days = $newUsersLast7Days ?? 0;
$newArticlesLast7Days = $newArticlesLast7Days ?? 0;
$recentArticles = $recentArticles ?? [];
$recentDrafts = $recentDrafts ?? [];
$recentUsers = $recentUsers ?? [];
$moderationFilters = $moderationFilters ?? [];
$moderationReasonByArticleId = $moderationReasonByArticleId ?? [];
?>
<div class="dashboard-shell">
<div class="dashboard admin-dash">

    <header class="dashboard__hero admin-dash__hero">
        <div class="dashboard__hero-copy admin-dash__hero-copy">
            <h2><?= __('Admin Dashboard') ?></h2>
            <p><?= __('Control center for content, members, and publication health.') ?></p>
        </div>
        <div class="admin-dash__hero-meta">
            <?php if ($identity) : ?>
                <span class="dashboard__badge"><?= __('Signed in as {0}', h($identity->get('email'))) ?></span>
            <?php endif; ?>
            <span class="admin-dash__status"><?= __('Admin Mode Active') ?></span>
        </div>
    </header>

    <section class="dashboard__actions" aria-label="<?= __('Quick actions') ?>">
        <?= $this->Html->link(__('Regular Dashboard'), ['controller' => 'Dashboard', 'action' => 'index'], ['class' => 'dashboard__action-btn admin-dash__action']) ?>
        <?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__action-btn admin-dash__action']) ?>
        <?= $this->Html->link(__('Manage Users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dashboard__action-btn admin-dash__action']) ?>
        <?= $this->Html->link(__('Open Settings'), ['controller' => 'Settings', 'action' => 'index'], ['class' => 'dashboard__action-btn admin-dash__action']) ?>
    </section>

    <section class="dashboard__stats" aria-label="<?= __('Overview metrics') ?>">
        <article class="dashboard__card admin-dash__metric admin-dash__metric--articles">
            <span class="dashboard__card-icon" aria-hidden="true">📰</span>
            <span class="dashboard__card-value"><?= $articleCount ?></span>
            <span class="dashboard__card-label"><?= __('Total Articles') ?></span>
            <?= $this->Html->link(__('View all'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </article>

        <article class="dashboard__card admin-dash__metric admin-dash__metric--users">
            <span class="dashboard__card-icon" aria-hidden="true">👥</span>
            <span class="dashboard__card-value"><?= $userCount ?></span>
            <span class="dashboard__card-label"><?= __('Total Users') ?></span>
            <?= $this->Html->link(__('Manage users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </article>

        <article class="dashboard__card admin-dash__metric admin-dash__metric--published">
            <span class="dashboard__card-icon" aria-hidden="true">✅</span>
            <span class="dashboard__card-value"><?= $publishedCount ?></span>
            <span class="dashboard__card-label"><?= __('Published Posts') ?></span>
            <?= $this->Html->link(__('Write new'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__card-link']) ?>
        </article>

        <article class="dashboard__card admin-dash__metric admin-dash__metric--drafts">
            <span class="dashboard__card-icon" aria-hidden="true">📝</span>
            <span class="dashboard__card-value"><?= $draftCount ?></span>
            <span class="dashboard__card-label"><?= __('Draft Posts') ?></span>
            <?= $this->Html->link(__('Review drafts'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </article>
    </section>

    <section class="admin-dash__insights" aria-label="<?= __('Weekly insight') ?>">
        <article class="admin-dash__insight-card">
            <h3><?= __('Last 7 Days') ?></h3>
            <div class="admin-dash__insight-grid">
                <p><strong><?= $newArticlesLast7Days ?></strong> <?= __('new posts') ?></p>
                <p><strong><?= $newUsersLast7Days ?></strong> <?= __('new users') ?></p>
            </div>
        </article>
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
                        <?php $matchedKeywords = $moderationReasonByArticleId[(int)$article->id] ?? []; ?>
                        <li class="dashboard__list-item">
                            <div class="dashboard__list-main">
                                <?= $this->Html->link(
                                    h($article->title),
                                    ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                    ['class' => 'dashboard__list-title']
                                ) ?>
                                <span class="dashboard__list-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                                <?php if (!empty($matchedKeywords)) : ?>
                                    <span class="admin-dash__reason-badge">
                                        <?= __('Filtered by: {0}', h(implode(', ', $matchedKeywords))) ?>
                                    </span>
                                <?php endif; ?>
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

        <section class="dashboard__panel">
            <div class="dashboard__panel-head">
                <h3><?= __('Recent Drafts') ?></h3>
                <?= $this->Html->link(__('All drafts'), ['controller' => 'Articles', 'action' => 'index']) ?>
            </div>

            <?php if (count($recentDrafts) === 0) : ?>
                <p class="dashboard__empty"><?= __('No drafts waiting for review.') ?></p>
            <?php else : ?>
                <ul class="dashboard__list">
                    <?php foreach ($recentDrafts as $article) : ?>
                        <?php $matchedKeywords = $moderationReasonByArticleId[(int)$article->id] ?? []; ?>
                        <li class="dashboard__list-item">
                            <div class="dashboard__list-main">
                                <?= $this->Html->link(
                                    h($article->title),
                                    ['controller' => 'Articles', 'action' => 'edit', $article->slug],
                                    ['class' => 'dashboard__list-title']
                                ) ?>
                                <span class="dashboard__list-meta"><?= h($article->modified->i18nFormat('MMM d, yyyy')) ?></span>
                                <?php if (!empty($matchedKeywords)) : ?>
                                    <span class="admin-dash__reason-badge">
                                        <?= __('Filtered by: {0}', h(implode(', ', $matchedKeywords))) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="dashboard__pill"><?= __('Draft') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="dashboard__panel admin-dash__moderation-panel">
            <div class="dashboard__panel-head">
                <h3><?= __('Content Filters') ?></h3>
                <?= $this->Form->postLink(
                    __('Reprocess Posts'),
                    ['controller' => 'Dashboard', 'action' => 'reprocessFilters'],
                    ['class' => 'dashboard__card-link', 'confirm' => __('Run moderation rules against all posts?')]
                ) ?>
            </div>

            <?= $this->Form->create(null, ['url' => ['controller' => 'Dashboard', 'action' => 'addFilter'], 'class' => 'admin-dash__filter-form']) ?>
                <?= $this->Form->control('keyword', [
                    'label' => __('Keyword or phrase'),
                    'required' => true,
                    'placeholder' => __('example: spam phrase'),
                ]) ?>
                <div class="admin-dash__filter-actions">
                    <?= $this->Form->control('action_delete', ['type' => 'checkbox', 'label' => __('Delete matching posts')]) ?>
                    <?= $this->Form->control('action_silence', ['type' => 'checkbox', 'label' => __('Silence from feed'), 'checked' => true]) ?>
                    <?= $this->Form->control('action_ban', ['type' => 'checkbox', 'label' => __('Ban author account')]) ?>
                </div>
                <?= $this->Form->button(__('Add Filter'), ['class' => 'button']) ?>
            <?= $this->Form->end() ?>

            <?php if (count($moderationFilters) === 0) : ?>
                <p class="dashboard__empty"><?= __('No moderation filters configured yet.') ?></p>
            <?php else : ?>
                <ul class="dashboard__list admin-dash__filters-list">
                    <?php foreach ($moderationFilters as $filter) : ?>
                        <li class="dashboard__list-item admin-dash__filter-item">
                            <div class="dashboard__list-main">
                                <span class="dashboard__list-title">"<?= h($filter->keyword) ?>"</span>
                                <span class="dashboard__list-meta">
                                    <?php
                                    $labels = [];
                                    if ($filter->action_delete) {
                                        $labels[] = __('delete');
                                    }
                                    if ($filter->action_silence) {
                                        $labels[] = __('silence');
                                    }
                                    if ($filter->action_ban) {
                                        $labels[] = __('ban');
                                    }
                                    echo h(implode(', ', $labels));
                                    ?>
                                </span>
                            </div>
                            <div class="admin-dash__filter-item-actions">
                                <?= $this->Form->postLink(
                                    $filter->active ? __('Disable') : __('Enable'),
                                    ['controller' => 'Dashboard', 'action' => 'toggleFilter', $filter->id],
                                    ['class' => 'dashboard__card-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Dashboard', 'action' => 'deleteFilter', $filter->id],
                                    ['class' => 'dashboard__card-link', 'confirm' => __('Delete this filter?')]
                                ) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

</div>
</div>
