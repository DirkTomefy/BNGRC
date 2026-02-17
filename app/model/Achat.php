<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Achat - Achats effectués avec les dons en argent
 * Les achats vont au stock global (pas de ville)
 */
class Achat
{
    private PdoWrapper $db;
    private const TAUX_FRAIS_DEFAUT = 10.00; // 10% de frais par défaut

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
                   e.pu AS prix_catalogue
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            ORDER BY a.date DESC
        ");
    }

    /**
     * Récupère un achat par ID
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("
            SELECT a.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_catalogue
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE a.id = ?
        ", [$id]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    /**
     * Insère un nouvel achat (va au stock global)
     * Calcule automatiquement HT, frais et TTC
     */
    public function insert(
        int $idElement, 
        int $quantite, 
        float $prixUnitaire, 
        float $tauxFrais = self::TAUX_FRAIS_DEFAUT,
        string $date = '',
        string $description = ''
    ): int {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        $montantHT = $quantite * $prixUnitaire;
        $montantFrais = $montantHT * ($tauxFrais / 100);
        $montantTTC = $montantHT + $montantFrais;

        $this->db->runQuery(
            "INSERT INTO bn_achat (idelement, quantite, prixUnitaire, montantHT, tauxFrais, montantFrais, montantTTC, `date`, description) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$idElement, $quantite, $prixUnitaire, $montantHT, $tauxFrais, $montantFrais, $montantTTC, $date, $description]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Vérifie si on a assez d'argent pour un achat
     */
    public function peutAcheter(float $montantTTC): bool
    {
        $stockModel = new Stock($this->db);
        $argentDisponible = $stockModel->getArgentDisponible();
        return $argentDisponible >= $montantTTC;
    }

    /**
     * Calcule le montant TTC d'un achat
     */
    public static function calculerMontantTTC(int $quantite, float $prixUnitaire, float $tauxFrais = self::TAUX_FRAIS_DEFAUT): array
    {
        $montantHT = $quantite * $prixUnitaire;
        $montantFrais = $montantHT * ($tauxFrais / 100);
        $montantTTC = $montantHT + $montantFrais;

        return [
            'montantHT' => $montantHT,
            'montantFrais' => $montantFrais,
            'montantTTC' => $montantTTC,
            'tauxFrais' => $tauxFrais
        ];
    }

    /**
     * Récupère le total des achats
     */
    public function getTotal(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_achats,
                COALESCE(SUM(quantite), 0) AS quantite_totale,
                COALESCE(SUM(montantHT), 0) AS montant_ht_total,
                COALESCE(SUM(montantFrais), 0) AS montant_frais_total,
                COALESCE(SUM(montantTTC), 0) AS montant_ttc_total
            FROM bn_achat
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_achats' => 0, 'quantite_totale' => 0, 'montant_ht_total' => 0, 'montant_frais_total' => 0, 'montant_ttc_total' => 0];
    }

    /**
     * Récupère les achats par élément
     */
    public function getByElement(int $idElement): array
    {
        return $this->db->fetchAll("
            SELECT a.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin
            FROM bn_achat a
            JOIN bn_element e ON a.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE a.idelement = ?
            ORDER BY a.date DESC
        ", [$idElement]);
    }

    /**
     * Supprime un achat
     */
    public function delete(int $id): bool
    {
        $this->db->runQuery("DELETE FROM bn_achat WHERE id = ?", [$id]);
        return true;
    }
}
