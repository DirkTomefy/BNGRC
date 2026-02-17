<?php

$elements = $elements ?? [];
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];
$panierDons = $panierDons ?? [];
$stockDisponible = $stockDisponible ?? [];
$stockRecap = $stockRecap ?? [];

$baseUrl = Flight::app()->get('flight.base_url');

// Calculer les totaux pour les stats
$totalStock = array_sum(array_column($stockDisponible, 'stock_disponible'));
$totalValeur = array_sum(array_column($stockDisponible, 'valeur_stock'));

$pageTitle = 'Saisie des dons - Madagascar';
$currentPage = 'don';
$pageCss = ['assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-heart-fill text-danger"></i> Saisie des dons
                </h1>
                <p class="lead text-secondary">Madagascar - Enregistrement des dons humanitaires (Stock global)</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Stats Stock -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <i class="bi bi-box-seam text-primary fs-3"></i>
                                <h5 class="mt-2 mb-0"><?= number_format($totalStock, 0, ',', ' ') ?></h5>
                                <small class="text-muted">Unités en stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <i class="bi bi-cash-coin text-success fs-3"></i>
                                <h5 class="mt-2 mb-0"><?= number_format($totalValeur, 0, ',', ' ') ?> Ar</h5>
                                <small class="text-muted">Valeur du stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <i class="bi bi-list-check text-warning fs-3"></i>
                                <h5 class="mt-2 mb-0"><?= count($stockDisponible) ?></h5>
                                <small class="text-muted">Types d'éléments</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages de succès/erreur -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire de saisie -->
                <div class="card form-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-gift-fill me-2"></i>Ajouter un don au panier
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?= $baseUrl ?>/don/saisie">
                            <div class="row g-3">
                                <!-- Élément -->
                                <div class="col-md-12">
                                    <label for="element" class="form-label fw-bold">
                                        <i class="bi bi-box text-success me-1"></i>Élément
                                    </label>
                                    <select class="form-select" id="element" name="element" required>
                                        <option value="">Sélectionner un élément...</option>
                                        <?php foreach ($elements as $element): ?>
                                            <option value="<?= $element['id'] ?>" 
                                                data-pu="<?= $element['pu'] ?>" 
                                                data-type="<?= htmlspecialchars($element['type_besoin_libele']) ?>"
                                                <?= (isset($form['element']) && $form['element'] == $element['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($element['libele']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="elementInfo" class="element-info d-none">
                                        <small class="text-muted">
                                            <strong>Type:</strong> <span id="elementType"></span><br>
                                            <strong>Prix unitaire:</strong> <span id="elementPu"></span> Ar
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Quantité -->
                                <div class="col-md-6">
                                    <label for="quantite" class="form-label fw-bold">
                                        <i class="bi bi-calculator text-success me-1"></i>Quantité
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="quantite" 
                                           name="quantite" 
                                           min="1" 
                                           value="<?= htmlspecialchars($form['quantite'] ?? '') ?>"
                                           placeholder="Entrez la quantité..." 
                                           required>
                                </div>
                                <!-- Aperçu du total -->
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100">
                                        <label class="form-label fw-bold mb-1">
                                            <i class="bi bi-cash-coin text-success me-1"></i>Total estimé
                                        </label>
                                        <div id="apercuTotal" class="alert alert-secondary py-2 px-3 mb-0">
                                            <span id="totalMontant" class="fw-bold fs-5">0</span> <span class="text-muted">Ar</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date -->
                                <div class="col-md-6">
                                    <label for="date" class="form-label fw-bold">
                                        <i class="bi bi-calendar text-success me-1"></i>Date
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date" 
                                           name="date" 
                                           value="<?= htmlspecialchars($form['date'] ?? date('Y-m-d')) ?>"
                                           required>
                                </div>
                                
                                <!-- Description -->
                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">
                                        <i class="bi bi-text-paragraph text-success me-1"></i>Description (optionnelle)
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="2" 
                                              placeholder="Ajoutez une description..."><?= htmlspecialchars($form['description'] ?? '') ?></textarea>
                                </div>
                                
                                <!-- Boutons -->
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-submit btn-lg">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter au panier
                                    </button>
                                    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                                        <i class="bi bi-arrow-left me-2"></i>Retour
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($panierDons)): ?>
                <div class="card mt-4 border-0 shadow">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-cart-fill me-2"></i>Panier de dons en attente
                            <span class="badge bg-dark ms-2"><?= count($panierDons) ?> don(s)</span>
                        </h5>
                        <form method="POST" action="<?= $baseUrl ?>/don/vider" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Vider tout le panier ?')">
                                <i class="bi bi-trash me-1"></i>Vider le panier
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Flux STOCK :</strong> Les dons seront d'abord ajoutés au stock central. 
                            La distribution aux villes se fait ensuite via la <a href="<?= $baseUrl ?>/don/simulation" class="alert-link">page de simulation</a>.
                        </div>

                        <!-- Tableau des dons dans le panier -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Élément</th>
                                        <th>Type</th>
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">P.U.</th>
                                        <th class="text-end">Montant</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalPanier = 0;
                                    foreach ($panierDons as $index => $item): 
                                        $montant = $item['quantite'] * $item['element_pu'];
                                        $totalPanier += $montant;
                                    ?>
                                    <tr>
                                        <td class="text-muted"><?= $index + 1 ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['element_libele']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary badge-custom"><?= htmlspecialchars($item['type_besoin']) ?></span>
                                        </td>
                                        <td class="text-end"><?= number_format($item['quantite'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($item['element_pu'], 0, ',', ' ') ?> Ar</td>
                                        <td class="text-end fw-bold"><?= number_format($montant, 0, ',', ' ') ?> Ar</td>
                                        <td><small class="text-muted"><?= date('d/m/Y', strtotime($item['date'])) ?></small></td>
                                        <td class="text-center">
                                            <form method="POST" action="<?= $baseUrl ?>/don/supprimer" class="d-inline">
                                                <input type="hidden" name="index" value="<?= $index ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php if (!empty($item['description'])): ?>
                                    <tr>
                                        <td></td>
                                        <td colspan="7" class="py-1">
                                            <small class="text-muted fst-italic">
                                                <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars($item['description']) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Total</td>
                                        <td class="text-end text-success"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Résumé + bouton ajouter au stock -->
                        <div class="total-footer p-3 mt-3 rounded bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-gift text-success fs-4 me-3"></i>
                                        <div>
                                            <small class="text-secondary">Dons dans le panier</small>
                                            <div class="fw-bold h5 mb-0"><?= count($panierDons) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin text-warning fs-4 me-3"></i>
                                        <div>
                                            <small class="text-secondary">Montant total</small>
                                            <div class="fw-bold h5 mb-0"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton AJOUTER AU STOCK -->
                        <div class="text-center mt-4">
                            <form method="POST" action="<?= $baseUrl ?>/don/ajouter-stock">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow"
                                        onclick="return confirm('Confirmer l\'ajout de <?= count($panierDons) ?> don(s) au stock ?')">
                                    <i class="bi bi-box-arrow-in-down me-2"></i>Ajouter au stock
                                </button>
                            </form>
                            <p class="text-muted mt-2 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Après ajout au stock, allez sur la <a href="<?= $baseUrl ?>/don/simulation">page de simulation</a> pour distribuer aux villes.
                            </p>
                        </div>

                    </div>
                </div>
                <?php else: ?>
                <!-- Panier vide -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Le panier est vide. Ajoutez des dons via le formulaire ci-dessus.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lien vers simulation -->
                <div class="card mt-4 border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-truck fs-1"></i>
                        <h5 class="mt-2">Distribution aux villes</h5>
                        <p class="mb-3">Allez sur la page de simulation pour distribuer le stock disponible aux villes selon les besoins FIFO.</p>
                        <a href="<?= $baseUrl ?>/don/simulation" class="btn btn-light btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>Aller à la simulation
                        </a>
                    </div>
                </div>

                <!-- Informations -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            <i class="bi bi-info-circle me-2"></i>Informations
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-box me-1"></i>
                                    <strong><?= count($elements) ?></strong> éléments disponibles
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    Date du jour: <?= date('d/m/Y') ?>
                                </small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <strong>Flux :</strong> Dons → Stock → Simulation → Distribution aux villes (FIFO)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $pageJs = ['assets/js/don/saisie-total.js']; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
