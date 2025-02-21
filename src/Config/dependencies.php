<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use PDO;
use Predis\Client as RedisClient;
use App\Controllers\AuthController;
use App\Controllers\CardController;
use App\Controllers\EditionController;
use App\Models\User;
use App\Models\Edition;
use App\Models\Card;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    PDO::class => function (ContainerInterface $c) {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        return new PDO(
            "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    },
    RedisClient::class => function () {
        $host = $_ENV['REDIS_HOST'];
        $port = $_ENV['REDIS_PORT'];
        return new RedisClient([ 
            'scheme' => 'tcp',
            'host'   => $host,
            'port'   => $port,
        ]);
    },
    AuthMiddleware::class => function (ContainerInterface $c) {
        return new AuthMiddleware($c->get(RedisClient::class));
    },
    GuestMiddleware::class => function (ContainerInterface $c) {
        return new GuestMiddleware($c->get(RedisClient::class));
    },
    User::class => function (ContainerInterface $c) {
        return new User($c->get(PDO::class));
    },
    Card::class => function (ContainerInterface $c) {
        return new Card($c->get(PDO::class));
    },
    Edition::class => function (ContainerInterface $c) {
        return new Edition($c->get(PDO::class));
    },
    AuthController::class => function (ContainerInterface $c) {
        return new AuthController($c->get( User::class), $c->get(RedisClient::class));
    },
    CardController::class => function (ContainerInterface $c) {
        return new CardController($c->get(Card::class), $c->get(RedisClient::class));
    },
    EditionController::class => function (ContainerInterface $c) {
        return new EditionController($c->get(Edition::class));
    },
]);

$container = $containerBuilder->build();

return $container;
