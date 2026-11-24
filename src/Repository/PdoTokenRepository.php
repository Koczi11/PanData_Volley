<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\UserToken;

final class PdoTokenRepository implements TokenRepositoryInterface
{
    public function __construct(private readonly \PDO $pdo)
    {
    }

    public function create(
        int $userId,
        string $tokenType,
        string $selector,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
    ): UserToken {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            INSERT INTO user_tokens (user_id, token_type, selector, token_hash, expires_at)
            VALUES (:user_id, :token_type, :selector, :token_hash, :expires_at)
            RETURNING id, user_id, token_type, selector, token_hash, expires_at, used_at, created_at
            SQL
        );

        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':token_type', $tokenType, \PDO::PARAM_STR);
        $statement->bindValue(':selector', $selector, \PDO::PARAM_STR);
        $statement->bindValue(':token_hash', $tokenHash, \PDO::PARAM_STR);
        $statement->bindValue(':expires_at', $expiresAt->format(\DateTimeInterface::ATOM), \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch();
        if (!is_array($row)) {
            throw new \RuntimeException('Failed to create user token.');
        }

        return UserToken::fromRow($row);
    }

    public function findValidBySelector(string $selector, string $tokenType): ?UserToken
    {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT id, user_id, token_type, selector, token_hash, expires_at, used_at, created_at
            FROM user_tokens
            WHERE selector = :selector
              AND token_type = :token_type
              AND used_at IS NULL
              AND expires_at > NOW()
            LIMIT 1
            SQL
        );

        $statement->bindValue(':selector', $selector, \PDO::PARAM_STR);
        $statement->bindValue(':token_type', $tokenType, \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        return UserToken::fromRow($row);
    }

    public function markUsed(int $tokenId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE user_tokens SET used_at = NOW() WHERE id = :id AND used_at IS NULL'
        );
        $statement->bindValue(':id', $tokenId, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function deleteBySelector(string $selector): void
    {
        $statement = $this->pdo->prepare('DELETE FROM user_tokens WHERE selector = :selector');
        $statement->bindValue(':selector', $selector, \PDO::PARAM_STR);
        $statement->execute();
    }

    public function deleteAllForUser(int $userId, ?string $tokenType = null): void
    {
        if ($tokenType === null) {
            $statement = $this->pdo->prepare('DELETE FROM user_tokens WHERE user_id = :user_id');
            $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $statement->execute();
            return;
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM user_tokens WHERE user_id = :user_id AND token_type = :token_type'
        );
        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':token_type', $tokenType, \PDO::PARAM_STR);
        $statement->execute();
    }

    public function purgeExpired(): int
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM user_tokens WHERE expires_at <= NOW() OR used_at IS NOT NULL'
        );
        $statement->execute();

        return $statement->rowCount();
    }
}
