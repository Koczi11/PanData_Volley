<?php

declare(strict_types=1);

namespace App\Security;

use App\Enum\UserRole;
use App\Model\User;
use App\Repository\AuditLogRepositoryInterface;
use App\Repository\TokenRepositoryInterface;
use App\Repository\UserRepositoryInterface;

final class AuthService
{
    public const REMEMBER_COOKIE_NAME = 'pandata_remember';
    public const REMEMBER_TOKEN_TYPE = 'remember_me';
    private const SESSION_USER_ID_KEY = 'auth_user_id';
    private const DUMMY_BCRYPT_HASH = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
    private const GENERIC_AUTH_FAILURE_MESSAGE = 'Invalid email, password, or selected role.';

    private ?User $cachedUser = null;
    private bool $userResolved = false;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TokenRepositoryInterface $tokens,
        private readonly AuditLogRepositoryInterface $auditLogs,
        private readonly SessionManager $session,
        private readonly CsrfTokenManager $csrf,
        private readonly int $rememberLifetimeSeconds = 2592000,
    ) {
    }

    public function authenticate(
        string $selectedRole,
        string $email,
        string $password,
        bool $remember,
        ?string $ipAddress = null,
    ): AuthResult {
        $normalizedRole = strtolower(trim($selectedRole));
        if (!UserRole::isValidLoginSelector($normalizedRole)) {
            return AuthResult::failed(
                'invalid_role',
                'Please select a valid role before signing in.'
            );
        }

        $normalizedEmail = mb_strtolower(trim($email));
        if ($normalizedEmail === '' || $password === '') {
            return AuthResult::failed(
                'missing_credentials',
                'Email and password are required.'
            );
        }

        $user = $this->users->findByEmail($normalizedEmail);
        $hashToVerify = $user !== null ? $user->passwordHash : self::DUMMY_BCRYPT_HASH;
        $passwordMatches = password_verify($password, $hashToVerify);

        if ($user === null || !$passwordMatches) {
            $this->auditLogs->log(
                userId: $user?->id,
                clubId: $user?->clubId,
                action: 'auth.login_failed',
                entityType: 'users',
                entityId: $user?->id,
                ipAddress: $ipAddress,
                metadata: [
                    'email' => $normalizedEmail,
                    'selected_role' => $normalizedRole,
                    'reason' => 'invalid_credentials',
                ],
            );

            return AuthResult::failed('invalid_credentials', self::GENERIC_AUTH_FAILURE_MESSAGE);
        }

        if (!$user->canAuthenticate()) {
            if (!$user->isActive) {
                $this->auditLogs->log(
                    userId: $user->id,
                    clubId: $user->clubId,
                    action: 'auth.login_inactive',
                    entityType: 'users',
                    entityId: $user->id,
                    ipAddress: $ipAddress,
                    metadata: ['selected_role' => $normalizedRole],
                );

                return AuthResult::failed(
                    'inactive_account',
                    self::GENERIC_AUTH_FAILURE_MESSAGE
                );
            }

            $this->auditLogs->log(
                userId: $user->id,
                clubId: $user->clubId,
                action: 'auth.login_club_inactive',
                entityType: 'clubs',
                entityId: $user->clubId,
                ipAddress: $ipAddress,
                metadata: ['selected_role' => $normalizedRole],
            );

            return AuthResult::failed(
                'inactive_club',
                self::GENERIC_AUTH_FAILURE_MESSAGE
            );
        }

        if (!$user->role->matchesLoginSelector($normalizedRole)) {
            $this->auditLogs->log(
                userId: $user->id,
                clubId: $user->clubId,
                action: 'auth.login_role_mismatch',
                entityType: 'users',
                entityId: $user->id,
                ipAddress: $ipAddress,
                metadata: [
                    'selected_role' => $normalizedRole,
                    'actual_role' => $user->role->value,
                ],
            );

            return AuthResult::failed(
                'role_mismatch',
                self::GENERIC_AUTH_FAILURE_MESSAGE
            );
        }

        if (password_needs_rehash($user->passwordHash, PASSWORD_BCRYPT)) {
            $this->users->updatePasswordHash($user->id, password_hash($password, PASSWORD_BCRYPT));
        }

        $sessionCookieLifetime = $remember ? $this->rememberLifetimeSeconds : 0;
        $this->session->regenerate($sessionCookieLifetime);
        $this->session->set(self::SESSION_USER_ID_KEY, $user->id);
        $this->csrf->regenerateToken('login');
        $this->csrf->regenerateToken('logout');
        $this->csrf->regenerateToken('default');

        $this->users->updateLastLoginAt($user->id);

        if ($remember) {
            $this->issueRememberToken($user->id);
        } else {
            $this->clearRememberCookie();
        }

        $this->auditLogs->log(
            userId: $user->id,
            clubId: $user->clubId,
            action: 'auth.login_success',
            entityType: 'users',
            entityId: $user->id,
            ipAddress: $ipAddress,
            metadata: [
                'selected_role' => $normalizedRole,
                'remember' => $remember,
            ],
        );

        $freshUser = $this->users->findById($user->id) ?? $user;
        $this->cachedUser = $freshUser;
        $this->userResolved = true;

        return AuthResult::ok($freshUser);
    }

    public function restoreSessionFromRememberCookie(
        ?string $cookieValue = null,
        ?string $ipAddress = null,
    ): ?User {
        $rawCookie = $cookieValue ?? ($_COOKIE[self::REMEMBER_COOKIE_NAME] ?? null);
        if (!is_string($rawCookie) || $rawCookie === '') {
            return null;
        }

        $parsed = $this->parseRememberCookie($rawCookie);
        if ($parsed === null) {
            $this->clearRememberCookie();
            return null;
        }

        [$selector, $validator] = $parsed;
        $token = $this->tokens->findValidBySelector($selector, self::REMEMBER_TOKEN_TYPE);
        if ($token === null) {
            $this->clearRememberCookie();
            return null;
        }

        $validatorHash = hash('sha256', $validator);
        if (!hash_equals($token->tokenHash, $validatorHash)) {
            $this->tokens->deleteBySelector($selector);
            $this->clearRememberCookie();

            $this->auditLogs->log(
                userId: $token->userId,
                clubId: null,
                action: 'auth.remember_token_mismatch',
                entityType: 'user_tokens',
                entityId: $token->id,
                ipAddress: $ipAddress,
                metadata: ['selector' => $selector],
            );

            return null;
        }

        $user = $this->users->findById($token->userId);
        if ($user === null || !$user->canAuthenticate()) {
            $this->tokens->deleteBySelector($selector);
            $this->clearRememberCookie();
            return null;
        }

        // Rotate remember-me token on every successful restoration.
        $this->tokens->markUsed($token->id);
        $this->issueRememberToken($user->id);

        $this->session->regenerate($this->rememberLifetimeSeconds);
        $this->session->set(self::SESSION_USER_ID_KEY, $user->id);
        $this->csrf->regenerateToken('logout');
        $this->csrf->regenerateToken('default');

        $this->users->updateLastLoginAt($user->id);

        $this->auditLogs->log(
            userId: $user->id,
            clubId: $user->clubId,
            action: 'auth.remember_restored',
            entityType: 'users',
            entityId: $user->id,
            ipAddress: $ipAddress,
        );

        $freshUser = $this->users->findById($user->id) ?? $user;
        $this->cachedUser = $freshUser;
        $this->userResolved = true;

        return $freshUser;
    }

    public function currentUser(?string $rememberCookieValue = null, ?string $ipAddress = null): ?User
    {
        if ($this->userResolved) {
            return $this->cachedUser;
        }

        $this->userResolved = true;
        $this->session->start();

        $sessionUserId = $this->session->get(self::SESSION_USER_ID_KEY);
        if (is_int($sessionUserId) && $sessionUserId > 0) {
            $user = $this->users->findById($sessionUserId);
            if ($user !== null && $user->canAuthenticate()) {
                $this->cachedUser = $user;
                return $user;
            }

            // User was deleted, deactivated, or belongs to a deactivated club: invalidate session immediately.
            $this->session->destroy();
            $this->clearRememberCookie();
            $this->cachedUser = null;

            return null;
        }

        $restoredUser = $this->restoreSessionFromRememberCookie($rememberCookieValue, $ipAddress);
        $this->cachedUser = $restoredUser;

        return $restoredUser;
    }

    public function logout(?string $cookieValue = null, ?string $ipAddress = null): void
    {
        $this->session->start();
        $sessionUserId = $this->session->get(self::SESSION_USER_ID_KEY);
        $user = is_int($sessionUserId) && $sessionUserId > 0
            ? $this->users->findById($sessionUserId)
            : null;

        $rawCookie = $cookieValue ?? ($_COOKIE[self::REMEMBER_COOKIE_NAME] ?? null);
        if (is_string($rawCookie) && $rawCookie !== '') {
            $parsed = $this->parseRememberCookie($rawCookie);
            if ($parsed !== null) {
                $this->tokens->deleteBySelector($parsed[0]);
            }
        }

        $this->clearRememberCookie();
        $this->session->destroy();

        $this->cachedUser = null;
        $this->userResolved = true;

        if ($user !== null) {
            $this->auditLogs->log(
                userId: $user->id,
                clubId: $user->clubId,
                action: 'auth.logout',
                entityType: 'users',
                entityId: $user->id,
                ipAddress: $ipAddress,
            );
        }
    }

    private function issueRememberToken(int $userId): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $validator);
        $expiresAt = (new \DateTimeImmutable())->modify(sprintf('+%d seconds', $this->rememberLifetimeSeconds));

        $this->tokens->create(
            userId: $userId,
            tokenType: self::REMEMBER_TOKEN_TYPE,
            selector: $selector,
            tokenHash: $tokenHash,
            expiresAt: $expiresAt,
        );

        if (!headers_sent()) {
            setcookie(self::REMEMBER_COOKIE_NAME, $selector . ':' . $validator, [
                'expires' => $expiresAt->getTimestamp(),
                'path' => '/',
                'domain' => '',
                'secure' => $this->session->isSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    private function clearRememberCookie(): void
    {
        if (!headers_sent()) {
            setcookie(self::REMEMBER_COOKIE_NAME, '', [
                'expires' => time() - 42000,
                'path' => '/',
                'domain' => '',
                'secure' => $this->session->isSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function parseRememberCookie(string $cookieValue): ?array
    {
        if (!preg_match('/^([0-9a-f]{32}):([0-9a-f]{64})$/', trim($cookieValue), $matches)) {
            return null;
        }

        return [$matches[1], $matches[2]];
    }
}
