<?php

namespace app\model;

use flight\database\PdoWrapper;

class VueVilleDons
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM vue_ville_dons ORDER BY ville_libele, don_date DESC");
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("SELECT * FROM vue_ville_dons WHERE don_id = ?", [$id]);
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    public function getByVille(int $villeId): array
    {
        return $this->db->fetchAll("SELECT * FROM vue_ville_dons WHERE ville_id = ? ORDER BY don_date DESC", [$villeId]);
    }

    public function getByRegion(int $regionId): array
    {
        return $this->db->fetchAll("SELECT * FROM vue_ville_dons WHERE region_id = ? ORDER BY ville_libele, don_date DESC", [$regionId]);
    }

    public function getDonVille(): array
    {
        return $this->db->fetchAll("
            SELECT 
                ville_id,
                ville_libele,
                region_id,
                region_libele,
                COUNT(don_id) as nombre_dons,
                SUM(don_quantite) as quantite_totale,
                MAX(don_date) as dernier_don
            FROM vue_ville_dons 
            WHERE don_id IS NOT NULL
            GROUP BY ville_id, ville_libele, region_id, region_libele
            ORDER BY quantite_totale DESC
        ");
    }

    public function getDonVilleByRegion(int $regionId): array
    {
        return $this->db->fetchAll("
            SELECT 
                ville_id,
                ville_libele,
                region_id,
                region_libele,
                COUNT(don_id) as nombre_dons,
                SUM(don_quantite) as quantite_totale,
                MAX(don_date) as dernier_don
            FROM vue_ville_dons 
            WHERE region_id = ? AND don_id IS NOT NULL
            GROUP BY ville_id, ville_libele, region_id, region_libele
            ORDER BY quantite_totale DESC
        ", [$regionId]);
    }

    public function getVillesSansDons(): array
    {
        return $this->db->fetchAll("
            SELECT DISTINCT ville_id, ville_libele, region_id, region_libele
            FROM vue_ville_dons 
            WHERE don_id IS NULL
            ORDER BY ville_libele
        ");
    }

    public function getStatsByRegion(): array
    {
        return $this->db->fetchAll("
            SELECT 
                region_id,
                region_libele,
                COUNT(DISTINCT ville_id) as nombre_villes_avec_dons,
                COUNT(don_id) as nombre_total_dons,
                SUM(don_quantite) as quantite_totale_region
            FROM vue_ville_dons 
            WHERE don_id IS NOT NULL
            GROUP BY region_id, region_libele
            ORDER BY quantite_totale_region DESC
        ");
    }
}
