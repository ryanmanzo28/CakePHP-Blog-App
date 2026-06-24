<?php
use function Cake\I18n\__;
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'Hydracor';
$themeClass = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-theme' : 'light-theme';
$fontCookie = $_COOKIE['font'] ?? 'raleway';
$fontWhitelist = ['raleway', 'merriweather', 'fira-sans', 'jetbrains-mono'];
$fontClass = in_array($fontCookie, $fontWhitelist, true) ? 'font-' . $fontCookie : 'font-raleway';
$cssVersion = (string)(@filemtime(WWW_ROOT . 'css/theme.css') ?: time());
?>
<!DOCTYPE html>
<html class="<?= h($themeClass . ' ' . $fontClass) ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ?> &mdash; <?= $cakeDescription ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&family=Merriweather:wght@400;700&family=Fira+Sans:wght@400;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <?= $this->Html->css([
        'normalize.min',
        'milligram.min',
        'cake.css?v=' . $cssVersion,
        'settings-menu.css?v=' . $cssVersion,
        'theme.css?v=' . $cssVersion,
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
<?php
$navIdentity = $this->getRequest()->getAttribute('identity');
$navIsLoggedIn = $navIdentity !== null;
$navIdentityEntity = $navIsLoggedIn ? $navIdentity->getOriginalData() : null;
$navIsAdmin = $navIdentityEntity && method_exists($navIdentityEntity, 'isAdmin') && $navIdentityEntity->isAdmin();
?>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>">Hydracor</a>
        </div>
        <div class="top-nav-links">
            <?php if ($navIsLoggedIn) : ?>
                <?= $this->Html->link(__('Feed'), ['controller' => 'Dashboard', 'action' => 'index']) ?>
                <?php if ($navIsAdmin) : ?>
                    <?= $this->Html->link(__('Admin'), ['controller' => 'Dashboard', 'action' => 'admin']) ?>
                <?php endif; ?>
                <?= $this->Html->link(__('Write'), ['controller' => 'Articles', 'action' => 'add']) ?>
                <?= $this->Html->link(__('Profile'), ['controller' => 'Users', 'action' => 'profile']) ?>
                <?= $this->Html->link(__('Sign out'), ['controller' => 'Users', 'action' => 'logout']) ?>
            <?php else : ?>
                <?= $this->Html->link(__('Sign in'), ['controller' => 'Users', 'action' => 'login']) ?>
                <?= $this->Html->link(__('Register'), ['controller' => 'Users', 'action' => 'add']) ?>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?php
            $announcementFile = TMP . 'announcement.txt';
            if (file_exists($announcementFile)) :
                $announcementText = trim((string)file_get_contents($announcementFile));
                if ($announcementText !== '') :
            ?>
                <div class="site-announcement" role="alert">
                    <span class="site-announcement__icon">📢</span>
                    <span class="site-announcement__text"><?= h($announcementText) ?></span>
                </div>
            <?php endif; endif; ?>
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <?= $this->element('settings_menu') ?>
    <footer class="site-footer">
        <div class="site-footer__inner container">
            <span class="site-footer__brand">Hydracor</span>
            <span class="site-footer__copy">&copy; <?= date('Y') ?></span>
            <nav class="site-footer__links">
                <?= $this->Html->link(__('Feed'), ['controller' => 'Dashboard', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Articles'), ['controller' => 'Articles', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Settings'), ['controller' => 'Settings', 'action' => 'index']) ?>
            </nav>
        </div>
    </footer>
</body>
</html>
