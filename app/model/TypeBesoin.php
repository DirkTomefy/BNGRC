<?php

namespace app\model;

use flight\database\PdoWrapper;

class TypeBesoin
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM bn_typeBesoin ORDER BY libele");
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("SELECT * FROM bn_typeBesoin WHERE id = ?", [$id]);
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    public function create(string $libele): int
    {
        $this->db->runQuery("INSERT INTO bn_typeBesoin (libele) VALUES (?)", [$libele]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $libele): bool
    {
        $stmt = $this->db->runQuery("UPDATE bn_typeBesoin SET libele = ? WHERE id = ?", [$libele, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->runQuery("DELETE FROM bn_typeBesoin WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}