<?php

namespace app\controller;

use app\model\Besoin;
use app\model\Don;
use app\model\Element;
use flight\Engine;

class DonController
{
    private Engine $app;
    private Element $elementModel;
    private Don $donModel;
    private Besoin $besoinModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->elementModel = new Element($app->db());
        $this->donModel = new Don($app->db());
        $this->besoinModel = new Besoin($app->db());
    }

    /**
     * Affiche le formulaire de saisie + traite l'ajout au panier temporaire (session)
     * L'utilisateur saisit uniquement l'élément, quantité, date et description.
     * Pas de sélection de ville : la répartition se fait en FIFO lors de la distribution.
     */
    public function saisie(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $success = '';
        $error = '';

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

                // Récupérer les libellés pour l'affichage dans le tableau temporaire
                $elementInfo = $this->elementModel->getById($idElement);

                if (!$elementInfo) {
                    throw new \Exception('Élément invalide');
                }

                // Ajouter au panier en session
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

                $success = 'Don ajouté au panier ! Vous pouvez continuer à ajouter ou distribuer.';
                $_POST = [];
            } catch (\Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }

        $elements = $this->elementModel->getAll();
        $panierDons = $_SESSION['panier_dons'] ?? [];

        // Calculer la prévisualisation de répartition FIFO pour l'affichage
        $previsualisation = [];
        if (!empty($panierDons)) {
            $previsualisation = $this->calculerDistributionFIFO($panierDons);
        }

        $this->app->render('don/saisie', [
            'elements'          => $elements,
            'success'           => $success,
            'error'             => $error,
            'form'              => $_POST,
            'panierDons'        => $panierDons,
            'previsualisation'  => $previsualisation,
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
     * Distribue tous les dons du panier en FIFO :
     * Pour chaque don du panier, on cherche le besoin le plus ancien (par date ASC, id ASC)
     * qui demande le même élément et qui n'est pas encore satisfait.
     * Le don est inséré dans bn_don avec l'idVille du besoin trouvé.
     * Si aucun besoin n'existe pour cet élément, le don est ignoré (ou signalé).
     */
    public function distribuer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $panierDons = $_SESSION['panier_dons'] ?? [];

        if (empty($panierDons)) {
            $_SESSION['don_error'] = 'Le panier est vide, rien à distribuer.';
            $this->app->redirect('/don/saisie');
            return;
        }

        try {
            $nbDistribues = 0;
            $nonDistribues = [];

            foreach ($panierDons as $don) {
                $quantiteRestante = (int)$don['quantite'];
                $idElement = (int)$don['id_element'];

                // Récupérer les besoins non satisfaits pour cet élément, triés FIFO (date ASC, id ASC)
                $besoins = $this->getBesoinsNonSatisfaits($idElement);

                if (empty($besoins)) {
                    $nonDistribues[] = $don['element_libele'] . ' (qté: ' . $don['quantite'] . ')';
                    continue;
                }

                // Distribuer en FIFO sur les besoins les plus anciens
                foreach ($besoins as $besoin) {
                    if ($quantiteRestante <= 0) {
                        break;
                    }

                    $quantiteBesoin = (int)$besoin['quantite'];
                    $quantiteADonner = min($quantiteRestante, $quantiteBesoin);

                    // Insérer le don dans bn_don avec la ville du besoin
                    $this->donModel->insertDon(
                        (int)$besoin['idVille'],
                        $idElement,
                        $quantiteADonner,
                        $don['date'],
                        $don['description']
                    );

                    $quantiteRestante -= $quantiteADonner;
                    $nbDistribues++;
                }

                // S'il reste de la quantité non distribuée, on le signale
                if ($quantiteRestante > 0) {
                    $nonDistribues[] = $don['element_libele'] . ' (reste: ' . $quantiteRestante . ')';
                }
            }

            // Vider le panier après distribution
            $_SESSION['panier_dons'] = [];

            $message = $nbDistribues . ' don(s) distribué(s) avec succès aux villes par FIFO !';
            if (!empty($nonDistribues)) {
                $message .= ' | Non distribués (aucun besoin trouvé) : ' . implode(', ', $nonDistribues);
            }
            $_SESSION['don_success'] = $message;
        } catch (\Exception $e) {
            $_SESSION['don_error'] = 'Erreur lors de la distribution : ' . $e->getMessage();
        }

        $this->app->redirect('/don/saisie');
    }

    /**
     * Récupère les besoins non satisfaits pour un élément donné, triés FIFO (date ASC, id ASC)
     */
    private function getBesoinsNonSatisfaits(int $idElement): array
    {
        return $this->app->db()->fetchAll("
            SELECT b.id, b.idelement, b.quantite, b.idVille, b.date,
                   v.libele as ville_libele
            FROM bn_besoin b
            LEFT JOIN bn_ville v ON b.idVille = v.id
            WHERE b.idelement = ?
            ORDER BY b.date ASC, b.id ASC
        ", [$idElement]);
    }

    /**
     * Page de simulation : affiche le résultat prévu de la distribution FIFO
     * sans modifier la BDD
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

        $elements = $this->elementModel->getAll();
        $panierDons = $_SESSION['panier_dons'] ?? [];
        $resultatSimulation = $_SESSION['resultat_simulation'] ?? null;

        $this->app->render('don/simulation', [
            'elements' => $elements,
            'panierDons' => $panierDons,
            'resultatSimulation' => $resultatSimulation,
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * Simuler la distribution FIFO (sans modifier la BDD)
     */
    public function simuler(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $panierDons = $_SESSION['panier_dons'] ?? [];

        if (empty($panierDons)) {
            $_SESSION['simulation_error'] = 'Le panier est vide, rien à simuler.';
            $this->app->redirect('/don/simulation');
            return;
        }

        try {
            $resultat = $this->calculerDistributionFIFO($panierDons);
            $_SESSION['resultat_simulation'] = $resultat;
            $_SESSION['simulation_success'] = 'Simulation effectuée ! Vérifiez le résultat ci-dessous.';
        } catch (\Exception $e) {
            $_SESSION['simulation_error'] = 'Erreur lors de la simulation : ' . $e->getMessage();
        }

        $this->app->redirect('/don/simulation');
    }

    /**
     * Valider la distribution : exécuter réellement les insertions en BDD
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
            $nbInseres = 0;

            foreach ($resultatSimulation['distributions'] as $distribution) {
                $this->donModel->insertDon(
                    (int)$distribution['idVille'],
                    (int)$distribution['idElement'],
                    (int)$distribution['quantite'],
                    $distribution['date'],
                    $distribution['description']
                );
                $nbInseres++;
            }

            // Vider le panier et la simulation
            $_SESSION['panier_dons'] = [];
            $_SESSION['resultat_simulation'] = null;

            $_SESSION['don_success'] = $nbInseres . ' don(s) distribué(s) avec succès aux villes par FIFO !';
            $this->app->redirect('/don/saisie');
        } catch (\Exception $e) {
            $_SESSION['simulation_error'] = 'Erreur lors de la validation : ' . $e->getMessage();
            $this->app->redirect('/don/simulation');
        }
    }

    /**
     * Calcule la distribution FIFO sans modifier la BDD
     * Retourne un tableau avec les distributions prévues et les non-distribués
     */
    private function calculerDistributionFIFO(array $panierDons): array
    {
        $distributions = [];
        $nonDistribues = [];
        $parVille = [];

        // Récupérer tous les besoins non satisfaits (copie pour simulation)
        $besoinsDisponibles = [];

        foreach ($panierDons as $don) {
            $idElement = (int)$don['id_element'];
            $quantiteRestante = (int)$don['quantite'];

            // Récupérer les besoins pour cet élément
            $besoins = $this->getBesoinsNonSatisfaits($idElement);

            if (empty($besoins)) {
                $nonDistribues[] = [
                    'element_libele' => $don['element_libele'],
                    'quantite' => $don['quantite'],
                    'raison' => 'Aucun besoin trouvé pour cet élément'
                ];
                continue;
            }

            foreach ($besoins as $besoin) {
                if ($quantiteRestante <= 0) {
                    break;
                }

                $quantiteBesoin = (int)$besoin['quantite'];
                $quantiteADonner = min($quantiteRestante, $quantiteBesoin);

                $distribution = [
                    'idVille' => (int)$besoin['idVille'],
                    'ville_libele' => $besoin['ville_libele'],
                    'idElement' => $idElement,
                    'element_libele' => $don['element_libele'],
                    'quantite' => $quantiteADonner,
                    'date' => $don['date'],
                    'description' => $don['description'],
                    'element_pu' => $don['element_pu'],
                    'montant' => $quantiteADonner * $don['element_pu']
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

            if ($quantiteRestante > 0) {
                $nonDistribues[] = [
                    'element_libele' => $don['element_libele'],
                    'quantite' => $quantiteRestante,
                    'raison' => 'Quantité excédentaire (pas assez de besoins)'
                ];
            }
        }

        return [
            'distributions' => $distributions,
            'nonDistribues' => $nonDistribues,
            'parVille' => $parVille,
            'totalDistributions' => count($distributions),
            'totalQuantite' => array_sum(array_column($distributions, 'quantite')),
            'totalMontant' => array_sum(array_column($distributions, 'montant'))
        ];
    }
}
