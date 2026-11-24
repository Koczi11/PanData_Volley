<?php

declare(strict_types=1);

namespace App\Security;

use App\Enum\UserRole;
use App\Http\ForbiddenException;
use App\Http\RedirectException;
use App\Http\Request;
use App\Http\UnauthorizedException;
use App\Model\User;

final class AccessGuard
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly SessionManager $session,
        private readonly Request $request,
    ) {
    }

    public function requireAuthenticated(bool $redirectBrowser = true): User
    {
        $this->session->sendNoCacheHeaders();

        $user = $this->auth->currentUser(
            $this->request->cookie(AuthService::REMEMBER_COOKIE_NAME),
            $this->request->clientIp,
        );

        if ($user !== null) {
            return $user;
        }

        if ($redirectBrowser && $this->request->isBrowserHtmlGet()) {
            throw new RedirectException('/login', 302);
        }

        throw new UnauthorizedException('Authentication is required to access this resource.');
    }

    public function requireRoles(UserRole ...$allowedRoles): User
    {
        $user = $this->requireAuthenticated();

        if (!in_array($user->role, $allowedRoles, true)) {
            throw new ForbiddenException('You do not have permission to access this resource.');
        }

        return $user;
    }

    public function requireClubAccess(int $clubId): User
    {
        $user = $this->requireAuthenticated();

        if (!$user->canAccessClub($clubId)) {
            throw new ForbiddenException('You do not have permission to access this club.');
        }

        return $user;
    }
}
