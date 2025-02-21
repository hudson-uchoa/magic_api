<?php

namespace App\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Predis\Client as RedisClient;

class AuthController
{
    private User $user;
    private RedisClient $redis;
    private string $jwtSecret;
    private int $jwtExpiration;
    private string $jwtAlgo;

    public function __construct(User $user, RedisClient $redis)
    {
        $this->user = $user;
        $this->redis = $redis;
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->jwtExpiration = (int) ($_ENV['JWT_EXPIRATION'] ?? 3600);
        $this->jwtAlgo = $_ENV['JWT_ALGO'] ?: 'HS256';
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = trim($data['password'] ?? '');
        $name = trim($data['name'] ?? '');

        if (empty($email) || empty($password) || empty($name)) {
            return ResponseHelper::jsonResponse(['error' => 'Todos os campos são obrigatórios'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::jsonResponse(['error' => 'E-mail inválido'], 400);
        }

        if ($this->user->emailExists($email)) {
            return ResponseHelper::jsonResponse(['error' => 'E-mail já cadastrado'], 409);
        }

        if ($this->user->createUser($name, $email, $password)) {
            return ResponseHelper::jsonResponse(['message' => 'Usuário registrado com sucesso'], 201);
        }

        return ResponseHelper::jsonResponse(['error' => 'Erro ao registrar usuário'], 500);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = trim($data['password'] ?? '');

        $user = $this->user->getUserByEmail($email);

        if(!$user || !password_verify($password, $user['password'])) {
            return ResponseHelper::jsonResponse(['error' => 'Credenciais inválidas'], 401);
        }

        $payload = [
            'sub' => $user['id'],
            'iat' => time(),
            'exp' => time() + $this->jwtExpiration
        ];
        
        $token = JWT::encode($payload, $this->jwtSecret, $this->jwtAlgo);

        return ResponseHelper::jsonResponse([
            'message' => "Login realizado com sucesso",
            'token' => $token
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return ResponseHelper::jsonResponse(['error' => 'Token não fornecido'], 401);
        }

        $jwt = $matches[1];

        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, $this->jwtAlgo));
            $expTime = $decoded->exp;
            $remainingTime = $expTime - time();

            if ($remainingTime > 0) {
                $this->redis->setex("blacklist:$jwt", $remainingTime, 'revoked');
            }

            return ResponseHelper::jsonResponse(['message' => 'Deslogado com sucesso']);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(['error' => 'Token inválido'], 401);
        }
    }
}
