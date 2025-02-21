<?php

namespace App\Helpers;

use Slim\Psr7\Response;

class ResponseHelper
{
    public static function jsonResponse(array $data, int $status = 200): Response
    {
        $response = new Response();
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
