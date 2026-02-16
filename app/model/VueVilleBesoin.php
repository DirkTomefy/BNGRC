<?php

namespace app\model;

use flight\database\PdoWrapper;

class VueVilleBesoin
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->getAllVilleBesoin();
    }

    public function getAllVilleBesoin(): array
    {
        return $this->db->fetchAll("
            SELECT *
            FROM vue_ville_besoins
            ORDER BY besoin_date DESC, ville_libele ASC
        ");
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM vue_ville_besoins WHERE besoin_id = ?", [$id]);
    }

    public function getByVille(int $villeId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM vue_ville_besoins WHERE ville_id = ? ORDER BY besoin_date DESC",
            [$villeId]
        );
    }

    public function getByRegion(int $regionId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM vue_ville_besoins WHERE region_id = ? ORDER BY ville_libele, besoin_date DESC",
            [$regionId]
        );
    }

    public function getBesoinVille(): array
    {
        return $this->db->fetchAll("
            SELECT
                ville_id,
                ville_libele,
                region_id,
                region_libele,
                COUNT(besoin_id) as nombre_besoins,
                SUM(montant_total) as montant_total,
                MAX(besoin_date) as dernier_besoin
            FROM vue_ville_besoins
            WHERE besoin_id IS NOT NULL
            GROUP BY ville_id, ville_libele, region_id, region_libele
            ORDER BY montant_total DESC
        ");
    }
}