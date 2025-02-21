<?php

namespace App\Controllers;

use App\Models\Edition;
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EditionController
{
    private Edition $editionModel;

    public function __construct(Edition $editionModel)
    {
        $this->editionModel = $editionModel;
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

        if ($this->editionModel->createEdition($data['name_pt'], $data['name_en'], $data['release_date'], $data['card_count'])) {
            return ResponseHelper::jsonResponse(['message' => 'Edição criada com sucesso'], 201);
        }

        return ResponseHelper::jsonResponse(['error' => 'Erro ao criar a edição'], 500);
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if ($this->editionModel->updateEdition((int) $request->getAttribute('id'), $data['name_pt'], $data['name_en'], $data['release_date'], $data['card_count'])) {
            return ResponseHelper::jsonResponse(['message' => 'Edição atualizada com sucesso']);
        }

        return ResponseHelper::jsonResponse(['error' => 'Erro ao atualizar a edição'], 500);
    }

    public function delete(Request $request, Response $response): Response
    {
        if ($this->editionModel->deleteEdition((int) $request->getAttribute('id'))) {
            return ResponseHelper::jsonResponse(['message' => 'Edição deletada com sucesso']);
        }

        return ResponseHelper::jsonResponse(['error' => 'Erro ao deletar a edição'], 500);
    }
}
