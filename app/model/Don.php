<?php

namespace app\model;

use flight\database\PdoWrapper;

class Don
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, v.libele as ville_libele 
            FROM bn_don d 
            LEFT JOIN bn_ville v ON d.idVille = v.id 
            ORDER BY d.date DESC
        ");
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchRow("
            SELECT d.*, v.libele as ville_libele 
            FROM bn_don d 
            LEFT JOIN bn_ville v ON d.idVille = v.id 
            WHERE d.id = ?
        ", [$id]);

        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return empty($data) ? null : $data;
    }

    public function getByVille(int $idVille): array
    {
        return $this->db->fetchAll("
            SELECT * FROM bn_don 
            WHERE idVille = ? 
            ORDER BY date DESC
        ", [$idVille]);
    }

    public function insertDon(int $idVille, int $idElement, int $quantite, string $date = '', string $description = ''): int
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        $this->db->runQuery(
            "INSERT INTO bn_don (idVille, idelement, quantite, `date`, description) VALUES (?, ?, ?, ?, ?)",
            [$idVille, $idElement, $quantite, $date, $description]
        );
        return (int)$this->db->lastInsertId();
    }

    public function getTotalQuantite(): int
    {
        $row = $this->db->fetchRow("SELECT SUM(quantite) as total FROM bn_don");
        $data = $row instanceof \flight\util\Collection ? $row->getData() : $row;
        return (int)($data['total'] ?? 0);
    }

    public function dispatchDon(): array
    {
        try {
            $this->db->runQuery("START TRANSACTION");
            
            $dons = $this->getDonsNonDistribues();
            $besoins = $this->getBesoinsEnAttente();
            $besoinsMap = $this->creerBesoinMap($besoins);
            
            $result = $this->effectuerDistribution($dons, $besoinsMap);
            
            $this->db->runQuery("COMMIT");
            
            $result['summary'] = [
                'total_distributions' => count($result),
                'total_quantite_distribuee' => array_sum(array_column($result, 'quantite_distribuee')),
                'date_distribution' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->db->runQuery("ROLLBACK");
            $result['error'] = 'Erreur lors de la distribution: ' . $e->getMessage();
        }
        
        return $result;
    }

    private function getDonsNonDistribues(): array
    {
        return $this->db->fetchAll("
            SELECT d.*, e.libele as element_libele
            FROM bn_don d
            LEFT JOIN bn_element e ON d.idelement = e.id
            WHERE d.distribue = 0 OR d.distribue IS NULL
            ORDER BY d.date ASC, d.id ASC
        ");
    }

    private function getBesoinsEnAttente(): array
    {
        return $this->db->fetchAll("
            SELECT b.*, e.libele as element_libele, v.libele as ville_libele
            FROM bn_besoin b
            LEFT JOIN bn_element e ON b.idelement = e.id
            LEFT JOIN bn_ville v ON b.idVille = v.id
            WHERE b.satisfait = 0 OR b.satisfait IS NULL
            ORDER BY b.date ASC, b.id ASC
        ");
    }

    private function creerBesoinMap(array $besoins): array
    {
        $besoinsMap = [];
        foreach ($besoins as $besoin) {
            $key = $besoin['idelement'] . '_' . $besoin['idVille'];
            if (!isset($besoinsMap[$key])) {
                $besoinsMap[$key] = [
                    'besoin' => $besoin,
                    'quantite_restante' => $besoin['quantite']
                ];
            }
        }
        return $besoinsMap;
    }

    private function effectuerDistribution(array $dons, array &$besoinsMap): array
    {
        $result = [];
        
        foreach ($dons as $don) {
            $distributions = $this->distribuerDon($don, $besoinsMap);
            $result = array_merge($result, $distributions);
            
            if (!empty($distributions)) {
                $this->marquerDonDistribue($don['id']);
            }
        }
        
        return $result;
    }

    private function distribuerDon(array $don, array &$besoinsMap): array
    {
        $distributions = [];
        $quantiteRestante = $don['quantite'];
        
        foreach ($besoinsMap as $key => $besoinInfo) {
            if ($this->peutDistribuer($don, $besoinInfo, $quantiteRestante)) {
                $quantiteADistribuer = min($quantiteRestante, $besoinInfo['quantite_restante']);
                
                $this->creerDistribution($don['id'], $besoinInfo['besoin']['id'], $quantiteADistribuer);
                
                $quantiteRestante -= $quantiteADistribuer;
                $besoinsMap[$key]['quantite_restante'] -= $quantiteADistribuer;
                
                if ($besoinsMap[$key]['quantite_restante'] == 0) {
                    $this->marquerBesoinSatisfait($besoinInfo['besoin']['id']);
                }
                
                $distributions[] = [
                    'don_id' => $don['id'],
                    'element' => $don['element_libele'],
                    'ville' => $besoinInfo['besoin']['ville_libele'],
                    'quantite_distribuee' => $quantiteADistribuer,
                    'date_distribution' => date('Y-m-d H:i:s')
                ];
                
                if ($quantiteRestante == 0) {
                    break;
                }
            }
        }
        
        return $distributions;
    }

    private function peutDistribuer(array $don, array $besoinInfo, int $quantiteRestante): bool
    {
        return $besoinInfo['besoin']['idelement'] == $don['idelement'] && 
               $besoinInfo['quantite_restante'] > 0 && 
               $quantiteRestante > 0;
    }

    private function creerDistribution(int $donId, int $besoinId, int $quantite): void
    {
        $this->db->runQuery("
            INSERT INTO bn_distribution (id_don, id_besoin, quantite_distribuee, date_distribution)
            VALUES (?, ?, ?, ?)
        ", [$donId, $besoinId, $quantite, date('Y-m-d H:i:s')]);
    }

    private function marquerDonDistribue(int $donId): void
    {
        $this->db->runQuery("UPDATE bn_don SET distribue = 1, date_distribution = ? WHERE id = ?", [date('Y-m-d H:i:s'), $donId]);
    }

    private function marquerBesoinSatisfait(int $besoinId): void
    {
        $this->db->runQuery("UPDATE bn_besoin SET satisfait = 1 WHERE id = ?", [$besoinId]);
    }
}
