<?php
/**
 * Dashboard - Gestion des besoins et dons avec détails fournis/restants
 * 
 * Variables disponibles depuis le contrôleur :
 * @var array $donnees          Données regroupées par ville avec éléments détaillés
 * @var array $statsGlobales    Statistiques globales (totaux)
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

// Fonction pour obtenir la classe de couleur selon le taux de couverture
function getCouvertureClass($pourcentage) {
    if ($pourcentage >= 100) return 'bg-success';
    if ($pourcentage >= 75) return 'bg-info';
    if ($pourcentage >= 50) return 'bg-warning';
    return 'bg-danger';
}

$pageTitle = 'Gestion des besoins et dons - Madagascar';
$currentPage = 'dashboard';
$pageCss = ['/assets/css/dashboard/dasboard.css'];
include __DIR__ . '/../layouts/header.php';

// Initialisation des variables
$statsGlobales = $statsGlobales ?? [];
$donnees = $donnees ?? [];
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
    // Calcul du taux de couverture global
    $totalBesoins = (int)($statsGlobales['total_besoins'] ?? 0);
    $totalDons = (int)($statsGlobales['total_dons'] ?? 0);
    $totalRestants = (int)($statsGlobales['total_restants'] ?? 0);
    $totalVilles = (int)($statsGlobales['total_villes'] ?? count($donnees));
    $montantTotalBesoins = (float)($statsGlobales['montant_total_besoins'] ?? 0);
    $montantTotalRestants = (float)($statsGlobales['montant_total_restants'] ?? 0);
    $tauxCouverture = $totalBesoins > 0 ? round(($totalDons / $totalBesoins) * 100) : 0;
    ?>

    <!-- Statistiques globales -->
    <div class="container fadeInUp">
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-building fs-1 text-primary"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= $totalVilles ?></h2>
                        <p class="text-secondary mb-0">Villes concernées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= number_format($totalBesoins, 0, ',', ' ') ?></h2>
                        <p class="text-secondary mb-0">Besoins totaux</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-gift fs-1 text-success"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= number_format($totalDons, 0, ',', ' ') ?></h2>
                        <p class="text-secondary mb-0">Déjà fournis</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card text-center h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="bi bi-hourglass-split fs-1 text-danger"></i>
                        <h2 class="display-4 fw-bold mt-3"><?= number_format($totalRestants, 0, ',', ' ') ?></h2>
                        <p class="text-secondary mb-0">Reste à fournir</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de progression globale -->
        <div class="card border-0 shadow mb-5">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Progression globale
                    </h5>
                    <span class="badge <?= getCouvertureClass($tauxCouverture) ?> fs-5 px-3 py-2">
                        <?= $tauxCouverture ?>% couvert
                    </span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar <?= getCouvertureClass($tauxCouverture) ?>" 
                         role="progressbar" 
                         style="width: <?= min($tauxCouverture, 100) ?>%;"
                         aria-valuenow="<?= $tauxCouverture ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <?= number_format($totalDons, 0, ',', ' ') ?> / <?= number_format($totalBesoins, 0, ',', ' ') ?>
                    </div>
                </div>
                <div class="row mt-3 text-center">
                    <div class="col-md-4">
                        <small class="text-muted">Montant total des besoins</small>
                        <div class="fw-bold text-warning"><?= number_format($montantTotalBesoins, 0, ',', ' ') ?> Ar</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Montant déjà couvert</small>
                        <div class="fw-bold text-success"><?= number_format($montantTotalBesoins - $montantTotalRestants, 0, ',', ' ') ?> Ar</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Montant restant</small>
                        <div class="fw-bold text-danger"><?= number_format($montantTotalRestants, 0, ',', ' ') ?> Ar</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes des villes -->
        <div class="row g-4">
            <?php foreach ($donnees as $data): ?>
                <?php
                $totaux = $data['totaux'];
                $progressionVille = $totaux['quantite_besoin'] > 0 
                    ? ($totaux['quantite_donnee'] / $totaux['quantite_besoin']) * 100 
                    : 0;
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
                                    <span class="fw-bold">
                                        <?= number_format($totaux['quantite_donnee'], 0, ',', ' ') ?> / 
                                        <?= number_format($totaux['quantite_besoin'], 0, ',', ' ') ?>
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?= getCouvertureClass($progressionVille) ?>" 
                                         style="width: <?= min($progressionVille, 100) ?>%;"></div>
                                </div>
                            </div>
                            
                            <!-- Tableau détaillé des éléments -->
                            <div class="section-title text-primary mb-3">
                                <i class="bi bi-list-check me-2"></i>Détail des besoins et fournitures
                                <span class="badge bg-primary rounded-pill float-end"><?= count($data['elements']) ?> élément(s)</span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">P.U.</th>
                                            <th class="text-end">Besoin</th>
                                            <th class="text-end">
                                                <span class="text-success">Fourni</span>
                                            </th>
                                            <th class="text-end">
                                                <span class="text-danger">Reste</span>
                                            </th>
                                            <th class="text-end">Montant restant</th>
                                            <th class="text-center" style="width: 120px;">Progression</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['elements'] as $elem): ?>
                                            <?php 
                                            $progElem = $elem['quantite_besoin'] > 0 
                                                ? ($elem['quantite_donnee'] / $elem['quantite_besoin']) * 100 
                                                : 0;
                                            ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($elem['element']) ?></td>
                                                <td>
                                                    <span class="badge <?= getBadgeClass($elem['type_besoin']) ?> badge-custom">
                                                        <?= htmlspecialchars($elem['type_besoin']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?= $elem['prix_unitaire'] == 0 
                                                        ? '<span class="text-success">Gratuit</span>' 
                                                        : number_format($elem['prix_unitaire'], 0, ',', ' ') . ' Ar' ?>
                                                </td>
                                                <td class="text-end"><?= number_format($elem['quantite_besoin'], 0, ',', ' ') ?></td>
                                                <td class="text-end">
                                                    <span class="badge bg-success">
                                                        <?= number_format($elem['quantite_donnee'], 0, ',', ' ') ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($elem['quantite_restante'] > 0): ?>
                                                        <span class="badge bg-danger">
                                                            <?= number_format($elem['quantite_restante'], 0, ',', ' ') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-lg"></i> Complet
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end <?= $elem['montant_restant'] > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                                                    <?= $elem['montant_restant'] > 0 
                                                        ? number_format($elem['montant_restant'], 0, ',', ' ') . ' Ar'
                                                        : '✓' ?>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar <?= getCouvertureClass($progElem) ?>" 
                                                             style="width: <?= min($progElem, 100) ?>%;">
                                                            <?= round($progElem) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <th colspan="3">TOTAL</th>
                                            <th class="text-end"><?= number_format($totaux['quantite_besoin'], 0, ',', ' ') ?></th>
                                            <th class="text-end text-success"><?= number_format($totaux['quantite_donnee'], 0, ',', ' ') ?></th>
                                            <th class="text-end text-danger"><?= number_format($totaux['quantite_restante'], 0, ',', ' ') ?></th>
                                            <th class="text-end text-danger"><?= number_format($totaux['montant_restant'], 0, ',', ' ') ?> Ar</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Pied de carte avec résumé -->
                            <div class="total-footer p-3 mt-4 rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Besoins totaux</small>
                                                <div class="fw-bold h5 mb-0"><?= number_format($totaux['quantite_besoin'], 0, ',', ' ') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Déjà fournis</small>
                                                <div class="fw-bold h5 mb-0 text-success"><?= number_format($totaux['quantite_donnee'], 0, ',', ' ') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-hourglass-split text-danger fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Reste à fournir</small>
                                                <div class="fw-bold h5 mb-0 <?= $totaux['quantite_restante'] > 0 ? 'text-danger' : 'text-success' ?>">
                                                    <?= number_format($totaux['quantite_restante'], 0, ',', ' ') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin text-danger fs-4 me-3"></i>
                                            <div>
                                                <small class="text-secondary">Montant restant</small>
                                                <div class="fw-bold h5 mb-0 text-danger">
                                                    <?= number_format($totaux['montant_restant'], 0, ',', ' ') ?> Ar
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