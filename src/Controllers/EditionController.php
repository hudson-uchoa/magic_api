<?php

namespace App\Controllers;

use App\Models\Edition;
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Predis\Client as RedisClient;

class EditionController
{
    private Edition $editionModel;
    private RedisClient $redis;

    public function __construct(Edition $editionModel, RedisClient $redis)
    {
        $this->editionModel = $editionModel;
        $this->redis = $redis;
    }

    public function index(Request $request, Response $response): Response
    {
        $editions = $this->editionModel->getAllEditions();
        return ResponseHelper::jsonResponse($editions);
    }

    public function show(Request $request, Response $response): Response
    {
        $edition = $this->editionModel->getEditionById((int) $request->getAttribute('id'));

        if (!$edition) {
            return ResponseHelper::jsonResponse(['error' => 'Edição não encontrada'], 404);
        }

        return ResponseHelper::jsonResponse($edition);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        try {
            $name_pt = $data['name_pt'] ?? '';
            $name_en = $data['name_en'] ?? '';
            $release_date = $data['release_date'] ?? '';
            $card_count = $data['card_count'] ?? 0;

            $validation = $this->validateEditionData(
                $name_pt,
                $name_en,
                $release_date,
                $card_count
            );
    
            if (!$validation['status']) {
                throw new \Exception($validation['message'], $validation['code']);
            }
            $edition_created = $this->editionModel->createEdition(
                $name_pt,
                $name_en,
                $release_date,
                $card_count
            );
            if(!$edition_created['status']){
                throw new \Exception($edition_created['message'], $edition_created['code']);
            }
            return ResponseHelper::jsonResponse(['message' => $edition_created['message']], $edition_created['code']);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $name_pt = $data['name_pt'] ?? '';
            $name_en = $data['name_en'] ?? '';
            $release_date = $data['release_date'] ?? '';
            $card_count = $data['card_count'] ?? 0;

            if(!$request->getAttribute('id')){
                throw new \Exception('Erro ao atualizar a edição: O parametro para esta rota {id} é obrigatório', 400);
            }
            $edition_updated = $this->editionModel->updateEdition(
                (int) $request->getAttribute('id'), 
                $name_pt, 
                $name_en, 
                $release_date, 
                $card_count
            );
            if (!$edition_updated['status']) {
                throw new \Exception($edition_updated['message'], $edition_updated['code']);
            }
            
            return ResponseHelper::jsonResponse(['message' => $edition_updated['message']], $edition_updated['code']);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function delete(Request $request, Response $response): Response
    {
        try{
            $deleted_edition = $this->editionModel->deleteEdition((int) $request->getAttribute('id'));
            if (!$deleted_edition['status']) {
                throw new \Exception($deleted_edition['message'], $deleted_edition['code']);
            }
            $this->redis->del("cards:list"); 
            return ResponseHelper::jsonResponse(['message' => $deleted_edition['message']], $deleted_edition['code']);
        }catch(\Exception $e){
            return ResponseHelper::jsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    private function validateEditionData(
        ?string $name_pt, 
        ?string $name_en, 
        ?string $release_date, 
        ?int $card_count
    ): array {
        $requiredFields = compact(
            'name_pt', 
            'name_en', 
            'release_date', 
            'card_count', 
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
    
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $release_date)) {
            return [
                'status' => false,
                'message' => 'A data de lançamento deve estar no formato YYYY-MM-DD.',
                'code' => 400
            ];
        }
    
        if ($card_count <= 0) {
            return [
                'status' => false,
                'message' => 'O número de cartas deve ser um valor positivo.',
                'code' => 400
            ];
        }
    
        return ['status' => true];
    }
}
