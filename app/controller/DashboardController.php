<?php

namespace app\controller;

use app\model\VueVilleBesoin;
use app\model\VueVilleDons;
use flight\Engine;

class DashboardController
{
    private Engine $app;
    private VueVilleBesoin $vueVilleBesoin;
    private VueVilleDons $vueVilleDons;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->vueVilleBesoin = new VueVilleBesoin($app->db());
        $this->vueVilleDons = new VueVilleDons($app->db());
    }

    /**
     * Page principale du dashboard
     */
    public function index(): void
    {
        // Récupérer les besoins par ville
        $besoinsParVille = $this->vueVilleBesoin->getAllVilleBesoin();
        // Récupérer les dons par ville
        $donsParVille = $this->vueVilleDons->getAll();

        // Regrouper les données par ville
        $donnees = $this->regrouperParVille($besoinsParVille, $donsParVille);

        // Statistiques globales des besoins
        $statsBesoins = $this->vueVilleBesoin->getBesoinVille();
        // Statistiques globales des dons
        $statsDons = $this->vueVilleDons->getDonVille();
        // Statistiques par région
        $statsRegionDons = $this->vueVilleDons->getStatsByRegion();

        $this->app->render('dashboard/dashboard', [
            'donnees'         => $donnees,
            'statsBesoins'    => $statsBesoins,
            'statsDons'       => $statsDons,
            'statsRegionDons' => $statsRegionDons,
        ]);
    }

    /**
     * Dashboard filtré par région
     */
    public function parRegion(int $regionId): void
    {
        $besoinsParVille = $this->vueVilleBesoin->getByRegion($regionId);
        $donsParVille = $this->vueVilleDons->getByRegion($regionId);

        $donnees = $this->regrouperParVille($besoinsParVille, $donsParVille);

        $statsDonsRegion = $this->vueVilleDons->getDonVilleByRegion($regionId);

        $this->app->render('dashboard/dashboard', [
            'donnees'         => $donnees,
            'statsBesoins'    => [],
            'statsDons'       => $statsDonsRegion,
            'statsRegionDons' => [],
            'regionId'        => $regionId,
        ]);
    }

    /**
     * Dashboard filtré par ville
     */
    public function parVille(int $villeId): void
    {
        $besoinsParVille = $this->vueVilleBesoin->getByVille($villeId);
        $donsParVille = $this->vueVilleDons->getByVille($villeId);

        $donnees = $this->regrouperParVille($besoinsParVille, $donsParVille);

        $this->app->render('dashboard/dashboard', [
            'donnees'         => $donnees,
            'statsBesoins'    => [],
            'statsDons'       => [],
            'statsRegionDons' => [],
        ]);
    }

    /**
     * Regroupe les besoins et dons par ville pour l'affichage
     */
    private function regrouperParVille(array $besoins, array $dons): array
    {
        $villes = [];

        // Regrouper les besoins par ville
        foreach ($besoins as $besoin) {
            $villeId = $besoin['ville_id'];
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'ville_id'      => $villeId,
                    'ville'         => $besoin['ville_libele'],
                    'region_id'     => $besoin['region_id'],
                    'region'        => $besoin['region_libele'],
                    'besoins'       => [],
                    'dons'          => [],
                ];
            }
            if (!empty($besoin['besoin_id'])) {
                $villes[$villeId]['besoins'][] = [
                    'besoin_id'         => $besoin['besoin_id'],
                    'element'           => $besoin['element_libele'],
                    'quantite'          => (int)$besoin['quantite'],
                    'prix_unitaire'     => (float)$besoin['element_pu'],
                    'type_besoin'       => $besoin['type_besoin_libele'],
                    'montant_total'     => (float)$besoin['montant_total'],
                    'date'              => $besoin['besoin_date'],
                ];
            }
        }

        // Regrouper les dons par ville
        foreach ($dons as $don) {
            $villeId = $don['ville_id'];
            if (!isset($villes[$villeId])) {
                $villes[$villeId] = [
                    'ville_id'      => $villeId,
                    'ville'         => $don['ville_libele'],
                    'region_id'     => $don['region_id'],
                    'region'        => $don['region_libele'],
                    'besoins'       => [],
                    'dons'          => [],
                ];
            }
            if (!empty($don['don_id'])) {
                $villes[$villeId]['dons'][] = [
                    'don_id'        => $don['don_id'],
                    'description'   => $don['description'] ?? '',
                    'quantite'      => (int)$don['don_quantite'],
                    'date'          => $don['don_date'],
                ];
            }
        }

        return array_values($villes);
    }
}
