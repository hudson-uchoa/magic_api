<?php

namespace App\Middleware;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Predis\Client as RedisClient;

class GuestMiddleware
{
    private string $jwtSecret;
    private string $jwtAlgo;
    private RedisClient $redis;

    public function __construct(RedisClient $redis)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->jwtAlgo = $_ENV['JWT_ALGO'] ?: 'HS256';
        $this->redis = $redis;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];

            if ($this->redis->exists("blacklist:$jwt")) {
                return $handler->handle($request);
            }

            try {
                JWT::decode($jwt, new Key($this->jwtSecret, $this->jwtAlgo));
                return ResponseHelper::jsonResponse(['error' => 'Ação não permitida para usuários autenticados'], 403);
            }  
            catch (\Firebase\JWT\ExpiredException $e) {return $handler->handle($request);} 
            catch (\Firebase\JWT\SignatureInvalidException $e) {return $handler->handle($request);} 
            catch (\Exception $e) {
                error_log("Erro ao decodificar JWT: " . $e->getMessage());
                return $handler->handle($request);
            }
        }

        return $handler->handle($request);
    }
}
