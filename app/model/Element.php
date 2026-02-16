<?php

namespace app\model;

use flight\database\PdoWrapper;

class Element
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT e.*, tb.libele as type_besoin_libele 
            FROM bn_element e 
            LEFT JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id 
            ORDER BY e.libele
        ");
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT e.*, tb.libele as type_besoin_libele 
            FROM bn_element e 
            LEFT JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id 
            WHERE e.id = ?
        ", [$id]);
    }

    public function getByTypeBesoin(int $idTypeBesoin): array
    {
        return $this->db->fetchAll("SELECT * FROM bn_element WHERE idtypebesoin = ? ORDER BY libele", [$idTypeBesoin]);
    }

    public function create(string $libele, int $idTypeBesoin, float $pu): int
    {
        $this->db->run("INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES (?, ?, ?)", [$libele, $idTypeBesoin, $pu]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $libele, int $idTypeBesoin, float $pu): bool
    {
        $stmt = $this->db->run("UPDATE bn_element SET libele = ?, idtypebesoin = ?, pu = ? WHERE id = ?", [$libele, $idTypeBesoin, $pu, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->run("DELETE FROM bn_element WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}