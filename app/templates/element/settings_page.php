<?php
/**
 * Settings page content.
 *
 * Light/dark theme is applied on <html> by the layout (from the `theme`
 * cookie); this script just toggles it and saves the preference.
 *
 * @var \App\View\AppView $this
 */
?>
<div class="settings-page">
    <h2><?= __('Settings') ?></h2>
    <p><?= __('Edit Your Settings Here') ?></p>
    <p><b><?= __('Your changes will be saved automatically.') ?></b></p>

    <button id="theme-toggle" type="button" class="settings-page__button">
        <?= __('Toggle Theme') ?>
    </button>
</div>

<script>
    document.getElementById('theme-toggle').addEventListener('click', function () {
        var root = document.documentElement;
        var isDark = root.classList.contains('dark-theme');
        var newTheme = isDark ? 'light' : 'dark';

        root.classList.remove('dark-theme', 'light-theme');
        root.classList.add(newTheme + '-theme');

        // Save preference for 1 year.
        document.cookie = 'theme=' + newTheme + '; path=/; max-age=31536000; SameSite=Lax';
    });
</script>
