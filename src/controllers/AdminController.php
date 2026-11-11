<?php

declare(strict_types=1);

namespace App\Controller;

final class AdminController extends AppController
{
    public function index(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!is_array($user) || ($user['role'] ?? null) !== 'admin') {
            $this->redirect('/');
        }

        $this->render('admin/home', [
            'pageTitle' => 'PanData Volley - Admin Home',
            'pageLang' => 'en',
            'userEmail' => (string) ($user['email'] ?? 'admin@pandata.com'),
            'activeNav' => 'home',
        ]);
    }
}
