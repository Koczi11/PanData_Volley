<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;

final class AnalystController extends BaseRoleController
{
    public function index(): void
    {
        $this->renderRolePage(
            template: 'analyst/home',
            pageTitle: 'PanData Volley - Analyst Workspace',
            activeNav: 'home',
            sectionTitle: 'Analyst Workspace',
            sectionDescription: 'Code volleyball rallies, synchronize match video timestamps, and publish telemetry reports.',
        );
    }

    public function matches(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Analyst Matches',
            activeNav: 'matches',
            sectionTitle: 'Match Telemetry',
            sectionDescription: 'Manage match datasets, set breakdowns, and event coding completeness.',
        );
    }

    public function video(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Video Tagging',
            activeNav: 'video',
            sectionTitle: 'Video Tagging',
            sectionDescription: 'Link YouTube match recordings and align rally events with video timestamps.',
        );
    }

    public function reports(): void
    {
        $this->renderRolePage(
            template: 'shared/section',
            pageTitle: 'PanData Volley - Analyst Reports',
            activeNav: 'reports',
            sectionTitle: 'Performance Reports',
            sectionDescription: 'Generate and verify player and team statistical summaries across competitions.',
        );
    }

    protected function requiredRole(): UserRole
    {
        return UserRole::Analyst;
    }

    protected function navAriaLabel(): string
    {
        return 'Analyst primary navigation';
    }

    protected function navigationItems(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'href' => '/analyst'],
            ['key' => 'matches', 'label' => 'Matches', 'href' => '/analyst/matches'],
            ['key' => 'video', 'label' => 'Video Tagging', 'href' => '/analyst/video'],
            ['key' => 'reports', 'label' => 'Reports', 'href' => '/analyst/reports'],
        ];
    }
}
