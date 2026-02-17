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
$pageCss = ['assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-cart-check-fill text-primary"></i> Saisie des achats
                </h1>
                <p class="lead text-secondary">Acheter des fournitures avec l'argent des dons</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Budget disponible -->
                <div class="card mb-4 border-0 shadow-sm bg-success text-white">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center text-md-start">
                                <h5 class="mb-0">
                                    <i class="bi bi-wallet2 me-2"></i>Budget disponible (Dons en argent)
                                </h5>
                            </div>
                            <div class="col-md-6 text-center text-md-end">
                                <h2 class="mb-0"><?= number_format($argentDisponible, 0, ',', ' ') ?> Ar</h2>
                            </div>
                        </div>
                        <?php if ($totalPanier > 0): ?>
                        <div class="row mt-2">
                            <div class="col-12 text-center">
                                <small>
                                    Après validation du panier : 
                                    <strong><?= number_format($argentDisponible - $totalPanier, 0, ',', ' ') ?> Ar</strong>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
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

                <div class="row">
                    <!-- Formulaire -->
                    <div class="col-lg-5">
                        <div class="card form-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-bag-plus-fill me-2"></i>Ajouter un achat
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="<?= $baseUrl ?>/achat/saisie" id="formAchat">
                                    
                                    <!-- Élément à acheter -->
                                    <div class="mb-3">
                                        <label for="element" class="form-label fw-bold">
                                            <i class="bi bi-box-seam text-primary me-1"></i>Élément
                                        </label>
                                        <select class="form-select" id="element" name="element" required>
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
                                    <div id="elementInfo" class="alert alert-info py-2 d-none mb-3">
                                        <small>
                                            <strong>Type:</strong> <span id="infoType"></span><br>
                                            <strong>Prix unitaire:</strong> <span id="infoPu"></span> Ar
                                        </small>
                                    </div>

                                    <!-- Quantité -->
                                    <div class="mb-3">
                                        <label for="quantite" class="form-label fw-bold">
                                            <i class="bi bi-123 text-primary me-1"></i>Quantité
                                        </label>
                                        <input type="number" class="form-control" id="quantite" name="quantite" 
                                               min="1" placeholder="Entrez la quantité..." required>
                                    </div>

                                    <!-- Prix unitaire (automatique depuis l'élément) -->
                                    <div class="mb-3">
                                        <label for="prix_unitaire" class="form-label fw-bold">
                                            <i class="bi bi-currency-exchange text-primary me-1"></i>Prix unitaire (Ar)
                                        </label>
                                        <input type="number" step="0.01" class="form-control bg-light" id="prix_unitaire" name="prix_unitaire" 
                                               min="0.01" readonly required>
                                    </div>

                                    <!-- Taux de frais -->
                                    <div class="mb-3">
                                        <label for="taux_frais" class="form-label fw-bold">
                                            <i class="bi bi-percent text-primary me-1"></i>Taux de frais (%)
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="taux_frais" name="taux_frais" 
                                               min="0" max="100" value="10" placeholder="10" required>
                                        <div class="form-text">Frais appliqués sur le montant HT (par défaut 10%)</div>
                                    </div>

                                    <!-- Date -->
                                    <div class="mb-3">
                                        <label for="date" class="form-label fw-bold">
                                            <i class="bi bi-calendar text-primary me-1"></i>Date
                                        </label>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <!-- Aperçu du calcul -->
                                    <div class="card bg-light mb-3">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-2">
                                                <i class="bi bi-calculator me-1"></i>Aperçu du coût
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted">Montant HT</small><br>
                                                    <span class="fw-bold" id="montantHT">0</span> <small>Ar</small>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Frais</small><br>
                                                    <span class="fw-bold text-warning" id="montantFrais">0</span> <small>Ar</small>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Total TTC</small><br>
                                                    <span class="fs-5 fw-bold text-primary" id="montantTotal">0</span> <small>Ar</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boutons -->
                                    <div class="d-grid gap-2">
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
                        <div class="card">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-cart3 me-2"></i>Panier d'achats
                                    <span class="badge bg-dark ms-2"><?= count($panierAchats) ?></span>
                                </h5>
                                <?php if (!empty($panierAchats)): ?>
                                <form method="POST" action="<?= $baseUrl ?>/achat/vider" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Vider le panier ?')">
                                        <i class="bi bi-trash me-1"></i>Vider
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($panierAchats)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Le panier est vide</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Élément</th>
                                                    <th class="text-end">Qté</th>
                                                    <th class="text-end">P.U.</th>
                                                    <th class="text-end">Frais</th>
                                                    <th class="text-end">TTC</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($panierAchats as $index => $item): ?>
                                                <tr>
                                                    <td class="text-muted"><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($item['element_libele']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($item['type_besoin'] ?? '') ?></small>
                                                    </td>
                                                    <td class="text-end"><?= number_format($item['quantite'], 0, ',', ' ') ?></td>
                                                    <td class="text-end"><?= number_format($item['prix_unitaire'], 0, ',', ' ') ?></td>
                                                    <td class="text-end"><small class="text-muted"><?= number_format($item['taux_frais'] ?? 10, 1) ?>%</small></td>
                                                    <td class="text-end fw-bold"><?= number_format($item['montant_ttc'] ?? $item['montant'], 0, ',', ' ') ?></td>
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
                                                    <td colspan="5" class="text-end fw-bold">TOTAL TTC</td>
                                                    <td class="text-end fw-bold"><?= number_format($totalPanier, 0, ',', ' ') ?> Ar</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Validation -->
                                    <div class="p-3 text-center">
                                        <?php if ($totalPanier <= $argentDisponible): ?>
                                            <form method="POST" action="<?= $baseUrl ?>/achat/valider">
                                                <button type="submit" class="btn btn-success btn-lg"
                                                        onclick="return confirm('Confirmer les achats pour <?= number_format($totalPanier, 0, ',', ' ') ?> Ar ?')">
                                                    <i class="bi bi-check-circle me-2"></i>Valider les achats
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-danger mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                Budget insuffisant ! Retirez des articles du panier.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Info flux -->
                        <div class="card mt-3 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-info-circle me-2"></i>Flux des achats
                                </h6>
                                <small class="text-muted">
                                    <strong>1.</strong> Les dons en argent alimentent le budget<br>
                                    <strong>2.</strong> Les achats puisent dans ce budget<br>
                                    <strong>3.</strong> Les articles achetés vont au stock global<br>
                                    <strong>4.</strong> Le stock est distribué aux villes via la simulation
                                </small>
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
        const montantHT = document.getElementById('montantHT');
        const montantFrais = document.getElementById('montantFrais');
        const montantTotal = document.getElementById('montantTotal');

        function updateInfo() {
            const option = selectElement.options[selectElement.selectedIndex];
            if (option && option.value) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const type = option.dataset.type || '';
                
                document.getElementById('infoType').textContent = type;
                document.getElementById('infoPu').textContent = pu.toLocaleString('fr-FR');
                elementInfo.classList.remove('d-none');
                
                // Toujours mettre le prix unitaire depuis l'élément
                inputPrix.value = pu;
            } else {
                elementInfo.classList.add('d-none');
            }
            updateMontant();
        }

        function updateMontant() {
            const qte = parseInt(inputQuantite.value) || 0;
            const prix = parseFloat(inputPrix.value) || 0;
            const taux = parseFloat(inputTaux.value) || 0;
            const ht = qte * prix;
            const frais = ht * (taux / 100);
            const ttc = ht + frais;
            montantHT.textContent = ht.toLocaleString('fr-FR');
            montantFrais.textContent = frais.toLocaleString('fr-FR');
            montantTotal.textContent = ttc.toLocaleString('fr-FR');
        }

        selectElement.addEventListener('change', updateInfo);
        inputQuantite.addEventListener('input', updateMontant);
        inputPrix.addEventListener('input', updateMontant);
        inputTaux.addEventListener('input', updateMontant);
    });
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
