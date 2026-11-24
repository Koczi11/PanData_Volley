<?php

declare(strict_types=1);

namespace App\Model;

final readonly class UserToken
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $tokenType,
        public string $selector,
        public string $tokenHash,
        public \DateTimeImmutable $expiresAt,
        public ?\DateTimeImmutable $usedAt,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $usedAtRaw = $row['used_at'] ?? null;

        return new self(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            tokenType: (string) $row['token_type'],
            selector: (string) $row['selector'],
            tokenHash: (string) $row['token_hash'],
            expiresAt: new \DateTimeImmutable((string) $row['expires_at']),
            usedAt: is_string($usedAtRaw) && $usedAtRaw !== ''
                ? new \DateTimeImmutable($usedAtRaw)
                : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
        );
    }

    public function isExpired(?\DateTimeImmutable $now = null): bool
    {
        $reference = $now ?? new \DateTimeImmutable();
        return $this->expiresAt <= $reference;
    }

    public function isUsable(?\DateTimeImmutable $now = null): bool
    {
        return $this->usedAt === null && !$this->isExpired($now);
    }
}
