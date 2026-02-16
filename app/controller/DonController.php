<?php

namespace app\controller;

use app\model\Don;
use app\model\Element;
use app\model\Ville;
use flight\Engine;

class DonController
{
    private Engine $app;
    private Ville $villeModel;
    private Element $elementModel;
    private Don $donModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->villeModel = new Ville($app->db());
        $this->elementModel = new Element($app->db());
        $this->donModel = new Don($app->db());
    }

    public function saisie(): void
    {
        $success = '';
        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $idVille = (int)($_POST['ville'] ?? 0);
                $idElement = (int)($_POST['element'] ?? 0);
                $quantite = (int)($_POST['quantite'] ?? 0);

                if (empty($idVille) || empty($idElement) || empty($quantite)) {
                    throw new \Exception('Tous les champs sont obligatoires');
                }

                if ($quantite <= 0) {
                    throw new \Exception('La quantité doit être supérieure à 0');
                }

                $this->donModel->insertDon($idVille, $idElement, $quantite);
                $success = 'Don enregistré avec succès !';

                $_POST = [];
            } catch (\Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }

        $villes = $this->villeModel->getAll();
        $elements = $this->elementModel->getAll();

        $this->app->render('don/saisie', [
            'villes' => $villes,
            'elements' => $elements,
            'success' => $success,
            'error' => $error,
            'form' => $_POST,
        ]);
    }
}
