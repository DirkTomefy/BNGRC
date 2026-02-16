<?php

namespace app\model;

use flight\database\PdoWrapper;

class Don
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, v.libele as ville_libele 
            FROM bn_don d 
            LEFT JOIN bn_ville v ON d.idVille = v.id 
            ORDER BY d.date DESC
        ");
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT d.*, v.libele as ville_libele 
            FROM bn_don d 
            LEFT JOIN bn_ville v ON d.idVille = v.id 
            WHERE d.id = ?
        ", [$id]);
    }

    public function getByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT * FROM bn_don 
            WHERE idVille = ? 
            ORDER BY date DESC
        ", [$idVille]);
    }

    public function create(string $date, int $idVille, ?string $description, int $quantite): int
    {
        $this->db->run("INSERT INTO bn_don (date, idVille, description, quantite) VALUES (?, ?, ?, ?)", [$date, $idVille, $description, $quantite]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $date, int $idVille, ?string $description, int $quantite): bool
    {
        $stmt = $this->db->run("UPDATE bn_don SET date = ?, idVille = ?, description = ?, quantite = ? WHERE id = ?", [$date, $idVille, $description, $quantite, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->run("DELETE FROM bn_don WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getStatsByVille(): array
    {
        return $this->db->fetchAll("
            SELECT v.libele as ville_libele, COUNT(d.id) as nombre_dons, 
                   SUM(d.quantite) as quantite_totale
            FROM bn_don d 
            LEFT JOIN bn_ville v ON d.idVille = v.id 
            GROUP BY v.id, v.libele 
            ORDER BY quantite_totale DESC
        ");
    }

    public function getTotalQuantite(): int
    {
        $result = $this->db->fetchOne("SELECT SUM(quantite) as total FROM bn_don");
        return (int)($result['total'] ?? 0);
    }
}
