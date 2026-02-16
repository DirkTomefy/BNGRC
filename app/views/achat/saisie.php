<?php

$villes = $villes ?? [];
$besoinsRestants = $besoinsRestants ?? [];
$tauxFrais = $tauxFrais ?? 10;
$success = $success ?? '';
$error = $error ?? '';
$form = $form ?? [];

$pageTitle = 'Saisie des achats - BNGRC';
$currentPage = 'achat';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-cart-check-fill text-primary"></i> Saisie des achats
                </h1>
                <p class="lead text-secondary">Acheter des besoins avec les dons en argent (frais: <?= $tauxFrais ?>%)</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
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
                                    <i class="bi bi-bag-plus-fill me-2"></i>Nouvel achat
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="/achat/saisie" id="formAchat">
                                    
                                    <!-- Filtre par ville (optionnel) -->
                                    <div class="mb-3">
                                        <label for="filtreVille" class="form-label fw-bold">
                                            <i class="bi bi-funnel text-primary me-1"></i>Filtrer par ville
                                        </label>
                                        <select class="form-select" id="filtreVille">
                                            <option value="">Toutes les villes</option>
                                            <?php foreach ($villes as $ville): ?>
                                                <option value="<?= $ville['id'] ?>">
                                                    <?= htmlspecialchars($ville['libele']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Besoin à acheter -->
                                    <div class="mb-3">
                                        <label for="besoin" class="form-label fw-bold">
                                            <i class="bi bi-box-seam text-primary me-1"></i>Besoin restant
                                        </label>
                                        <select class="form-select" id="besoin" name="besoin" required>
                                            <option value="">Sélectionner un besoin...</option>
                                            <?php foreach ($besoinsRestants as $besoin): ?>
                                                <option value="<?= $besoin['id'] ?>" 
                                                    data-ville="<?= $besoin['idVille'] ?>"
                                                    data-element="<?= htmlspecialchars($besoin['element_libele']) ?>"
                                                    data-type="<?= htmlspecialchars($besoin['type_besoin']) ?>"
                                                    data-qte-restante="<?= $besoin['quantite_restante'] ?>"
                                                    data-pu="<?= $besoin['prix_unitaire'] ?>"
                                                    data-ville-libele="<?= htmlspecialchars($besoin['ville_libele']) ?>">
                                                    <?= htmlspecialchars($besoin['ville_libele']) ?> — 
                                                    <?= htmlspecialchars($besoin['element_libele']) ?> 
                                                    (reste: <?= number_format($besoin['quantite_restante'], 0, ',', ' ') ?> — 
                                                    <?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar/u)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Infos besoin sélectionné -->
                                    <div id="besoinInfo" class="alert alert-info py-2 d-none mb-3">
                                        <small>
                                            <strong>Élément:</strong> <span id="infoElement"></span><br>
                                            <strong>Type:</strong> <span id="infoType"></span><br>
                                            <strong>Ville:</strong> <span id="infoVille"></span><br>
                                            <strong>Qté restante:</strong> <span id="infoQteRestante"></span><br>
                                            <strong>Prix unitaire:</strong> <span id="infoPu"></span> Ar
                                        </small>
                                    </div>

                                    <!-- Quantité -->
                                    <div class="mb-3">
                                        <label for="quantite" class="form-label fw-bold">
                                            <i class="bi bi-123 text-primary me-1"></i>Quantité à acheter
                                        </label>
                                        <input type="number" class="form-control" id="quantite" name="quantite" 
                                               min="1" placeholder="Entrez la quantité..." required>
                                        <div class="form-text">Maximum: <span id="maxQte">-</span></div>
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
                                                <i class="bi bi-calculator me-1"></i>Calcul du montant
                                            </h6>
                                            <table class="table table-sm mb-0">
                                                <tr>
                                                    <td>Montant HT</td>
                                                    <td class="text-end fw-bold"><span id="montantHT">0</span> Ar</td>
                                                </tr>
                                                <tr>
                                                    <td>Frais (<?= $tauxFrais ?>%)</td>
                                                    <td class="text-end text-warning"><span id="montantFrais">0</span> Ar</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><strong>Total TTC</strong></td>
                                                    <td class="text-end fw-bold fs-5"><span id="montantTTC">0</span> Ar</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Boutons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-bag-check me-2"></i>Valider l'achat
                                        </button>
                                        <a href="/achat/liste" class="btn btn-outline-secondary">
                                            <i class="bi bi-list-ul me-2"></i>Voir la liste des achats
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des besoins restants -->
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Besoins restants (Nature & Matériel)
                                    <span class="badge bg-dark ms-2"><?= count($besoinsRestants) ?></span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($besoinsRestants)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-check-circle fs-1 text-success"></i>
                                        <p class="text-muted mt-2">Tous les besoins sont satisfaits !</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Ville</th>
                                                    <th>Élément</th>
                                                    <th>Type</th>
                                                    <th class="text-end">Reste</th>
                                                    <th class="text-end">P.U.</th>
                                                    <th class="text-end">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tableBesoins">
                                                <?php foreach ($besoinsRestants as $besoin): ?>
                                                    <tr data-ville="<?= $besoin['idVille'] ?>">
                                                        <td>
                                                            <span class="badge bg-secondary"><?= htmlspecialchars($besoin['ville_libele']) ?></span>
                                                        </td>
                                                        <td class="fw-bold"><?= htmlspecialchars($besoin['element_libele']) ?></td>
                                                        <td>
                                                            <span class="badge <?= $besoin['type_besoin'] === 'Nature' ? 'bg-success' : 'bg-info' ?>">
                                                                <?= htmlspecialchars($besoin['type_besoin']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end"><?= number_format($besoin['quantite_restante'], 0, ',', ' ') ?></td>
                                                        <td class="text-end"><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                                        <td class="text-end fw-bold">
                                                            <?= number_format($besoin['quantite_restante'] * $besoin['prix_unitaire'], 0, ',', ' ') ?> Ar
                                                        </td>
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

            </div>
        </div>
    </div>

<?php $pageJs = ['/assets/js/achat/saisie.js']; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
