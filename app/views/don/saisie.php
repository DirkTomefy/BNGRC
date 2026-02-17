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
$pageCss = [];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-heart-fill text-danger me-3"></i>Saisie des dons
            </h1>
            <p class="lead text-secondary">
                Madagascar - Enregistrement des dons humanitaires
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Stats Stock -->
                <div class="row g-4 mb-5">
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
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Types d'éléments</h6>
                                        <h3 class="display-6 fw-bold mb-0"><?= count($stockDisponible) ?></h3>
                                    </div>
                                    <i class="bi bi-list-check fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages de succès/erreur -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire de saisie et Panier -->
                <div class="row g-4">
                    <!-- Formulaire -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                            <div class="card-header bg-success text-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-gift-fill me-2"></i>Ajouter un don au panier
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="<?= $baseUrl ?>/don/saisie" id="formDon">
                                    <!-- Élément -->
                                    <div class="mb-4">
                                        <label for="element" class="form-label fw-bold">
                                            <i class="bi bi-box text-success me-1"></i>Élément
                                        </label>
                                        <select class="form-select form-select-lg" id="element" name="element" required>
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
                                        
                                        <!-- Info élément -->
                                        <div id="elementInfo" class="mt-3 p-3 bg-light rounded-3 d-none">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Type:</span>
                                                <span class="fw-bold" id="elementType"></span>
                                            </div>
                                            <div class="d-flex justify-content-between mt-2">
                                                <span class="text-muted">Prix unitaire:</span>
                                                <span class="fw-bold text-success" id="elementPu">0</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mb-4">
                                        <!-- Quantité -->
                                        <div class="col-md-6">
                                            <label for="quantite" class="form-label fw-bold">
                                                <i class="bi bi-calculator text-success me-1"></i>Quantité
                                            </label>
                                            <input type="number" 
                                                   class="form-control form-control-lg" 
                                                   id="quantite" 
                                                   name="quantite" 
                                                   min="1" 
                                                   value="<?= htmlspecialchars($form['quantite'] ?? '') ?>"
                                                   placeholder="Quantité..." 
                                                   required>
                                        </div>
                                        
                                        <!-- Date -->
                                        <div class="col-md-6">
                                            <label for="date" class="form-label fw-bold">
                                                <i class="bi bi-calendar text-success me-1"></i>Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="date" 
                                                   name="date" 
                                                   value="<?= htmlspecialchars($form['date'] ?? date('Y-m-d')) ?>"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <!-- Aperçu du total -->
                                    <div class="card bg-light border-0 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold mb-3">Aperçu du don</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Total estimé :</span>
                                                <span class="fs-3 fw-bold text-success" id="totalMontant">0</span>
                                            </div>
                                            <small class="text-muted d-block mt-2">Ar</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="mb-4">
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
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                                        </button>
                                        <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-2"></i>Retour
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Panier -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-cart-fill me-2"></i>Panier de dons
                                </h5>
                                <div>
                                    <span class="badge bg-dark rounded-pill me-2"><?= count($panierDons) ?> don(s)</span>
                                    <?php if (!empty($panierDons)): ?>
                                        <form method="POST" action="<?= $baseUrl ?>/don/vider" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Vider tout le panier ?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-body p-0">
                                <?php if (empty($panierDons)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">Le panier est vide</p>
                                        <small class="text-muted">Ajoutez des dons via le formulaire</small>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Élément</th>
                                                    <th class="text-end">Qté</th>
                                                    <th class="text-end">P.U.</th>
                                                    <th class="text-end">Total</th>
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
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($item['element_libele']) ?></div>
                                                        <small class="text-muted"><?= date('d/m/Y', strtotime($item['date'])) ?></small>
                                                        <?php if (!empty($item['description'])): ?>
                                                            <br><small class="text-muted fst-italic"><i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($item['description']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end fw-bold"><?= number_format($item['quantite'], 0, ',', ' ') ?></td>
                                                    <td class="text-end"><?= number_format($item['element_pu'], 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-end text-success fw-bold"><?= number_format($montant, 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-center">
                                                        <form method="POST" action="<?= $baseUrl ?>/don/supprimer" class="d-inline">
                                                            <input type="hidden" name="index" value="<?= $index ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-dark">
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold">TOTAL PANIER</td>
                                                    <td class="text-end fw-bold text-warning"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <!-- Bouton Ajouter au stock -->
                                    <div class="p-4 bg-light">
                                        <div class="alert alert-info mb-4">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Flux :</strong> Les dons seront ajoutés au stock central avant distribution.
                                        </div>
                                        
                                        <form method="POST" action="<?= $baseUrl ?>/don/ajouter-stock">
                                            <button type="submit" class="btn btn-primary btn-lg w-100"
                                                    onclick="return confirm('Confirmer l\'ajout de <?= count($panierDons) ?> don(s) au stock ?')">
                                                <i class="bi bi-box-arrow-in-down me-2"></i>Ajouter au stock
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Carte lien vers simulation -->
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body p-4 text-center">
                                <i class="bi bi-truck fs-1"></i>
                                <h5 class="mt-3">Distribution aux villes</h5>
                                <p class="mb-4">Distribuez le stock disponible aux villes selon les besoins</p>
                                <a href="<?= $baseUrl ?>/don/simulation" class="btn btn-light btn-lg px-5">
                                    <i class="bi bi-arrow-right me-2"></i>Aller à la simulation
                                </a>
                            </div>
                        </div>
                        
                        <!-- Informations -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-info-circle text-primary me-2"></i>Informations
                                </h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-box text-success me-2"></i>
                                            <span><strong><?= count($elements) ?></strong> éléments disponibles</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-check text-info me-2"></i>
                                            <span><?= date('d/m/Y') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-light rounded-3">
                                    <small class="text-muted">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        <strong>Processus :</strong> Dons → Stock → Simulation → Distribution (FIFO)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('element');
        const inputQuantite = document.getElementById('quantite');
        const elementInfo = document.getElementById('elementInfo');
        const elementType = document.getElementById('elementType');
        const elementPu = document.getElementById('elementPu');
        const totalMontant = document.getElementById('totalMontant');
        
        function updateElementInfo() {
            const option = selectElement.options[selectElement.selectedIndex];
            if (option && option.value) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const type = option.dataset.type || '';
                
                elementType.textContent = type;
                elementPu.textContent = pu.toLocaleString('fr-FR');
                elementInfo.classList.remove('d-none');
                
                updateTotal();
            } else {
                elementInfo.classList.add('d-none');
            }
        }
        
        function updateTotal() {
            const option = selectElement.options[selectElement.selectedIndex];
            const qte = parseInt(inputQuantite.value) || 0;
            
            if (option && option.value && qte > 0) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const total = qte * pu;
                totalMontant.textContent = total.toLocaleString('fr-FR');
            } else {
                totalMontant.textContent = '0';
            }
        }
        
        selectElement.addEventListener('change', updateElementInfo);
        inputQuantite.addEventListener('input', updateTotal);
    });
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>