<?php

namespace App\Controllers;

use App\Models\Card;
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Predis\Client as RedisClient;

class CardController
{
    private Card $cardModel;
    private RedisClient $redis;
    private int $redisTtl;

    public function __construct(Card $cardModel, RedisClient $redis)
    {
        $this->cardModel = $cardModel;
        $this->redis = $redis;
        $this->redisTtl = $_ENV['REDIS_TTL'] ?: 3600;
    }

    public function index(Request $request, Response $response): Response
    {
        $cacheKey = "cards:list";
        $cachedData = $this->redis->get($cacheKey);

        if ($cachedData) {
            return ResponseHelper::jsonResponse(json_decode($cachedData, true));
        }

        $cards = $this->cardModel->getAllCards();

        $this->redis->setex($cacheKey, $this->redisTtl, json_encode($cards));

        return ResponseHelper::jsonResponse($cards);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $name_pt = $data['name_pt'];
        $name_en = $data['name_en'];
        $color = $data['color'];
        $type = $data['type'];
        $artist = $data['artist'];
        $rarity = $data['rarity'];
        $image = $data['image'];
        $description = $data['description'];
        $price = $data['price'];
        $stock = $data['stock'];
        $edition_id = $data['edition_id'];

        $created_card = $this->cardModel->createCard($name_pt, $name_en, $color, $type, $artist, $rarity, $image, $description, $price, $stock, $edition_id);
        if ($created_card['status']) {
            $this->redis->del("cards:list"); 
            return ResponseHelper::jsonResponse(['message' => $created_card['message']], 201);
        }

        return ResponseHelper::jsonResponse(['error' => $created_card['message']], $created_card['code'] ?: 500);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $card = $this->cardModel->getCardById((int) $request->getAttribute('id'));

        if (!$card) {
            return ResponseHelper::jsonResponse(['error' => 'Carta não encontrado'], 404);
        }

        return ResponseHelper::jsonResponse($card);
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $name_pt = $data['name_pt'];
        $name_en = $data['name_en'];
        $color = $data['color'];
        $type = $data['type'];
        $artist = $data['artist'];
        $rarity = $data['rarity'];
        $image = $data['image'];
        $description = $data['description'];
        $price = $data['price'];
        $stock = $data['stock']; 
        $edition_id = $data['edition_id'];

        $updated_card = $this->cardModel->updateCard((int) $request->getAttribute('id'), $name_pt, $name_en, $color, $type, $artist, $rarity, $image, $description, $price, $stock, $edition_id);
        if ($updated_card['status']) {
            $this->redis->del("cards:list");
            return ResponseHelper::jsonResponse(['message' => $updated_card['message']]);
        }

        return ResponseHelper::jsonResponse(['error' => $updated_card['message']], $updated_card['code'] ?: 500);
    }

    public function delete(Request $request, Response $response): Response
    {
        if ($this->cardModel->deleteCard((int) $request->getAttribute('id'))) {
            $this->redis->del("cards:list");
            return ResponseHelper::jsonResponse(['message' => 'Carta deletado com sucesso']);
        }

        return ResponseHelper::jsonResponse(['error' => 'Erro ao deletar o carta'], 500);
    }
}
