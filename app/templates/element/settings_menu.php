<?php
/**
 * Popout settings / menu bar (skeleton)
 *
 * A fixed button in the bottom-right corner that opens a slide-out panel.
 * The links below are placeholders — point them at real pages as you build them.
 *
 * @var \App\View\AppView $this
 */

// Edit this list to add / rename menu items.
// 'url' accepts any value you'd pass to $this->Url->build() (string or array).
$menuItems = [
    ['label' => __('Dashboard'), 'url' => ['controller' => 'Dashboard', 'action' => 'index'], 'icon' => '🏠'],
    ['label' => __('Articles'), 'url' => ['controller' => 'Articles', 'action' => 'index'], 'icon' => '📄'],
    ['label' => __('Profile'), 'url' => '#', 'icon' => '👤'],
    ['label' => __('Settings'), 'url' => ['controller' => 'Settings', 'action' => 'index'], 'icon' => '⚙️'],
];

$identity = $this->getRequest()->getAttribute('identity');
?>
<div class="popout-menu" data-popout-menu>
    <button
        type="button"
        class="popout-menu__toggle"
        aria-expanded="false"
        aria-controls="popout-menu-panel"
        aria-label="<?= __('Open menu') ?>"
        data-popout-toggle
    >
        <span class="popout-menu__toggle-bar"></span>
        <span class="popout-menu__toggle-bar"></span>
        <span class="popout-menu__toggle-bar"></span>
    </button>

    <nav id="popout-menu-panel" class="popout-menu__panel" aria-hidden="true" data-popout-panel>
        <div class="popout-menu__header">
            <span class="popout-menu__title"><?= __('Menu') ?></span>
            <button
                type="button"
                class="popout-menu__close"
                aria-label="<?= __('Close menu') ?>"
                data-popout-close
            >&times;</button>
        </div>

        <ul class="popout-menu__list">
            <?php foreach ($menuItems as $item) : ?>
                <li class="popout-menu__item">
                    <?= $this->Html->link(
                        '<span class="popout-menu__icon">' . h($item['icon']) . '</span>'
                            . '<span class="popout-menu__label">' . h($item['label']) . '</span>',
                        $item['url'],
                        ['class' => 'popout-menu__link', 'escape' => false]
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="popout-menu__footer">
            <?php if ($identity) : ?>
                <?= $this->Html->link(
                    __('Logout'),
                    ['controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'popout-menu__link popout-menu__link--accent']
                ) ?>
            <?php else : ?>
                <?= $this->Html->link(
                    __('Login'),
                    ['controller' => 'Users', 'action' => 'login'],
                    ['class' => 'popout-menu__link popout-menu__link--accent']
                ) ?>
            <?php endif; ?>
        </div>
    </nav>
</div>

<script>
(function () {
    var root = document.querySelector('[data-popout-menu]');
    if (!root) {
        return;
    }
    var toggle = root.querySelector('[data-popout-toggle]');
    var panel = root.querySelector('[data-popout-panel]');
    var closeBtn = root.querySelector('[data-popout-close]');

    function setOpen(open) {
        root.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        panel.setAttribute('aria-hidden', String(!open));
    }

    toggle.addEventListener('click', function () {
        setOpen(!root.classList.contains('is-open'));
    });
    closeBtn.addEventListener('click', function () {
        setOpen(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setOpen(false);
        }
    });
    document.addEventListener('click', function (e) {
        if (root.classList.contains('is-open') && !root.contains(e.target)) {
            setOpen(false);
        }
    });
})();
</script>
