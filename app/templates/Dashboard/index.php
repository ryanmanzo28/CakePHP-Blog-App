<?php
/**
 * Dashboard view (scaffold).
 *
 * Variables available:
 *   $articleCount   (int)
 *   $userCount      (int)
 *   $recentArticles (iterable)
 *   $identity       (\Authorization\IdentityInterface|null)
 *
 * @var \App\View\AppView $this
 * @var int $articleCount
 * @var int $userCount
 * @var iterable $recentArticles
 */
$this->assign('title', __('Dashboard'));
?>
<div class="dashboard">

    <div class="dashboard__header">
        <h2><?= __('Dashboard') ?></h2>
        <?php if ($identity) : ?>
            <p class="dashboard__welcome">
                <?= __('Welcome back, {0}', h($identity->get('email'))) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Stat cards -->
    <div class="dashboard__stats">
        <div class="dashboard__card">
            <span class="dashboard__card-value"><?= $articleCount ?></span>
            <span class="dashboard__card-label"><?= __('Articles') ?></span>
            <?= $this->Html->link(__('View all'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </div>

        <div class="dashboard__card">
            <span class="dashboard__card-value"><?= $userCount ?></span>
            <span class="dashboard__card-label"><?= __('Users') ?></span>
            <?= $this->Html->link(__('View all'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dashboard__card-link']) ?>
        </div>

        <!-- Add more stat cards here as you build out the app -->
    </div>

    <!-- Recent activity feed -->
    <div class="dashboard__section">
        <h3><?= __('Recent Articles') ?></h3>
        <?php if ($recentArticles->isEmpty()) : ?>
            <p><?= __('No articles yet.') ?></p>
        <?php else : ?>
            <table class="dashboard__table">
                <thead>
                    <tr>
                        <th><?= __('Title') ?></th>
                        <th><?= __('Created') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentArticles as $article) : ?>
                        <tr>
                            <td>
                                <?= $this->Html->link(
                                    h($article->title),
                                    ['controller' => 'Articles', 'action' => 'view', $article->slug]
                                ) ?>
                            </td>
                            <td><?= h($article->created->toDateString()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Placeholder sections — fill these out as you build -->
    <div class="dashboard__section dashboard__section--placeholder">
        <h3><?= __('Quick Actions') ?></h3>
        <div class="dashboard__actions">
            <?= $this->Html->link('+ ' . __('New Article'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'dashboard__action-btn']) ?>
            <!-- Add more quick-action buttons here -->
        </div>
    </div>

</div>
