<?php

declare(strict_types=1);

namespace App\Security;

final class SessionManager
{
    public function __construct(
        private readonly bool $isSecure = false,
        private readonly string $sessionName = 'PANDATA_SESSID',
        private readonly int $gcMaxLifetime = 2592000,
    ) {
    }

    public function start(int $cookieLifetime = 0): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.gc_maxlifetime', (string) max($this->gcMaxLifetime, $cookieLifetime));

            session_name($this->sessionName);
            session_set_cookie_params([
                'lifetime' => max(0, $cookieLifetime),
                'path' => '/',
                'domain' => '',
                'secure' => $this->isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        session_start();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->start();

        return array_key_exists($key, $_SESSION);
    }

    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function regenerate(?int $cookieLifetime = null): void
    {
        $this->start($cookieLifetime ?? 0);
        session_regenerate_id(true);

        if ($cookieLifetime !== null && !headers_sent() && ini_get('session.use_cookies')) {
            $sessionId = session_id();
            $sessionName = session_name();
            if ($sessionId !== false && $sessionId !== '' && $sessionName !== false && $sessionName !== '') {
                setcookie($sessionName, $sessionId, [
                    'expires' => $cookieLifetime > 0 ? time() + $cookieLifetime : 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $this->isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }
    }

    public function destroy(): void
    {
        $this->start();
        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $sessionName = session_name();
            if ($sessionName !== false && $sessionName !== '') {
                setcookie($sessionName, '', [
                    'expires' => time() - 42000,
                    'path' => (string) ($params['path'] ?: '/'),
                    'domain' => (string) ($params['domain'] ?? ''),
                    'secure' => $this->isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function sendNoCacheHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    public function isSecure(): bool
    {
        return $this->isSecure;
    }
}
