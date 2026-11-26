<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;

final class PlayerController extends BaseRoleController
{
    public function index(): void
    {
        $this->renderRolePage(
            template: 'player/home',
            pageTitle: 'PanData Volley - Player Portal',
            activeNav: 'home',
            sectionTitle: 'Player Portal',
            sectionDescription: 'Track your personal match performance, seasonal statistics, and tagged video clips.',
        );
    }

    public function matches(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Player Matches',
            activeNav: 'matches',
            sectionTitle: 'My Matches',
            sectionDescription: 'View club fixtures, match results, and sets played during the season.',
        );
    }

    public function stats(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - My Statistics',
            activeNav: 'stats',
            sectionTitle: 'My Statistics',
            sectionDescription: 'Inspect your serve, reception, attack, block, and dig metrics over the season.',
        );
    }

    public function clips(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - My Video Clips',
            activeNav: 'clips',
            sectionTitle: 'Video Clips',
            sectionDescription: 'Jump directly to your tagged match actions and coach feedback highlights.',
        );
    }

    protected function requiredRole(): UserRole
    {
        return UserRole::Player;
    }

    protected function navAriaLabel(): string
    {
        return 'Player primary navigation';
    }

    protected function navigationItems(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'href' => '/player'],
            ['key' => 'matches', 'label' => 'Matches', 'href' => '/player/matches'],
            ['key' => 'stats', 'label' => 'My Stats', 'href' => '/player/stats'],
            ['key' => 'clips', 'label' => 'Video Clips', 'href' => '/player/clips'],
        ];
    }
}
