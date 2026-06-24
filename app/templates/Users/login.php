<?php
use function Cake\I18n\__;
$this->assign('title', __('Sign In'));
?>
<div class="auth-card content">
    <div class="auth-card__header">
        <h2 class="auth-card__title"><?= __('Welcome back') ?></h2>
        <p class="auth-card__sub"><?= __('Sign in to your account to continue.') ?></p>
    </div>

    <?= $this->Flash->render() ?>

    <?= $this->Form->create(null, ['class' => 'auth-card__form']) ?>
        <?= $this->Form->control('email', [
            'required' => true,
            'label' => __('Email address'),
            'placeholder' => 'you@example.com',
            'type' => 'email',
        ]) ?>
        <?= $this->Form->control('password', [
            'required' => true,
            'label' => __('Password'),
            'placeholder' => '••••••••',
        ]) ?>
        <?= $this->Form->button(__('Sign In'), ['class' => 'button auth-card__submit']) ?>
    <?= $this->Form->end() ?>

    <p class="auth-card__switch">
        <?= __('New here?') ?>
        <?= $this->Html->link(__('Create an account'), ['action' => 'add'], ['class' => 'auth-card__link']) ?>
    </p>
</div>
