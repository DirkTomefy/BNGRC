<?php

namespace app\model;

use flight\database\PdoWrapper;

class Besoin
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT b.*, e.libele as element_libele, e.pu as element_pu, v.libele as ville_libele,
                   (b.quantite * e.pu) as montant_total
            FROM bn_besoin b 
            LEFT JOIN bn_element e ON b.idelement = e.id 
            LEFT JOIN bn_ville v ON b.idVille = v.id 
            ORDER BY b.date DESC
        ");
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("
            SELECT b.*, e.libele as element_libele, e.pu as element_pu, v.libele as ville_libele,
                   (b.quantite * e.pu) as montant_total
            FROM bn_besoin b 
            LEFT JOIN bn_element e ON b.idelement = e.id 
            LEFT JOIN bn_ville v ON b.idVille = v.id 
            WHERE b.id = ?
        ", [$id]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    public function getByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT b.*, e.libele as element_libele, e.pu as element_pu,
                   (b.quantite * e.pu) as montant_total
            FROM bn_besoin b 
            LEFT JOIN bn_element e ON b.idelement = e.id 
            WHERE b.idVille = ? 
            ORDER BY b.date DESC
        ", [$idVille]);
    }

    public function getByElement(int $idElement): array
    {
        return $this->db->fetchAll("
            SELECT b.*, v.libele as ville_libele, e.pu as element_pu,
                   (b.quantite * e.pu) as montant_total
            FROM bn_besoin b 
            LEFT JOIN bn_ville v ON b.idVille = v.id 
            LEFT JOIN bn_element e ON b.idelement = e.id 
            WHERE b.idelement = ? 
            ORDER BY b.date DESC
        ", [$idElement]);
    }

    public function insert(int $idElement, int $quantite, int $idVille, string $date): int
    {
        $this->db->runQuery("INSERT INTO bn_besoin (idelement, quantite, idVille, date) VALUES (?, ?, ?, ?)", [$idElement, $quantite, $idVille, $date]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $idElement, int $quantite, int $idVille, string $date): bool
    {
        $stmt = $this->db->runQuery("UPDATE bn_besoin SET idelement = ?, quantite = ?, idVille = ?, date = ? WHERE id = ?", [$idElement, $quantite, $idVille, $date, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->runQuery("DELETE FROM bn_besoin WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getStatsByVille(): array
    {
        return $this->db->fetchAll("
            SELECT v.libele as ville_libele, COUNT(b.id) as nombre_besoins, 
                   SUM(b.quantite * e.pu) as montant_total
            FROM bn_besoin b 
            LEFT JOIN bn_element e ON b.idelement = e.id 
            LEFT JOIN bn_ville v ON b.idVille = v.id 
            GROUP BY v.id, v.libele 
            ORDER BY montant_total DESC
        ");
    }

    /**
     * Récupère les besoins non satisfaits groupés par ville
     */
    public function getBesoinsParVille(): array
    {
        return $this->db->fetchAll("
            SELECT 
                v.id AS ville_id,
                v.libele AS ville_libele,
                COUNT(b.id) AS nb_besoins,
                SUM(b.quantite) AS quantite_totale,
                SUM(b.quantite * e.pu) AS montant_total,
                COALESCE((
                    SELECT SUM(d.quantite)
                    FROM bn_distribution d
                    WHERE d.idVille = v.id
                ), 0) AS deja_recu
            FROM bn_ville v
            LEFT JOIN bn_besoin b ON v.id = b.idVille AND (b.satisfait = 0 OR b.satisfait IS NULL)
            LEFT JOIN bn_element e ON b.idelement = e.id
            GROUP BY v.id, v.libele
            HAVING nb_besoins > 0
            ORDER BY v.libele
        ");
    }

    /**
     * Marque un besoin comme satisfait
     */
    public function marquerSatisfait(int $id): bool
    {
        $stmt = $this->db->runQuery("UPDATE bn_besoin SET satisfait = 1 WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupère les besoins non satisfaits
     */
    public function getNonSatisfaits(): array
    {
        return $this->db->fetchAll("
            SELECT b.*, 
                   e.libele AS element_libele, 
                   e.pu AS element_pu, 
                   v.libele AS ville_libele,
                   tb.libele AS type_besoin,
                   (b.quantite * e.pu) AS montant_total,
                   COALESCE((
                       SELECT SUM(d.quantite)
                       FROM bn_distribution d
                       WHERE d.idVille = b.idVille AND d.idelement = b.idelement
                   ), 0) AS deja_recu
            FROM bn_besoin b
            JOIN bn_element e ON b.idelement = e.id
            JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
            JOIN bn_ville v ON b.idVille = v.id
            WHERE b.satisfait = 0 OR b.satisfait IS NULL
            ORDER BY b.date ASC, b.id ASC
        ");
    }
}