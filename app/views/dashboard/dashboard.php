<?php
/**
 * Dashboard - Gestion des besoins et distributions par ville
 * 
 * Variables disponibles depuis le contrôleur :
 * @var array $donneesParVille     Données regroupées par ville
 * @var array $statsGlobales       Statistiques globales
 * @var array $recapGlobal         Récap global (stock)
 * @var array $stockDisponible     Stock disponible
 * @var array $stockParType        Stock par type
 * @var float $argentDisponible    Argent disponible pour achats
 * @var float $totalValeurStock    Valeur totale du stock
 */

// Fonction pour obtenir la classe de badge selon le type
function getBadgeClass($type) {
    return match($type) {
        'Nature' => 'bg-success',
        'Materiel' => 'bg-info',
        'Argent' => 'bg-warning text-dark',
        default => 'bg-secondary'
    };
}

// Fonction pour obtenir la classe de couleur selon le taux de couverture
function getCouvertureClass($pourcentage) {
    if ($pourcentage >= 100) return 'bg-success';
    if ($pourcentage >= 75) return 'bg-info';
    if ($pourcentage >= 50) return 'bg-warning';
    return 'bg-danger';
}

$pageTitle = 'Dashboard - BNGRC Madagascar';
$currentPage = 'dashboard';
$pageCss = ['assets/css/dashboard/dashboard.css'];
include __DIR__ . '/../layouts/header.php';

// Initialisation des variables
$statsGlobales = $statsGlobales ?? [];
$donneesParVille = $donneesParVille ?? [];
$recapGlobal = $recapGlobal ?? [];
$stockDisponible = $stockDisponible ?? [];
$stockParType = $stockParType ?? [];
$argentDisponible = $argentDisponible ?? 0;
$totalValeurStock = $totalValeurStock ?? 0;

// Calculs des totaux
$totalBesoins = 0;
$totalDistribue = 0;
$totalRestant = 0;
$montantTotalBesoins = 0;
$montantTotalRestant = 0;

foreach ($donneesParVille as $ville) {
    $totalBesoins += $ville['totaux']['quantite_besoin'] ?? 0;
    $totalDistribue += $ville['totaux']['quantite_donnee'] ?? 0;
    $totalRestant += $ville['totaux']['quantite_restante'] ?? 0;
    $montantTotalBesoins += $ville['totaux']['montant_besoin'] ?? 0;
    $montantTotalRestant += $ville['totaux']['montant_restant'] ?? 0;
}

$tauxCouverture = $totalBesoins > 0 ? round(($totalDistribue / $totalBesoins) * 100) : 0;

