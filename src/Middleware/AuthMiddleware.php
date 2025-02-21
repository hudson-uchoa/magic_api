<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;
use Predis\Client as RedisClient;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class AuthMiddleware
{
    private RedisClient $redis;
    private string $jwtSecret;
    private string $jwtAlgo;

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->jwtAlgo = $_ENV['JWT_ALGO'] ?: 'HS256';
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse($handler->handle($request));
        }

        $jwt = $matches[1];

        if ($this->redis->exists("blacklist:$jwt")) {
            return ResponseHelper::jsonResponse(['error' => 'Token revogado'], 401);
        }

        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, $this->jwtAlgo));
            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($handler->handle($request));
        }

        return $handler->handle($request);
    }

    private function unauthorizedResponse(Response $response): Response
    {
        return ResponseHelper::jsonResponse(['error' => 'Acesso não autorizado'], 401);
    }
}
