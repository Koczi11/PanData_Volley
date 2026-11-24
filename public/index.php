<?php

declare(strict_types=1);

use App\Config\ContainerFactory;
use App\Controller\AdminController;
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
$routing->get('/admin', AdminController::class, 'index');
$routing->get('/admin/matches', AdminController::class, 'matches');
$routing->get('/admin/analytics', AdminController::class, 'analytics');
$routing->get('/api/auth/me', SecurityController::class, 'me');

try {
    $routing->dispatch($request);
} catch (\Throwable $exception) {
    /** @var ErrorResponder $errorResponder */
    $errorResponder = $container->get(ErrorResponder::class);
    $errorResponder->respondServerError($request, $exception);
}