// Stock total
$totalStock = 0;
foreach ($stockDisponible as $item) {
    $totalStock += $item['stock_disponible'] ?? 0;
}
?>

    <!-- En-tête -->
    <div class="container-fluid py-4 bg-light">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold text-primary">
                    <i class="bi bi-speedometer2 me-2"></i>Tableau de bord BNGRC
                </h1>
                <p class="lead text-secondary mb-0">Suivi des besoins et distributions - Madagascar</p>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        
        <!-- Statistiques globales -->
        <div class="row g-3 mb-4">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-geo-alt fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= count($donneesParVille) ?></h3>
                        <small>Villes</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-exclamation-triangle fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= number_format($totalBesoins, 0, ',', ' ') ?></h3>
                        <small>Besoins totaux</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-success text-white">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-truck fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= number_format($totalDistribue, 0, ',', ' ') ?></h3>
                        <small>Distribués</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-danger text-white">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-hourglass-split fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= number_format($totalRestant, 0, ',', ' ') ?></h3>
                        <small>Manquants</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-info text-white">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-box-seam fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= number_format($totalStock, 0, ',', ' ') ?></h3>
                        <small>En stock</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-secondary text-white">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-cash-coin fs-2"></i>
                        <h3 class="mb-0 mt-2"><?= number_format($argentDisponible, 0, ',', ' ') ?></h3>
                        <small>Ar disponible</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de progression globale -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Progression globale des distributions
                    </h6>
                    <span class="badge <?= getCouvertureClass($tauxCouverture) ?> px-3 py-2">
                        <?= $tauxCouverture ?>% couvert
                    </span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped <?= getCouvertureClass($tauxCouverture) ?>" 
                         role="progressbar" 
                         style="width: <?= min($tauxCouverture, 100) ?>%;">
                        <?= number_format($totalDistribue, 0, ',', ' ') ?> / <?= number_format($totalBesoins, 0, ',', ' ') ?>
                    </div>
                </div>
                <div class="row mt-3 text-center small">
                    <div class="col-4">
                        <span class="text-muted">Montant total besoins</span>
                        <div class="fw-bold text-warning"><?= number_format($montantTotalBesoins, 0, ',', ' ') ?> Ar</div>
                    </div>
                    <div class="col-4">
                        <span class="text-muted">Montant couvert</span>
                        <div class="fw-bold text-success"><?= number_format($montantTotalBesoins - $montantTotalRestant, 0, ',', ' ') ?> Ar</div>
                    </div>
                    <div class="col-4">
                        <span class="text-muted">Montant manquant</span>
                        <div class="fw-bold text-danger"><?= number_format($montantTotalRestant, 0, ',', ' ') ?> Ar</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock disponible (résumé) -->
        <?php if (!empty($stockDisponible)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white py-2">
                <h6 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>Stock global disponible
                    <a href="<?= $toUrl('/don/simulation') ?>" class="btn btn-sm btn-light float-end">
                        <i class="bi bi-truck me-1"></i>Distribuer
                    </a>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Élément</th>
                                <th>Type</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockDisponible as $item): ?>
                                <?php if (($item['type_besoin'] ?? '') !== 'Argent'): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($item['element_libele'] ?? '') ?></td>
                                    <td><span class="badge <?= getBadgeClass($item['type_besoin'] ?? '') ?>"><?= htmlspecialchars($item['type_besoin'] ?? '') ?></span></td>
                                    <td class="text-end"><?= number_format($item['stock_disponible'] ?? 0, 0, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format($item['valeur_stock'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cartes par ville -->
        <h5 class="mb-3">
            <i class="bi bi-building me-2 text-primary"></i>Détail par ville
        </h5>
        
        <?php if (empty($donneesParVille)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Aucun besoin enregistré pour le moment.
                <a href="<?= $toUrl('/besoin/saisie') ?>" class="alert-link">Ajouter un besoin</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($donneesParVille as $data): ?>
                    <?php
                    $totaux = $data['totaux'] ?? [];
                    $qBesoin = (int)($totaux['quantite_besoin'] ?? 0);
                    $qDonnee = (int)($totaux['quantite_donnee'] ?? 0);
                    $qRestante = (int)($totaux['quantite_restante'] ?? 0);
                    $mRestant = (float)($totaux['montant_restant'] ?? 0);
                    $progression = $qBesoin > 0 ? round(($qDonnee / $qBesoin) * 100) : 0;
                    $estComplet = $qRestante <= 0;
                    ?>
                    
                    <div class="col-xl-6 col-12">
                        <div class="card border-0 shadow h-100 <?= $estComplet ? 'border-success' : '' ?>">
                            <!-- En-tête ville -->
                            <div class="card-header <?= $estComplet ? 'bg-success' : 'bg-primary' ?> text-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($data['ville'] ?? '') ?>
                                        </h5>
                                        <small class="opacity-75">
                                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($data['region'] ?? '') ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="display-6 fw-bold"><?= $progression ?>%</div>
                                        <?php if ($estComplet): ?>
                                            <span class="badge bg-light text-success">
                                                <i class="bi bi-check-circle me-1"></i>Complet
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Barre de progression -->
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar <?= getCouvertureClass($progression) ?>" 
                                         style="width: <?= min($progression, 100) ?>%;"></div>
                                </div>
                                
                                <!-- Résumé rapide -->
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                            <div class="fw-bold"><?= number_format($qBesoin, 0, ',', ' ') ?></div>
                                            <small class="text-muted">Besoins</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <i class="bi bi-check-circle text-success"></i>
                                            <div class="fw-bold text-success"><?= number_format($qDonnee, 0, ',', ' ') ?></div>
                                            <small class="text-muted">Reçu</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <i class="bi bi-hourglass-split text-danger"></i>
                                            <div class="fw-bold <?= $qRestante > 0 ? 'text-danger' : 'text-success' ?>">
                                                <?= number_format($qRestante, 0, ',', ' ') ?>
                                            </div>
                                            <small class="text-muted">Manque</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tableau détaillé des éléments -->
                                <?php if (!empty($data['elements'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Élément</th>
                                                <th class="text-center">Besoin</th>
                                                <th class="text-center">Reçu</th>
                                                <th class="text-center">Manque</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['elements'] as $elem): ?>
                                                <?php 
                                                $elemRestant = (int)($elem['quantite_restante'] ?? 0);
                                                $elemComplet = $elemRestant <= 0;
                                                ?>
                                                <tr class="<?= $elemComplet ? 'table-success' : '' ?>">
                                                    <td>
                                                        <span class="fw-bold"><?= htmlspecialchars($elem['element'] ?? '') ?></span>
                                                        <br>
                                                        <span class="badge <?= getBadgeClass($elem['type_besoin'] ?? '') ?> badge-sm">
                                                            <?= htmlspecialchars($elem['type_besoin'] ?? '') ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center"><?= number_format($elem['quantite_besoin'] ?? 0, 0, ',', ' ') ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success"><?= number_format($elem['quantite_donnee'] ?? 0, 0, ',', ' ') ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($elemComplet): ?>
                                                            <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger"><?= number_format($elemRestant, 0, ',', ' ') ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end small <?= ($elem['montant_restant'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                                        <?= ($elem['montant_restant'] ?? 0) > 0 
                                                            ? number_format($elem['montant_restant'], 0, ',', ' ') . ' Ar'
                                                            : '<i class="bi bi-check-circle"></i>' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pied de carte -->
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        <i class="bi bi-list-check me-1"></i><?= count($data['elements'] ?? []) ?> élément(s)
                                    </span>
                                    <?php if ($mRestant > 0): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-cash me-1"></i><?= number_format($mRestant, 0, ',', ' ') ?> Ar manquants
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Tous les besoins couverts
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Actions rapides -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-lightning me-2 text-warning"></i>Actions rapides</h6>
                <div class="row g-2">
                    <div class="col-md-3 col-6">
                        <a href="<?= $toUrl('/besoin/saisie') ?>" class="btn btn-outline-warning w-100">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter besoin
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= $toUrl('/don/saisie') ?>" class="btn btn-outline-success w-100">
                            <i class="bi bi-gift me-2"></i>Ajouter don
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= $toUrl('/achat/saisie') ?>" class="btn btn-outline-info w-100">
                            <i class="bi bi-bag me-2"></i>Nouvel achat
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= $toUrl('/don/simulation') ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-truck me-2"></i>Distribuer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
