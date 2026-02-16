<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle pour la vue récapitulative ville avec besoins, dons et reste
 */
class VueVilleRecap
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère toutes les données de la vue récap
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM vue_ville_recap ORDER BY region_libele, ville_libele, element_libele";
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les données regroupées par ville
     */
    public function getGroupedByVille(): array
    {
        $sql = "SELECT * FROM vue_ville_recap ORDER BY region_libele, ville_libele, element_libele";
        $rows = $this->db->fetchAll($sql);

        $villes = [];
        foreach ($rows as $row) {
            $villeId = $row['ville_id'];
            
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'ville_id'      => $villeId,
                    'ville'         => $row['ville_libele'],
                    'region_id'     => $row['region_id'],
                    'region'        => $row['region_libele'],
                    'elements'      => [],
                    'totaux'        => [
                        'quantite_besoin'   => 0,
                        'quantite_donnee'   => 0,
                        'quantite_restante' => 0,
                        'montant_besoin'    => 0,
                        'montant_restant'   => 0,
                    ],
                ];
            }

            // Ajouter l'élément
            $villes[$villeId]['elements'][] = [
                'element_id'        => $row['element_id'],
                'element'           => $row['element_libele'],
                'prix_unitaire'     => (float)$row['element_pu'],
                'type_besoin_id'    => $row['type_besoin_id'],
                'type_besoin'       => $row['type_besoin_libele'],
                'quantite_besoin'   => (int)$row['quantite_besoin'],
                'quantite_donnee'   => (int)$row['quantite_donnee'],
                'quantite_restante' => (int)$row['quantite_restante'],
                'montant_besoin'    => (float)$row['montant_besoin'],
                'montant_restant'   => (float)$row['montant_restant'],
            ];

            // Mettre à jour les totaux
            $villes[$villeId]['totaux']['quantite_besoin']   += (int)$row['quantite_besoin'];
            $villes[$villeId]['totaux']['quantite_donnee']   += (int)$row['quantite_donnee'];
            $villes[$villeId]['totaux']['quantite_restante'] += (int)$row['quantite_restante'];
            $villes[$villeId]['totaux']['montant_besoin']    += (float)$row['montant_besoin'];
            $villes[$villeId]['totaux']['montant_restant']   += (float)$row['montant_restant'];
        }

        return array_values($villes);
    }

    /**
     * Récupère les données par région
     */
    public function getByRegion(int $regionId): array
    {
        $sql = "SELECT * FROM vue_ville_recap WHERE region_id = ? ORDER BY ville_libele, element_libele";
        $rows = $this->db->fetchAll($sql, [$regionId]);

        return $this->groupRows($rows);
    }

    /**
     * Récupère les données par ville
     */
    public function getByVille(int $villeId): array
    {
        $sql = "SELECT * FROM vue_ville_recap WHERE ville_id = ? ORDER BY element_libele";
        $rows = $this->db->fetchAll($sql, [$villeId]);

        return $this->groupRows($rows);
    }

    /**
     * Statistiques globales
     */
    public function getStatsGlobales(): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT ville_id) AS total_villes,
                    COUNT(DISTINCT element_id) AS total_elements,
                    SUM(quantite_besoin) AS total_besoins,
                    SUM(quantite_donnee) AS total_dons,
                    SUM(quantite_restante) AS total_restants,
                    SUM(montant_besoin) AS montant_total_besoins,
                    SUM(montant_restant) AS montant_total_restants
                FROM vue_ville_recap";
        
        $result = $this->db->fetchRow($sql);
        
        // Convertir Collection en array si nécessaire
        if ($result instanceof \flight\util\Collection) {
            return $result->getData();
        }
        
        return $result ?: [];
    }

    /**
     * Méthode interne pour grouper les lignes par ville
     */
    private function groupRows(array $rows): array
    {
        $villes = [];
        foreach ($rows as $row) {
            $villeId = $row['ville_id'];
            
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'ville_id'      => $villeId,
                    'ville'         => $row['ville_libele'],
                    'region_id'     => $row['region_id'],
                    'region'        => $row['region_libele'],
                    'elements'      => [],
                    'totaux'        => [
                        'quantite_besoin'   => 0,
                        'quantite_donnee'   => 0,
                        'quantite_restante' => 0,
                        'montant_besoin'    => 0,
                        'montant_restant'   => 0,
                    ],
                ];
            }

            $villes[$villeId]['elements'][] = [
                'element_id'        => $row['element_id'],
                'element'           => $row['element_libele'],
                'prix_unitaire'     => (float)$row['element_pu'],
                'type_besoin_id'    => $row['type_besoin_id'],
                'type_besoin'       => $row['type_besoin_libele'],
                'quantite_besoin'   => (int)$row['quantite_besoin'],
                'quantite_donnee'   => (int)$row['quantite_donnee'],
                'quantite_restante' => (int)$row['quantite_restante'],
                'montant_besoin'    => (float)$row['montant_besoin'],
                'montant_restant'   => (float)$row['montant_restant'],
            ];

            $villes[$villeId]['totaux']['quantite_besoin']   += (int)$row['quantite_besoin'];
            $villes[$villeId]['totaux']['quantite_donnee']   += (int)$row['quantite_donnee'];
            $villes[$villeId]['totaux']['quantite_restante'] += (int)$row['quantite_restante'];
            $villes[$villeId]['totaux']['montant_besoin']    += (float)$row['montant_besoin'];
            $villes[$villeId]['totaux']['montant_restant']   += (float)$row['montant_restant'];
        }

        return array_values($villes);
    }
}
