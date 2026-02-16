<?php

namespace app\controller;

use app\model\VueVilleRecap;
use flight\Engine;

class DashboardController
{
    private Engine $app;
    private VueVilleRecap $vueVilleRecap;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->vueVilleRecap = new VueVilleRecap($app->db());
    }

    /**
     * Page principale du dashboard
     */
    public function index(): void
    {
        // Récupérer les données récapitulatives groupées par ville
        $donnees = $this->vueVilleRecap->getGroupedByVille();
        
        // Statistiques globales
        $statsGlobales = $this->vueVilleRecap->getStatsGlobales();

        $this->app->render('dashboard/dashboard', [
            'donnees'       => $donnees,
            'statsGlobales' => $statsGlobales,
        ]);
    }

    /**
     * Dashboard filtré par région
     */
    public function parRegion(int $regionId): void
    {
        $donnees = $this->vueVilleRecap->getByRegion($regionId);

        $this->app->render('dashboard/dashboard', [
            'donnees'       => $donnees,
            'statsGlobales' => [],
            'regionId'      => $regionId,
        ]);
    }

    /**
     * Dashboard filtré par ville
     */
    public function parVille(int $villeId): void
    {
        $donnees = $this->vueVilleRecap->getByVille($villeId);

        $this->app->render('dashboard/dashboard', [
            'donnees'       => $donnees,
            'statsGlobales' => [],
        ]);
    }
}
