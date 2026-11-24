<?php

declare(strict_types=1);

namespace App\Controller;

final class AdminController extends AppController
{
    public function index(): void
    {
        $this->renderAdminPage('admin/home', 'PanData Volley - Admin Home', 'home');
    }

    private function renderAdminPage(string $template, string $pageTitle, string $activeNav): void
    {
        $this->startSession();

        if (!$this->isAdminAuthenticated()) {
            $this->redirect('/');
        }

        $user = $_SESSION['user'];

        $this->render($template, [
            'pageTitle' => $pageTitle,
            'pageLang' => 'en',
            'userEmail' => (string) ($user['email'] ?? 'admin@pandata.com'),
            'activeNav' => $activeNav,
        ]);
    }
}
