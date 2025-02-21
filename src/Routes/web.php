<?php 

use App\Controllers\AuthController;
use App\Controllers\CardController;
use App\Controllers\EditionController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;


return function (App $app) {
    
    $app->group('', function (RouteCollectorProxy $group) {
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);
    
    $app->post('/logout', [AuthController::class, 'logout']);

    $app->get('/cards', [CardController::class, 'index']);

    $app->group('/cards', function (RouteCollectorProxy $group) {
        $group->post('', [CardController::class, 'store']);
        $group->get('/{id}', [CardController::class, 'show']);
        $group->put('/{id}', [CardController::class, 'update']);
        $group->delete('/{id}', [CardController::class, 'delete']);
    })->add(AuthMiddleware::class);

    $app->group('/editions', function (RouteCollectorProxy $group) {
        $group->get('', [EditionController::class, 'index']);
        $group->post('', [EditionController::class, 'store']);
        $group->get('/{id}', [EditionController::class, 'show']);
        $group->put('/{id}', [EditionController::class, 'update']);
        $group->delete('/{id}', [EditionController::class, 'delete']);
    })->add(AuthMiddleware::class);
};