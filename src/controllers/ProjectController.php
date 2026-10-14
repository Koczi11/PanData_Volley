<?php

declare(strict_types=1);

namespace App\Controller;

final class ProjectController extends AppController
{
    public function show(int $id): void
    {
        $this->render('project', [
            'pageTitle' => 'Szczegóły projektu',
            'projectId' => $id,
        ]);
    }
}