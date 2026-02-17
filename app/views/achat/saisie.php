<?php

$elements = $elements ?? [];
$argentDisponible = $argentDisponible ?? 0;
$panierAchats = $panierAchats ?? [];
$totalPanier = $totalPanier ?? 0;
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];

$baseUrl = Flight::app()->get('flight.base_url');

$pageTitle = 'Saisie des achats - BNGRC';
$currentPage = 'achat';
$pageCss = [];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-cart-check-fill text-primary me-3"></i>Saisie des achats
            </h1>
            <p class="lead text-secondary">
                Acheter des fournitures avec l'argent des dons
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">

                <!-- Budget disponible -->
                <div class="card border-0 shadow-sm bg-success text-white mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-wallet2 fs-1 me-4"></i>
                                    <div>
                                        <h5 class="fw-bold mb-1">Budget disponible</h5>
                                        <p class="mb-0 text-white-50">Dons en argent disponibles pour les achats</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h2 class="display-5 fw-bold mb-0"><?= number_format($argentDisponible, 0, ',', ' ') ?> Ar</h2>
                                <?php if ($totalPanier > 0): ?>
                                    <small class="text-white-50">
                                        Après validation : <?= number_format($argentDisponible - $totalPanier, 0, ',', ' ') ?> Ar
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages de succès/erreur -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Formulaire -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-bag-plus-fill me-2"></i>Ajouter un achat
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="<?= $baseUrl ?>/achat/saisie" id="formAchat">
                                    
                                    <!-- Élément à acheter -->
                                    <div class="mb-4">
                                        <label for="element" class="form-label fw-bold">
                                            <i class="bi bi-box-seam text-primary me-1"></i>Élément
                                        </label>
                                        <select class="form-select form-select-lg" id="element" name="element" required>
                                            <option value="">Sélectionner un élément...</option>
                                            <?php foreach ($elements as $element): ?>
                                                <option value="<?= $element['id'] ?>" 
                                                    data-pu="<?= $element['pu'] ?>"
                                                    data-type="<?= htmlspecialchars($element['type_besoin_libele'] ?? '') ?>">
                                                    <?= htmlspecialchars($element['libele']) ?> 
                                                    (<?= number_format($element['pu'], 0, ',', ' ') ?> Ar/u)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Infos élément sélectionné -->
                                    <div id="elementInfo" class="alert alert-info bg-info bg-opacity-10 border-0 d-none mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Type :</span>
                                            <span class="fw-bold" id="infoType"></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Prix unitaire :</span>
                                            <span class="fw-bold" id="infoPu">0</span>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <!-- Quantité -->
                                        <div class="col-md-6">
                                            <label for="quantite" class="form-label fw-bold">
                                                <i class="bi bi-123 text-primary me-1"></i>Quantité
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="quantite" name="quantite" 
                                                   min="1" placeholder="Quantité..." required>
                                        </div>

                                        <!-- Prix unitaire -->
                                        <div class="col-md-6">
                                            <label for="prix_unitaire" class="form-label fw-bold">
                                                <i class="bi bi-currency-exchange text-primary me-1"></i>Prix unitaire
                                            </label>
                                            <input type="number" step="0.01" class="form-control form-control-lg bg-light" 
                                                   id="prix_unitaire" name="prix_unitaire" readonly required>
                                        </div>
                                    </div>

                                    <!-- Taux de frais -->
                                    <div class="mb-4">
                                        <label for="taux_frais" class="form-label fw-bold">
                                            <i class="bi bi-percent text-primary me-1"></i>Taux de frais (%)
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-lg" 
                                               id="taux_frais" name="taux_frais" 
                                               min="0" max="100" value="10" required>
                                        <div class="form-text">Frais appliqués sur le montant HT</div>
                                    </div>

                                    <!-- Date -->
                                    <div class="mb-4">
                                        <label for="date" class="form-label fw-bold">
                                            <i class="bi bi-calendar text-primary me-1"></i>Date
                                        </label>
                                        <input type="date" class="form-control form-control-lg" id="date" name="date" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <!-- Aperçu du calcul -->
                                    <div class="card bg-light border-0 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold mb-3">Aperçu du coût</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Montant HT :</span>
                                                <span class="fw-bold" id="montantHT">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Frais (10%) :</span>
                                                <span class="fw-bold text-warning" id="montantFrais">0</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">Total TTC :</span>
                                                <span class="fs-4 fw-bold text-primary" id="montantTotal">0</span>
                                            </div>
                                            <small class="text-muted d-block mt-2">Ar</small>
                                        </div>
                                    </div>

                                    <!-- Boutons -->
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
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
                                    <i class="bi bi-cart3 me-2"></i>Panier d'achats
                                </h5>
                                <div>
                                    <span class="badge bg-dark rounded-pill me-2"><?= count($panierAchats) ?> article(s)</span>
                                    <?php if (!empty($panierAchats)): ?>
                                        <form method="POST" action="<?= $baseUrl ?>/achat/vider" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Vider le panier ?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-body p-0">
                                <?php if (empty($panierAchats)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">Le panier est vide</p>
                                        <small class="text-muted">Ajoutez des achats via le formulaire</small>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Élément</th>
                                                    <th class="text-end">Qté</th>
                                                    <th class="text-end">P.U.</th>
                                                    <th class="text-end">Frais</th>
                                                    <th class="text-end">TTC</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($panierAchats as $index => $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($item['element_libele']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($item['type_besoin'] ?? '') ?></small>
                                                    </td>
                                                    <td class="text-end"><?= number_format($item['quantite'], 0, ',', ' ') ?></td>
                                                    <td class="text-end"><?= number_format($item['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-end"><span class="badge bg-warning"><?= number_format($item['taux_frais'] ?? 10, 1) ?>%</span></td>
                                                    <td class="text-end fw-bold text-success"><?= number_format($item['montant_ttc'] ?? $item['montant'], 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-center">
                                                        <form method="POST" action="<?= $baseUrl ?>/achat/supprimer" class="d-inline">
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
                                                    <td colspan="4" class="text-end fw-bold">TOTAL TTC</td>
                                                    <td class="text-end fw-bold text-warning fs-5"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Validation -->
                                    <div class="p-4 bg-light">
                                        <?php if ($totalPanier <= $argentDisponible): ?>
                                            <form method="POST" action="<?= $baseUrl ?>/achat/valider">
                                                <button type="submit" class="btn btn-success btn-lg w-100"
                                                        onclick="return confirm('Confirmer les achats pour <?= number_format($totalPanier, 0, ',', ' ') ?> Ar ?')">
                                                    <i class="bi bi-check-circle me-2"></i>Valider les achats
                                                </button>
                                            </form>
                                            <p class="text-center text-muted small mt-3">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Les articles achetés seront ajoutés au stock
                                            </p>
                                        <?php else: ?>
                                            <div class="alert alert-danger mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                Budget insuffisant ! (Manque <?= number_format($totalPanier - $argentDisponible, 0, ',', ' ') ?> Ar)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Info flux -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-info-circle text-info me-2"></i>Flux des achats
                                </h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-1-circle-fill text-success"></i>
                                            </div>
                                            <small>Dons en argent → Budget</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-2-circle-fill text-primary"></i>
                                            </div>
                                            <small>Achats → Stock global</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-3-circle-fill text-warning"></i>
                                            </div>
                                            <small>Stock → Distribution aux villes</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-4-circle-fill text-info"></i>
                                            </div>
                                            <small>Simulation FIFO disponible</small>
                                        </div>
                                    </div>
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
        const inputPrix = document.getElementById('prix_unitaire');
        const inputTaux = document.getElementById('taux_frais');
        const elementInfo = document.getElementById('elementInfo');
        const infoType = document.getElementById('infoType');
        const infoPu = document.getElementById('infoPu');
        const montantHT = document.getElementById('montantHT');
        const montantFrais = document.getElementById('montantFrais');
        const montantTotal = document.getElementById('montantTotal');

        function updateInfo() {
            const option = selectElement.options[selectElement.selectedIndex];
            if (option && option.value) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const type = option.dataset.type || '';
                
                infoType.textContent = type;
                infoPu.textContent = pu.toLocaleString('fr-FR') + ' Ar';
                elementInfo.classList.remove('d-none');
                
                inputPrix.value = pu;
                updateMontant();
            } else {
                elementInfo.classList.add('d-none');
                resetMontant();
            }
        }

        function updateMontant() {
            const qte = parseInt(inputQuantite.value) || 0;
            const prix = parseFloat(inputPrix.value) || 0;
            const taux = parseFloat(inputTaux.value) || 0;
            
            if (qte > 0 && prix > 0) {
                const ht = qte * prix;
                const frais = ht * (taux / 100);
                const ttc = ht + frais;
                
                montantHT.textContent = ht.toLocaleString('fr-FR');
                montantFrais.textContent = frais.toLocaleString('fr-FR');
                montantTotal.textContent = ttc.toLocaleString('fr-FR');
            } else {
                resetMontant();
            }
        }
        
        function resetMontant() {
            montantHT.textContent = '0';
            montantFrais.textContent = '0';
            montantTotal.textContent = '0';
        }

        selectElement.addEventListener('change', updateInfo);
        inputQuantite.addEventListener('input', updateMontant);
        inputTaux.addEventListener('input', updateMontant);
        
        // Initialiser si un élément est pré-sélectionné
        if (selectElement.value) {
            updateInfo();
        }
    });
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>