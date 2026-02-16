<?php

namespace app\modele;

use flight\database\PdoWrapper;

class VilleBesoin
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAllVilleBesoin(): array
    {
        return $this->db->fetchAll("
            SELECT *
            FROM vue_ville_besoins
            ORDER BY besoin_date DESC, ville_libele ASC
        ");
    }
}