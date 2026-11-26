<?php
$items = is_array($navItems ?? null) ? $navItems : [];
$currentNav = (string) ($activeNav ?? 'home');
$brandTarget = (string) ($brandHref ?? '/');
$consoleTag = (string) ($consoleLabel ?? 'CONSOLE');
$ariaLabel = (string) ($navAriaLabel ?? 'Primary navigation');
$signedInEmail = (string) ($userEmail ?? '');
$signedInName = (string) ($userFullName ?? '');
$roleBadge = (string) ($userRoleLabel ?? 'User');
$clubLabel = isset($clubName) && is_string($clubName) && $clubName !== '' ? $clubName : null;
$pillTitleParts = array_filter([$signedInName, $signedInEmail, $clubLabel], static fn (?string $v): bool => $v !== null && $v !== '');
$pillTitle = implode(' · ', $pillTitleParts);
$csrfValue = (string) ($logoutCsrfToken ?? '');
?>

<header class="app-navbar" data-app-navbar>
    <div class="app-navbar__inner">
        <a class="app-navbar__brand" href="<?= htmlspecialchars($brandTarget, ENT_QUOTES, 'UTF-8') ?>">
            <span class="app-navbar__logo-slot" aria-hidden="true">
                <i class="fa-solid fa-volleyball"></i>
            </span>
            <span class="app-navbar__brand-text">
                <span class="app-navbar__brand-name">PanData Volley</span>
                <span class="app-navbar__brand-tag"><?= htmlspecialchars($consoleTag, ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        </a>

        <nav
            id="app-navbar-menu"
            class="app-navbar__nav"
            aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>"
        >
            <ul class="app-navbar__list">
                <?php foreach ($items as $item): ?>
                    <?php $isActive = $currentNav === ($item['key'] ?? ''); ?>
                    <li>
                        <a
                            class="app-navbar__link<?= $isActive ? ' is-active' : '' ?>"
                            href="<?= htmlspecialchars((string) ($item['href'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"
                            <?= $isActive ? 'aria-current="page"' : '' ?>
                        >
                            <?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="app-navbar__mobile-account">
                <div class="app-navbar__mobile-user">
                    <span class="app-navbar__status-dot" aria-hidden="true"></span>
                    <span><?= htmlspecialchars($signedInEmail, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <form action="/logout" method="POST">
                    <input
                        type="hidden"
                        name="_csrf_token"
                        value="<?= htmlspecialchars($csrfValue, ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <button type="submit" class="app-navbar__logout app-navbar__logout--mobile">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        Sign Out
                    </button>
                </form>
            </div>
        </nav>

        <div class="app-navbar__actions">
            <span class="app-navbar__user-pill" title="<?= htmlspecialchars($pillTitle, ENT_QUOTES, 'UTF-8') ?>">
                <span class="app-navbar__status-dot" aria-hidden="true"></span>
                <span><?= htmlspecialchars($roleBadge, ENT_QUOTES, 'UTF-8') ?></span>
            </span>

            <?php require __DIR__ . '/theme-toggle.html.php'; ?>

            <form class="app-navbar__logout-form" action="/logout" method="POST">
                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= htmlspecialchars($csrfValue, ENT_QUOTES, 'UTF-8') ?>"
                >
                <button type="submit" class="app-navbar__logout">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Sign Out</span>
                </button>
            </form>

            <button
                type="button"
                class="app-navbar__hamburger"
                aria-expanded="false"
                aria-controls="app-navbar-menu"
                aria-label="Toggle navigation menu"
            >
                <span class="app-navbar__hamburger-line" aria-hidden="true"></span>
                <span class="app-navbar__hamburger-line" aria-hidden="true"></span>
                <span class="app-navbar__hamburger-line" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>
