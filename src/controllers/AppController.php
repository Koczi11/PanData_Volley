<?php

declare(strict_types=1);

namespace App\Controller;

abstract class AppController
{
    protected const REMEMBER_SESSION_LIFETIME = 2592000; // 30 days in seconds

    protected function render(string $template, array $variables = []): void
    {
        $viewsPath = dirname(__DIR__, 2) . '/public/views';
        $templatePath = $viewsPath . '/' . $template . '.html.php';
        $layoutPath = $viewsPath . '/layout.html.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($variables, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    protected function startSession(int $cookieLifetime = 0): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', (string) self::REMEMBER_SESSION_LIFETIME);

        session_set_cookie_params([
            'lifetime' => $cookieLifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    protected function isAdminAuthenticated(): bool
    {
        $user = $_SESSION['user'] ?? null;

        return is_array($user) && ($user['role'] ?? null) === 'admin';
    }

    protected function redirect(string $path, int $statusCode = 302): never
    {
        header('Location: ' . $path, true, $statusCode);
        exit;
    }
}
