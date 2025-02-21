<?php

namespace App\Models;

use PDO;
use Exception;

class Card
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllCards(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM cards");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao buscar cartas: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function createCard(
        string $name_pt, 
        string $name_en, 
        string $color, 
        string $type, 
        string $artist, 
        string $rarity, 
        string $image_url, 
        string $description, 
        float $price, 
        int $stock, 
        int $edition_id
    ): array {
        try {
            $validation = $this->validateEditionId($edition_id);
            if (!$validation['status']) {
                throw new Exception($validation['message'], $validation['code']);
            }

            $stmt = $this->db->prepare("INSERT INTO cards 
                                    (name_pt, name_en, color, type, artist, rarity, image_url, description, price, stock, edition_id) 
                                    VALUES 
                                    (:name_pt, :name_en, :color, :type, :artist, :rarity, :image_url, :description, :price, :stock, :edition_id)");

            $result = $stmt->execute(compact(
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
            ));

            return [
                'status' => $result,
                'message' => $result ? 'Carta criada com sucesso!' : 'Erro ao criar carta.',
                'code' => $result ? 200 : 500
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao criar carta: ' . $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    public function getCardById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM cards WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao buscar carta: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function updateCard(
        int $id, 
        string $name_pt, 
        string $name_en, 
        string $color, 
        string $type, 
        string $artist, 
        string $rarity, 
        string $image_url, 
        string $description, 
        float $price, 
        int $stock, 
        int $edition_id
    ): array {
        $validation = $this->validateEditionId($edition_id);
        if (!$validation['status']) {
            throw new Exception($validation['message'], $validation['code']);
        }

        try {
            $stmt = $this->db->prepare("UPDATE cards SET 
                                    name_pt = :name_pt, 
                                    name_en = :name_en, 
                                    color = :color, 
                                    type = :type, 
                                    artist = :artist, 
                                    rarity = :rarity, 
                                    image_url = :image_url, 
                                    description = :description, 
                                    price = :price, 
                                    stock = :stock, 
                                    edition_id = :edition_id 
                                    WHERE id = :id");

            $result = $stmt->execute(compact(
                'id', 
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
            ));

            return [
                'status' => $result,
                'message' => $result ? 'Carta atualizada com sucesso!' : 'Erro ao atualizar carta.',
                'code' => $result ? 200 : 500
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao atualizar carta: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    public function deleteCard(int $id): array
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cards WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);
            return [
                'status' => $result,
                'message' => $result ? 'Carta deletada com sucesso!' : 'Erro ao deletar carta.',
                'code' => $result ? 200 : 500
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao deletar carta: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    private function validateEditionId(int $edition_id): array
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM editions WHERE id = :edition_id");
        $stmt->execute(['edition_id' => $edition_id]);
        
        if ($stmt->fetchColumn() == 0) {
            return [
                'status' => false,
                'message' => 'ID da Edição informada não consta nos registros.',
                'code' => 400
            ];
        }

        return ['status' => true];
    }
}
