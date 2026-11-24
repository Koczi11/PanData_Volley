<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

final class PdoUserRepository implements UserRepositoryInterface
{
    private const BASE_SELECT = <<<'SQL'
        SELECT
            u.id,
            u.role_id,
            r.code AS role_code,
            r.name AS role_name,
            u.club_id,
            c.name AS club_name,
            c.slug AS club_slug,
            c.is_active AS club_is_active,
            u.email,
            u.password_hash,
            u.first_name,
            u.last_name,
            u.avatar_path,
            u.preferred_locale,
            u.is_active,
            u.last_login_at,
            u.created_by,
            u.created_at,
            u.updated_at
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        LEFT JOIN clubs c ON c.id = u.club_id
        SQL;

    public function __construct(private readonly \PDO $pdo)
    {
    }

    public function findById(int $id): ?User
    {
        $statement = $this->pdo->prepare(self::BASE_SELECT . ' WHERE u.id = :id LIMIT 1');
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        return User::fromRow($row);
    }

    public function findByEmail(string $email): ?User
    {
        $normalizedEmail = mb_strtolower(trim($email));
        if ($normalizedEmail === '') {
            return null;
        }

        $statement = $this->pdo->prepare(self::BASE_SELECT . ' WHERE u.email = :email LIMIT 1');
        $statement->bindValue(':email', $normalizedEmail, \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        return User::fromRow($row);
    }

    public function updateLastLoginAt(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = :id'
        );
        $statement->bindValue(':id', $userId, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id = :id'
        );
        $statement->bindValue(':password_hash', $passwordHash, \PDO::PARAM_STR);
        $statement->bindValue(':id', $userId, \PDO::PARAM_INT);
        $statement->execute();
    }
}
