<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Stock - Vue sur le stock global (Dons + Achats - Distributions)
 */
class Stock
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère le stock disponible (utilise la vue vue_stock)
     */
    public function getStockDisponible(): array
    {
        return $this->db->fetchAll("
            SELECT s.*, 
                   s.quantite_stock AS stock_disponible,
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (s.quantite_stock * e.pu) AS valeur_stock
            FROM vue_stock s
            JOIN bn_element e ON s.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE s.quantite_stock > 0
            ORDER BY tb.libele, e.libele
        ");
    }

    /**
     * Récupère tout le stock (même à zéro)
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT s.*, 
                   s.quantite_stock AS stock_disponible,
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (s.quantite_stock * e.pu) AS valeur_stock
            FROM vue_stock s
            JOIN bn_element e ON s.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            ORDER BY tb.libele, e.libele
        ");
    }

    /**
     * Récupère le stock d'un élément spécifique
     */
    public function getByElement(int $idElement): ?array
    {
        $row = $this->db->fetchRow("
            SELECT s.*, 
                   s.quantite_stock AS stock_disponible,
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (s.quantite_stock * e.pu) AS valeur_stock
            FROM vue_stock s
            JOIN bn_element e ON s.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE s.idelement = ?
        ", [$idElement]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    /**
     * Récupère le stock total (valeur)
     */
    public function getTotalValeur(): float
    {
        $row = $this->db->fetchRow("
            SELECT COALESCE(SUM(s.quantite_stock * e.pu), 0) AS total
            FROM vue_stock s
            JOIN bn_element e ON s.idelement = e.id
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return (float)($data['total'] ?? 0);
    }

    /**
     * Récupère le récap global (besoins vs stock)
     */
    public function getRecapGlobal(): array
    {
        return $this->db->fetchAll("SELECT * FROM vue_recap_global ORDER BY type_besoin, element");
    }

    /**
     * Récapitulatif par type de besoin
     */
    public function getRecapParType(): array
    {
        return $this->db->fetchAll("
            SELECT 
                tb.id AS type_id,
                tb.libele AS type_besoin,
                COALESCE(SUM(s.quantite_dons), 0) AS total_dons,
                COALESCE(SUM(s.quantite_achats), 0) AS total_achats,
                COALESCE(SUM(s.quantite_distribuee), 0) AS total_distribue,
                COALESCE(SUM(s.quantite_stock), 0) AS stock_disponible,
                COALESCE(SUM(s.quantite_stock * e.pu), 0) AS valeur_stock
            FROM bn_typeBesoin tb
            LEFT JOIN bn_element e ON e.idtypebesoin = tb.id
            LEFT JOIN vue_stock s ON s.idelement = e.id
            GROUP BY tb.id, tb.libele
            ORDER BY tb.libele
        ");
    }

    public function getArgentDisponible(): float
    {
        $row = $this->db->fetchRow("
            SELECT 
                COALESCE((
                    SELECT SUM(d.quantite * e.pu)
                    FROM bn_don d
                    JOIN bn_element e ON d.idelement = e.id
                    JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
                    WHERE tb.libele = 'Argent'
                ), 0) - COALESCE((
                    SELECT SUM(montantTTC)
                    FROM bn_achat
                ), 0) AS argent_disponible
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return max(0, (float)($data['argent_disponible'] ?? 0));
    }
}
