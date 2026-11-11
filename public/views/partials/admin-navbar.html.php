<?php
$navItems = [
    ['key' => 'home', 'label' => 'Home', 'href' => '/admin'],
    ['key' => 'club', 'label' => 'Club', 'href' => '/admin#club'],
    ['key' => 'schedule', 'label' => 'Schedule', 'href' => '/admin#schedule'],
    ['key' => 'matches', 'label' => 'Matches', 'href' => '/admin#matches'],
    ['key' => 'analytics', 'label' => 'Analytics', 'href' => '/admin#analytics'],
    ['key' => 'accounts', 'label' => 'Accounts', 'href' => '/admin#accounts'],
];
$currentNav = $activeNav ?? 'home';
$signedInEmail = $userEmail ?? 'admin@pandata.com';
?>

<header class="admin-navbar" data-admin-navbar>
    <div class="admin-navbar__inner">
        <a class="admin-navbar__brand" href="/admin">
            <span class="admin-navbar__logo-slot" aria-hidden="true">
                <i class="fa-solid fa-volleyball"></i>
            </span>
            <span class="admin-navbar__brand-text">
                <span class="admin-navbar__brand-name">PanData Volley</span>
                <span class="admin-navbar__brand-tag">ADMIN CONSOLE</span>
            </span>
        </a>

        <nav
            id="admin-navbar-menu"
            class="admin-navbar__nav"
            aria-label="Admin primary navigation"
        >
            <ul class="admin-navbar__list">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = $currentNav === $item['key']; ?>
                    <li>
                        <a
                            class="admin-navbar__link<?= $isActive ? ' is-active' : '' ?>"
                            href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= $isActive ? 'aria-current="page"' : '' ?>
                        >
                            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="admin-navbar__mobile-account">
                <div class="admin-navbar__mobile-user">
                    <span class="admin-navbar__status-dot" aria-hidden="true"></span>
                    <span><?= htmlspecialchars((string) $signedInEmail, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <form action="/logout" method="POST">
                    <button type="submit" class="admin-navbar__logout admin-navbar__logout--mobile">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        Sign Out
                    </button>
                </form>
            </div>
        </nav>

        <div class="admin-navbar__actions">
            <span class="admin-navbar__user-pill" title="<?= htmlspecialchars((string) $signedInEmail, ENT_QUOTES, 'UTF-8') ?>">
                <span class="admin-navbar__status-dot" aria-hidden="true"></span>
                <span>Admin</span>
            </span>

            <?php require __DIR__ . '/theme-toggle.html.php'; ?>

            <form class="admin-navbar__logout-form" action="/logout" method="POST">
                <button type="submit" class="admin-navbar__logout">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Sign Out</span>
                </button>
            </form>

            <button
                type="button"
                class="admin-navbar__hamburger"
                aria-expanded="false"
                aria-controls="admin-navbar-menu"
                aria-label="Toggle navigation menu"
            >
                <span class="admin-navbar__hamburger-line" aria-hidden="true"></span>
                <span class="admin-navbar__hamburger-line" aria-hidden="true"></span>
                <span class="admin-navbar__hamburger-line" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>
