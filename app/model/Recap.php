<?php

namespace app\model;

use flight\database\PdoWrapper;

class Recap
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère le total des besoins (tous les besoins)
     */
    public function getTotalBesoins(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_besoins,
                COALESCE(SUM(b.quantite), 0) AS quantite_totale,
                COALESCE(SUM(b.quantite * e.pu), 0) AS montant_total
            FROM bn_besoin b
            JOIN bn_element e ON b.idelement = e.id
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_besoins' => 0, 'quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère les besoins satisfaits (couverts par les dons + achats)
     */
    public function getBesoinsSatisfaits(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COALESCE(SUM(quantite_satisfaite), 0) AS quantite_totale,
                COALESCE(SUM(montant_satisfait), 0) AS montant_total
            FROM (
                SELECT 
                    LEAST(
                        b.quantite,
                        COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) +
                        COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)
                    ) AS quantite_satisfaite,
                    LEAST(
                        b.quantite,
                        COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) +
                        COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)
                    ) * e.pu AS montant_satisfait
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
            ) AS satisfaits
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère les besoins restants (non satisfaits)
     */
    public function getBesoinsRestants(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COALESCE(SUM(quantite_restante), 0) AS quantite_totale,
                COALESCE(SUM(montant_restant), 0) AS montant_total
            FROM (
                SELECT 
                    GREATEST(
                        0,
                        b.quantite - 
                        COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) -
                        COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)
                    ) AS quantite_restante,
                    GREATEST(
                        0,
                        b.quantite - 
                        COALESCE((SELECT SUM(d.quantite) FROM bn_don d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0) -
                        COALESCE((SELECT SUM(a.quantite) FROM bn_achat a WHERE a.idBesoin = b.id), 0)
                    ) * e.pu AS montant_restant
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
            ) AS restants
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère le total des dons
     */
    public function getTotalDons(): array
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
     * Récupère le total des achats
     */
    public function getTotalAchats(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_achats,
                COALESCE(SUM(quantite), 0) AS quantite_totale,
                COALESCE(SUM(montantHT), 0) AS montant_ht,
                COALESCE(SUM(montantFrais), 0) AS montant_frais,
                COALESCE(SUM(montantTTC), 0) AS montant_ttc
            FROM bn_achat
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_achats' => 0, 'quantite_totale' => 0, 'montant_ht' => 0, 'montant_frais' => 0, 'montant_ttc' => 0];
    }

    /**
     * Récapitulatif complet pour l'API
     */
    public function getRecapComplet(): array
    {
        return [
            'besoins_totaux' => $this->getTotalBesoins(),
            'besoins_satisfaits' => $this->getBesoinsSatisfaits(),
            'besoins_restants' => $this->getBesoinsRestants(),
            'dons_totaux' => $this->getTotalDons(),
            'achats_totaux' => $this->getTotalAchats(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
