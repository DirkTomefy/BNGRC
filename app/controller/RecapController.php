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

        // Adapter les données pour la vue
        $recap = [
            'besoins_totaux' => $recapComplet['besoins_totaux']['montant_total'] ?? 0,
            'besoins_satisfaits' => $recapComplet['besoins_satisfaits']['montant_total'] ?? 0,
            'besoins_restants' => $recapComplet['besoins_restants']['montant_total'] ?? 0,
            'total_dons' => $recapComplet['dons_totaux']['montant_total'] ?? 0,
            'total_achats' => $recapComplet['achats_totaux']['montant_ttc'] ?? 0
        ];

        $this->app->render('recap/index', [
            'recap' => $recap,
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

        // Adapter les données pour l'API
        $recap = [
            'besoins_totaux' => (int)($recapComplet['besoins_totaux']['montant_total'] ?? 0),
            'besoins_satisfaits' => (int)($recapComplet['besoins_satisfaits']['montant_total'] ?? 0),
            'besoins_restants' => (int)($recapComplet['besoins_restants']['montant_total'] ?? 0),
            'total_dons' => (int)($recapComplet['dons_totaux']['montant_total'] ?? 0),
            'total_achats' => (int)($recapComplet['achats_totaux']['montant_ttc'] ?? 0),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        echo json_encode($recap);
    }
}
