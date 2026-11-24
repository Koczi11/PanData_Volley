<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\ForbiddenException;

final class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_tokens';

    public function __construct(private readonly SessionManager $session)
    {
    }

    public function getToken(string $tokenId = 'default'): string
    {
        $tokens = $this->getTokensMap();
        if (isset($tokens[$tokenId]) && is_string($tokens[$tokenId]) && $tokens[$tokenId] !== '') {
            return $tokens[$tokenId];
        }

        return $this->regenerateToken($tokenId);
    }

    public function regenerateToken(string $tokenId = 'default'): string
    {
        $tokens = $this->getTokensMap();
        $token = bin2hex(random_bytes(32));
        $tokens[$tokenId] = $token;
        $this->session->set(self::SESSION_KEY, $tokens);

        return $token;
    }

    public function validate(?string $submittedToken, string $tokenId = 'default'): bool
    {
        if (!is_string($submittedToken) || $submittedToken === '') {
            return false;
        }

        $tokens = $this->getTokensMap();
        $storedToken = $tokens[$tokenId] ?? null;

        if (!is_string($storedToken) || $storedToken === '') {
            return false;
        }

        return hash_equals($storedToken, $submittedToken);
    }

    public function assertValid(?string $submittedToken, string $tokenId = 'default'): void
    {
        if (!$this->validate($submittedToken, $tokenId)) {
            throw new ForbiddenException('Invalid or expired CSRF token.');
        }
    }

    public function clearAll(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    /**
     * @return array<string, string>
     */
    private function getTokensMap(): array
    {
        $tokens = $this->session->get(self::SESSION_KEY, []);

        return is_array($tokens) ? $tokens : [];
    }
}
