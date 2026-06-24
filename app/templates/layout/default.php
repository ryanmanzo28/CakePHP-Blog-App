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

$cakeDescription = 'CakePHP: the rapid development php framework';
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
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
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
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Cake</span>PHP</a>
        </div>
        <div class="top-nav-links">
            <a target="_blank" rel="noopener" href="https://book.cakephp.org/4/">Documentation</a>
            <a target="_blank" rel="noopener" href="https://api.cakephp.org/">API</a>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <?= $this->element('settings_menu') ?>
    <footer>
    </footer>
</body>
</html>
