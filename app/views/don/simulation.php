<?php

$stockDisponible = $stockDisponible ?? [];
$besoinsParVille = $besoinsParVille ?? [];
$recapGlobal = $recapGlobal ?? [];
$resultat = $resultatSimulation ?? [];
$success = $success ?? '';
$error = $error ?? '';
$simule = !empty($resultat) && !empty($resultat['distributions']);

$baseUrl = Flight::app()->get('flight.base_url');

// Calculer les totaux
$totalStock = array_sum(array_column($stockDisponible, 'stock_disponible'));
$totalValeur = array_sum(array_column($stockDisponible, 'valeur_stock'));

$pageTitle = 'Distribution du stock - BNGRC';
$currentPage = 'don';
$pageCss = ['assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-truck text-primary"></i> Distribution du stock
                </h1>
                <p class="lead text-secondary">
                    Distribuez le stock disponible aux villes selon leurs besoins (FIFO)
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

                <!-- Stats rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center bg-primary text-white">
                            <div class="card-body py-3">
                                <i class="bi bi-box-seam fs-3"></i>
                                <h4 class="mt-2 mb-0"><?= number_format($totalStock, 0, ',', ' ') ?></h4>
                                <small>Unités en stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center bg-success text-white">
                            <div class="card-body py-3">
                                <i class="bi bi-cash-coin fs-3"></i>
                                <h4 class="mt-2 mb-0"><?= number_format($totalValeur, 0, ',', ' ') ?> Ar</h4>
                                <small>Valeur du stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center bg-warning text-dark">
                            <div class="card-body py-3">
                                <i class="bi bi-geo-alt fs-3"></i>
                                <h4 class="mt-2 mb-0"><?= count($besoinsParVille) ?></h4>
                                <small>Villes en attente</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock disponible -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam me-2"></i>Stock disponible
                            <span class="badge bg-light text-dark ms-2"><?= count($stockDisponible) ?> éléments</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($stockDisponible)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-box fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Aucun stock disponible</p>
                                <a href="<?= $baseUrl ?>/don/saisie" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter des dons
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">Stock disponible</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Valeur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stockDisponible as $item): ?>
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
                                                <td class="text-end"><?= number_format($item['stock_disponible'] ?? 0, 0, ',', ' ') ?></td>
                                                <td class="text-end"><?= number_format($item['prix_unitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end fw-bold"><?= number_format($item['valeur_stock'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="2" class="text-end fw-bold">TOTAL</td>
                                            <td class="text-end fw-bold"><?= number_format($totalStock, 0, ',', ' ') ?></td>
                                            <td></td>
                                            <td class="text-end fw-bold"><?= number_format($totalValeur, 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Besoins par ville -->
                <?php if (!empty($besoinsParVille)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt me-2"></i>Besoins par ville
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ville</th>
                                        <th class="text-end">Nb besoins</th>
                                        <th class="text-end">Quantité totale</th>
                                        <th class="text-end">Montant</th>
                                        <th class="text-end">Déjà reçu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($besoinsParVille as $ville): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($ville['ville_libele'] ?? '') ?></td>
                                            <td class="text-end"><?= $ville['nb_besoins'] ?? 0 ?></td>
                                            <td class="text-end"><?= number_format($ville['quantite_totale'] ?? 0, 0, ',', ' ') ?></td>
                                            <td class="text-end"><?= number_format($ville['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td class="text-end text-success"><?= number_format($ville['deja_recu'] ?? 0, 0, ',', ' ') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Boutons d'action -->
                <?php if (!empty($stockDisponible) && !$simule): ?>
                    <div class="text-center mb-4">
                        <form method="POST" action="<?= $baseUrl ?>/don/simuler" class="d-inline">
                            <button type="submit" class="btn btn-warning btn-lg me-2">
                                <i class="bi bi-play-fill me-2"></i>Simuler la distribution FIFO
                            </button>
                        </form>
                        <form method="POST" action="<?= $baseUrl ?>/don/distribuer-auto" class="d-inline">
                            <button type="submit" class="btn btn-success btn-lg"
                                    onclick="return confirm('Distribuer automatiquement le stock aux villes ?')">
                                <i class="bi bi-lightning me-2"></i>Distribution automatique
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
                                <strong>Distribution FIFO:</strong> Le stock sera distribué en priorité aux besoins les plus anciens.
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
                                                        <th>Type</th>
                                                        <th class="text-end">Quantité</th>
                                                        <th class="text-end">Prix unitaire</th>
                                                        <th class="text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($villeData['items'] ?? [] as $dist): ?>
                                                        <tr>
                                                            <td class="fw-bold"><?= htmlspecialchars($dist['element_libele'] ?? '') ?></td>
                                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($dist['type_besoin'] ?? '') ?></span></td>
                                                            <td class="text-end"><?= number_format($dist['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                            <td class="text-end"><?= number_format($dist['prix_unitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                            <td class="text-end fw-bold"><?= number_format($dist['montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="table-secondary">
                                                    <tr>
                                                        <td colspan="2" class="text-end fw-bold">Total</td>
                                                        <td class="text-end fw-bold"><?= number_format($villeData['total_quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                        <td></td>
                                                        <td class="text-end fw-bold"><?= number_format($villeData['total_montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
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
                                    <h6><i class="bi bi-exclamation-triangle me-2"></i>Stock non distribué</h6>
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
                        <form method="POST" action="<?= $baseUrl ?>/don/valider" class="d-inline">
                            <button type="submit" class="btn btn-success btn-lg me-3"
                                    onclick="return confirm('Confirmer la distribution ?')">
                                <i class="bi bi-check-circle me-2"></i>Valider et distribuer
                            </button>
                        </form>
                        <a href="<?= $baseUrl ?>/don/simulation" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>Annuler
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Lien retour -->
                <div class="text-center mt-4">
                    <a href="<?= $baseUrl ?>/don/saisie" class="btn btn-outline-primary me-2">
                        <i class="bi bi-plus-circle me-2"></i>Ajouter des dons
                    </a>
                    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-2"></i>Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
