<?php

namespace App\Models;

use PDO;

class Card
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllCards(): array
    {
        $stmt = $this->db->query("SELECT * FROM cards");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM editions WHERE id = :edition_id");
        $stmt->execute(['edition_id' => $edition_id]);

        if ($stmt->fetchColumn() == 0) {
            return [
                'status' => false,
                'message' => 'ID da Edição informada não consta nos registros',
                'code' => 400
            ];
        }

        $stmt = $this->db->prepare("INSERT INTO cards 
                                    (name_pt, name_en, color, type, artist, rarity, image_url, description, price, stock, edition_id) 
                                    VALUES 
                                    (:name_pt, :name_en, :color, :type, :artist, :rarity, :image_url, :description, :price, :stock, :edition_id)");

        return [
            'status' => $stmt->execute(compact(
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
                'edition_id', 
            )),
            'message' => 'Carta criada com sucesso!',
        ];
    }

    public function getCardById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cards WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM editions WHERE id = :edition_id");
        $stmt->execute(['edition_id' => $edition_id]);
    
        if ($stmt->fetchColumn() == 0) {
            return [
                'status' => false,
                'message' => 'ID da Edição informada não consta nos registros',
                'code' => 400
            ];
        }
    
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

        return [
            'status' => $stmt->execute(compact(
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
            )),
            'message' => 'Carta atualizada com sucesso!'
        ];
    }

    public function deleteCard(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cards WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
