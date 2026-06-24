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

    <div class="settings-page__controls">
        <button id="theme-toggle" type="button" class="settings-page__button">
            <?= __('Switch Theme') ?>
        </button>

        <label for="font-select" class="settings-page__label"><?= __('Font Family') ?></label>
        <select id="font-select" class="settings-page__select">
            <option value="raleway"><?= __('Raleway') ?></option>
            <option value="merriweather"><?= __('Merriweather') ?></option>
            <option value="fira-sans"><?= __('Fira Sans') ?></option>
            <option value="jetbrains-mono"><?= __('JetBrains Mono') ?></option>
        </select>
    </div>
</div>

<script>
    var root = document.documentElement;
    var themeToggle = document.getElementById('theme-toggle');
    var fontSelect = document.getElementById('font-select');
    var allowedFonts = ['raleway', 'merriweather', 'fira-sans', 'jetbrains-mono'];
    var currentFont = (document.cookie.match(/(?:^|; )font=([^;]+)/) || [null, 'raleway'])[1];

    function safeDecode(value) {
        try {
            return decodeURIComponent(value);
        } catch (e) {
            return value;
        }
    }

    function setThemeToggleText(theme) {
        if (themeToggle) {
            themeToggle.textContent = theme === 'dark'
                ? 'Switch to Light Mode'
                : 'Switch to Dark Mode';
        }
    }

    // Initialize current font selection from cookie/html class.
    if (fontSelect) {
        var initialFont = safeDecode(currentFont);
        if (allowedFonts.indexOf(initialFont) === -1) {
            initialFont = 'raleway';
        }
        fontSelect.value = initialFont;
    }

    setThemeToggleText(root.classList.contains('dark-theme') ? 'dark' : 'light');

    document.getElementById('theme-toggle').addEventListener('click', function () {
        var root = document.documentElement;
        var isDark = root.classList.contains('dark-theme');
        var newTheme = isDark ? 'light' : 'dark';

        root.classList.remove('dark-theme', 'light-theme');
        root.classList.add(newTheme + '-theme');
        setThemeToggleText(newTheme);

        // Save preference for 1 year.
        document.cookie = 'theme=' + newTheme + '; path=/; max-age=31536000; SameSite=Lax';
    });

    if (fontSelect) {
        fontSelect.addEventListener('change', function () {
            var selected = fontSelect.value;
            root.classList.remove('font-raleway', 'font-merriweather', 'font-fira-sans', 'font-jetbrains-mono');
            root.classList.add('font-' + selected);
            document.cookie = 'font=' + encodeURIComponent(selected) + '; path=/; max-age=31536000; SameSite=Lax';
        });
    }
</script>
