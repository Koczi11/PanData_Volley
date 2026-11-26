<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;

final class AdminController extends BaseRoleController
{
    public function index(): void
    {
        $this->renderRolePage(
            template: 'admin/home',
            pageTitle: 'PanData Volley - Club Admin Home',
            activeNav: 'home',
            sectionTitle: 'Club Administration',
            sectionDescription: 'Manage club operations, staff and player accounts, match schedules, and club analytics.',
        );
    }

    public function club(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Club Settings',
            activeNav: 'club',
            sectionTitle: 'Club Profile',
            sectionDescription: 'Configure club identity, league details, and roster availability for your organization.',
        );
    }

    public function matches(): void
    {
        $this->renderRolePage(
            template: 'admin/matches',
            pageTitle: 'PanData Volley - Club Matches',
            activeNav: 'matches',
            sectionTitle: 'Club Matches',
            sectionDescription: 'Schedule and maintain official club fixtures, scores, and linked match recordings.',
        );
    }

    public function analytics(): void
    {
        $this->renderRolePage(
            template: 'admin/analytics',
            pageTitle: 'PanData Volley - Club Analytics',
            activeNav: 'analytics',
            sectionTitle: 'Club Analytics',
            sectionDescription: 'Review club-level performance indicators and seasonal match statistics.',
        );
    }

    public function accounts(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Club Accounts',
            activeNav: 'accounts',
            sectionTitle: 'Club Accounts',
            sectionDescription: 'Manage coaches, analysts, and player accounts scoped to your club.',
        );
    }

    protected function requiredRole(): UserRole
    {
        return UserRole::ClubAdmin;
    }

    protected function navAriaLabel(): string
    {
        return 'Admin primary navigation';
    }

    protected function navigationItems(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'href' => '/admin'],
            ['key' => 'club', 'label' => 'Club', 'href' => '/admin/club'],
            ['key' => 'matches', 'label' => 'Matches', 'href' => '/admin/matches'],
            ['key' => 'analytics', 'label' => 'Analytics', 'href' => '/admin/analytics'],
            ['key' => 'accounts', 'label' => 'Accounts', 'href' => '/admin/accounts'],
        ];
    }
}
