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
    <?php
    $identity = $this->getRequest()->getAttribute('identity');
    $identityEntity = $identity ? $identity->getOriginalData() : null;
    $isAdmin = $identityEntity && method_exists($identityEntity, 'isAdmin') && $identityEntity->isAdmin();
    ?>

    <div class="settings-page__hero">
        <h2><?= __('Settings') ?></h2>
        <p><?= __('Personalize your experience. Theme and font changes are saved automatically.') ?></p>
    </div>

    <div class="settings-page__grid">
        <section class="settings-page__panel">
            <h3><?= __('Appearance') ?></h3>
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
        </section>

        <section class="settings-page__panel">
            <h3><?= __('Account Access') ?></h3>
            <?php if ($isAdmin) : ?>
                <p class="settings-page__status settings-page__status--ok"><?= __('Your account currently has admin access.') ?></p>
            <?php else : ?>
                <p class="settings-page__status"><?= __('Enter the admin upgrade code to unlock admin dashboard access.') ?></p>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Settings', 'action' => 'index'], 'class' => 'settings-page__upgrade-form']) ?>
                    <?= $this->Form->control('admin_upgrade_code', [
                        'label' => __('Admin Upgrade Code'),
                        'type' => 'password',
                        'required' => true,
                        'autocomplete' => 'off',
                    ]) ?>
                    <?= $this->Form->button(__('Upgrade to Admin'), ['class' => 'button']) ?>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($isAdmin) : ?>
    <div class="settings-page__admin-section">
        <div class="settings-page__admin-heading">
            <span class="settings-page__admin-badge">⚙ Admin Controls</span>
            <p class="settings-page__status"><?= __('Only visible to administrators.') ?></p>
        </div>

        <div class="settings-page__grid">

            <section class="settings-page__panel">
                <h3><?= __('Change User Role') ?></h3>
                <p class="settings-page__status"><?= __('Promote, demote, or ban any account by email.') ?></p>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Settings', 'action' => 'changeUserRole']]) ?>
                    <?= $this->Form->control('target_email', [
                        'label' => __('User Email'),
                        'type'  => 'email',
                        'placeholder' => 'user@example.com',
                        'required' => true,
                    ]) ?>
                    <?= $this->Form->control('target_role', [
                        'label'   => __('New Role'),
                        'type'    => 'select',
                        'options' => ['user' => __('User'), 'admin' => __('Admin'), 'banned' => __('Banned')],
                    ]) ?>
                    <?= $this->Form->button(__('Apply Role'), ['class' => 'button']) ?>
                <?= $this->Form->end() ?>
            </section>

            <section class="settings-page__panel">
                <h3><?= __('Bulk Publish Drafts') ?></h3>
                <p class="settings-page__status"><?= __('Publish every unpublished article in one action.') ?></p>
                <?= $this->Form->postLink(
                    __('Publish All Drafts'),
                    ['controller' => 'Settings', 'action' => 'bulkPublish'],
                    [
                        'class'   => 'button',
                        'confirm' => __('Publish all draft articles? This cannot be undone.'),
                    ]
                ) ?>
            </section>

            <section class="settings-page__panel settings-page__panel--full">
                <h3><?= __('Site Announcement') ?></h3>
                <p class="settings-page__status"><?= __('Show a banner to all visitors. Leave blank to remove it.') ?></p>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Settings', 'action' => 'setAnnouncement']]) ?>
                    <?= $this->Form->control('announcement', [
                        'label'       => false,
                        'type'        => 'textarea',
                        'value'       => h($currentAnnouncement ?? ''),
                        'placeholder' => __('Enter an announcement message...'),
                        'rows'        => 3,
                    ]) ?>
                    <div class="settings-page__row">
                        <?= $this->Form->button(__('Save Announcement'), ['class' => 'button']) ?>
                        <?php if (!empty($currentAnnouncement)) : ?>
                            <?= $this->Form->postLink(
                                __('Clear Announcement'),
                                ['controller' => 'Settings', 'action' => 'setAnnouncement'],
                                ['class' => 'button button-outline', 'data-value' => '']
                            ) ?>
                        <?php endif; ?>
                    </div>
                <?= $this->Form->end() ?>
            </section>

        </div>
    </div>
    <?php endif; ?>

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
