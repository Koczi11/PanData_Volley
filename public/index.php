<?php

declare(strict_types=1);

use App\Config\ContainerFactory;
use App\Controller\AdminController;
use App\Controller\AnalystController;
use App\Controller\CoachController;
use App\Controller\PlatformController;
use App\Controller\PlayerController;
use App\Controller\SecurityController;
use App\Http\ErrorResponder;
use App\Http\Request;
use App\Routing;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::fromGlobals();
$container = ContainerFactory::create(dirname(__DIR__), $request);

/** @var Routing $routing */
$routing = $container->get(Routing::class);

$routing->get('/', SecurityController::class, 'login');
$routing->get('/login', SecurityController::class, 'login');
$routing->post('/login', SecurityController::class, 'authenticate');
$routing->post('/logout', SecurityController::class, 'logout');
$routing->get('/api/auth/me', SecurityController::class, 'me');

$routing->get('/platform', PlatformController::class, 'index');
$routing->get('/platform/clubs', PlatformController::class, 'clubs');
$routing->get('/platform/users', PlatformController::class, 'users');
$routing->get('/platform/audit', PlatformController::class, 'audit');

$routing->get('/admin', AdminController::class, 'index');
$routing->get('/admin/club', AdminController::class, 'club');
$routing->get('/admin/matches', AdminController::class, 'matches');
$routing->get('/admin/analytics', AdminController::class, 'analytics');
$routing->get('/admin/accounts', AdminController::class, 'accounts');

$routing->get('/coach', CoachController::class, 'index');
$routing->get('/coach/roster', CoachController::class, 'roster');
$routing->get('/coach/matches', CoachController::class, 'matches');
$routing->get('/coach/analytics', CoachController::class, 'analytics');

$routing->get('/analyst', AnalystController::class, 'index');
$routing->get('/analyst/matches', AnalystController::class, 'matches');
$routing->get('/analyst/video', AnalystController::class, 'video');
$routing->get('/analyst/reports', AnalystController::class, 'reports');

$routing->get('/player', PlayerController::class, 'index');
$routing->get('/player/matches', PlayerController::class, 'matches');
$routing->get('/player/stats', PlayerController::class, 'stats');
$routing->get('/player/clips', PlayerController::class, 'clips');

try {
    $routing->dispatch($request);
} catch (\Throwable $exception) {
    /** @var ErrorResponder $errorResponder */
    $errorResponder = $container->get(ErrorResponder::class);
    $errorResponder->respondServerError($request, $exception);
}
