<?php

namespace app\controller;

use app\model\Besoin;
use app\model\Distribution;
use app\model\Don;
use app\model\Element;
use app\model\Stock;
use flight\Engine;

class DonController
{
    private Engine $app;
    private Element $elementModel;
    private Don $donModel;
    private Besoin $besoinModel;
    private Stock $stockModel;
    private Distribution $distributionModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->elementModel = new Element($app->db());
        $this->donModel = new Don($app->db());
        $this->besoinModel = new Besoin($app->db());
        $this->stockModel = new Stock($app->db());
        $this->distributionModel = new Distribution($app->db());
    }

    /**
     * Affiche le formulaire de saisie des dons
     * Les dons vont directement dans le STOCK GLOBAL (pas de ville)
     */
    public function saisie(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $success = '';
        $error = '';

        // Messages flash
        if (!empty($_SESSION['don_success'])) {
            $success = $_SESSION['don_success'];
            unset($_SESSION['don_success']);
        }
        if (!empty($_SESSION['don_error'])) {
            $error = $_SESSION['don_error'];
            unset($_SESSION['don_error']);
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $idElement = (int)($_POST['element'] ?? 0);
                $quantite = (int)($_POST['quantite'] ?? 0);
                $date = $_POST['date'] ?? date('Y-m-d');
                $description = trim($_POST['description'] ?? '');

                if (empty($idElement) || empty($quantite) || empty($date)) {
                    throw new \Exception('Tous les champs obligatoires doivent être remplis');
                }

                if ($quantite <= 0) {
                    throw new \Exception('La quantité doit être supérieure à 0');
                }

                // Récupérer les infos de l'élément
                $elementInfo = $this->elementModel->getById($idElement);

                if (!$elementInfo) {
                    throw new \Exception('Élément invalide');
                }

                // Ajouter au panier en session (pour revue avant ajout au stock)
                if (!isset($_SESSION['panier_dons'])) {
                    $_SESSION['panier_dons'] = [];
                }

                $_SESSION['panier_dons'][] = [
                    'id_element'        => $idElement,
                    'element_libele'    => $elementInfo['libele'],
                    'element_pu'        => (float)$elementInfo['pu'],
                    'type_besoin'       => $elementInfo['type_besoin_libele'] ?? '',
                    'quantite'          => $quantite,
                    'date'              => $date,
                    'description'       => $description,
                ];

                $success = 'Don ajouté au panier ! Vous pouvez continuer ou valider pour ajouter au stock.';
                $_POST = [];
            } catch (\Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }

        $elements = $this->elementModel->getAll();
        $panierDons = $_SESSION['panier_dons'] ?? [];

        // Récupérer le stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();

        // Récap du stock
        $stockRecap = $this->stockModel->getRecapParType();

        $this->app->render('don/saisie', [
            'elements'          => $elements,
            'success'           => $success,
            'error'             => $error,
            'form'              => $_POST,
            'panierDons'        => $panierDons,
            'stockDisponible'   => $stockDisponible,
            'stockRecap'        => $stockRecap,
        ]);
    }

    /**
     * Supprime un don du panier temporaire
     */
    public function supprimerDuPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $index = (int)($_POST['index'] ?? -1);

        if (isset($_SESSION['panier_dons'][$index])) {
            array_splice($_SESSION['panier_dons'], $index, 1);
        }

        $this->app->redirect('/don/saisie');
    }

    /**
     * Vide le panier temporaire
     */
    public function viderPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['panier_dons'] = [];
        $this->app->redirect('/don/saisie');
    }

    /**
     * Valide les dons du panier et les ajoute au STOCK GLOBAL
     * (PAS de distribution aux villes - ça se fait dans la page simulation)
     */
    public function ajouterAuStock(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $panierDons = $_SESSION['panier_dons'] ?? [];

        if (empty($panierDons)) {
            $_SESSION['don_error'] = 'Le panier est vide, rien à ajouter au stock.';
            $this->app->redirect('/don/saisie');
            return;
        }

        try {
            $nbAjoutes = 0;

            foreach ($panierDons as $don) {
                // Insérer directement dans bn_don (va au stock global)
                $this->donModel->insert(
                    (int)$don['id_element'],
                    (int)$don['quantite'],
                    $don['date'],
                    $don['description']
                );
                $nbAjoutes++;
            }

            // Vider le panier après ajout au stock
            $_SESSION['panier_dons'] = [];

            $_SESSION['don_success'] = $nbAjoutes . ' don(s) ajouté(s) au stock avec succès ! Allez sur la page Distribution pour assigner aux villes.';
        } catch (\Exception $e) {
            $_SESSION['don_error'] = 'Erreur lors de l\'ajout au stock : ' . $e->getMessage();
        }

        $this->app->redirect('/don/saisie');
    }

    /**
     * Page de simulation/distribution : affiche le stock disponible et les besoins des villes
     * Permet de distribuer le stock aux villes selon les besoins FIFO
     */
    public function simulation(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $success = '';
        $error = '';

        // Messages flash
        if (!empty($_SESSION['simulation_success'])) {
            $success = $_SESSION['simulation_success'];
            unset($_SESSION['simulation_success']);
        }
        if (!empty($_SESSION['simulation_error'])) {
            $error = $_SESSION['simulation_error'];
            unset($_SESSION['simulation_error']);
        }

        // Stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();

        // Besoins non satisfaits par ville
        $besoinsParVille = $this->besoinModel->getBesoinsParVille();

        // Récap global
        $recapGlobal = $this->stockModel->getRecapGlobal();

        // Résultat de la simulation (si elle a été lancée)
        $resultatSimulation = $_SESSION['resultat_simulation'] ?? null;

        $this->app->render('don/simulation', [
            'stockDisponible'       => $stockDisponible,
            'besoinsParVille'       => $besoinsParVille,
            'recapGlobal'           => $recapGlobal,
            'resultatSimulation'    => $resultatSimulation,
            'success'               => $success,
            'error'                 => $error
        ]);
    }

    public function simuler(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier qu'il y a du stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();

        if (empty($stockDisponible)) {
            $_SESSION['simulation_error'] = 'Aucun stock disponible pour la distribution.';
            $this->app->redirect('/don/simulation');
            return;
        }

        $methode = $_POST['methode'] ?? 'fifo';

        try {
            switch ($methode) {
                case 'plus_petit_besoin':
                    $resultat = $this->calculerDistributionPlusPetitBesoin();
                    break;
                case 'proportionnelle':
                    $resultat = $this->calculerDistributionProportionnelle();
                    break;
                case 'fifo':
                default:
                    $resultat = $this->calculerDistributionFIFO();
                    $methode = 'fifo';
                    break;
            }
            $resultat['methode'] = $methode;
            $_SESSION['resultat_simulation'] = $resultat;
            $_SESSION['simulation_success'] = 'Simulation effectuée ! Vérifiez le résultat ci-dessous puis validez pour distribuer.';
        } catch (\Exception $e) {
            $_SESSION['simulation_error'] = 'Erreur lors de la simulation : ' . $e->getMessage();
        }

        $this->app->redirect('/don/simulation');
    }

    /**
     * Valider la distribution : exécuter réellement les assignations du stock aux villes
     */
    public function valider(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $resultatSimulation = $_SESSION['resultat_simulation'] ?? null;

        if (empty($resultatSimulation) || empty($resultatSimulation['distributions'])) {
            $_SESSION['simulation_error'] = 'Aucune simulation à valider. Veuillez d\'abord simuler.';
            $this->app->redirect('/don/simulation');
            return;
        }

        try {
            $nbDistribues = 0;
            $totalQuantite = 0;

            foreach ($resultatSimulation['distributions'] as $distribution) {
                // Créer l'enregistrement de distribution
                $this->distributionModel->insert(
                    (int)$distribution['idVille'],
                    (int)$distribution['idElement'],
                    (int)$distribution['quantite'],
                    'simulation',
                    (int)$distribution['besoin_id']
                );

                $nbDistribues++;
                $totalQuantite += (int)$distribution['quantite'];

                // Marquer le besoin comme satisfait si entièrement couvert
                if (!empty($distribution['besoin_satisfait'])) {
                    $this->besoinModel->marquerSatisfait((int)$distribution['besoin_id']);
                }
            }

            // Vider la simulation
            $_SESSION['resultat_simulation'] = null;

            $_SESSION['simulation_success'] = $totalQuantite . ' unité(s) distribuée(s) vers ' . $nbDistribues . ' besoin(s) avec succès !';
        } catch (\Exception $e) {
            $_SESSION['simulation_error'] = 'Erreur lors de la distribution : ' . $e->getMessage();
        }

        $this->app->redirect('/don/simulation');
    }

    /**
     * Distribution automatique (sans simulation)
     */
    public function distribuerAuto(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $resultat = $this->distributionModel->distribuerAuto();

            if (!empty($resultat['errors'])) {
                $_SESSION['simulation_error'] = implode(', ', $resultat['errors']);
            } else {
                $_SESSION['simulation_success'] = $resultat['summary']['total_quantite'] . ' unité(s) distribuée(s) automatiquement !';
            }
        } catch (\Exception $e) {
            $_SESSION['simulation_error'] = 'Erreur lors de la distribution automatique : ' . $e->getMessage();
        }

        $this->app->redirect('/don/simulation');
    }

    /**
     * Récupère les besoins non satisfaits pour un élément donné, triés FIFO (date ASC, id ASC)
     */
    private function getBesoinsNonSatisfaits(int $idElement, string $ordre = 'fifo'): array
    {
        $orderBy = match ($ordre) {
            'plus_petit_besoin' => 'ORDER BY (b.quantite - COALESCE((SELECT SUM(d.quantite) FROM bn_distribution d WHERE d.idVille = b.idVille AND d.idelement = b.idelement), 0)) ASC, b.id ASC',
            default => 'ORDER BY b.date ASC, b.id ASC',
        };

        return $this->app->db()->fetchAll("
            SELECT b.id, b.idelement, b.quantite, b.idVille, b.date,
                   v.libele AS ville_libele,
                   COALESCE((
                       SELECT SUM(d.quantite) 
                       FROM bn_distribution d 
                       WHERE d.idVille = b.idVille AND d.idelement = b.idelement
                   ), 0) AS deja_recu
            FROM bn_besoin b
            LEFT JOIN bn_ville v ON b.idVille = v.id
            WHERE b.idelement = ?
              AND (b.satisfait = 0 OR b.satisfait IS NULL)
            $orderBy
        ", [$idElement]);
    }

    /**
     * Calcule la distribution FIFO depuis le stock vers les besoins
     * Retourne un tableau avec les distributions prévues
     */
    private function calculerDistributionFIFO(): array
    {
        return $this->calculerDistributionParPriorite('fifo');
    }

    /**
     * Calcule la distribution par priorité au plus petit besoin
     * Les villes ayant les plus petits besoins restants sont servies en premier
     */
    private function calculerDistributionPlusPetitBesoin(): array
    {
        return $this->calculerDistributionParPriorite('plus_petit_besoin');
    }

    /**
     * Calcule la distribution par priorité (FIFO ou plus petit besoin)
     */
    private function calculerDistributionParPriorite(string $ordre): array
    {
        $distributions = [];
        $nonDistribues = [];
        $parVille = [];

        // Récupérer le stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();

        foreach ($stockDisponible as $stock) {
            $idElement = (int)$stock['idelement'];
            $quantiteStock = (int)$stock['stock_disponible'];
            $quantiteRestante = $quantiteStock;

            // Récupérer les besoins pour cet élément selon l'ordre choisi
            $besoins = $this->getBesoinsNonSatisfaits($idElement, $ordre);

            if (empty($besoins)) {
                $nonDistribues[] = [
                    'element_libele' => $stock['element_libele'],
                    'quantite' => $quantiteStock,
                    'raison' => 'Aucun besoin trouvé pour cet élément'
                ];
                continue;
            }

            foreach ($besoins as $besoin) {
                if ($quantiteRestante <= 0) {
                    break;
                }

                $quantiteBesoin = (int)$besoin['quantite'] - (int)$besoin['deja_recu'];
                
                if ($quantiteBesoin <= 0) {
                    continue;
                }

                $quantiteADonner = min($quantiteRestante, $quantiteBesoin);

                $distribution = [
                    'idVille'           => (int)$besoin['idVille'],
                    'ville_libele'      => $besoin['ville_libele'],
                    'idElement'         => $idElement,
                    'element_libele'    => $stock['element_libele'],
                    'type_besoin'       => $stock['type_besoin'],
                    'quantite'          => $quantiteADonner,
                    'prix_unitaire'     => (float)$stock['prix_unitaire'],
                    'montant'           => $quantiteADonner * (float)$stock['prix_unitaire'],
                    'besoin_id'         => (int)$besoin['id'],
                    'besoin_satisfait'  => ($quantiteADonner >= $quantiteBesoin)
                ];

                $distributions[] = $distribution;

                // Regrouper par ville
                $villeId = (int)$besoin['idVille'];
                if (!isset($parVille[$villeId])) {
                    $parVille[$villeId] = [
                        'ville_libele' => $besoin['ville_libele'],
                        'items' => [],
                        'total_quantite' => 0,
                        'total_montant' => 0
                    ];
                }
                $parVille[$villeId]['items'][] = $distribution;
                $parVille[$villeId]['total_quantite'] += $quantiteADonner;
                $parVille[$villeId]['total_montant'] += $distribution['montant'];

                $quantiteRestante -= $quantiteADonner;
            }

            // Stock excédentaire
            if ($quantiteRestante > 0) {
                $nonDistribues[] = [
                    'element_libele' => $stock['element_libele'],
                    'quantite' => $quantiteRestante,
                    'raison' => 'Stock excédentaire (pas assez de besoins)'
                ];
            }
        }

        return [
            'distributions'         => $distributions,
            'nonDistribues'         => $nonDistribues,
            'parVille'              => $parVille,
            'totalDistributions'    => count($distributions),
            'totalQuantite'         => array_sum(array_column($distributions, 'quantite')),
            'totalMontant'          => array_sum(array_column($distributions, 'montant'))
        ];
    }

    /**
     * Calcule la distribution proportionnelle depuis le stock vers les besoins
     * Chaque ville reçoit une part proportionnelle à son besoin restant, arrondie vers le bas
     */
    private function calculerDistributionProportionnelle(): array
    {
        $distributions = [];
        $nonDistribues = [];
        $parVille = [];

        // Récupérer le stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();

        foreach ($stockDisponible as $stock) {
            $idElement = (int)$stock['idelement'];
            $quantiteStock = (int)$stock['stock_disponible'];

            // Récupérer tous les besoins pour cet élément
            $besoins = $this->getBesoinsNonSatisfaits($idElement);

            if (empty($besoins)) {
                $nonDistribues[] = [
                    'element_libele' => $stock['element_libele'],
                    'quantite' => $quantiteStock,
                    'raison' => 'Aucun besoin trouvé pour cet élément'
                ];
                continue;
            }

            // Calculer le besoin restant pour chaque besoin
            $besoinsAvecRestant = [];
            $totalBesoinRestant = 0;

            foreach ($besoins as $besoin) {
                $besoinRestant = (int)$besoin['quantite'] - (int)$besoin['deja_recu'];
                if ($besoinRestant > 0) {
                    $besoin['besoin_restant'] = $besoinRestant;
                    $besoinsAvecRestant[] = $besoin;
                    $totalBesoinRestant += $besoinRestant;
                }
            }

            if (empty($besoinsAvecRestant) || $totalBesoinRestant <= 0) {
                $nonDistribues[] = [
                    'element_libele' => $stock['element_libele'],
                    'quantite' => $quantiteStock,
                    'raison' => 'Tous les besoins sont déjà satisfaits'
                ];
                continue;
            }

            $quantiteDistribuee = 0;

            foreach ($besoinsAvecRestant as $besoin) {
                // Part proportionnelle arrondie vers le bas
                $proportion = $besoin['besoin_restant'] / $totalBesoinRestant;
                $quantiteProportionnelle = (int)floor($quantiteStock * $proportion);

                // Ne pas dépasser le besoin restant
                $quantiteADonner = min($quantiteProportionnelle, $besoin['besoin_restant']);

                if ($quantiteADonner <= 0) {
                    continue;
                }

                $distribution = [
                    'idVille'           => (int)$besoin['idVille'],
                    'ville_libele'      => $besoin['ville_libele'],
                    'idElement'         => $idElement,
                    'element_libele'    => $stock['element_libele'],
                    'type_besoin'       => $stock['type_besoin'],
                    'quantite'          => $quantiteADonner,
                    'prix_unitaire'     => (float)$stock['prix_unitaire'],
                    'montant'           => $quantiteADonner * (float)$stock['prix_unitaire'],
                    'besoin_id'         => (int)$besoin['id'],
                    'besoin_satisfait'  => ($quantiteADonner >= $besoin['besoin_restant'])
                ];

                $distributions[] = $distribution;
                $quantiteDistribuee += $quantiteADonner;

                // Regrouper par ville
                $villeId = (int)$besoin['idVille'];
                if (!isset($parVille[$villeId])) {
                    $parVille[$villeId] = [
                        'ville_libele' => $besoin['ville_libele'],
                        'items' => [],
                        'total_quantite' => 0,
                        'total_montant' => 0
                    ];
                }
                $parVille[$villeId]['items'][] = $distribution;
                $parVille[$villeId]['total_quantite'] += $quantiteADonner;
                $parVille[$villeId]['total_montant'] += $distribution['montant'];
            }

            // Stock non distribué (reste après arrondis vers le bas)
            $quantiteRestante = $quantiteStock - $quantiteDistribuee;
            if ($quantiteRestante > 0) {
                $nonDistribues[] = [
                    'element_libele' => $stock['element_libele'],
                    'quantite' => $quantiteRestante,
                    'raison' => 'Reste après distribution proportionnelle (arrondis)'
                ];
            }
        }

        return [
            'distributions'         => $distributions,
            'nonDistribues'         => $nonDistribues,
            'parVille'              => $parVille,
            'totalDistributions'    => count($distributions),
            'totalQuantite'         => array_sum(array_column($distributions, 'quantite')),
            'totalMontant'          => array_sum(array_column($distributions, 'montant'))
        ];
    }
}
