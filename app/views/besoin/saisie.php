<?php

$villes = $villes ?? [];
$elements = $elements ?? [];
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];
$pageTitle = 'Saisie des besoins - Madagascar';
$currentPage = 'besoin';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';

?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-plus-circle-fill"></i> Saisie des besoins
                </h1>
                <p class="lead text-secondary">Madagascar - Enregistrement des besoins humanitaires</p>
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-plus me-2"></i>Nouveau besoin
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <!-- Ville -->
                                <div class="col-md-6">
                                    <label for="ville" class="form-label fw-bold">
                                        <i class="bi bi-geo-alt text-primary me-1"></i>Ville
                                    </label>
                                    <select class="form-select" id="ville" name="ville" required>
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
                                        <i class="bi bi-calculator text-primary me-1"></i>Quantité
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
                                
                                <!-- Date -->
                                <div class="col-md-6">
                                    <label for="date" class="form-label fw-bold">
                                        <i class="bi bi-calendar text-primary me-1"></i>Date
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date" 
                                           name="date" 
                                           value="<?= htmlspecialchars($form['date'] ?? date('Y-m-d')) ?>"
                                           required>
                                </div>
                                
                                <!-- Bouton de soumission -->
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-submit btn-lg">
                                        <i class="bi bi-save me-2"></i>Enregistrer le besoin
                                    </button>
                                    <a href="<?= htmlspecialchars($toUrl('/dashboard')) ?>" class="btn btn-outline-secondary btn-lg ms-2">
                                        <i class="bi bi-arrow-left me-2"></i>Retour au tableau de bord
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations supplémentaires -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            <i class="bi bi-info-circle me-2"></i>Informations
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i>
                                    <strong><?= count($villes) ?></strong> villes disponibles
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-box me-1"></i>
                                    <strong><?= count($elements) ?></strong> éléments disponibles
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    Date du jour: <?= date('d/m/Y') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $pageJs = ['/assets/js/besoin/saisie.js']; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>