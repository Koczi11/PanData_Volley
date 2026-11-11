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
        $this->renderLogin();
    }

    public function authenticate(): void
    {
        $role = trim((string) ($_POST['role'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($role === '') {
            http_response_code(401);
            $this->renderLogin(
                'Please select a role before signing in.',
                $role,
                $email,
            );
            return;
        }

        if ($role !== self::DEMO_ADMIN_ROLE) {
            http_response_code(401);
            $this->renderLogin(
                'Invalid role selected. Please choose Admin to sign in.',
                $role,
                $email,
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
            );
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'email' => self::DEMO_ADMIN_EMAIL,
            'role' => self::DEMO_ADMIN_ROLE,
        ];

        $this->redirect('/admin', 303);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
            );
        }

        session_destroy();
        $this->redirect('/', 303);
    }

    private function renderLogin(
        ?string $errorMessage = null,
        string $selectedRole = '',
        string $enteredEmail = '',
    ): void {
        $this->render('login', [
            'pageTitle' => 'PanData Volley - Sign In',
            'pageLang' => 'en',
            'heroTitle' => 'Professional Analysis System',
            'errorMessage' => $errorMessage,
            'selectedRole' => $selectedRole,
            'enteredEmail' => $enteredEmail,
        ]);
    }
}
