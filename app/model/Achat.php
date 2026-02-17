<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Achat - Achats effectués (stock global, sans ville)
 * L'argent pour les achats provient des dons en argent
 */
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
                   e.pu AS prix_unitaire
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
                   e.pu AS prix_unitaire
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
     * Le montant est déduit des dons en argent
     */
    public function insert(int $idElement, int $quantite, float $prix, string $date = ''): int
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        $montant = $quantite * $prix;

        $this->db->runQuery(
            "INSERT INTO bn_achat (idelement, quantite, prix, montants, `date`) VALUES (?, ?, ?, ?, ?)",
            [$idElement, $quantite, $prix, $montant, $date]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Vérifie si on a assez d'argent pour un achat
     */
    public function peutAcheter(float $montant): bool
    {
        $stockModel = new Stock($this->db);
        $argentDisponible = $stockModel->getArgentDisponible();
        return $argentDisponible >= $montant;
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
                COALESCE(SUM(montants), 0) AS montant_total
            FROM bn_achat
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_achats' => 0, 'quantite_totale' => 0, 'montant_total' => 0];
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
