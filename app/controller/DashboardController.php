<?php

namespace app\controller;

use app\model\Stock;
use app\model\Distribution;
use app\model\Besoin;
use app\model\Recap;
use app\model\VueVilleRecap;
use flight\Engine;

class DashboardController
{
    private Engine $app;
    private Stock $stockModel;
    private Distribution $distributionModel;
    private Besoin $besoinModel;
    private Recap $recapModel;
    private VueVilleRecap $vueVilleRecap;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->stockModel = new Stock($app->db());
        $this->distributionModel = new Distribution($app->db());
        $this->besoinModel = new Besoin($app->db());
        $this->recapModel = new Recap($app->db());
        $this->vueVilleRecap = new VueVilleRecap($app->db());
    }

    /**
     * Page principale du dashboard
     */
    public function index(): void
    {
        // Récapitulatif global (besoins, dons, achats, distribué)
        $recapGlobal = $this->stockModel->getRecapGlobal();
        
        // Stock disponible
        $stockDisponible = $this->stockModel->getStockDisponible();
        $totalValeurStock = $this->stockModel->getTotalValeur();
        
        // Récap par type de besoin
        $stockParType = $this->stockModel->getRecapParType();
        
        // Argent disponible pour achats
        $argentDisponible = $this->stockModel->getArgentDisponible();
        
        // Besoins non satisfaits par ville
        $besoinsParVille = $this->besoinModel->getBesoinsParVille();
        
        // Dernières distributions
        $dernieresDistributions = $this->distributionModel->getAll();
        $dernieresDistributions = array_slice($dernieresDistributions, 0, 10); // Limiter à 10
        
        // Données récapitulatives groupées par ville (besoins vs distributions)
        $donneesParVille = $this->vueVilleRecap->getGroupedByVille();
        
        // Statistiques globales
        $statsGlobales = $this->vueVilleRecap->getStatsGlobales();
        
        // Récap par ville (besoins, distributions, manque)
        $recapParVille = $this->recapModel->getRecapParVille();

        $this->app->render('dashboard/dashboard', [
            'recapGlobal'               => $recapGlobal,
            'stockDisponible'           => $stockDisponible,
            'totalValeurStock'          => $totalValeurStock,
            'stockParType'              => $stockParType,
            'argentDisponible'          => $argentDisponible,
            'besoinsParVille'           => $besoinsParVille,
            'dernieresDistributions'    => $dernieresDistributions,
            'donneesParVille'           => $donneesParVille,
            'statsGlobales'             => $statsGlobales,
            'recapParVille'             => $recapParVille,
        ]);
    }

    /**
     * Dashboard filtré par région
     */
    public function parRegion(int $regionId): void
    {
        $donnees = $this->vueVilleRecap->getByRegion($regionId);
        $recapGlobal = $this->stockModel->getRecapGlobal();

        $this->app->render('dashboard/dashboard', [
            'donneesParVille'   => $donnees,
            'recapGlobal'       => $recapGlobal,
            'statsGlobales'     => [],
            'regionId'          => $regionId,
            'stockDisponible'   => [],
            'stockParType'      => [],
            'besoinsParVille'   => [],
            'dernieresDistributions' => [],
            'totalValeurStock'  => 0,
            'argentDisponible'  => 0,
        ]);
    }

    /**
     * Dashboard filtré par ville
     */
    public function parVille(int $villeId): void
    {
        $donnees = $this->vueVilleRecap->getByVille($villeId);
        $distributions = $this->distributionModel->getByVille($villeId);
        $recapGlobal = $this->stockModel->getRecapGlobal();

        $this->app->render('dashboard/dashboard', [
            'donneesParVille'   => $donnees,
            'recapGlobal'       => $recapGlobal,
            'statsGlobales'     => [],
            'villeId'           => $villeId,
            'distributionsVille' => $distributions,
            'stockDisponible'   => [],
            'stockParType'      => [],
            'besoinsParVille'   => [],
            'dernieresDistributions' => [],
            'totalValeurStock'  => 0,
            'argentDisponible'  => 0,
        ]);
    }
}
