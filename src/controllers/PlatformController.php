<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;

final class PlatformController extends BaseRoleController
{
    public function index(): void
    {
        $this->renderRolePage(
            template: 'platform/home',
            pageTitle: 'PanData Volley - Platform Overview',
            activeNav: 'home',
            sectionTitle: 'Platform Overview',
            sectionDescription: 'Global system administration across all volleyball clubs, accounts, and audit trails.',
        );
    }

    public function clubs(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Platform Clubs',
            activeNav: 'clubs',
            sectionTitle: 'Clubs Management',
            sectionDescription: 'Manage active tenant clubs, league metadata, and club lifecycle status.',
        );
    }

    public function users(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Platform Users',
            activeNav: 'users',
            sectionTitle: 'Global Users Directory',
            sectionDescription: 'Review platform-wide user accounts, role assignments, and authentication status.',
        );
    }

    public function audit(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Platform Audit Logs',
            activeNav: 'audit',
            sectionTitle: 'Security Audit Logs',
            sectionDescription: 'Inspect authentication events, role checks, and administrative activity across the platform.',
        );
    }

    protected function requiredRole(): UserRole
    {
        return UserRole::SuperAdmin;
    }

    protected function navAriaLabel(): string
    {
        return 'Platform primary navigation';
    }

    protected function navigationItems(): array
    {
        return [
            ['key' => 'home', 'label' => 'Overview', 'href' => '/platform'],
            ['key' => 'clubs', 'label' => 'Clubs', 'href' => '/platform/clubs'],
            ['key' => 'users', 'label' => 'Users', 'href' => '/platform/users'],
            ['key' => 'audit', 'label' => 'Audit Logs', 'href' => '/platform/audit'],
        ];
    }
}
