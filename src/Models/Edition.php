<?php

namespace App\Models;

use PDO;

class Edition
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllEditions(): array
    {
        $stmt = $this->db->query("SELECT * FROM editions");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEdition(string $name_pt, string $name_en, string $release_date, int $card_count): bool
    {
        $stmt = $this->db->prepare("INSERT INTO editions (name_pt, name_en, release_date, card_count) VALUES (:name_pt, :name_en, :release_date, :card_count)");
        return $stmt->execute([
            'name_pt' => $name_pt,
            'name_en' => $name_en,
            'release_date' => $release_date,
            'card_count' => $card_count
        ]);
    }

    public function getEditionById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM editions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateEdition(int $id, string $name_pt, string $name_en, string $release_date, int $card_count): bool
    {
        $stmt = $this->db->prepare("UPDATE editions SET name_pt = :name_pt, name_en = :name_en, release_date = :release_date, card_count = :card_count WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name_pt' => $name_pt,
            'name_en' => $name_en,
            'release_date' => $release_date,
            'card_count' => $card_count
        ]);
    }

    public function deleteEdition(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM editions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
