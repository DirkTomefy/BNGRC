<?php

namespace app\model;

use flight\database\PdoWrapper;

class Achat
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les achats
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT a.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   v.libele AS ville_libele,
                   r.libele AS region_libele
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            JOIN bn_ville v ON a.idVille = v.id
            JOIN bn_region r ON v.idRegion = r.id
            ORDER BY a.date DESC
        ");
    }

    /**
     * Récupère les achats par ville
     */
    public function getByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT a.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   v.libele AS ville_libele,
                   r.libele AS region_libele
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            JOIN bn_ville v ON a.idVille = v.id
            JOIN bn_region r ON v.idRegion = r.id
            WHERE a.idVille = ?
            ORDER BY a.date DESC
        ", [$idVille]);
    }

    /**
     * Récupère un achat par ID
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("
            SELECT a.*, 
                   e.libele AS element_libele,
                   v.libele AS ville_libele
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_ville v ON a.idVille = v.id
            WHERE a.id = ?
        ", [$id]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    /**
     * Insère un nouvel achat
     */
    public function insert(
        int $idBesoin,
        int $idVille,
        int $idElement,
        int $quantite,
        float $prixUnitaire,
        float $tauxFrais,
        string $date = ''
    ): int {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        $montantHT = $quantite * $prixUnitaire;
        $montantFrais = $montantHT * ($tauxFrais / 100);
        $montantTTC = $montantHT + $montantFrais;

        $this->db->runQuery("
            INSERT INTO bn_achat 
            (idBesoin, idVille, idelement, quantite, prixUnitaire, montantHT, tauxFrais, montantFrais, montantTTC, `date`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [$idBesoin, $idVille, $idElement, $quantite, $prixUnitaire, $montantHT, $tauxFrais, $montantFrais, $montantTTC, $date]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Récupère les besoins restants (Nature et Matériel uniquement, non satisfaits)
     */
    public function getBesoinsRestants(): array
    {
        return $this->db->fetchAll("
            SELECT * FROM (
                SELECT 
                    b.id,
                    b.idelement,
                    e.libele AS element_libele,
                    tb.id AS idTypeBesoin,
                    tb.libele AS type_besoin,
                    b.quantite AS quantite_demandee,
                    COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) AS quantite_donnee,
                    COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0) AS quantite_achetee,
                    (b.quantite - COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) - COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)) AS quantite_restante,
                    e.pu AS prix_unitaire,
                    b.idVille,
                    v.libele AS ville_libele,
                    r.id AS idRegion,
                    r.libele AS region_libele,
                    b.date
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
                JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
                JOIN bn_ville v ON b.idVille = v.id
                JOIN bn_region r ON v.idRegion = r.id
            ) AS sub
            WHERE sub.quantite_restante > 0
            ORDER BY sub.date ASC, sub.id ASC
        ");
    }

    /**
     * Récupère les besoins restants par ville
     */
    public function getBesoinsRestantsByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT * FROM (
                SELECT 
                    b.id,
                    b.idelement,
                    e.libele AS element_libele,
                    tb.id AS idTypeBesoin,
                    tb.libele AS type_besoin,
                    b.quantite AS quantite_demandee,
                    COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) AS quantite_donnee,
                    COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0) AS quantite_achetee,
                    (b.quantite - COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) - COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)) AS quantite_restante,
                    e.pu AS prix_unitaire,
                    b.idVille,
                    v.libele AS ville_libele,
                    r.id AS idRegion,
                    r.libele AS region_libele,
                    b.date
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
                JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
                JOIN bn_ville v ON b.idVille = v.id
                JOIN bn_region r ON v.idRegion = r.id
                WHERE b.idVille = ?
            ) AS sub
            WHERE sub.quantite_restante > 0
            ORDER BY sub.date ASC, sub.id ASC
        ", [$idVille]);
    }

    /**
     * Vérifie si un don existe déjà pour cet élément dans les dons restants
     * Retourne true si un don existe (donc l'achat ne doit pas être fait)
     */
    public function verifierDonExistant(int $idElement, int $idVille): bool
    {
        $row = $this->db->fetchRow("
            SELECT COUNT(*) AS nb
            FROM bn_don d
            WHERE d.idelement = ? AND d.idVille = ?
        ", [$idElement, $idVille]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return (int)($data['nb'] ?? 0) > 0;
    }

    /**
     * Récupère le total des achats
     */
    public function getTotalAchats(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_achats,
                COALESCE(SUM(quantite), 0) AS quantite_totale,
                COALESCE(SUM(montantHT), 0) AS montant_ht_total,
                COALESCE(SUM(montantFrais), 0) AS frais_total,
                COALESCE(SUM(montantTTC), 0) AS montant_ttc_total
            FROM bn_achat
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: [
            'nb_achats' => 0,
            'quantite_totale' => 0,
            'montant_ht_total' => 0,
            'frais_total' => 0,
            'montant_ttc_total' => 0
        ];
    }

    /**
     * Récupère le total des achats par ville
     */
    public function getTotalAchatsByVille(int $idVille): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_achats,
                COALESCE(SUM(quantite), 0) AS quantite_totale,
                COALESCE(SUM(montantHT), 0) AS montant_ht_total,
                COALESCE(SUM(montantFrais), 0) AS frais_total,
                COALESCE(SUM(montantTTC), 0) AS montant_ttc_total
            FROM bn_achat
            WHERE idVille = ?
        ", [$idVille]);
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: [
            'nb_achats' => 0,
            'quantite_totale' => 0,
            'montant_ht_total' => 0,
            'frais_total' => 0,
            'montant_ttc_total' => 0
        ];
    }
}
