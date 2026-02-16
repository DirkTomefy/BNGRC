<?php

namespace app\model;

use flight\database\PdoWrapper;

class Config
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère une valeur de configuration par sa clé
     */
    public function get(string $cle, string $default = ''): string
    {
        $row = $this->db->fetchRow("SELECT valeur FROM bn_config WHERE cle = ?", [$cle]);
        if ($row instanceof \flight\util\Collection) {
            $data = $row->getData();
            return $data['valeur'] ?? $default;
        }
        return $row['valeur'] ?? $default;
    }

    /**
     * Met à jour une valeur de configuration
     */
    public function set(string $cle, string $valeur): bool
    {
        $stmt = $this->db->runQuery(
            "INSERT INTO bn_config (cle, valeur) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)",
            [$cle, $valeur]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupère toutes les configurations
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM bn_config ORDER BY cle");
    }

    /**
     * Récupère le pourcentage de frais d'achat
     */
    public function getFraisAchatPourcent(): float
    {
        return (float)$this->get('frais_achat_pourcent', '10');
    }

    /**
     * Met à jour le pourcentage de frais d'achat
     */
    public function setFraisAchatPourcent(float $pourcent): bool
    {
        return $this->set('frais_achat_pourcent', (string)$pourcent);
    }
}
