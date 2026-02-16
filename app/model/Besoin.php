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
}