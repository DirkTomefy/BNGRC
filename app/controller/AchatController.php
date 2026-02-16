<?php

namespace app\controller;

use app\model\Achat;
use app\model\Config;
use app\model\Ville;
use app\model\Element;
use app\model\Besoin;
use flight\Engine;

class AchatController
{
    private Engine $app;
    private Achat $achatModel;
    private Config $configModel;
    private Ville $villeModel;
    private Element $elementModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->achatModel = new Achat($app->db());
        $this->configModel = new Config($app->db());
        $this->villeModel = new Ville($app->db());
        $this->elementModel = new Element($app->db());
    }

    /**
     * Page de saisie des achats
     */
    public function saisie(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $success = '';
        $error = '';

        // Messages flash
        if (!empty($_SESSION['achat_success'])) {
            $success = $_SESSION['achat_success'];
            unset($_SESSION['achat_success']);
        }
        if (!empty($_SESSION['achat_error'])) {
            $error = $_SESSION['achat_error'];
            unset($_SESSION['achat_error']);
        }

        // Traitement POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $idBesoin = (int)($_POST['besoin'] ?? 0);
                $quantite = (int)($_POST['quantite'] ?? 0);
                $date = $_POST['date'] ?? date('Y-m-d');

                if (empty($idBesoin) || empty($quantite)) {
                    throw new \Exception('Veuillez sélectionner un besoin et une quantité');
                }

                if ($quantite <= 0) {
                    throw new \Exception('La quantité doit être supérieure à 0');
                }

                // Récupérer les infos du besoin
                $besoinModel = new Besoin($this->app->db());
                $besoin = $besoinModel->getById($idBesoin);

                if (!$besoin) {
                    throw new \Exception('Besoin non trouvé');
                }

                // Vérifier si un don existe déjà pour cet élément dans cette ville
                if ($this->achatModel->verifierDonExistant((int)$besoin['idelement'], (int)$besoin['idVille'])) {
                    throw new \Exception('Un don existe déjà pour cet élément dans cette ville. Utilisez le don existant au lieu d\'acheter.');
                }

                // Récupérer le taux de frais
                $tauxFrais = $this->configModel->getFraisAchatPourcent();

                // Récupérer le prix unitaire
                $element = $this->elementModel->getById((int)$besoin['idelement']);
                $prixUnitaire = (float)($element['pu'] ?? 0);

                // Insérer l'achat
                $this->achatModel->insert(
                    $idBesoin,
                    (int)$besoin['idVille'],
                    (int)$besoin['idelement'],
                    $quantite,
                    $prixUnitaire,
                    $tauxFrais,
                    $date . ' ' . date('H:i:s')
                );

                $_SESSION['achat_success'] = 'Achat enregistré avec succès !';
                $this->app->redirect('/achat/saisie');
                return;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $villes = $this->villeModel->getAll();
        $besoinsRestants = $this->achatModel->getBesoinsRestants();
        $tauxFrais = $this->configModel->getFraisAchatPourcent();

        $this->app->render('achat/saisie', [
            'villes' => $villes,
            'besoinsRestants' => $besoinsRestants,
            'tauxFrais' => $tauxFrais,
            'success' => $success,
            'error' => $error,
            'form' => $_POST ?? []
        ]);
    }

    /**
     * Liste des achats (filtrable par ville)
     */
    public function liste(?int $villeId = null): void
    {
        $villes = $this->villeModel->getAll();
        
        if ($villeId) {
            $achats = $this->achatModel->getByVille($villeId);
            $villeSelectionnee = $this->villeModel->getById($villeId);
            $totaux = $this->achatModel->getTotalAchatsByVille($villeId);
        } else {
            $achats = $this->achatModel->getAll();
            $villeSelectionnee = null;
            $totaux = $this->achatModel->getTotalAchats();
        }

        $tauxFrais = $this->configModel->getFraisAchatPourcent();

        $this->app->render('achat/liste', [
            'achats' => $achats,
            'villes' => $villes,
            'villeId' => $villeId,
            'villeSelectionnee' => $villeSelectionnee,
            'totaux' => $totaux,
            'tauxFrais' => $tauxFrais
        ]);
    }

    /**
     * API : Récupère les besoins restants (pour AJAX)
     */
    public function apiBesoinsRestants(?int $villeId = null): void
    {
        header('Content-Type: application/json');

        if ($villeId) {
            $besoins = $this->achatModel->getBesoinsRestantsByVille($villeId);
        } else {
            $besoins = $this->achatModel->getBesoinsRestants();
        }

        echo json_encode([
            'success' => true,
            'data' => $besoins,
            'tauxFrais' => $this->configModel->getFraisAchatPourcent()
        ]);
    }
}
