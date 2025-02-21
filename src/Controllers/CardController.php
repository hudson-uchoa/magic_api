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
        try{
            $name_pt = $data['name_pt'] ?? '';
            $name_en = $data['name_en'] ?? '';
            $color = $data['color'] ?? '';
            $type = $data['type'] ?? '';
            $artist = $data['artist'] ?? '';
            $rarity = $data['rarity'] ?? '';
            $image = $data['image'] ?? '';
            $description = $data['description'] ?? '';
            $price = $data['price'] ?? 0;
            $stock = $data['stock'] ?? 0;
            $edition_id = $data['edition_id'] ?? 0;
    
            $validation = $this->validateCardData(
                $name_pt, 
                $name_en, 
                $color, 
                $type, 
                $artist, 
                $rarity, 
                $image, 
                $description, 
                $price, 
                $stock, 
                $edition_id
            );
            if(!$validation['status']){
                throw new \Exception($validation['message'], $validation['code']);
            }
            $created_card = $this->cardModel->createCard($name_pt, 
                $name_en, 
                $color, 
                $type, 
                $artist, 
                $rarity, 
                $image, 
                $description, 
                $price, 
                $stock, 
                $edition_id
            );
            if (!$created_card['status']) {
                throw new \Exception($created_card['message'], $created_card['code']);
            }

            $this->redis->del("cards:list"); 
            return ResponseHelper::jsonResponse(['message' => $created_card['message']], 201);
        }catch(\Exception $e){
            return ResponseHelper::jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

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
        try{
            $name_pt = $data['name_pt'] ?? '';
            $name_en = $data['name_en'] ?? '';
            $color = $data['color'] ?? '';
            $type = $data['type'] ?? '';
            $artist = $data['artist'] ?? '';
            $rarity = $data['rarity'] ?? '';
            $image = $data['image'] ?? '';
            $description = $data['description'] ?? '';
            $price = $data['price'] ?? 0;
            $stock = $data['stock'] ?? 0;
            $edition_id = $data['edition_id'] ?? 0;
    
            $updated_card = $this->cardModel->updateCard(
                (int) $request->getAttribute('id'),
                $name_pt, 
                $name_en, 
                $color, 
                $type, 
                $artist, 
                $rarity, 
                $image, 
                $description, 
                $price, 
                $stock, 
                $edition_id
            );
            
            if (!$updated_card['status']) {
                throw new \Exception($updated_card['message'], $updated_card['code']);
            }
            $this->redis->del("cards:list");
            return ResponseHelper::jsonResponse(['message' => $updated_card['message']]);
        }catch(\Exception $e){
            return ResponseHelper::jsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function delete(Request $request, Response $response): Response
    {
        try{
            $deleted_card = $this->cardModel->deleteCard((int) $request->getAttribute('id'));
            if (!$deleted_card['status']) {
                throw new \Exception($deleted_card['message'], $deleted_card['code']);
            }
            $this->redis->del("cards:list");
            return ResponseHelper::jsonResponse(['message' => $deleted_card['message']], $deleted_card['code']);
        }catch(\Exception $e){
            return ResponseHelper::jsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    private function validateCardData(
        ?string $name_pt, 
        ?string $name_en, 
        ?string $color, 
        ?string $type, 
        ?string $artist, 
        ?string $rarity, 
        ?string $image_url, 
        ?string $description, 
        ?float $price, 
        ?int $stock, 
        ?int $edition_id
    ): array {
        $requiredFields = compact(
            'name_pt', 
            'name_en', 
            'color', 
            'type', 
            'artist', 
            'rarity', 
            'image_url', 
            'description', 
            'price', 
            'stock', 
            'edition_id'
        );
        foreach ($requiredFields as $key => $value) {
            if (empty($value)) {
                return [
                    'status' => false,
                    'message' => "Campo $key é obrigatório.",
                    'code' => 400
                ];
            }
        }

        if ($price <= 0) {
            return [
                'status' => false,
                'message' => 'Campo price deve ser um valor positivo.',
                'code' => 400
            ];
        }

        if ($stock < 0) {
            return [
                'status' => false,
                'message' => 'Campo stock não pode ser negativo.',
                'code' => 400
            ];
        }

        return ['status' => true];
    }
}
