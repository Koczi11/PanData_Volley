<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;
use App\Security\AccessGuard;
use App\Security\CsrfTokenManager;

final class AdminController extends AppController
{
    public function __construct(
        private readonly AccessGuard $guard,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function index(): void
    {
        $this->renderAdminPage('admin/home', 'PanData Volley - Admin Home', 'home');
    }

    public function matches(): void
    {
        $this->renderAdminPage('admin/matches', 'PanData Volley - Admin Matches', 'matches');
    }

    public function analytics(): void
    {
        $this->renderAdminPage('admin/analytics', 'PanData Volley - Admin Analytics', 'analytics');
    }

    private function renderAdminPage(string $template, string $pageTitle, string $activeNav): void
    {
        $user = $this->guard->requireRoles(UserRole::SuperAdmin, UserRole::ClubAdmin);

        $this->render($template, [
            'pageTitle' => $pageTitle,
            'pageLang' => 'en',
            'userEmail' => $user->email,
            'userFullName' => $user->fullName(),
            'userRoleLabel' => $user->role->label(),
            'clubName' => $user->clubName,
            'activeNav' => $activeNav,
            'logoutCsrfToken' => $this->csrf->getToken('logout'),
        ]);
    }
}
