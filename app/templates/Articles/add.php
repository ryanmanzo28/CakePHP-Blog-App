<?php
use function Cake\I18n\__;
?>
<!-- File: templates/Articles/add.php -->

<section class="article-form article-form--create content">
    <header class="article-form__header">
        <h1><?= __('Create New Article') ?></h1>
        <p><?= __('Write your post, add tags, and choose whether to publish immediately.') ?></p>
    </header>

    <?php
        echo $this->Form->create($article, ['class' => 'article-form__form']);
        echo $this->Form->control('user_id', ['type' => 'hidden', 'value' => 1]);
    ?>

    <div class="article-form__grid">
        <div class="article-form__main">
            <?php
                echo $this->Form->control('title', [
                    'label' => __('Title'),
                    'placeholder' => __('Enter a clear, descriptive headline'),
                ]);

                echo $this->Form->control('body', [
                    'label' => __('Article Content'),
                    'rows' => 10,
                    'placeholder' => __('Write your article content here...'),
                ]);
            ?>
        </div>

        <aside class="article-form__side">
            <div class="article-form__card">
                <?php
                    echo $this->Form->control('tag_string', [
                        'type' => 'text',
                        'label' => __('Tags'),
                        'placeholder' => __('news, product, update'),
                    ]);
                ?>
                <p class="article-form__hint"><?= __('Separate tags with commas.') ?></p>

                <?= $this->Form->control('published', [
                    'type' => 'checkbox',
                    'label' => __('Publish now'),
                ]) ?>
            </div>
        </aside>
    </div>

    <div class="article-form__actions">
        <?= $this->Form->button(__('Save Article'), ['class' => 'button button-primary']) ?>
        <?= $this->Html->link(__('Cancel'), ['controller' => 'Articles', 'action' => 'index'], ['class' => 'button button-outline']) ?>
    </div>

    <?= $this->Form->end() ?>
</section>