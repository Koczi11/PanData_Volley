<?php
$stylesheets = ['/css/login.css'];
$roles = [
    'admin' => 'Admin',
    'coach' => 'Coach',
    'analyst' => 'Analyst',
    'player' => 'Player',
];
$currentRole = $selectedRole ?? '';
?>

<main>
    <section aria-labelledby="branding-heading">
        <header>
            <p>PANDATA VOLLEY</p>
            <small>PRO VOLLEYBALL ANALYTICS</small>
        </header>

        <h1 id="branding-heading"><?= htmlspecialchars($heroTitle ?? 'Professional Analysis System', ENT_QUOTES, 'UTF-8') ?></h1>
        <p>
            Pro Analysis System for professional volleyball teams.
        </p>
    </section>

    <section aria-labelledby="form-heading">
        <header>
            <small>AUTHORIZED ACCESS</small>
            <h2 id="form-heading">SIGN IN</h2>
        </header>

        <?php if (!empty($errorMessage)): ?>
            <div class="login-alert" role="alert" aria-live="polite">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <span><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <fieldset>
                <legend>Select Role:</legend>
                <?php foreach ($roles as $value => $label): ?>
                    <label>
                        <input
                            type="radio"
                            name="role"
                            value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $currentRole === $value ? 'checked' : '' ?>
                        >
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <div>
                <label for="email">Work Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars((string) ($enteredEmail ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div>
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

                <a href="#forgot-password">Forgot password?</a>
            </div>

            <div>
                <label>
                    <input
                        type="checkbox"
                        name="remember_session"
                        value="1"
                        <?= !empty($rememberSession) ? 'checked' : '' ?>
                    >
                    Remember session
                </label>
            </div>

            <button type="submit">Sign In &rarr;</button>
        </form>

        <footer>
            <small>&copy; 2026 Pandata</small>
            <nav>
                <a href="#privacy">Privacy Policy</a>
            </nav>
        </footer>
    </section>
</main>
