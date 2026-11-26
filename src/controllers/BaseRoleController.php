<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;
use App\Security\AccessGuard;
use App\Security\CsrfTokenManager;

abstract class BaseRoleController extends AppController
{
    public function __construct(
        protected readonly AccessGuard $guard,
        protected readonly CsrfTokenManager $csrf,
    ) {
    }

    abstract protected function requiredRole(): UserRole;

    abstract protected function navAriaLabel(): string;

    /**
     * @return list<array{key: string, label: string, href: string}>
     */
    abstract protected function navigationItems(): array;

    protected function consoleLabel(): string
    {
        return $this->requiredRole()->consoleLabel();
    }

    /**
     * @param array<string, mixed> $extraVariables
     */
    protected function renderRolePage(
        string $template,
        string $pageTitle,
        string $activeNav,
        string $sectionTitle,
        string $sectionDescription,
        array $extraVariables = [],
    ): void {
        $role = $this->requiredRole();
        $user = $this->guard->requireRoles($role);

        $this->render($template, array_merge([
            'pageTitle' => $pageTitle,
            'pageLang' => 'en',
            'useAppNavbar' => true,
            'stylesheets' => ['/css/app-shell.css'],
            'scripts' => ['/js/app-nav.js'],
            'brandHref' => $role->homePath(),
            'consoleLabel' => $this->consoleLabel(),
            'navAriaLabel' => $this->navAriaLabel(),
            'navItems' => $this->navigationItems(),
            'activeNav' => $activeNav,
            'userEmail' => $user->email,
            'userFullName' => $user->fullName(),
            'userRoleLabel' => $user->role->label(),
            'clubName' => $user->clubName,
            'logoutCsrfToken' => $this->csrf->getToken('logout'),
            'sectionTitle' => $sectionTitle,
            'sectionDescription' => $sectionDescription,
        ], $extraVariables));
    }
}
