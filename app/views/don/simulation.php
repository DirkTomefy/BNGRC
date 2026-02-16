<?php

$panier = $panierDons ?? [];
$resultat = $resultatSimulation ?? [];
$simule = !empty($resultat) && !empty($resultat['distributions']);

$pageTitle = 'Simulation de distribution - BNGRC';
$currentPage = 'don';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';

// Calculer le total du panier
$totalPanier = 0;
foreach ($panier as $item) {
    $totalPanier += $item['quantite'] * ($item['element_pu'] ?? 0);
}
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-play-circle-fill text-primary"></i> Simulation de distribution
                </h1>
                <p class="lead text-secondary">
                    Prévisualisez la distribution FIFO avant validation
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <!-- Panier actuel -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cart3 me-2"></i>Panier de dons
                            <span class="badge bg-light text-dark ms-2"><?= count($panier) ?> éléments</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($panier)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cart-x fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Le panier est vide</p>
                                <a href="/don/saisie" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter des dons
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($panier as $item): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($item['element_libele'] ?? '') ?></td>
                                                <td>
                                                    <?php
                                                    $typeBesoin = $item['type_besoin'] ?? '';
                                                    $typeBadge = 'bg-secondary';
                                                    if ($typeBesoin === 'Argent') $typeBadge = 'bg-warning text-dark';
                                                    elseif ($typeBesoin === 'Nature') $typeBadge = 'bg-success';
                                                    elseif ($typeBesoin === 'Materiel') $typeBadge = 'bg-info';
                                                    ?>
                                                    <span class="badge <?= $typeBadge ?>"><?= htmlspecialchars($typeBesoin) ?></span>
                                                </td>
                                                <td class="text-end"><?= number_format($item['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                <td class="text-end"><?= number_format($item['element_pu'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end fw-bold"><?= number_format(($item['quantite'] ?? 0) * ($item['element_pu'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                                            <td class="text-end fw-bold"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bouton Simuler (affiché si panier non vide et pas encore simulé) -->
                <?php if (!empty($panier) && !$simule): ?>
                    <div class="text-center mb-4">
                        <form method="POST" action="/don/simuler">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-play-fill me-2"></i>Simuler la distribution FIFO
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Résultat de la simulation -->
                <?php if ($simule): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-check2-all me-2"></i>Résultat de la simulation
                                <span class="badge bg-light text-dark ms-2"><?= $resultat['totalDistributions'] ?? 0 ?> distributions</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Distribution FIFO:</strong> Les dons seront distribués en priorité aux villes ayant les besoins les plus anciens.
                            </div>

                            <!-- Résumé -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4><?= $resultat['totalDistributions'] ?? 0 ?></h4>
                                            <small>Distributions</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4><?= number_format($resultat['totalQuantite'] ?? 0, 0, ',', ' ') ?></h4>
                                            <small>Quantité totale</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4><?= number_format($resultat['totalMontant'] ?? 0, 0, ',', ' ') ?> Ar</h4>
                                            <small>Montant total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Distributions par ville -->
                            <?php if (!empty($resultat['parVille'])): ?>
                                <?php foreach ($resultat['parVille'] as $villeId => $villeData): ?>
                                    <div class="card mb-3">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($villeData['ville_libele'] ?? '') ?>
                                                <span class="badge bg-light text-dark ms-2"><?= count($villeData['items'] ?? []) ?> éléments</span>
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Élément</th>
                                                        <th class="text-end">Quantité</th>
                                                        <th class="text-end">Prix unitaire</th>
                                                        <th class="text-end">Montant</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($villeData['items'] ?? [] as $dist): ?>
                                                        <tr>
                                                            <td class="fw-bold"><?= htmlspecialchars($dist['element_libele'] ?? '') ?></td>
                                                            <td class="text-end"><?= number_format($dist['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                            <td class="text-end"><?= number_format($dist['element_pu'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                            <td class="text-end fw-bold"><?= number_format($dist['montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                            <td><small class="text-muted"><?= date('d/m/Y', strtotime($dist['date'] ?? 'now')) ?></small></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="table-secondary">
                                                    <tr>
                                                        <td class="text-end fw-bold">Total</td>
                                                        <td class="text-end fw-bold"><?= number_format($villeData['total_quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                        <td></td>
                                                        <td class="text-end fw-bold"><?= number_format($villeData['total_montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Non distribués -->
                            <?php if (!empty($resultat['nonDistribues'])): ?>
                                <div class="alert alert-warning mt-4">
                                    <h6><i class="bi bi-exclamation-triangle me-2"></i>Éléments non distribués</h6>
                                    <ul class="mb-0">
                                        <?php foreach ($resultat['nonDistribues'] as $nonDist): ?>
                                            <li>
                                                <?= htmlspecialchars($nonDist['element_libele'] ?? '') ?> 
                                                (qté: <?= $nonDist['quantite'] ?? 0 ?>) 
                                                - <?= htmlspecialchars($nonDist['raison'] ?? '') ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Boutons Valider / Annuler -->
                    <div class="text-center mb-4">
                        <form method="POST" action="/don/valider" class="d-inline">
                            <button type="submit" class="btn btn-success btn-lg me-3">
                                <i class="bi bi-check-circle me-2"></i>Valider et enregistrer
                            </button>
                        </form>
                        <a href="/don/saisie" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Modifier le panier
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Lien retour -->
                <div class="text-center mt-4">
                    <a href="/don/saisie" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Retour à la saisie des dons
                    </a>
                </div>

            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
