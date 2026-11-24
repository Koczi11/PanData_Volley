<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\UserToken;

interface TokenRepositoryInterface
{
    public function create(
        int $userId,
        string $tokenType,
        string $selector,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
    ): UserToken;

    public function findValidBySelector(string $selector, string $tokenType): ?UserToken;

    public function markUsed(int $tokenId): void;

    public function deleteBySelector(string $selector): void;

    public function deleteAllForUser(int $userId, ?string $tokenType = null): void;

    public function purgeExpired(): int;
}
