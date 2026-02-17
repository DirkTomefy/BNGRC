<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution aux Villes - BNGRC</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        .card-stock { border-left: 4px solid #28a745; }
        .card-besoin { border-left: 4px solid #dc3545; }
        .card-distribution { border-left: 4px solid #007bff; }
        .table-hover tbody tr:hover { background-color: rgba(0,123,255,0.1); }
        .badge-stock { background-color: #28a745; }
        .badge-besoin { background-color: #dc3545; }
        .ville-card { transition: all 0.3s ease; }
        .ville-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/"><i class="bi bi-box-seam"></i> BNGRC</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/don/saisie"><i class="bi bi-gift"></i> Saisie Dons</a>
            <a class="nav-link active" href="/don/simulation"><i class="bi bi-truck"></i> Distribution</a>
            <a class="nav-link" href="/achat/saisie"><i class="bi bi-cart"></i> Achats</a>
            <a class="nav-link" href="/"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4"><i class="bi bi-truck"></i> Distribution aux Villes</h1>
    <p class="text-muted">Distribuez le stock global vers les villes selon leurs besoins (ordre FIFO - Premier Arrivé, Premier Servi)</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Récapitulatif Global -->
    <?php if ($recapGlobal): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-clipboard-check"></i> Besoins Total</h6>
                    <h3><?= number_format($recapGlobal['montant_besoins_total'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-gift"></i> Dons Total</h6>
                    <h3><?= number_format($recapGlobal['montant_dons_total'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-cart"></i> Achats Total</h6>
                    <h3><?= number_format($recapGlobal['montant_achats_total'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-truck"></i> Distribué Total</h6>
                    <h3><?= number_format($recapGlobal['montant_distribue_total'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Stock Disponible -->
        <div class="col-lg-6 mb-4">
            <div class="card card-stock h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock Disponible</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stockDisponible)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Aucun stock disponible. 
                            <a href="/don/saisie" class="alert-link">Ajouter des dons</a> ou 
                            <a href="/achat/saisie" class="alert-link">faire des achats</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Élément</th>
                                        <th>Type</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalStock = 0;
                                    $totalValeur = 0;
                                    foreach ($stockDisponible as $item): 
                                        $totalStock += $item['stock_disponible'];
                                        $totalValeur += $item['valeur_stock'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['element_libele']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($item['type_besoin']) ?></span></td>
                                        <td class="text-end"><strong><?= number_format($item['stock_disponible'], 0, ',', ' ') ?></strong></td>
                                        <td class="text-end"><?= number_format($item['valeur_stock'], 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-success">
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th class="text-end"><?= number_format($totalStock, 0, ',', ' ') ?></th>
                                        <th class="text-end"><?= number_format($totalValeur, 0, ',', ' ') ?> Ar</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Besoins des Villes -->
        <div class="col-lg-6 mb-4">
            <div class="card card-besoin h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Besoins des Villes (Non Satisfaits)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($besoinsParVille)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-check-circle"></i> Tous les besoins des villes sont satisfaits !
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Ville</th>
                                        <th class="text-center">Nb Besoins</th>
                                        <th class="text-end">Qté Totale</th>
                                        <th class="text-end">Déjà Reçu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($besoinsParVille as $ville): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ville['ville_libele']) ?></strong></td>
                                        <td class="text-center"><span class="badge bg-danger"><?= $ville['nb_besoins'] ?></span></td>
                                        <td class="text-end"><?= number_format($ville['quantite_totale'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($ville['deja_recu'], 0, ',', ' ') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-center gap-3">
                    <form method="POST" action="/don/simuler" class="d-inline">
                        <button type="submit" class="btn btn-primary btn-lg" <?= empty($stockDisponible) ? 'disabled' : '' ?>>
                            <i class="bi bi-calculator"></i> Simuler la Distribution
                        </button>
                    </form>
                    
                    <form method="POST" action="/don/distribuer-auto" class="d-inline">
                        <button type="submit" class="btn btn-success btn-lg" <?= empty($stockDisponible) ? 'disabled' : '' ?>>
                            <i class="bi bi-lightning"></i> Distribution Automatique
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultat de la Simulation -->
    <?php if (!empty($resultatSimulation)): ?>
    <div class="card card-distribution mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Résultat de la Simulation</h5>
            <span class="badge bg-light text-primary">
                <?= $resultatSimulation['totalDistributions'] ?? 0 ?> distribution(s) - 
                <?= number_format($resultatSimulation['totalQuantite'] ?? 0, 0, ',', ' ') ?> unité(s) - 
                <?= number_format($resultatSimulation['totalMontant'] ?? 0, 0, ',', ' ') ?> Ar
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($resultatSimulation['parVille'])): ?>
                <div class="row">
                    <?php foreach ($resultatSimulation['parVille'] as $villeId => $villeData): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card ville-card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                    <?= htmlspecialchars($villeData['ville_libele']) ?>
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($villeData['items'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                        <span class="small"><?= htmlspecialchars($item['element_libele']) ?></span>
                                        <span class="badge bg-primary rounded-pill"><?= $item['quantite'] ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer bg-primary text-white text-center py-1">
                                <small>
                                    <strong><?= number_format($villeData['total_quantite'], 0, ',', ' ') ?></strong> unités - 
                                    <strong><?= number_format($villeData['total_montant'], 0, ',', ' ') ?></strong> Ar
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Stock non distribué -->
                <?php if (!empty($resultatSimulation['nonDistribues'])): ?>
                <div class="alert alert-warning mt-3">
                    <h6><i class="bi bi-exclamation-triangle"></i> Stock non distribué :</h6>
                    <ul class="mb-0">
                        <?php foreach ($resultatSimulation['nonDistribues'] as $nd): ?>
                        <li><?= htmlspecialchars($nd['element_libele']) ?> : <?= $nd['quantite'] ?> unités - <em><?= htmlspecialchars($nd['raison']) ?></em></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Bouton de validation -->
                <div class="text-center mt-4">
                    <form method="POST" action="/don/valider" class="d-inline">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check2-circle"></i> Valider et Distribuer
                        </button>
                    </form>
                    <form method="POST" action="/don/annuler-simulation" class="d-inline ms-2">
                        <button type="submit" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Annuler
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Aucune distribution possible avec le stock et les besoins actuels.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
