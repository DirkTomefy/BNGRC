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
            SELECT 
                idelement,
                element_libele,
                type_besoin,
                prix_unitaire,
                quantite_dons,
                quantite_achats,
                quantite_distribuee,
                quantite_stock AS stock_disponible,
                (quantite_stock * prix_unitaire) AS valeur_stock
            FROM vue_stock
            WHERE quantite_stock > 0
            ORDER BY type_besoin, element_libele
        ");
    }

    /**
     * Récupère tout le stock (même à zéro)
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT 
                idelement,
                element_libele,
                type_besoin,
                prix_unitaire,
                quantite_dons,
                quantite_achats,
                quantite_distribuee,
                quantite_stock AS stock_disponible,
                (quantite_stock * prix_unitaire) AS valeur_stock
            FROM vue_stock
            ORDER BY type_besoin, element_libele
        ");
    }

    /**
     * Récupère le stock d'un élément spécifique
     */
    public function getByElement(int $idElement): ?array
    {
        $row = $this->db->fetchRow("
            SELECT 
                idelement,
                element_libele,
                type_besoin,
                prix_unitaire,
                quantite_dons,
                quantite_achats,
                quantite_distribuee,
                quantite_stock AS stock_disponible,
                (quantite_stock * prix_unitaire) AS valeur_stock
            FROM vue_stock
            WHERE idelement = ?
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
            SELECT COALESCE(SUM(quantite_stock * prix_unitaire), 0) AS total
            FROM vue_stock
        ");
        
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return (float)($data['total'] ?? 0);
    }

    /**
     * Récupère le récap global (besoins vs stock) - retourne une seule ligne
     */
    public function getRecapGlobal(): ?array
    {
        $row = $this->db->fetchRow("SELECT * FROM vue_recap_global");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    /**
     * Récapitulatif par type de besoin
     */
    public function getRecapParType(): array
    {
        return $this->db->fetchAll("
            SELECT 
                idTypeBesoin AS type_id,
                type_besoin,
                SUM(quantite_dons) AS total_dons,
                SUM(quantite_achats) AS total_achats,
                SUM(quantite_distribuee) AS total_distribue,
                SUM(quantite_stock) AS stock_disponible,
                SUM(quantite_stock * prix_unitaire) AS valeur_stock
            FROM vue_stock
            GROUP BY idTypeBesoin, type_besoin
            ORDER BY type_besoin
        ");
    }

    /**
     * Argent disponible pour achats = Dons en argent - Montant des achats effectués
     */
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
