<?php

namespace app\modele;

use flight\database\PdoWrapper;

class Ville
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT v.*, r.libele as region_libele 
            FROM bn_ville v 
            LEFT JOIN bn_region r ON v.idRegion = r.id 
            ORDER BY v.libele
        ");
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT v.*, r.libele as region_libele 
            FROM bn_ville v 
            LEFT JOIN bn_region r ON v.idRegion = r.id 
            WHERE v.id = ?
        ", [$id]);
    }

    public function getByRegion(int $idRegion): array
    {
        return $this->db->fetchAll("SELECT * FROM bn_ville WHERE idRegion = ? ORDER BY libele", [$idRegion]);
    }

    public function create(string $libele, int $idRegion): int
    {
        $this->db->run("INSERT INTO bn_ville (libele, idRegion) VALUES (?, ?)", [$libele, $idRegion]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $libele, int $idRegion): bool
    {
        $stmt = $this->db->run("UPDATE bn_ville SET libele = ?, idRegion = ? WHERE id = ?", [$libele, $idRegion, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->run("DELETE FROM bn_ville WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}