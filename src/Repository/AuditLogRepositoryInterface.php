<?php

declare(strict_types=1);

namespace App\Repository;

interface AuditLogRepositoryInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function log(
        ?int $userId,
        ?int $clubId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $ipAddress = null,
        array $metadata = [],
    ): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function findRecent(int $limit = 50, ?int $clubId = null): array;
}
