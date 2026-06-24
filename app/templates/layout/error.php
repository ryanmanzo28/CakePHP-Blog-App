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
?>
<!DOCTYPE html>
<?php
$themeClass = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-theme' : 'light-theme';
$fontCookie = $_COOKIE['font'] ?? 'raleway';
$fontWhitelist = ['raleway', 'merriweather', 'fira-sans', 'jetbrains-mono'];
$fontClass = in_array($fontCookie, $fontWhitelist, true) ? 'font-' . $fontCookie : 'font-raleway';
$cssVersion = (string)(@filemtime(WWW_ROOT . 'css/theme.css') ?: time());
?>
<html class="<?= h($themeClass . ' ' . $fontClass) ?>">
<head>
    <?= $this->Html->charset() ?>
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&family=Merriweather:wght@400;700&family=Fira+Sans:wght@400;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <?= $this->Html->css([
        'normalize.min',
        'milligram.min',
        'cake.css?v=' . $cssVersion,
        'theme.css?v=' . $cssVersion,
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <div class="error-container">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
        <?= $this->Html->link(__('Back'), 'javascript:history.back()') ?>
    </div>
</body>
</html>
