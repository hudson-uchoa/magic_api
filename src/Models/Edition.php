<?php

namespace App\Models;

use PDO;
use Exception;

class Edition
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllEditions(): array
    {
        try{
            $stmt = $this->db->query("SELECT * FROM editions");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return [
                'status' => false,
                'message' => 'Erro ao buscar edições: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function createEdition(string $name_pt, string $name_en, string $release_date, int $card_count): array
    {
        try{
            $stmt = $this->db->prepare("INSERT INTO editions (name_pt, name_en, release_date, card_count) VALUES (:name_pt, :name_en, :release_date, :card_count)");
            $result = $stmt->execute([
                'name_pt' => $name_pt,
                'name_en' => $name_en,
                'release_date' => $release_date,
                'card_count' => $card_count
            ]);

            return [
                'status' => $result,
                'message' => $result ? 'Edição criada com sucesso!' : 'Erro ao criar edição.',
                'code' => $result ? 200 : 500
            ];
        }catch(Exception $e){
            return [
                'status' => false,
                'message' => 'Erro ao criar edição: ' . $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    public function getEditionById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM editions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateEdition(int $id, string $name_pt, string $name_en, string $release_date, int $card_count): array
    {
        try{
            $stmt = $this->db->prepare("UPDATE editions SET name_pt = :name_pt, name_en = :name_en, release_date = :release_date, card_count = :card_count WHERE id = :id");
            $result =  $stmt->execute([
                'id' => $id,
                'name_pt' => $name_pt,
                'name_en' => $name_en,
                'release_date' => $release_date,
                'card_count' => $card_count
            ]);

            return [
                'status' => $result,
                'message' => $result ? 'Edição atualizada com sucesso!' : 'Erro ao atualizar edição.',
                'code' => $result ? 200 : 500
            ];
        }catch(Exception $e){
            return [
                'status' => false,
                'message' => 'Erro ao atualizar edição: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    public function deleteEdition(int $id): array
    {
        try{
            $stmt = $this->db->prepare("DELETE FROM editions WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            return [
                'status' => $result,
                'message' => $result ? 'Edição deletada com sucesso!' : 'Erro ao deletar edição.',
                'code' => $result ? 200 : 500
            ];
        }catch(Exception $e){
            return [
                'status' => false,
                'message' => 'Erro ao deletar edição: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
