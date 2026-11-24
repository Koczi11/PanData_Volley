<main class="error-page">
    <section class="error-card" aria-labelledby="error-heading">
        <p class="error-card__code"><?= (int) ($statusCode ?? 500) ?></p>
        <h1 id="error-heading" class="error-card__title">
            <?= htmlspecialchars((string) ($errorHeading ?? 'Error'), ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="error-card__message">
            <?= htmlspecialchars((string) ($errorMessage ?? 'An unexpected error occurred.'), ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="error-card__actions">
            <a class="error-card__link" href="<?= htmlspecialchars((string) ($actionHref ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string) ($actionLabel ?? 'Return to Home'), ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
    </section>
</main>
