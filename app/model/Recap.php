<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Recap - Récapitulatif des besoins, distributions et stock
 * 
 * Nouveau schéma:
 * - Dons/Achats → Stock global (sans ville)
 * - Distributions → Assignation du stock aux villes
 * - Besoins satisfaits = quantité distribuée pour ce besoin
 */
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
     * Récupère les besoins satisfaits (couverts par les distributions)
     * Un besoin est satisfait par les distributions reçues par la ville pour cet élément
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
                        COALESCE((
                            SELECT SUM(dist.quantite) 
                            FROM bn_distribution dist 
                            WHERE dist.idVille = b.idVille AND dist.idelement = b.idelement
                        ), 0)
                    ) AS quantite_satisfaite,
                    LEAST(
                        b.quantite,
                        COALESCE((
                            SELECT SUM(dist.quantite) 
                            FROM bn_distribution dist 
                            WHERE dist.idVille = b.idVille AND dist.idelement = b.idelement
                        ), 0)
                    ) * e.pu AS montant_satisfait
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
            ) AS satisfaits
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère les besoins restants (non satisfaits par les distributions)
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
                        b.quantite - COALESCE((
                            SELECT SUM(dist.quantite) 
                            FROM bn_distribution dist 
                            WHERE dist.idVille = b.idVille AND dist.idelement = b.idelement
                        ), 0)
                    ) AS quantite_restante,
                    GREATEST(
                        0,
                        b.quantite - COALESCE((
                            SELECT SUM(dist.quantite) 
                            FROM bn_distribution dist 
                            WHERE dist.idVille = b.idVille AND dist.idelement = b.idelement
                        ), 0)
                    ) * e.pu AS montant_restant
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
            ) AS restants
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récupère le total des dons (stock global)
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
     * Récupère le total des distributions
     */
    public function getTotalDistributions(): array
    {
        $row = $this->db->fetchRow("
            SELECT 
                COUNT(*) AS nb_distributions,
                COALESCE(SUM(d.quantite), 0) AS quantite_totale,
                COALESCE(SUM(d.quantite * e.pu), 0) AS montant_total
            FROM bn_distribution d
            JOIN bn_element e ON d.idelement = e.id
        ");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return $data ?: ['nb_distributions' => 0, 'quantite_totale' => 0, 'montant_total' => 0];
    }

    /**
     * Récapitulatif par ville
     */
    public function getRecapParVille(): array
    {
        return $this->db->fetchAll("
            SELECT 
                v.id AS ville_id,
                v.libele AS ville_libele,
                r.libele AS region_libele,
                COALESCE(besoins.quantite_totale, 0) AS besoins_quantite,
                COALESCE(besoins.montant_total, 0) AS besoins_montant,
                COALESCE(distributions.quantite_totale, 0) AS distributions_quantite,
                COALESCE(distributions.montant_total, 0) AS distributions_montant,
                GREATEST(0, COALESCE(besoins.quantite_totale, 0) - COALESCE(distributions.quantite_totale, 0)) AS manque_quantite,
                GREATEST(0, COALESCE(besoins.montant_total, 0) - COALESCE(distributions.montant_total, 0)) AS manque_montant
            FROM bn_ville v
            JOIN bn_region r ON v.idRegion = r.id
            LEFT JOIN (
                SELECT 
                    b.idVille,
                    SUM(b.quantite) AS quantite_totale,
                    SUM(b.quantite * e.pu) AS montant_total
                FROM bn_besoin b
                JOIN bn_element e ON b.idelement = e.id
                GROUP BY b.idVille
            ) AS besoins ON v.id = besoins.idVille
            LEFT JOIN (
                SELECT 
                    d.idVille,
                    SUM(d.quantite) AS quantite_totale,
                    SUM(d.quantite * e.pu) AS montant_total
                FROM bn_distribution d
                JOIN bn_element e ON d.idelement = e.id
                GROUP BY d.idVille
            ) AS distributions ON v.id = distributions.idVille
            ORDER BY v.libele
        ");
    }

    /**
     * Récapitulatif complet pour l'API
     */
    public function getRecapComplet(): array
    {
        $besoins = $this->getTotalBesoins();
        $satisfaits = $this->getBesoinsSatisfaits();
        $restants = $this->getBesoinsRestants();
        $dons = $this->getTotalDons();
        $achats = $this->getTotalAchats();
        $distributions = $this->getTotalDistributions();

        return [
            'besoins_totaux' => (int)($besoins['montant_total'] ?? 0),
            'besoins_satisfaits' => (int)($satisfaits['montant_total'] ?? 0),
            'besoins_restants' => (int)($restants['montant_total'] ?? 0),
            'total_dons' => (int)($dons['montant_total'] ?? 0),
            'total_achats' => (int)($achats['montant_ttc'] ?? 0),
            'total_distributions' => (int)($distributions['montant_total'] ?? 0),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
