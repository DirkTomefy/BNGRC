<?php

namespace app\controller;

use app\model\Achat;
use app\model\Element;
use app\model\Stock;
use flight\Engine;

class AchatController
{
    private Engine $app;
    private Achat $achatModel;
    private Element $elementModel;
    private Stock $stockModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->achatModel = new Achat($app->db());
        $this->elementModel = new Element($app->db());
        $this->stockModel = new Stock($app->db());
    }

    /**
     * Page de saisie des achats
     * L'argent pour les achats provient des dons en argent
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

        // Argent disponible pour les achats
        $argentDisponible = $this->stockModel->getArgentDisponible();

        // Traitement POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $idElement = (int)($_POST['element'] ?? 0);
                $quantite = (int)($_POST['quantite'] ?? 0);
                $prixUnitaire = (float)($_POST['prix_unitaire'] ?? 0);
                $date = $_POST['date'] ?? date('Y-m-d');

                if (empty($idElement) || empty($quantite)) {
                    throw new \Exception('Veuillez sélectionner un élément et une quantité');
                }

                if ($quantite <= 0) {
                    throw new \Exception('La quantité doit être supérieure à 0');
                }

                if ($prixUnitaire <= 0) {
                    throw new \Exception('Le prix unitaire doit être supérieur à 0');
                }

                $montantTotal = $quantite * $prixUnitaire;

                // Vérifier si on a assez d'argent
                if ($montantTotal > $argentDisponible) {
                    throw new \Exception("Fonds insuffisants. Disponible: " . number_format($argentDisponible, 2) . " Ar, Requis: " . number_format($montantTotal, 2) . " Ar");
                }

                // Récupérer les infos de l'élément
                $elementInfo = $this->elementModel->getById($idElement);
                if (!$elementInfo) {
                    throw new \Exception('Élément invalide');
                }

                // Ajouter au panier en session
                if (!isset($_SESSION['panier_achats'])) {
                    $_SESSION['panier_achats'] = [];
                }

                $_SESSION['panier_achats'][] = [
                    'id_element'        => $idElement,
                    'element_libele'    => $elementInfo['libele'],
                    'type_besoin'       => $elementInfo['type_besoin_libele'] ?? '',
                    'quantite'          => $quantite,
                    'prix_unitaire'     => $prixUnitaire,
                    'montant'           => $montantTotal,
                    'date'              => $date,
                ];

                $success = 'Achat ajouté au panier ! Vous pouvez continuer ou valider.';
                $_POST = [];
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Récupérer les éléments (sans argent, on n'achète pas de l'argent)
        $elements = $this->elementModel->getAllSansArgent();
        $panierAchats = $_SESSION['panier_achats'] ?? [];

        // Total du panier
        $totalPanier = array_sum(array_column($panierAchats, 'montant'));

        $this->app->render('achat/saisie', [
            'elements'          => $elements,
            'argentDisponible'  => $argentDisponible,
            'panierAchats'      => $panierAchats,
            'totalPanier'       => $totalPanier,
            'success'           => $success,
            'error'             => $error,
            'form'              => $_POST ?? []
        ]);
    }

    /**
     * Supprime un achat du panier
     */
    public function supprimerDuPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $index = (int)($_POST['index'] ?? -1);

        if (isset($_SESSION['panier_achats'][$index])) {
            array_splice($_SESSION['panier_achats'], $index, 1);
        }

        $this->app->redirect('/achat/saisie');
    }

    /**
     * Vide le panier
     */
    public function viderPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['panier_achats'] = [];
        $this->app->redirect('/achat/saisie');
    }

    /**
     * Valide les achats du panier et les ajoute au stock
     */
    public function validerAchats(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $panierAchats = $_SESSION['panier_achats'] ?? [];

        if (empty($panierAchats)) {
            $_SESSION['achat_error'] = 'Le panier est vide.';
            $this->app->redirect('/achat/saisie');
            return;
        }

        $totalPanier = array_sum(array_column($panierAchats, 'montant'));
        $argentDisponible = $this->stockModel->getArgentDisponible();

        if ($totalPanier > $argentDisponible) {
            $_SESSION['achat_error'] = "Fonds insuffisants. Disponible: " . number_format($argentDisponible, 2) . " Ar";
            $this->app->redirect('/achat/saisie');
            return;
        }

        try {
            $nbAjoutes = 0;

            foreach ($panierAchats as $achat) {
                $this->achatModel->insert(
                    (int)$achat['id_element'],
                    (int)$achat['quantite'],
                    (float)$achat['prix_unitaire'],
                    $achat['date']
                );
                $nbAjoutes++;
            }

            // Vider le panier
            $_SESSION['panier_achats'] = [];

            $_SESSION['achat_success'] = $nbAjoutes . ' achat(s) enregistré(s) au stock ! Montant total: ' . number_format($totalPanier, 2) . ' Ar';
        } catch (\Exception $e) {
            $_SESSION['achat_error'] = 'Erreur: ' . $e->getMessage();
        }

        $this->app->redirect('/achat/saisie');
    }

    /**
     * Liste des achats
     */
    public function liste(): void
    {
        $achats = $this->achatModel->getAll();
        $totaux = $this->achatModel->getTotal();
        $argentDisponible = $this->stockModel->getArgentDisponible();

        $this->app->render('achat/liste', [
            'achats'            => $achats,
            'totaux'            => $totaux,
            'argentDisponible'  => $argentDisponible
        ]);
    }
}
