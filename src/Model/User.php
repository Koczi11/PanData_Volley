<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\UserRole;

final readonly class User
{
    public function __construct(
        public int $id,
        public int $roleId,
        public UserRole $role,
        public string $roleName,
        public ?int $clubId,
        public ?string $clubName,
        public ?string $clubSlug,
        public ?bool $clubIsActive,
        public string $email,
        public string $passwordHash,
        public string $firstName,
        public string $lastName,
        public ?string $avatarPath,
        public string $preferredLocale,
        public bool $isActive,
        public ?\DateTimeImmutable $lastLoginAt,
        public ?int $createdBy,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $roleCode = (string) ($row['role_code'] ?? $row['code'] ?? '');
        $lastLoginRaw = $row['last_login_at'] ?? null;

        return new self(
            id: (int) $row['id'],
            roleId: (int) $row['role_id'],
            role: UserRole::fromDatabaseCode($roleCode),
            roleName: (string) ($row['role_name'] ?? ''),
            clubId: isset($row['club_id']) ? (int) $row['club_id'] : null,
            clubName: isset($row['club_name']) ? (string) $row['club_name'] : null,
            clubSlug: isset($row['club_slug']) ? (string) $row['club_slug'] : null,
            clubIsActive: array_key_exists('club_is_active', $row) && $row['club_is_active'] !== null
                ? filter_var($row['club_is_active'], FILTER_VALIDATE_BOOL)
                : null,
            email: (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            firstName: (string) $row['first_name'],
            lastName: (string) $row['last_name'],
            avatarPath: isset($row['avatar_path']) ? (string) $row['avatar_path'] : null,
            preferredLocale: (string) ($row['preferred_locale'] ?? 'en'),
            isActive: filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOL),
            lastLoginAt: is_string($lastLoginRaw) && $lastLoginRaw !== ''
                ? new \DateTimeImmutable($lastLoginRaw)
                : null,
            createdBy: isset($row['created_by']) ? (int) $row['created_by'] : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function belongsToClub(int $clubId): bool
    {
        return $this->clubId !== null && $this->clubId === $clubId;
    }

    public function canAccessClub(int $clubId): bool
    {
        return $this->isSuperAdmin() || $this->belongsToClub($clubId);
    }

    public function canAuthenticate(): bool
    {
        return $this->isActive
            && (!$this->role->isClubScoped() || ($this->clubId !== null && $this->clubIsActive === true));
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'roleName' => $this->roleName !== '' ? $this->roleName : $this->role->label(),
            'clubId' => $this->clubId,
            'clubName' => $this->clubName,
            'clubSlug' => $this->clubSlug,
            'clubIsActive' => $this->clubIsActive,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName(),
            'avatarPath' => $this->avatarPath,
            'preferredLocale' => $this->preferredLocale,
            'isActive' => $this->isActive,
            'lastLoginAt' => $this->lastLoginAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
