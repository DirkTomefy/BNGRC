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
$pageCss = [];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-truck text-primary me-3"></i>Distribution du stock
            </h1>
            <p class="lead text-secondary">
                Distribuez le stock disponible aux villes selon leurs besoins (FIFO)
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats rapides -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Unités en stock</h6>
                                        <h3 class="display-6 fw-bold mb-0"><?= number_format($totalStock, 0, ',', ' ') ?></h3>
                                    </div>
                                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Valeur du stock</h6>
                                        <h3 class="display-6 fw-bold mb-0"><?= number_format($totalValeur, 0, ',', ' ') ?></h3>
                                        <small>Ar</small>
                                    </div>
                                    <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning text-dark">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-dark-50 mb-2">Villes en attente</h6>
                                        <h3 class="display-6 fw-bold mb-0"><?= count($besoinsParVille) ?></h3>
                                    </div>
                                    <i class="bi bi-geo-alt fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock disponible -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-box-seam me-2"></i>Stock disponible
                            </h5>
                            <span class="badge bg-light text-dark rounded-pill"><?= count($stockDisponible) ?> éléments</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($stockDisponible)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-box fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-3">Aucun stock disponible</p>
                                <a href="<?= $baseUrl ?>/don/saisie" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter des dons
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Élément</th>
                                            <th>Type</th>
                                            <th class="text-end">Stock disponible</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Valeur totale</th>
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
                                                <td class="text-end fw-bold"><?= number_format($item['stock_disponible'] ?? 0, 0, ',', ' ') ?></td>
                                                <td class="text-end"><?= number_format($item['prix_unitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td class="text-end text-primary fw-bold"><?= number_format($item['valeur_stock'] ?? 0, 0, ',', ' ') ?> Ar</td>
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
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-geo-alt me-2"></i>Besoins par ville
                            </h5>
                            <span class="badge bg-dark text-white rounded-pill"><?= count($besoinsParVille) ?> villes</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ville</th>
                                        <th class="text-end">Besoins</th>
                                        <th class="text-end">Quantité totale</th>
                                        <th class="text-end">Montant</th>
                                        <th class="text-end">Déjà reçu</th>
                                        <th class="text-end">Progression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($besoinsParVille as $ville): 
                                        $progression = ($ville['montant_total'] ?? 0) > 0 
                                            ? round((($ville['deja_recu'] ?? 0) / $ville['montant_total']) * 100) 
                                            : 0;
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($ville['ville_libele'] ?? '') ?></td>
                                            <td class="text-end"><?= $ville['nb_besoins'] ?? 0 ?></td>
                                            <td class="text-end"><?= number_format($ville['quantite_totale'] ?? 0, 0, ',', ' ') ?></td>
                                            <td class="text-end"><?= number_format($ville['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td class="text-end text-success fw-bold"><?= number_format($ville['deja_recu'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td class="text-end">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="progress w-50 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success" style="width: <?= $progression ?>%"></div>
                                                    </div>
                                                    <span class="small"><?= $progression ?>%</span>
                                                </div>
                                            </td>
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
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-sliders me-2"></i>Méthode de distribution
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="<?= $baseUrl ?>/don/simuler" id="formSimulation">
                                <div class="row g-4 align-items-end">
                                    <div class="col-md-8">
                                        <label for="methode" class="form-label fw-bold">Choisir la méthode de dispatch :</label>
                                        <select name="methode" id="methode" class="form-select form-select-lg">
                                            <option value="fifo" selected>
                                                📅 Priorité par date (FIFO) - Les plus anciens d'abord
                                            </option>
                                            <option value="plus_petit_besoin">
                                                📉 Priorité au plus petit besoin - Équité territoriale
                                            </option>
                                            <option value="proportionnelle">
                                                ⚖️ Distribution proportionnelle - Au prorata des besoins
                                            </option>
                                        </select>
                                        <div class="form-text text-muted mt-2" id="methodeDescription">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Le stock est distribué en priorité aux besoins les plus anciens (FIFO).
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-warning btn-lg w-100">
                                            <i class="bi bi-calculator me-2"></i>Simuler
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                    document.getElementById('methode').addEventListener('change', function() {
                        const descriptions = {
                            'fifo': '📅 Distribution selon la règle FIFO : les besoins les plus anciens sont servis en premier.',
                            'plus_petit_besoin': '📉 Équité territoriale : les villes avec les plus petits besoins restants sont prioritaires.',
                            'proportionnelle': '⚖️ Répartition juste : le stock est distribué proportionnellement aux besoins de chaque ville.'
                        };
                        const descElement = document.getElementById('methodeDescription');
                        descElement.innerHTML = '<i class="bi bi-info-circle me-1"></i>' + descriptions[this.value];
                    });
                    </script>
                <?php endif; ?>

                <!-- Résultat de la simulation -->
                <?php if ($simule): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-check2-all me-2"></i>Résultat de la simulation
                                </h5>
                                <span class="badge bg-light text-dark rounded-pill">
                                    <?= $resultat['totalDistributions'] ?? 0 ?> distributions
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <?php
                            $methodeLabels = [
                                'fifo' => ['📅 FIFO', 'Distribution par ordre chronologique'],
                                'plus_petit_besoin' => ['📉 Plus petit besoin', 'Priorité aux villes les moins pourvues'],
                                'proportionnelle' => ['⚖️ Proportionnelle', 'Répartition équitable']
                            ];
                            $methodeUsed = $resultat['methode'] ?? 'fifo';
                            $methodeInfo = $methodeLabels[$methodeUsed] ?? $methodeLabels['fifo'];
                            ?>
                            
                            <div class="alert alert-info bg-info bg-opacity-10 border-0 mb-4">
                                <div class="d-flex">
                                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                    <div>
                                        <strong><?= $methodeInfo[0] ?> :</strong> <?= $methodeInfo[1] ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Résumé -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white border-0">
                                        <div class="card-body text-center p-4">
                                            <h2 class="display-6 fw-bold mb-0"><?= $resultat['totalDistributions'] ?? 0 ?></h2>
                                            <small>Distributions</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white border-0">
                                        <div class="card-body text-center p-4">
                                            <h2 class="display-6 fw-bold mb-0"><?= number_format($resultat['totalQuantite'] ?? 0, 0, ',', ' ') ?></h2>
                                            <small>Quantité totale</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white border-0">
                                        <div class="card-body text-center p-4">
                                            <h2 class="display-6 fw-bold mb-0"><?= number_format($resultat['totalMontant'] ?? 0, 0, ',', ' ') ?></h2>
                                            <small>Montant total (Ar)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Distributions par ville -->
                            <?php if (!empty($resultat['parVille'])): ?>
                                <?php foreach ($resultat['parVille'] as $villeId => $villeData): ?>
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-light py-3">
                                            <h6 class="mb-0 fw-bold">
                                                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                                <?= htmlspecialchars($villeData['ville_libele'] ?? '') ?>
                                                <span class="badge bg-secondary ms-2"><?= count($villeData['items'] ?? []) ?> éléments</span>
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
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
                                                                <td class="text-end text-success fw-bold"><?= number_format($dist['montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-light fw-bold">
                                                        <tr>
                                                            <td colspan="2" class="text-end">Total ville</td>
                                                            <td class="text-end"><?= number_format($villeData['total_quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                            <td></td>
                                                            <td class="text-end text-success"><?= number_format($villeData['total_montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Non distribués -->
                            <?php if (!empty($resultat['nonDistribues'])): ?>
                                <div class="alert alert-warning mt-4">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Stock non distribué</h6>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($resultat['nonDistribues'] as $nonDist): ?>
                                            <li class="mb-2">
                                                <i class="bi bi-dot me-2"></i>
                                                <strong><?= htmlspecialchars($nonDist['element_libele'] ?? '') ?></strong> 
                                                (<?= $nonDist['quantite'] ?? 0 ?> unités) - 
                                                <span class="text-muted"><?= htmlspecialchars($nonDist['raison'] ?? '') ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Boutons Valider / Annuler -->
                    <div class="text-center mb-5">
                        <form method="POST" action="<?= $baseUrl ?>/don/valider" class="d-inline">
                            <button type="submit" class="btn btn-success btn-lg px-5 me-3"
                                    onclick="return confirm('Confirmer la distribution ? Cette action est irréversible.')">
                                <i class="bi bi-check-circle me-2"></i>Valider et distribuer
                            </button>
                        </form>
                        <form method="POST" action="<?= $baseUrl ?>/don/annuler-simulation" class="d-inline">
                            <button type="submit" class="btn btn-outline-secondary btn-lg px-5">
                                <i class="bi bi-x-circle me-2"></i>Annuler
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Liens rapides -->
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