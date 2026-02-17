<?php

$villes = $villes ?? [];
$elements = $elements ?? [];
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];
$pageTitle = 'Saisie des besoins - Madagascar';
$currentPage = 'besoin';
$pageCss = [];
include __DIR__ . '/../layouts/header.php';

?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-plus-circle-fill text-primary me-3"></i>Saisie des besoins
            </h1>
            <p class="lead text-secondary">
                Madagascar - Enregistrement des besoins humanitaires
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
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
                
                <!-- Formulaire de saisie -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-plus me-2"></i>Nouveau besoin
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="" id="formBesoin">
                            <div class="row g-4">
                                <!-- Ville -->
                                <div class="col-md-6">
                                    <label for="ville" class="form-label fw-bold">
                                        <i class="bi bi-geo-alt text-primary me-1"></i>Ville
                                    </label>
                                    <select class="form-select form-select-lg" id="ville" name="ville" required>
                                        <option value="">Sélectionner une ville...</option>
                                        <?php foreach ($villes as $ville): ?>
                                            <option value="<?= $ville['id'] ?>" <?= (isset($form['ville']) && $form['ville'] == $ville['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ville['libele']) ?> 
                                                (<?= htmlspecialchars($ville['region_libele']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Élément -->
                                <div class="col-md-6">
                                    <label for="element" class="form-label fw-bold">
                                        <i class="bi bi-box text-primary me-1"></i>Élément
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
                                            <span class="text-muted">Type :</span>
                                            <span class="fw-bold" id="elementType"></span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span class="text-muted">Prix unitaire :</span>
                                            <span class="fw-bold text-primary" id="elementPu">0</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quantité -->
                                <div class="col-md-6">
                                    <label for="quantite" class="form-label fw-bold">
                                        <i class="bi bi-calculator text-primary me-1"></i>Quantité
                                    </label>
                                    <input type="number" 
                                           class="form-control form-control-lg" 
                                           id="quantite" 
                                           name="quantite" 
                                           min="1" 
                                           value="<?= htmlspecialchars($form['quantite'] ?? '') ?>"
                                           placeholder="Entrez la quantité..." 
                                           required>
                                </div>
                                
                                <!-- Date -->
                                <div class="col-md-6">
                                    <label for="date" class="form-label fw-bold">
                                        <i class="bi bi-calendar text-primary me-1"></i>Date
                                    </label>
                                    <input type="date" 
                                           class="form-control form-control-lg" 
                                           id="date" 
                                           name="date" 
                                           value="<?= htmlspecialchars($form['date'] ?? date('Y-m-d')) ?>"
                                           required>
                                </div>
                                
                                <!-- Aperçu du montant -->
                                <div class="col-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Montant estimé du besoin</h6>
                                                    <small class="text-muted">Basé sur le prix unitaire</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="display-6 fw-bold text-primary" id="montantTotal">0</span>
                                                    <span class="text-muted">Ar</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Boutons -->
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-save me-2"></i>Enregistrer le besoin
                                    </button>
                                    <a href="<?= htmlspecialchars($toUrl('/dashboard')) ?>" class="btn btn-outline-secondary btn-lg px-5 ms-3">
                                        <i class="bi bi-arrow-left me-2"></i>Retour
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations supplémentaires -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>Informations
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-building text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= count($villes) ?></div>
                                        <small class="text-muted">Villes disponibles</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-box text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= count($elements) ?></div>
                                        <small class="text-muted">Éléments disponibles</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-calendar-check text-info fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= date('d/m/Y') ?></div>
                                        <small class="text-muted">Date du jour</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rappel du processus -->
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <small class="text-muted">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <strong>Processus :</strong> Besoins enregistrés → Dons collectés → Distribution aux villes
                            </small>
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
        const montantTotal = document.getElementById('montantTotal');
        
        function updateElementInfo() {
            const option = selectElement.options[selectElement.selectedIndex];
            if (option && option.value) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const type = option.dataset.type || '';
                
                elementType.textContent = type;
                elementPu.textContent = pu.toLocaleString('fr-FR') + ' Ar';
                elementInfo.classList.remove('d-none');
                
                updateMontant();
            } else {
                elementInfo.classList.add('d-none');
                montantTotal.textContent = '0';
            }
        }
        
        function updateMontant() {
            const option = selectElement.options[selectElement.selectedIndex];
            const qte = parseInt(inputQuantite.value) || 0;
            
            if (option && option.value && qte > 0) {
                const pu = parseFloat(option.dataset.pu) || 0;
                const total = qte * pu;
                montantTotal.textContent = total.toLocaleString('fr-FR');
            } else {
                montantTotal.textContent = '0';
            }
        }
        
        selectElement.addEventListener('change', updateElementInfo);
        inputQuantite.addEventListener('input', updateMontant);
        
        // Initialiser si des valeurs sont pré-sélectionnées
        if (selectElement.value) {
            updateElementInfo();
        }
    });
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>