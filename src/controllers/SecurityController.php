<?php

declare(strict_types=1);

namespace App\Controller;

final class SecurityController extends AppController
{
    private const DEMO_ADMIN_ROLE = 'admin';
    private const DEMO_ADMIN_EMAIL = 'admin@pandata.com';
    private const DEMO_ADMIN_PASSWORD = '123';

    public function login(): void
    {
        $this->startSession();

        if ($this->isAdminAuthenticated()) {
            $this->redirect('/admin');
        }

        $this->renderLogin();
    }

    public function authenticate(): void
    {
        $role = trim((string) ($_POST['role'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $rememberSession = ($_POST['remember_session'] ?? '') === '1';

        if ($role === '') {
            http_response_code(401);
            $this->renderLogin(
                'Please select a role before signing in.',
                $role,
                $email,
                $rememberSession,
            );
            return;
        }

        if ($role !== self::DEMO_ADMIN_ROLE) {
            http_response_code(401);
            $this->renderLogin(
                'Invalid role selected. Please choose Admin to sign in.',
                $role,
                $email,
                $rememberSession,
            );
            return;
        }

        if (
            strtolower($email) !== self::DEMO_ADMIN_EMAIL
            || $password !== self::DEMO_ADMIN_PASSWORD
        ) {
            http_response_code(401);
            $this->renderLogin(
                'Invalid email or password.',
                $role,
                $email,
                $rememberSession,
            );
            return;
        }

        $cookieLifetime = $rememberSession ? self::REMEMBER_SESSION_LIFETIME : 0;
        $this->startSession($cookieLifetime);
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'email' => self::DEMO_ADMIN_EMAIL,
            'role' => self::DEMO_ADMIN_ROLE,
            'remember' => $rememberSession,
        ];

        $this->redirect('/admin', 303);
    }

    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?: 'Lax',
            ]);
        }

        session_destroy();
        $this->redirect('/', 303);
    }

    private function renderLogin(
        ?string $errorMessage = null,
        string $selectedRole = '',
        string $enteredEmail = '',
        bool $rememberSession = false,
    ): void {
        $this->render('login', [
            'pageTitle' => 'PanData Volley - Sign In',
            'pageLang' => 'en',
            'heroTitle' => 'Professional Analysis System',
            'errorMessage' => $errorMessage,
            'selectedRole' => $selectedRole,
            'enteredEmail' => $enteredEmail,
            'rememberSession' => $rememberSession,
        ]);
    }
}
