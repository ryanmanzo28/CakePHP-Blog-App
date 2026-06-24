<?php
use function Cake\I18n\__;

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array<\App\Model\Entity\Article> $myArticles
 */
$this->assign('title', __('My Profile'));
$initial = strtoupper(substr((string)$user->email, 0, 1));
?>
<section class="profile-page content">
    <header class="profile-page__header">
        <h2><?= __('My Profile') ?></h2>
        <p><?= __('Manage your profile picture and review your posts.') ?></p>
    </header>

    <div class="profile-page__grid">
        <section class="profile-page__card">
            <h3><?= __('Profile Picture') ?></h3>
            <?php if (!empty($user->profile_image)) : ?>
                <?= $this->Html->image($user->profile_image, ['class' => 'profile-page__avatar', 'alt' => __('Profile picture')]) ?>
            <?php else : ?>
                <span class="profile-page__avatar profile-page__avatar--placeholder"><?= h($initial) ?></span>
            <?php endif; ?>

            <?= $this->Form->create($user, ['type' => 'file']) ?>
                <?= $this->Form->control('profile_upload', [
                    'type' => 'file',
                    'label' => __('Upload new picture'),
                    'accept' => 'image/png,image/jpeg,image/gif,image/webp',
                    'required' => false,
                ]) ?>
                <?= $this->Form->button(__('Save Profile'), ['class' => 'button']) ?>
            <?= $this->Form->end() ?>
        </section>

        <section class="profile-page__card">
            <h3><?= __('Account') ?></h3>
            <p><strong><?= __('Email:') ?></strong> <?= h($user->email) ?></p>
            <p><strong><?= __('Role:') ?></strong> <?= h($user->role) ?></p>
            <p><strong><?= __('Joined:') ?></strong> <?= h($user->created?->i18nFormat('MMM d, yyyy')) ?></p>
        </section>
    </div>

    <section class="profile-page__posts profile-page__card">
        <div class="profile-page__posts-head">
            <h3><?= __('My Blog Posts') ?></h3>
            <?= $this->Html->link(__('Create New'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'button button-outline']) ?>
        </div>

        <?php if (count($myArticles) === 0) : ?>
            <p class="profile-page__empty"><?= __('You have not posted anything yet.') ?></p>
        <?php else : ?>
            <ul class="profile-page__list">
                <?php foreach ($myArticles as $article) : ?>
                    <li class="profile-page__list-item">
                        <div class="profile-page__post-main">
                            <?= $this->Html->link(
                                h($article->title),
                                ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                ['class' => 'profile-page__post-title']
                            ) ?>
                            <span class="profile-page__post-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                        </div>
                        <div class="profile-page__post-actions">
                            <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->slug], ['class' => 'dashboard__card-link']) ?>
                            <span class="dashboard__pill"><?= $article->published ? __('Published') : __('Draft') ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</section>
