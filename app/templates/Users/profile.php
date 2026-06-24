<?php
use function Cake\I18n\__;

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array<\App\Model\Entity\Article> $myArticles
 * @var array<\App\Model\Entity\Follow> $followers
 * @var array<\App\Model\Entity\Follow> $following
 */
$this->assign('title', __('My Profile'));
$initial = strtoupper(substr((string)$user->email, 0, 1));
$followers = $followers ?? [];
$following = $following ?? [];
?>
<section class="profile-page content">
    <header class="profile-page__header">
        <h2><?= __('My Profile') ?></h2>
        <p><?= __('Manage your profile picture and review your posts.') ?></p>
    </header>

    <div class="profile-page__grid">
        <section class="profile-page__card">
            <h3><?= __('Profile Picture') ?></h3>
            <div id="profile-avatar-preview" class="profile-page__avatar-wrap" data-initial="<?= h($initial) ?>">
                <?php if (!empty($user->profile_image)) : ?>
                    <?= $this->Html->image($user->profile_image, ['class' => 'profile-page__avatar', 'alt' => __('Profile picture'), 'id' => 'profile-avatar-image']) ?>
                <?php else : ?>
                    <span class="profile-page__avatar profile-page__avatar--placeholder" id="profile-avatar-placeholder"><?= h($initial) ?></span>
                <?php endif; ?>
            </div>

            <?= $this->Form->create($user, ['type' => 'file', 'id' => 'profile-form']) ?>
                <?= $this->Form->control('profile_upload', [
                    'type' => 'file',
                    'label' => __('Upload new picture'),
                    'id' => 'profile-upload-input',
                    'accept' => 'image/png,image/jpeg,image/gif,image/webp',
                    'required' => false,
                ]) ?>
                <p class="profile-page__hint"><?= __('Max file size: 2 MB. Large images are automatically optimized before upload.') ?></p>
                <p class="profile-page__upload-error" id="profile-upload-error" aria-live="polite"></p>
                <?= $this->Form->button(__('Save Profile'), ['class' => 'button']) ?>
            <?= $this->Form->end() ?>
        </section>

        <section class="profile-page__card">
            <h3><?= __('Account') ?></h3>
            <p><strong><?= __('Email:') ?></strong> <?= h($user->email) ?></p>
            <p><strong><?= __('Role:') ?></strong> <?= h($user->role) ?></p>
            <p><strong><?= __('Joined:') ?></strong> <?= h($user->created?->i18nFormat('MMM d, yyyy')) ?></p>
        </section>
    </div>

    <section class="profile-page__posts profile-page__card">
        <div class="profile-page__posts-head">
            <h3><?= __('My Blog Posts') ?></h3>
            <?= $this->Html->link(__('Create New'), ['controller' => 'Articles', 'action' => 'add'], ['class' => 'button button-outline']) ?>
        </div>

        <?php if (count($myArticles) === 0) : ?>
            <p class="profile-page__empty"><?= __('You have not posted anything yet.') ?></p>
        <?php else : ?>
            <ul class="profile-page__list">
                <?php foreach ($myArticles as $article) : ?>
                    <li class="profile-page__list-item">
                        <div class="profile-page__post-main">
                            <?= $this->Html->link(
                                h($article->title),
                                ['controller' => 'Articles', 'action' => 'view', $article->slug],
                                ['class' => 'profile-page__post-title']
                            ) ?>
                            <span class="profile-page__post-meta"><?= h($article->created->i18nFormat('MMM d, yyyy')) ?></span>
                        </div>
                        <div class="profile-page__post-actions">
                            <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->slug], ['class' => 'dashboard__card-link']) ?>
                            <span class="dashboard__pill"><?= $article->published ? __('Published') : __('Draft') ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="profile-page__card profile-page__relationships">
        <div class="profile-page__relationship-grid">
            <div>
                <div class="profile-page__posts-head">
                    <h3><?= __('Followers') ?></h3>
                    <span class="dashboard__pill"><?= __n('{0} user', '{0} users', count($followers), count($followers)) ?></span>
                </div>
                <?php if (count($followers) === 0) : ?>
                    <p class="profile-page__empty"><?= __('No followers yet.') ?></p>
                <?php else : ?>
                    <ul class="profile-page__list profile-page__list--compact">
                        <?php foreach ($followers as $follow) : ?>
                            <?php $followerUser = $follow->follower_user ?? null; ?>
                            <?php if ($followerUser) : ?>
                                <li class="profile-page__list-item">
                                    <div class="profile-page__post-main">
                                        <span class="profile-page__post-title"><?= h($followerUser->email) ?></span>
                                        <span class="profile-page__post-meta"><?= __('Since {0}', h($follow->created?->i18nFormat('MMM d, yyyy'))) ?></span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div>
                <div class="profile-page__posts-head">
                    <h3><?= __('Following') ?></h3>
                    <span class="dashboard__pill"><?= __n('{0} user', '{0} users', count($following), count($following)) ?></span>
                </div>
                <?php if (count($following) === 0) : ?>
                    <p class="profile-page__empty"><?= __('You are not following anyone yet.') ?></p>
                <?php else : ?>
                    <ul class="profile-page__list profile-page__list--compact">
                        <?php foreach ($following as $follow) : ?>
                            <?php $followingUser = $follow->following_user ?? null; ?>
                            <?php if ($followingUser) : ?>
                                <li class="profile-page__list-item">
                                    <div class="profile-page__post-main">
                                        <span class="profile-page__post-title"><?= h($followingUser->email) ?></span>
                                        <span class="profile-page__post-meta"><?= __('Following since {0}', h($follow->created?->i18nFormat('MMM d, yyyy'))) ?></span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </section>
