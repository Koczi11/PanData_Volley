<?php

declare(strict_types=1);

namespace App\Repository;

final class PdoAuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private readonly \PDO $pdo)
    {
    }

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
    ): void {
        $encodedMetadata = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encodedMetadata === false) {
            $encodedMetadata = '{}';
        }

        $statement = $this->pdo->prepare(
            <<<'SQL'
            INSERT INTO audit_logs (user_id, club_id, action, entity_type, entity_id, ip_address, metadata)
            VALUES (:user_id, :club_id, :action, :entity_type, :entity_id, :ip_address, CAST(:metadata AS jsonb))
            SQL
        );

        $statement->bindValue(':user_id', $userId, $userId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $statement->bindValue(':club_id', $clubId, $clubId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $statement->bindValue(':action', $action, \PDO::PARAM_STR);
        $statement->bindValue(':entity_type', $entityType, \PDO::PARAM_STR);
        $statement->bindValue(':entity_id', $entityId, $entityId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $statement->bindValue(':ip_address', $ipAddress, $ipAddress === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        $statement->bindValue(':metadata', $encodedMetadata, \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findRecent(int $limit = 50, ?int $clubId = null): array
    {
        $boundedLimit = max(1, min($limit, 500));

        if ($clubId === null) {
            $statement = $this->pdo->prepare(
                <<<'SQL'
                SELECT id, user_id, club_id, action, entity_type, entity_id, ip_address, metadata, created_at
                FROM audit_logs
                ORDER BY created_at DESC, id DESC
                LIMIT :limit
                SQL
            );
            $statement->bindValue(':limit', $boundedLimit, \PDO::PARAM_INT);
            $statement->execute();

            /** @var list<array<string, mixed>> $rows */
            $rows = $statement->fetchAll();
            return $rows;
        }

        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT id, user_id, club_id, action, entity_type, entity_id, ip_address, metadata, created_at
            FROM audit_logs
            WHERE club_id = :club_id
            ORDER BY created_at DESC, id DESC
            LIMIT :limit
            SQL
        );
        $statement->bindValue(':club_id', $clubId, \PDO::PARAM_INT);
        $statement->bindValue(':limit', $boundedLimit, \PDO::PARAM_INT);
        $statement->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll();
        return $rows;
    }
}
