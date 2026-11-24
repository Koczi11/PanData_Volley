<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case ClubAdmin = 'admin';
    case Coach = 'coach';
    case Analyst = 'analyst';
    case Player = 'player';

    public function homePath(): string
    {
        return match ($this) {
            self::SuperAdmin, self::ClubAdmin => '/admin',
            self::Coach => '/coach',
            self::Analyst => '/analyst',
            self::Player => '/player',
        };
    }

    public function matchesLoginSelector(string $selectedRole): bool
    {
        $normalized = strtolower(trim($selectedRole));

        return match ($this) {
            self::SuperAdmin, self::ClubAdmin => $normalized === 'admin',
            self::Coach => $normalized === 'coach',
            self::Analyst => $normalized === 'analyst',
            self::Player => $normalized === 'player',
        };
    }

    public function isClubScoped(): bool
    {
        return $this !== self::SuperAdmin;
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::ClubAdmin => 'Club Admin',
            self::Coach => 'Coach',
            self::Analyst => 'Analyst',
            self::Player => 'Player',
        };
    }

    public static function isValidLoginSelector(string $selectedRole): bool
    {
        return in_array(
            strtolower(trim($selectedRole)),
            ['admin', 'coach', 'analyst', 'player'],
            true
        );
    }

    public static function fromDatabaseCode(string $code): self
    {
        $normalized = strtolower(trim($code));
        if ($normalized === 'club_admin') {
            return self::ClubAdmin;
        }

        return self::from($normalized);
    }
}
