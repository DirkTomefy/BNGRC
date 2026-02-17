<?php

namespace app\model;

use flight\database\PdoWrapper;

/**
 * Modèle Distribution - Assignation du stock aux villes
 */
class Distribution
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère toutes les distributions
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, 
                   v.libele AS ville_libele,
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (d.quantite * e.pu) AS montant
            FROM bn_distribution d
            JOIN bn_ville v ON d.idVille = v.id
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            ORDER BY d.date DESC
        ");
    }

    /**
     * Récupère les distributions par ville
     */
    public function getByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT d.*, 
                   e.libele AS element_libele,
                   tb.libele AS type_besoin,
                   e.pu AS prix_unitaire,
                   (d.quantite * e.pu) AS montant
            FROM bn_distribution d
            JOIN bn_element e ON d.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            WHERE d.idVille = ?
            ORDER BY d.date DESC
        ", [$idVille]);
    }

    /**
     * Récupère les distributions par élément
     */
    public function getByElement(int $idElement): array
    {
        return $this->db->fetchAll("
            SELECT d.*, 
                   v.libele AS ville_libele
            FROM bn_distribution d
            JOIN bn_ville v ON d.idVille = v.id
            WHERE d.idelement = ?
            ORDER BY d.date DESC
        ", [$idElement]);
    }

    /**
     * Insère une nouvelle distribution
     */
    public function insert(int $idVille, int $idElement, int $quantite, string $source = 'manuel', ?int $idSource = null, string $date = ''): int
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        $this->db->runQuery(
            "INSERT INTO bn_distribution (idVille, idelement, quantite, `date`, source, id_source) VALUES (?, ?, ?, ?, ?, ?)",
            [$idVille, $idElement, $quantite, $date, $source, $idSource]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Distribution automatique: Distribue le stock selon les besoins des villes (FIFO)
     */
    public function distribuerAuto(): array
    {
        $result = [
            'distributions' => [],
            'errors' => [],
            'summary' => [
                'total_distributions' => 0,
                'total_quantite' => 0
            ]
        ];

        try {
            $this->db->runQuery("START TRANSACTION");

            // Récupérer le stock disponible
            $stock = $this->db->fetchAll("SELECT idelement, quantite_stock FROM vue_stock WHERE quantite_stock > 0");
            $stockMap = [];
            foreach ($stock as $s) {
                $stockMap[$s['idelement']] = $s['quantite_stock'];
            }

            // Récupérer les besoins non satisfaits via vue_besoins_ville (FIFO par date)
            $besoins = $this->db->fetchAll("
                SELECT 
                    id_besoin,
                    idVille,
                    ville_libele,
                    idelement,
                    element_libele,
                    quantite_demandee,
                    quantite_recue,
                    quantite_restante
                FROM vue_besoins_ville
                WHERE quantite_restante > 0
                ORDER BY date_besoin ASC, id_besoin ASC
            ");

            foreach ($besoins as $besoin) {
                $idElement = $besoin['idelement'];
                $idVille = $besoin['idVille'];
                $quantiteRestante = (int)$besoin['quantite_restante'];

                if (!isset($stockMap[$idElement]) || $stockMap[$idElement] <= 0) {
                    continue; // Pas de stock disponible pour cet élément
                }

                // Quantité à distribuer = min(stock disponible, besoin restant)
                $quantiteDistribuer = min($stockMap[$idElement], $quantiteRestante);

                if ($quantiteDistribuer > 0) {
                    // Créer la distribution
                    $distId = $this->insert($idVille, $idElement, $quantiteDistribuer, 'don', null);

                    $result['distributions'][] = [
                        'id' => $distId,
                        'ville' => $besoin['ville_libele'],
                        'element' => $besoin['element_libele'],
                        'quantite' => $quantiteDistribuer,
                        'besoin_id' => $besoin['id_besoin']
                    ];

                    // Mettre à jour le stock disponible localement
                    $stockMap[$idElement] -= $quantiteDistribuer;
                }
            }

            $this->db->runQuery("COMMIT");

            $result['summary']['total_distributions'] = count($result['distributions']);
            $result['summary']['total_quantite'] = array_sum(array_column($result['distributions'], 'quantite'));
            $result['summary']['date'] = date('Y-m-d H:i:s');

        } catch (\Exception $e) {
            $this->db->runQuery("ROLLBACK");
            $result['errors'][] = 'Erreur lors de la distribution: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Récupère le récap des distributions par ville
     */
    public function getRecapParVille(): array
    {
        return $this->db->fetchAll("
            SELECT 
                v.id AS ville_id,
                v.libele AS ville,
                COUNT(d.id) AS nb_distributions,
                COALESCE(SUM(d.quantite), 0) AS quantite_totale,
                COALESCE(SUM(d.quantite * e.pu), 0) AS montant_total
            FROM bn_ville v
            LEFT JOIN bn_distribution d ON v.id = d.idVille
            LEFT JOIN bn_element e ON d.idelement = e.id
            GROUP BY v.id, v.libele
            ORDER BY v.libele
        ");
    }

    /**
     * Supprime une distribution
     */
    public function delete(int $id): bool
    {
        $this->db->runQuery("DELETE FROM bn_distribution WHERE id = ?", [$id]);
        return true;
    }
}
