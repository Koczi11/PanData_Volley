<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;

final class CoachController extends BaseRoleController
{
    public function index(): void
    {
        $this->renderRolePage(
            template: 'coach/home',
            pageTitle: 'PanData Volley - Coach Workspace',
            activeNav: 'home',
            sectionTitle: 'Coach Workspace',
            sectionDescription: 'Prepare match lineups, review squad readiness, and study tactical performance.',
        );
    }

    public function roster(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Team Roster',
            activeNav: 'roster',
            sectionTitle: 'Team Roster',
            sectionDescription: 'Inspect player positions, physical metrics, and availability status for upcoming fixtures.',
        );
    }

    public function matches(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Coach Matches',
            activeNav: 'matches',
            sectionTitle: 'Match Preparation',
            sectionDescription: 'Review past and upcoming matches along with set results and tactical notes.',
        );
    }

    public function analytics(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Coach Analytics',
            activeNav: 'analytics',
            sectionTitle: 'Tactical Analytics',
            sectionDescription: 'Evaluate serve, reception, attack efficiency, and defensive coverage trends.',
        );
    }

    protected function requiredRole(): UserRole
    {
        return UserRole::Coach;
    }

    protected function navAriaLabel(): string
    {
        return 'Coach primary navigation';
    }

    protected function navigationItems(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'href' => '/coach'],
            ['key' => 'roster', 'label' => 'Roster', 'href' => '/coach/roster'],
            ['key' => 'matches', 'label' => 'Matches', 'href' => '/coach/matches'],
            ['key' => 'analytics', 'label' => 'Analytics', 'href' => '/coach/analytics'],
        ];
    }
}
