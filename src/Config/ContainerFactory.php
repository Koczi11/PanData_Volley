<?php

declare(strict_types=1);

namespace App\Config;

use App\Container;
use App\Controller\AdminController;
use App\Controller\AppController;
use App\Controller\SecurityController;
use App\Http\ErrorResponder;
use App\Http\Request;
use App\Repository\AuditLogRepositoryInterface;
use App\Repository\PdoAuditLogRepository;
use App\Repository\PdoTokenRepository;
use App\Repository\PdoUserRepository;
use App\Repository\TokenRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Routing;
use App\Security\AccessGuard;
use App\Security\AuthService;
use App\Security\CsrfTokenManager;
use App\Security\SessionManager;

final class ContainerFactory
{
    public static function create(string $rootPath, ?Request $request = null): Container
    {
        $container = new Container();
        $normalizedRoot = rtrim($rootPath, '/\\');

        $container->singleton(Env::class, static fn (): Env => Env::load($normalizedRoot . '/.env'));

        $container->singleton(Database::class, static fn (Container $c): Database => new Database($c->get(Env::class)));

        $container->singleton(\PDO::class, static fn (Container $c): \PDO => $c->get(Database::class)->getConnection());

        $container->singleton(Request::class, static fn (): Request => $request ?? Request::fromGlobals());

        $container->singleton(
            ErrorResponder::class,
            static fn (): ErrorResponder => new ErrorResponder($normalizedRoot . '/public/views')
        );

        $container->singleton(
            UserRepositoryInterface::class,
            static fn (Container $c): UserRepositoryInterface => new PdoUserRepository($c->get(\PDO::class))
        );

        $container->singleton(
            TokenRepositoryInterface::class,
            static fn (Container $c): TokenRepositoryInterface => new PdoTokenRepository($c->get(\PDO::class))
        );

        $container->singleton(
            AuditLogRepositoryInterface::class,
            static fn (Container $c): AuditLogRepositoryInterface => new PdoAuditLogRepository($c->get(\PDO::class))
        );

        $container->singleton(
            SessionManager::class,
            static function (Container $c): SessionManager {
                /** @var Request $req */
                $req = $c->get(Request::class);

                return new SessionManager(isSecure: $req->isHttps);
            }
        );

        $container->singleton(
            CsrfTokenManager::class,
            static fn (Container $c): CsrfTokenManager => new CsrfTokenManager($c->get(SessionManager::class))
        );

        $container->singleton(
            AuthService::class,
            static fn (Container $c): AuthService => new AuthService(
                users: $c->get(UserRepositoryInterface::class),
                tokens: $c->get(TokenRepositoryInterface::class),
                auditLogs: $c->get(AuditLogRepositoryInterface::class),
                session: $c->get(SessionManager::class),
                csrf: $c->get(CsrfTokenManager::class),
            )
        );

        $container->singleton(
            AccessGuard::class,
            static fn (Container $c): AccessGuard => new AccessGuard(
                auth: $c->get(AuthService::class),
                session: $c->get(SessionManager::class),
                request: $c->get(Request::class),
            )
        );

        $container->bind(
            SecurityController::class,
            static fn (Container $c): SecurityController => new SecurityController(
                auth: $c->get(AuthService::class),
                csrf: $c->get(CsrfTokenManager::class),
                session: $c->get(SessionManager::class),
                guard: $c->get(AccessGuard::class),
                request: $c->get(Request::class),
            )
        );

        $container->bind(
            AdminController::class,
            static fn (Container $c): AdminController => new AdminController(
                guard: $c->get(AccessGuard::class),
                csrf: $c->get(CsrfTokenManager::class),
            )
        );

        $container->singleton(
            Routing::class,
            static fn (Container $c): Routing => new Routing(
                controllerFactory: static fn (string $controllerClass): AppController => $c->get($controllerClass),
                errorResponder: $c->get(ErrorResponder::class),
            )
        );

        return $container;
    }
}
