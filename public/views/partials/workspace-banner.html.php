<?php
$scopeLabel = isset($clubName) && is_string($clubName) && $clubName !== ''
    ? $clubName
    : 'Global Platform Scope';
?>

<section class="workspace-banner" aria-labelledby="workspace-heading">
    <span class="workspace-banner__badge">
        <?= htmlspecialchars((string) ($consoleLabel ?? 'CONSOLE'), ENT_QUOTES, 'UTF-8') ?>
    </span>
    <h1 id="workspace-heading" class="workspace-banner__title">
        <?= htmlspecialchars((string) ($sectionTitle ?? 'Workspace'), ENT_QUOTES, 'UTF-8') ?>
    </h1>
    <p class="workspace-banner__description">
        <?= htmlspecialchars((string) ($sectionDescription ?? ''), ENT_QUOTES, 'UTF-8') ?>
    </p>
    <dl class="workspace-banner__meta">
        <div class="workspace-banner__meta-item">
            <dt>Signed in as</dt>
            <dd><?= htmlspecialchars((string) ($userFullName ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div class="workspace-banner__meta-item">
            <dt>Role</dt>
            <dd><?= htmlspecialchars((string) ($userRoleLabel ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div class="workspace-banner__meta-item">
            <dt>Email</dt>
            <dd><?= htmlspecialchars((string) ($userEmail ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div class="workspace-banner__meta-item">
            <dt>Scope</dt>
            <dd><?= htmlspecialchars($scopeLabel, ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
    </dl>
</section>
