<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Security\AccessGuard;
use App\Security\AuthService;
use App\Security\CsrfTokenManager;
use App\Security\SessionManager;

final class SecurityController extends AppController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly CsrfTokenManager $csrf,
        private readonly SessionManager $session,
        private readonly AccessGuard $guard,
        private readonly Request $request,
    ) {
    }

    public function login(): void
    {
        $this->session->sendNoCacheHeaders();

        $user = $this->auth->currentUser(
            $this->request->cookie(AuthService::REMEMBER_COOKIE_NAME),
            $this->request->clientIp,
        );

        if ($user !== null) {
            $this->redirect($user->role->homePath());
        }

        $this->renderLogin();
    }

    public function authenticate(): void
    {
        $this->session->sendNoCacheHeaders();

        $this->csrf->assertValid($this->request->postString('_csrf_token'), 'login');

        $role = trim($this->request->postString('role'));
        $email = trim($this->request->postString('email'));
        $password = $this->request->postString('password');
        $rememberSession = $this->request->postBool('remember_session');

        $result = $this->auth->authenticate(
            selectedRole: $role,
            email: $email,
            password: $password,
            remember: $rememberSession,
            ipAddress: $this->request->clientIp,
        );

        if (!$result->success || $result->user === null) {
            $this->renderLogin(
                errorMessage: $result->errorMessage ?? 'Authentication failed.',
                selectedRole: $role,
                enteredEmail: $email,
                rememberSession: $rememberSession,
                statusCode: 401,
            );
            return;
        }

        $this->redirect($result->user->role->homePath(), 303);
    }

    public function logout(): void
    {
        $this->session->sendNoCacheHeaders();
        $this->csrf->assertValid($this->request->postString('_csrf_token'), 'logout');

        $this->auth->logout(
            $this->request->cookie(AuthService::REMEMBER_COOKIE_NAME),
            $this->request->clientIp,
        );

        $this->redirect('/login', 303);
    }

    public function me(): void
    {
        $user = $this->guard->requireAuthenticated(redirectBrowser: false);

        $this->json([
            'user' => $user->toPublicArray(),
        ]);
    }

    private function renderLogin(
        ?string $errorMessage = null,
        string $selectedRole = '',
        string $enteredEmail = '',
        bool $rememberSession = false,
        int $statusCode = 200,
    ): void {
        $this->render('login', [
            'pageTitle' => 'PanData Volley - Sign In',
            'pageLang' => 'en',
            'heroTitle' => 'Professional Analysis System',
            'errorMessage' => $errorMessage,
            'selectedRole' => $selectedRole,
            'enteredEmail' => $enteredEmail,
            'rememberSession' => $rememberSession,
            'csrfToken' => $this->csrf->getToken('login'),
        ], $statusCode);
    }
}
