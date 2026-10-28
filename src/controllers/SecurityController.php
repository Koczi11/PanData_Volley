<?php

declare(strict_types=1);

namespace App\Controller;

final class SecurityController extends AppController
{
    public function login(): void
    {
        $this->render('login', [
            'pageTitle' => 'PanData Volley - Logowanie',
            'heroTitle' => 'Professional Analysis System'
        ]);
    }

    public function loginWithUser(int $id): void
    {
        $this->render('login', [
            'pageTitle' => 'Player #' . $id . ' Login',
            'heroTitle' => 'Login Player nr. ' . $id
        ]);
    }
}