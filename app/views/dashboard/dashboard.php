<?php
/**
 * Dashboard - Gestion des besoins et dons
 * 
 * Variables disponibles depuis le contrôleur :
 * @var array $donnees          Données regroupées par ville (besoins + dons)
 * @var array $statsBesoins     Statistiques des besoins par ville
 * @var array $statsDons        Statistiques des dons par ville  
 * @var array $statsRegionDons  Statistiques des dons par région
 */

// Fonction pour obtenir la classe de badge selon le type
function getBadgeClass($type) {
    $types = [
        'Alimentaire' => 'bg-success',
        'Nourriture'  => 'bg-success',
        'Urgence'     => 'bg-danger',
        'Santé'       => 'bg-info',
        'Hygiène'     => 'bg-purple',
        'Habillement' => 'bg-warning text-dark',
        'Logistique'  => 'bg-primary',
    ];
    
    $typeClean = str_replace(['é', 'è', 'ê'], ['e', 'e', 'e'], $type ?? '');
    
    foreach ($types as $key => $class) {
        if (stripos($typeClean, str_replace(['é', 'è', 'ê'], ['e', 'e', 'e'], $key)) !== false) {
            return $class;
        }
    }
    return 'bg-secondary';
}

$pageTitle = 'Gestion des besoins et dons - Madagascar';
$currentPage = 'dashboard';
$pageCss = ['assets/css/dashboard/dasboard.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary header-title">
                    <i class="bi bi-hand-thumbs-up-fill"></i> Gestion des besoins et dons
                </h1>
                <p class="lead text-secondary">Madagascar - Suivi en temps réel des aides humanitaires</p>
            </div>
        </div>
    </div>

    <?php
    // Calcul des statistiques globales à partir des données du contrôleur
    $totalVilles = count($donnees);
    $totalBesoins = 0;
    $totalDons = 0;
    $totalValeurBesoins = 0;
    $totalValeurDons = 0;
    
    foreach ($donnees as $data) {
        foreach ($data['besoins'] as $besoin) {
            $totalBesoins += $besoin['quantite'];
            $totalValeurBesoins += $besoin['quantite'] * $besoin['prix_unitaire'];
        }
        foreach ($data['dons'] as $don) {
            $totalDons += $don['quantite'];
        }
    }
    $tauxCouverture = $totalBesoins > 0 ? round(($totalDons / $totalBesoins) * 100) : 0;
    ?>

    <!-- Statistiques globales -->
    <div class="container fadeInUp">
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-building fs-1 text-primary"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= $totalVilles ?></h2>
                        <p class="text-secondary mb-0">Villes concernées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= number_format($totalBesoins, 0, ',', ' ') ?></h2>
                        <p class="text-secondary mb-0">Besoins totaux</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-gift fs-1 text-success"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= number_format($totalDons, 0, ',', ' ') ?></h2>
                        <p class="text-secondary mb-0">Dons reçus</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-graph-up fs-1 text-info"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= $tauxCouverture ?>%</h2>
                        <p class="text-secondary mb-0">Taux de couverture</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes des villes -->
        <div class="row g-4">
            <?php foreach ($donnees as $index => $data): ?>
                <?php
                // Calcul des totaux par ville
                $totalBesoinsVille = 0;
                $totalValeurBesoinsVille = 0;
                foreach ($data['besoins'] as $besoin) {
                    $totalBesoinsVille += $besoin['quantite'];
                    $totalValeurBesoinsVille += $besoin['quantite'] * $besoin['prix_unitaire'];
                }
                
                $totalDonsVille = 0;
                foreach ($data['dons'] as $don) {
                    $totalDonsVille += $don['quantite'];
                }
                
                $progressionVille = $totalBesoinsVille > 0 ? ($totalDonsVille / $totalBesoinsVille) * 100 : 0;
                $besoinRestant = $totalBesoinsVille - $totalDonsVille;
                ?>
                
                <div class="col-12">
                    <div class="card city-card">
                        <!-- En-tête de la carte -->
                        <div class="card-header-gradient d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h3 mb-1">
                                    <i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($data['ville']) ?>
                                </h2>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-tag me-1"></i><?= htmlspecialchars($data['region']) ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="display-6 fw-bold"><?= round($progressionVille) ?>%</div>
                                <small>de couverture</small>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Barre de progression -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-pie-chart me-2"></i>Progression des dons</span>
                                    <span class="fw-bold"><?= number_format($totalDonsVille, 0, ',', ' ') ?> / <?= number_format($totalBesoinsVille, 0, ',', ' ') ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?= $progressionVille ?>%;"></div>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <!-- Besoins -->
                                <div class="col-lg-6">
                                    <div class="section-title text-danger">
                                        <i class="bi bi-exclamation-circle-fill me-2"></i>Besoins urgents
                                        <span class="badge bg-danger rounded-pill float-end"><?= count($data['besoins']) ?></span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-custom">
                                            <thead>
                                                <tr>
                                                    <th>Élément</th>
                                                    <th class="text-end">Quantité</th>
                                                    <th class="text-end">Prix unit.</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['besoins'] as $besoin): ?>
                                                    <tr>
                                                        <td class="fw-bold"><?= htmlspecialchars($besoin['element']) ?></td>
                                                        <td class="text-end"><?= number_format($besoin['quantite'], 0, ',', ' ') ?></td>
                                                        <td class="text-end <?= $besoin['prix_unitaire'] == 0 ? 'price-free' : 'price-normal' ?>">
                                                            <?= $besoin['prix_unitaire'] == 0 ? 'Gratuit' : number_format($besoin['prix_unitaire'], 0, ',', ' ') . ' Ar' ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= getBadgeClass($besoin['type_besoin']) ?> badge-custom">
                                                                <?= htmlspecialchars($besoin['type_besoin']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th>Total</th>
                                                    <th class="text-end"><?= number_format($totalBesoinsVille, 0, ',', ' ') ?></th>
                                                    <th class="text-end"><?= number_format($totalValeurBesoinsVille, 0, ',', ' ') ?> Ar</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Dons -->
                                <div class="col-lg-6">
                                    <div class="section-title text-success">
                                        <i class="bi bi-gift-fill me-2"></i>Dons reçus
                                        <span class="badge bg-success rounded-pill float-end"><?= count($data['dons']) ?></span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-custom">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-end">Quantité</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['dons'] as $don): ?>
                                                    <tr>
                                                        <td class="fw-bold"><?= htmlspecialchars($don['description']) ?></td>
                                                        <td class="text-end"><?= number_format($don['quantite'], 0, ',', ' ') ?></td>
                                                        <td>
                                                            <small class="text-muted"><?= date('d/m/Y', strtotime($don['date'])) ?></small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th>Total</th>
                                                    <th class="text-end"><?= number_format($totalDonsVille, 0, ',', ' ') ?></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pied de carte avec résumé -->
                            <div class="total-footer p-3 mt-4 rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Besoins</small>
                                                <div class="fw-bold h5 mb-0"><?= number_format($totalBesoinsVille, 0, ',', ' ') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-gift-fill text-success fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Dons</small>
                                                <div class="fw-bold h5 mb-0"><?= number_format($totalDonsVille, 0, ',', ' ') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-hourglass-split text-warning fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Reste à fournir</small>
                                                <div class="fw-bold h5 mb-0 <?= $besoinRestant > 0 ? 'text-danger' : 'text-success' ?>">
                                                    <?= number_format($besoinRestant, 0, ',', ' ') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>