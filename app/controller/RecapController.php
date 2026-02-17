<?php

namespace app\controller;

use app\model\Recap;
use app\model\Config;
use flight\Engine;

class RecapController
{
    private Engine $app;
    private Recap $recapModel;
    private Config $configModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->recapModel = new Recap($app->db());
        $this->configModel = new Config($app->db());
    }

    /**
     * Page de récapitulation
     */
    public function index(): void
    {
        $recapComplet = $this->recapModel->getRecapComplet();
        $tauxFrais = $this->configModel->getFraisAchatPourcent();

        $this->app->render('recap/index', [
            'recap' => $recapComplet,
            'tauxFrais' => $tauxFrais
        ]);
    }

    /**
     * API : Récupère le récapitulatif en JSON (pour AJAX)
     */
    public function apiRecap(): void
    {
        header('Content-Type: application/json');

        $recapComplet = $this->recapModel->getRecapComplet();

        echo json_encode($recapComplet);
    }
}
