<?php

$achats = $achats ?? [];
$totaux = $totaux ?? [];
$tauxFrais = $tauxFrais ?? 10;

$pageTitle = 'Liste des achats - BNGRC';
$currentPage = 'achat';
$pageCss = [];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-bag-fill text-primary me-3"></i>Liste des achats
            </h1>
            <p class="lead text-secondary">
                Historique des achats effectués
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Barre d'actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Information</h6>
                                        <p class="text-muted small mb-0">
                                            Les achats alimentent le stock global et sont financés par les dons de type Argent
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <span class="badge bg-primary mb-2">Taux de frais</span>
                                    <h4 class="fw-bold mb-0"><?= $tauxFrais ?>%</h4>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>" class="btn btn-success btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Nouvel achat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé des totaux -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Nombre d'achats</h6>
                                        <h2 class="display-6 fw-bold mb-0"><?= number_format($totaux['nb_achats'] ?? 0, 0, ',', ' ') ?></h2>
                                    </div>
                                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Quantité totale</h6>
                                        <h2 class="display-6 fw-bold mb-0"><?= number_format($totaux['quantite_totale'] ?? 0, 0, ',', ' ') ?></h2>
                                    </div>
                                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-warning text-dark">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-dark-50 mb-2">Montant HT</h6>
                                        <h2 class="display-6 fw-bold mb-0"><?= number_format($totaux['montant_ht_total'] ?? 0, 0, ',', ' ') ?></h2>
                                        <small>Ar</small>
                                    </div>
                                    <i class="bi bi-cash fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Montant TTC</h6>
                                        <h2 class="display-6 fw-bold mb-0"><?= number_format($totaux['montant_ttc_total'] ?? 0, 0, ',', ' ') ?></h2>
                                        <small>Ar</small>
                                    </div>
                                    <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des achats -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-table me-2"></i>Détail des achats
                            </h5>
                            <span class="badge bg-light text-dark rounded-pill"><?= count($achats) ?> enregistrement(s)</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($achats)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-3">Aucun achat enregistré</p>
                                <a href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Créer un achat
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">P.U.</th>
                                            <th class="text-end">Montant HT</th>
                                            <th class="text-end">Frais</th>
                                            <th class="text-end">Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($achats as $index => $achat): ?>
                                            <tr>
                                                <td class="text-muted fw-bold"><?= $index + 1 ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($achat['date'])) ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted"><?= date('H:i', strtotime($achat['date'])) ?></small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?= htmlspecialchars($achat['element_libele'] ?? '') ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $typeClass = ($achat['type_besoin'] ?? '') === 'Nature' ? 'bg-success' : 'bg-info';
                                                    ?>
                                                    <span class="badge <?= $typeClass ?>">
                                                        <?= htmlspecialchars($achat['type_besoin'] ?? '') ?>
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold"><?= number_format($achat['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                <td class="text-end"><?= number_format($achat['prixUnitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end"><?= number_format($achat['montantHT'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end">
                                                    <span class="badge bg-warning">
                                                        +<?= number_format($achat['montantFrais'] ?? 0, 0, ',', ' ') ?> Ar
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">(<?= $achat['tauxFrais'] ?? 0 ?>%)</small>
                                                </td>
                                                <td class="text-end fw-bold text-success fs-5">
                                                    <?= number_format($achat['montantTTC'] ?? 0, 0, ',', ' ') ?> Ar
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">TOTAUX</td>
                                            <td class="text-end fw-bold"><?= number_format($totaux['quantite_totale'] ?? 0, 0, ',', ' ') ?></td>
                                            <td></td>
                                            <td class="text-end fw-bold"><?= number_format($totaux['montant_ht_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td class="text-end fw-bold text-warning"><?= number_format($totaux['frais_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td class="text-end fw-bold text-success"><?= number_format($totaux['montant_ttc_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Pied de tableau avec statistiques supplémentaires -->
                            <div class="card-footer bg-light py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Dernier achat : <?= !empty($achats) ? date('d/m/Y', strtotime($achats[0]['date'])) : 'N/A' ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <small class="text-muted">
                                            <i class="bi bi-calculator me-1"></i>
                                            Taux de frais moyen : <?= $tauxFrais ?>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="text-center mt-4">
                    <a href="<?= htmlspecialchars($toUrl('/dashboard')) ?>" class="btn btn-outline-primary me-2">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                    <a href="<?= htmlspecialchars($toUrl('/don/simulation')) ?>" class="btn btn-outline-success">
                        <i class="bi bi-truck me-2"></i>Distribution
                    </a>
                </div>

            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>