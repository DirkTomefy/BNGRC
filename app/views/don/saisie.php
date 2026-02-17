<?php

$elements = $elements ?? [];
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];
$panierDons = $panierDons ?? [];
$previsualisation = $previsualisation ?? [];

// Messages flash depuis la session (après redirect distribution)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['don_success'])) {
    $success = $_SESSION['don_success'];
    unset($_SESSION['don_success']);
}
if (!empty($_SESSION['don_error'])) {
    $error = $_SESSION['don_error'];
    unset($_SESSION['don_error']);
}

$pageTitle = 'Saisie des dons - Madagascar';
$currentPage = 'don';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-heart-fill text-danger"></i> Saisie des dons
                </h1>
                <p class="lead text-secondary">Madagascar - Enregistrement des dons humanitaires</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
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
                        <form method="POST" action="<?= htmlspecialchars($toUrl('/don/saisie')) ?>">
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
                                    <a href="<?= htmlspecialchars($toUrl('/dashboard')) ?>" class="btn btn-outline-secondary btn-lg ms-2">
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
                        <form method="POST" action="<?= htmlspecialchars($toUrl('/don/vider')) ?>" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Vider tout le panier ?')">
                                <i class="bi bi-trash me-1"></i>Vider le panier
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Distribution FIFO :</strong> Les dons seront répartis automatiquement
                            aux villes selon les besoins les plus anciens en premier (First In, First Out).
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
                                    <?php foreach ($panierDons as $index => $item): ?>
                                    <tr>
                                        <td class="text-muted"><?= $index + 1 ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['element_libele']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary badge-custom"><?= htmlspecialchars($item['type_besoin']) ?></span>
                                        </td>
                                        <td class="text-end"><?= number_format($item['quantite'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($item['element_pu'], 0, ',', ' ') ?> Ar</td>
                                        <td class="text-end fw-bold"><?= number_format($item['quantite'] * $item['element_pu'], 0, ',', ' ') ?> Ar</td>
                                        <td><small class="text-muted"><?= date('d/m/Y', strtotime($item['date'])) ?></small></td>
                                        <td class="text-center">
                                            <form method="POST" action="<?= htmlspecialchars($toUrl('/don/supprimer')) ?>" class="d-inline">
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
                            </table>
                        </div>

                        <!-- Prévisualisation de la répartition FIFO par ville -->
                        <?php if (!empty($previsualisation['parVille'])): ?>
                        <div class="mt-4">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-geo-alt-fill me-2"></i>Prévisualisation de la répartition par ville
                            </h6>
                            
                            <div class="row">
                                <?php foreach ($previsualisation['parVille'] as $villeId => $villeData): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white py-2">
                                            <strong>
                                                <i class="bi bi-pin-map me-1"></i>
                                                <?= htmlspecialchars($villeData['ville_libele']) ?>
                                            </strong>
                                            <span class="badge bg-light text-dark float-end">
                                                <?= count($villeData['items']) ?> élément(s)
                                            </span>
                                        </div>
                                        <div class="card-body p-2">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tbody>
                                                    <?php foreach ($villeData['items'] as $dist): ?>
                                                    <tr>
                                                        <td class="ps-2"><?= htmlspecialchars($dist['element_libele']) ?></td>
                                                        <td class="text-end pe-2">
                                                            <span class="badge bg-primary">
                                                                <?= number_format($dist['quantite'], 0, ',', ' ') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-2 text-success fw-bold">
                                                            <?= number_format($dist['montant'], 0, ',', ' ') ?> Ar
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="border-top">
                                                    <tr class="fw-bold">
                                                        <td class="ps-2">Total</td>
                                                        <td class="text-end pe-2">
                                                            <span class="badge bg-dark">
                                                                <?= number_format($villeData['total_quantite'], 0, ',', ' ') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-2 text-success">
                                                            <?= number_format($villeData['total_montant'], 0, ',', ' ') ?> Ar
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Éléments non distribués (avertissement) -->
                        <?php if (!empty($previsualisation['nonDistribues'])): ?>
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>Attention : Éléments sans destination
                            </h6>
                            <ul class="mb-0 small">
                                <?php foreach ($previsualisation['nonDistribues'] as $nonDist): ?>
                                <li>
                                    <strong><?= htmlspecialchars($nonDist['element_libele']) ?></strong> 
                                    (qté: <?= number_format($nonDist['quantite'], 0, ',', ' ') ?>) 
                                    — <?= htmlspecialchars($nonDist['raison']) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Résumé global + bouton distribuer -->
                        <div class="total-footer p-3 mt-3 rounded">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-gift text-success fs-4 me-3"></i>
                                        <div>
                                            <small class="text-secondary">Dons dans le panier</small>
                                            <div class="fw-bold h5 mb-0"><?= count($panierDons) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt text-primary fs-4 me-3"></i>
                                        <div>
                                            <small class="text-secondary">Villes bénéficiaires</small>
                                            <div class="fw-bold h5 mb-0"><?= count($previsualisation['parVille'] ?? []) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin text-warning fs-4 me-3"></i>
                                        <div>
                                            <small class="text-secondary">Montant total</small>
                                            <div class="fw-bold h5 mb-0">
                                                <?= number_format($previsualisation['totalMontant'] ?? 0, 0, ',', ' ') ?> Ar
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton DISTRIBUER -->
                        <div class="text-center mt-4">
                            <form method="POST" action="<?= htmlspecialchars($toUrl('/don/distribuer')) ?>">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow"
                                        onclick="return confirm('Confirmer la distribution FIFO de <?= count($panierDons) ?> don(s) vers <?= count($previsualisation['parVille'] ?? []) ?> ville(s) ?')">
                                    <i class="bi bi-truck me-2"></i>Distribuer les dons (FIFO)
                                </button>
                            </form>
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
                                <strong>Mode FIFO :</strong> Les dons sont automatiquement distribués aux besoins les plus anciens en priorité.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $pageJs = ['/assets/js/don/saisie-total.js']; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
