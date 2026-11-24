<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User;

final readonly class AuthResult
{
    private function __construct(
        public bool $success,
        public ?User $user = null,
        public ?string $errorMessage = null,
        public string $errorCode = '',
    ) {
    }

    public static function ok(User $user): self
    {
        return new self(success: true, user: $user);
    }

    public static function failed(string $errorCode, string $errorMessage): self
    {
        return new self(
            success: false,
            user: null,
            errorMessage: $errorMessage,
            errorCode: $errorCode,
        );
    }
}
