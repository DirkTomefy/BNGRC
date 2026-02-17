<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Don - Dons reçus (stock global, sans ville)
 */
class Don
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les dons
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (d.quantite * e.pu) AS montant
            FROM bn_don d
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            ORDER BY d.date DESC
        ");
    }

    /**
     * Récupère un don par ID
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("
            SELECT d.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire
            FROM bn_don d
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE d.id = ?
        ", [$id]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    /**
     * Insère un nouveau don (va au stock global)
     */
    public function insert(int $idElement, int $quantite, string $date = '', string $description = ''): int
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        $this->db->runQuery(
            "INSERT INTO bn_don (idelement, quantite, `date`, description) VALUES (?, ?, ?, ?)",
            [$idElement, $quantite, $date, $description]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Récupère le total des dons
     */
    public function getTotal(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_dons,
                COALESCE(SUM(d.quantite), 0) AS quantite_totale,
                COALESCE(SUM(d.quantite * e.pu), 0) AS montant_total
            FROM bn_don d
            JOIN bn_element e ON d.idelement = e.id
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_dons' => 0, 'quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère les dons en argent disponibles
     */
    public function getDonsArgent(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, 
                   e.libele AS element_libele,
                   e.pu AS prix_unitaire,
                   (d.quantite * e.pu) AS montant
            FROM bn_don d
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE tb.libele = 'Argent'
            ORDER BY d.date ASC
        ");
    }

    /**
     * Calcule le montant total des dons en argent
     */
    public function getMontantArgentTotal(): float
    {
        $row = $this->db->fetchRow("
            SELECT COALESCE(SUM(d.quantite * e.pu), 0) AS total
            FROM bn_don d
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE tb.libele = 'Argent'
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return (float)($data['total'] ?? 0);
    }

    /**
     * Supprime un don
     */
    public function delete(int $id): bool
    {
        $this->db->runQuery("DELETE FROM bn_don WHERE id = ?", [$id]);
        return true;
    }
}
