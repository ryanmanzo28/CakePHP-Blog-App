<?php
/**
 * @var \App\View\AppView $this
 * @var \Throwable $error
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;

// ── 403 Access Denied ──────────────────────────────────────────────────────
// Route ForbiddenException to the friendly access-denied page instead of the
// generic 400 error so users can navigate back without a hard crash.
$isForbidden = isset($error)
    && method_exists($error, 'getCode')
    && (int)$error->getCode() === 403;

if ($isForbidden) :
    $this->setLayout('default');
    $this->assign('title', __('Access Denied'));
?>
<div class="content access-denied">

    <div class="access-denied__icon" aria-hidden="true">🔒</div>

    <h2><?= __('Access Denied') ?></h2>

    <p><?= __("You don't have permission to perform that action.") ?></p>

    <div class="access-denied__actions">
        <a href="javascript:history.back()" class="button button-outline">
            &larr; <?= __('Go Back') ?>
        </a>
        <?= $this->Html->link(
            __('Dashboard'),
            ['controller' => 'Dashboard', 'action' => 'index'],
            ['class' => 'button']
        ) ?>
    </div>

    <?php if (Configure::read('debug')) : ?>
        <details class="access-denied__debug">
            <summary><?= __('Developer details') ?></summary>
            <pre><?= h($message) ?></pre>
            <pre><?= h((string) $error) ?></pre>
        </details>
    <?php endif; ?>

</div>
<?php return; endif; ?>
<?php
// ── Generic 400 error ──────────────────────────────────────────────────────
$this->setLayout('error');

if (Configure::read('debug')) :
    $this->setLayout('dev_error');

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');

    $errorVars = isset($error) ? get_object_vars($error) : [];
    $sqlQuery = $errorVars['queryString'] ?? null;
    $sqlParams = $errorVars['params'] ?? null;

    $this->start('file');
?>
<?php if (!empty($sqlQuery)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h((string)$sqlQuery) ?>
    </p>
<?php endif; ?>
<?php if (!empty($sqlParams)) : ?>
        <strong>SQL Query Params: </strong>
        <?php Debugger::dump($sqlParams) ?>
<?php endif; ?>
<?= $this->element('auto_table_warning') ?>
<?php

$this->end();
endif;
?>
<h2><?= h($message) ?></h2>
<p class="error">
    <strong><?= __d('cake', 'Error') ?>: </strong>
    <?= __d('cake', 'The requested address {0} was not found on this server.', "<strong>'{$url}'</strong>") ?>
</p>
