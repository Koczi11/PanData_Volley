<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function updateLastLoginAt(int $userId): void;

    public function updatePasswordHash(int $userId, string $passwordHash): void;
}
