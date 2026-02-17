<?php

$achats = $achats ?? [];
$totaux = $totaux ?? [];
$tauxFrais = $tauxFrais ?? 10;

$pageTitle = 'Liste des achats - BNGRC';
$currentPage = 'achat';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-bag-fill text-primary"></i> Liste des achats
                </h1>
                <p class="lead text-secondary">
                    Historique des achats effectués (stock global)
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Les achats alimentent le <strong>stock global</strong> et sont financés par les dons de type <strong>Argent</strong>.
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="badge bg-primary fs-6 p-2">
                                    <i class="bi bi-percent me-1"></i>Taux de frais: <?= $tauxFrais ?>%
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-2"></i>Nouvel achat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-bag fs-1"></i>
                                <h3 class="mb-0"><?= number_format($totaux['nb_achats'] ?? 0, 0, ',', ' ') ?></h3>
                                <small>Achats</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-box fs-1"></i>
                                <h3 class="mb-0"><?= number_format($totaux['quantite_totale'] ?? 0, 0, ',', ' ') ?></h3>
                                <small>Quantité totale</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <i class="bi bi-cash fs-1"></i>
                                <h3 class="mb-0"><?= number_format($totaux['montant_ht_total'] ?? 0, 0, ',', ' ') ?></h3>
                                <small>Montant HT (Ar)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-cash-coin fs-1"></i>
                                <h3 class="mb-0"><?= number_format($totaux['montant_ttc_total'] ?? 0, 0, ',', ' ') ?></h3>
                                <small>Montant TTC (Ar)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des achats -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2"></i>Détail des achats
                            <span class="badge bg-light text-dark ms-2"><?= count($achats) ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($achats)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Aucun achat enregistré</p>
                                <a href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Créer un achat
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">Qté</th>
                                            <th class="text-end">P.U.</th>
                                            <th class="text-end">Montant HT</th>
                                            <th class="text-end">Frais</th>
                                            <th class="text-end">Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($achats as $index => $achat): ?>
                                            <tr>
                                                <td class="text-muted"><?= $index + 1 ?></td>
                                                <td>
                                                    <small><?= date('d/m/Y H:i', strtotime($achat['date'])) ?></small>
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($achat['element_libele'] ?? '') ?></td>
                                                <td>
                                                    <span class="badge <?= ($achat['type_besoin'] ?? '') === 'Nature' ? 'bg-success' : 'bg-info' ?>">
                                                        <?= htmlspecialchars($achat['type_besoin'] ?? '') ?>
                                                    </span>
                                                </td>
                                                <td class="text-end"><?= number_format($achat['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                <td class="text-end"><?= number_format($achat['prixUnitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end"><?= number_format($achat['montantHT'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end text-warning">
                                                    +<?= number_format($achat['montantFrais'] ?? 0, 0, ',', ' ') ?> Ar
                                                    <small class="text-muted">(<?= $achat['tauxFrais'] ?? 0 ?>%)</small>
                                                </td>
                                                <td class="text-end fw-bold text-success">
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
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
