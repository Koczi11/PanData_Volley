<?php

declare(strict_types=1);

namespace App\Controller;

final class AdminController extends AppController
{
    public function index(): void
    {
        $this->startSession();

        if (!$this->isAdminAuthenticated()) {
            $this->redirect('/');
        }

        $user = $_SESSION['user'];

        $this->render('admin/home', [
            'pageTitle' => 'PanData Volley - Admin Home',
            'pageLang' => 'en',
            'userEmail' => (string) ($user['email'] ?? 'admin@pandata.com'),
            'activeNav' => 'home',
        ]);
    }
}