</section>

<script>
(function () {
    var input = document.getElementById('profile-upload-input');
    var form = document.getElementById('profile-form');
    var previewWrap = document.getElementById('profile-avatar-preview');
    var errorEl = document.getElementById('profile-upload-error');
    var maxBytes = 2 * 1024 * 1024;
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var maxDimension = 1200;
    var isSubmitting = false;

    if (!input || !form || !previewWrap || !errorEl) {
        return;
    }

    function setError(message) {
        errorEl.textContent = message;
    }

    function ensurePreviewElement() {
        var existingImg = document.getElementById('profile-avatar-image');
        var existingPlaceholder = document.getElementById('profile-avatar-placeholder');
        if (existingPlaceholder) {
            existingPlaceholder.remove();
        }

        if (!existingImg) {
            existingImg = document.createElement('img');
            existingImg.id = 'profile-avatar-image';
            existingImg.className = 'profile-page__avatar';
            existingImg.alt = 'Profile picture';
            previewWrap.appendChild(existingImg);
        }

        return existingImg;
    }

    function setPreviewFromFile(file) {
        var existingImg = ensurePreviewElement();
        existingImg.src = URL.createObjectURL(file);
    }

    function setPreviewFromDataUrl(dataUrl) {
        var existingImg = ensurePreviewElement();
        existingImg.src = String(dataUrl || '');
    }

    function replaceInputFile(file) {
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Unable to load image'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                if (!blob) {
                    reject(new Error('Could not export image'));
                    return;
                }
                resolve(blob);
            }, 'image/jpeg', quality);
        });
    }

    async function compressImage(file) {
        var image = await loadImage(file);
        var ratio = Math.min(1, maxDimension / Math.max(image.width, image.height));
        var width = Math.max(1, Math.round(image.width * ratio));
        var height = Math.max(1, Math.round(image.height * ratio));

        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            throw new Error('Canvas unavailable');
        }
        ctx.drawImage(image, 0, 0, width, height);

        var qualitySteps = [0.85, 0.75, 0.65, 0.55, 0.45, 0.35];
        var bestBlob = null;

        for (var i = 0; i < qualitySteps.length; i++) {
            var blob = await canvasToBlob(canvas, qualitySteps[i]);
            bestBlob = blob;
            if (blob.size <= maxBytes) {
                break;
            }
        }

        if (!bestBlob) {
            throw new Error('Compression failed');
        }

        var baseName = file.name.replace(/\.[^/.]+$/, '');
        return new File([bestBlob], baseName + '.jpg', { type: 'image/jpeg' });
    }

    input.addEventListener('change', function () {
        setError('');
        var file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) {
            return;
        }

        if (allowedTypes.indexOf(file.type) === -1) {
            setError('Invalid file type. Please choose jpg, png, gif, or webp.');
            input.value = '';
            return;
        }

        if (file.size > maxBytes) {
            setError('File is large and will be optimized on save.');
        }

        var reader = new FileReader();
        reader.onload = function (evt) {
            setPreviewFromDataUrl(evt.target && evt.target.result ? evt.target.result : '');
        };
        reader.readAsDataURL(file);
    });

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            return;
        }

        var file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) {
            return;
        }

        event.preventDefault();

        (async function () {
            try {
                var finalFile = file;

                if (file.type !== 'image/gif' && file.size > maxBytes) {
                    setError('Optimizing image...');
                    finalFile = await compressImage(file);
                    replaceInputFile(finalFile);
                    setPreviewFromFile(finalFile);
                }

                if (finalFile.size > maxBytes) {
                    setError('Image is still too large after optimization. Please choose a smaller one.');
                    return;
                }

                setError('');
                isSubmitting = true;
                form.submit();
            } catch (err) {
                setError('Could not optimize this image. Please try a different file.');
            }
        })();
    });
})();
</script>
