<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var int $likeCount
 * @var bool $likedByCurrentUser
 * @var int|null $currentUserId
 */
use function Cake\I18n\_\_;

$this->assign('title', h($article->title));

$wordCount = str_word_count(strip_tags((string)$article->body));
$readMinutes = max(1, (int)ceil($wordCount / 200));
$tagList = array_filter(array_map('trim', explode(',', (string)$article->tag_string)));
?>
<article class="article-view content">

    <header class="article-view__header">
        <div class="article-view__back">
            <?= $this->Html->link(__('← Back to feed'), ['controller' => 'Dashboard', 'action' => 'index'], ['class' => 'article-view__back-link']) ?>
        </div>

        <h1 class="article-view__title"><?= h($article->title) ?></h1>

        <div class="article-view__meta">
            <span class="article-view__date"><?= h($article->created->i18nFormat('MMMM d, yyyy')) ?></span>
            <span class="article-view__sep">·</span>
            <span class="article-view__read-time"><?= __n('{0} min read', '{0} min read', $readMinutes, $readMinutes) ?></span>
            <?php if (!empty($tagList)) : ?>
                <span class="article-view__sep">·</span>
                <span class="article-view__tags">
                    <?php foreach ($tagList as $tag) : ?>
                        <span class="article-view__tag"><?= h($tag) ?></span>
                    <?php endforeach; ?>
                </span>
            <?php endif; ?>
        </div>
    </header>

    <div class="article-view__body">
        <?= nl2br(h($article->body)) ?>
    </div>

    <footer class="article-view__footer">
        <div class="article-view__likes">
            <span class="article-view__like-count"><?= __n('{0} like', '{0} likes', $likeCount ?? 0, $likeCount ?? 0) ?></span>
            <?php if (!empty($currentUserId)) : ?>
                <?= $this->Form->postLink(
                    ($likedByCurrentUser ?? false) ? __('❤ Unlike') : __('♡ Like'),
                    ['action' => 'toggleLike', $article->id],
                    ['class' => 'article-view__like-btn' . (($likedByCurrentUser ?? false) ? ' is-liked' : '')]
                ) ?>
            <?php else : ?>
                <?= $this->Html->link(__('Sign in to like'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'article-view__like-btn']) ?>
            <?php endif; ?>
        </div>

        <div class="article-view__actions">
            <?php if (!empty($currentUserId) && (int)$article->user_id === (int)$currentUserId) : ?>
                <?= $this->Html->link(__('Edit post'), ['action' => 'edit', $article->slug], ['class' => 'button button-outline article-view__edit-btn']) ?>
            <?php endif; ?>
        </div>
    </footer>

</article>