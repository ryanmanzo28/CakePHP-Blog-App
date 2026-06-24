<?php
use function Cake\I18n\__;
?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php $this->assign('title', __('Create Account')); ?>
<div class="auth-card content">
    <div class="auth-card__header">
        <h2 class="auth-card__title"><?= __('Create your account') ?></h2>
        <p class="auth-card__sub"><?= __('Join the community and start sharing your posts.') ?></p>
    </div>

    <?= $this->Flash->render() ?>

    <?= $this->Form->create($user, ['class' => 'auth-card__form']) ?>
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
        <?= $this->Form->button(__('Create Account'), ['class' => 'button auth-card__submit']) ?>
    <?= $this->Form->end() ?>

    <p class="auth-card__switch">
        <?= __('Already have an account?') ?>
        <?= $this->Html->link(__('Sign in'), ['action' => 'login'], ['class' => 'auth-card__link']) ?>
    </p>
</div>
