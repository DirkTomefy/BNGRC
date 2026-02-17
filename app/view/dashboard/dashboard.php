<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BNGRC</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        .stat-card { transition: all 0.3s ease; border-radius: 10px; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .card-besoins { border-left: 4px solid #dc3545; }
        .card-dons { border-left: 4px solid #28a745; }
        .card-achats { border-left: 4px solid #17a2b8; }
        .card-distribue { border-left: 4px solid #007bff; }
        .card-stock { border-left: 4px solid #ffc107; }
        .card-argent { border-left: 4px solid #6f42c1; }
        .progress { height: 8px; }
        .ville-card { border-radius: 8px; transition: all 0.2s ease; }
        .ville-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .table-hover tbody tr:hover { background-color: rgba(0,123,255,0.08); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/"><i class="bi bi-speedometer2"></i> BNGRC Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/don/saisie"><i class="bi bi-gift"></i> Saisie Dons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/don/simulation"><i class="bi bi-truck"></i> Distribution</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/achat/saisie"><i class="bi bi-cart"></i> Achats</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/besoin/saisie"><i class="bi bi-clipboard-check"></i> Besoins</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Tableau de Bord</h1>

    <!-- Récapitulatif Global -->
    <?php if (!empty($recapGlobal)): ?>
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-besoins h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Besoins Total</h6>
                            <h4 class="mb-0 text-danger"><?= number_format($recapGlobal['montant_besoins_total'] ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clipboard-check fs-1 text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-dons h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Dons Total</h6>
                            <h4 class="mb-0 text-success"><?= number_format($recapGlobal['montant_dons_total'] ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-gift fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-achats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Achats Total</h6>
                            <h4 class="mb-0 text-info"><?= number_format($recapGlobal['montant_achats_total'] ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cart fs-1 text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-distribue h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Distribué Total</h6>
                            <h4 class="mb-0 text-primary"><?= number_format($recapGlobal['montant_distribue_total'] ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-truck fs-1 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-stock h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Stock Disponible</h6>
                            <h4 class="mb-0 text-warning"><?= number_format($totalValeurStock ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box-seam fs-1 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card card-argent h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Budget Achats</h6>
                            <h4 class="mb-0 text-purple" style="color: #6f42c1;"><?= number_format($argentDisponible ?? 0, 0, ',', ' ') ?> <small>Ar</small></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-exchange fs-1 opacity-50" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Stock par Type -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock par Type de Besoin</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stockParType)): ?>
                        <div class="alert alert-info mb-0">Aucun stock disponible.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Dons</th>
                                        <th class="text-end">Achats</th>
                                        <th class="text-end">Distribué</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stockParType as $type): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($type['type_besoin']) ?></strong></td>
                                        <td class="text-end text-success"><?= number_format($type['total_dons'], 0, ',', ' ') ?></td>
                                        <td class="text-end text-info"><?= number_format($type['total_achats'], 0, ',', ' ') ?></td>
                                        <td class="text-end text-primary"><?= number_format($type['total_distribue'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><strong><?= number_format($type['stock_disponible'], 0, ',', ' ') ?></strong></td>
                                        <td class="text-end"><?= number_format($type['valeur_stock'], 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="/don/simulation" class="btn btn-warning btn-sm"><i class="bi bi-truck"></i> Distribuer le Stock</a>
                </div>
            </div>
        </div>

        <!-- Besoins Non Satisfaits -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Villes avec Besoins Non Satisfaits</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($besoinsParVille)): ?>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle"></i> Tous les besoins sont satisfaits !
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Ville</th>
                                        <th class="text-center">Besoins</th>
                                        <th class="text-end">Qté Demandée</th>
                                        <th class="text-end">Déjà Reçu</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($besoinsParVille as $ville): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ville['ville_libele']) ?></strong></td>
                                        <td class="text-center"><span class="badge bg-danger"><?= $ville['nb_besoins'] ?></span></td>
                                        <td class="text-end"><?= number_format($ville['quantite_totale'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($ville['deja_recu'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($ville['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="/don/simulation" class="btn btn-danger btn-sm"><i class="bi bi-lightning"></i> Distribuer Automatiquement</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dernières Distributions -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Dernières Distributions</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($dernieresDistributions)): ?>
                        <div class="alert alert-info m-3">Aucune distribution effectuée.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Ville</th>
                                        <th>Élément</th>
                                        <th class="text-end">Qté</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dernieresDistributions as $dist): ?>
                                    <tr>
                                        <td><small><?= date('d/m/Y', strtotime($dist['date'])) ?></small></td>
                                        <td><?= htmlspecialchars($dist['ville_libele']) ?></td>
                                        <td><?= htmlspecialchars($dist['element_libele']) ?></td>
                                        <td class="text-end"><?= number_format($dist['quantite'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($dist['montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Récapitulatif par Ville -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Récapitulatif par Ville</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($donneesParVille)): ?>
                        <div class="alert alert-info m-3">Aucune donnée disponible.</div>
                    <?php else: ?>
                        <div class="accordion accordion-flush" id="accordionVilles">
                            <?php foreach ($donneesParVille as $index => $ville): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ville<?= $ville['ville_id'] ?>">
                                        <span class="me-auto">
                                            <strong><?= htmlspecialchars($ville['ville']) ?></strong>
                                            <small class="text-muted ms-2">(<?= htmlspecialchars($ville['region']) ?>)</small>
                                        </span>
                                        <?php 
                                        $taux = $ville['totaux']['quantite_besoin'] > 0 
                                            ? round(($ville['totaux']['quantite_donnee'] / $ville['totaux']['quantite_besoin']) * 100) 
                                            : 0;
                                        $badgeClass = $taux >= 100 ? 'bg-success' : ($taux >= 50 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?= $badgeClass ?> ms-2"><?= $taux ?>%</span>
                                    </button>
                                </h2>
                                <div id="ville<?= $ville['ville_id'] ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body p-2">
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: <?= min($taux, 100) ?>%"></div>
                                        </div>
                                        <div class="row text-center small">
                                            <div class="col-4">
                                                <div class="text-danger">Besoin</div>
                                                <strong><?= number_format($ville['totaux']['quantite_besoin'], 0, ',', ' ') ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-success">Reçu</div>
                                                <strong><?= number_format($ville['totaux']['quantite_donnee'], 0, ',', ' ') ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-warning">Restant</div>
                                                <strong><?= number_format($ville['totaux']['quantite_restante'], 0, ',', ' ') ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions Rapides</h5>
                </div>
                <div class="card-body d-flex flex-wrap justify-content-center gap-3">
                    <a href="/don/saisie" class="btn btn-success btn-lg">
                        <i class="bi bi-gift"></i> Ajouter un Don
                    </a>
                    <a href="/achat/saisie" class="btn btn-info btn-lg text-white">
                        <i class="bi bi-cart-plus"></i> Faire un Achat
                    </a>
                    <a href="/don/simulation" class="btn btn-primary btn-lg">
                        <i class="bi bi-truck"></i> Distribuer aux Villes
                    </a>
                    <a href="/besoin/saisie" class="btn btn-danger btn-lg">
                        <i class="bi bi-clipboard-plus"></i> Saisir un Besoin
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<footer class="bg-dark text-white text-center py-3 mt-4">
    <small>&copy; <?= date('Y') ?> BNGRC - Bureau National de Gestion des Risques et Catastrophes</small>
</footer>

<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>