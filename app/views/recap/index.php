<?php

$recap = $recap ?? [];

$pageTitle = 'Récapitulatif - BNGRC';
$currentPage = 'recap';
$pageCss = [];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-bar-chart-fill text-primary me-3"></i>Récapitulatif
            </h1>
            <p class="lead text-secondary">
                Vue d'ensemble des besoins et dons
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Bouton Actualiser -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="text-muted">
                            <i class="bi bi-clock-history me-1"></i>Dernière mise à jour : <?= date('d/m/Y H:i') ?>
                        </span>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnActualiser" onclick="actualiserRecap()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Actualiser
                    </button>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row g-4 mb-5" id="cardsContainer">
                    
                    <!-- Besoins Totaux -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-clipboard-data fs-1 text-primary"></i>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold">Besoins Totaux</h6>
                                <h2 class="display-6 fw-bold text-primary mb-2" id="besoinsTotaux">
                                    <?= number_format($recap['besoins_totaux'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant total des besoins déclarés</small>
                            </div>
                            <div class="card-footer bg-primary bg-opacity-10 border-0 py-2 text-center">
                                <small class="text-primary"><i class="bi bi-arrow-up"></i> +0% depuis hier</small>
                            </div>
                        </div>
                    </div>

                    <!-- Besoins Satisfaits -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-check2-all fs-1 text-success"></i>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold">Besoins Satisfaits</h6>
                                <h2 class="display-6 fw-bold text-success mb-2" id="besoinsSatisfaits">
                                    <?= number_format($recap['besoins_satisfaits'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant des besoins couverts par des dons</small>
                            </div>
                            <div class="card-footer bg-success bg-opacity-10 border-0 py-2 text-center">
                                <small class="text-success"><i class="bi bi-check-circle"></i> Couverts</small>
                            </div>
                        </div>
                    </div>

                    <!-- Besoins Restants -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-hourglass-split fs-1 text-danger"></i>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold">Besoins Restants</h6>
                                <h2 class="display-6 fw-bold text-danger mb-2" id="besoinsRestants">
                                    <?= number_format($recap['besoins_restants'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant des besoins non couverts</small>
                            </div>
                            <div class="card-footer bg-danger bg-opacity-10 border-0 py-2 text-center">
                                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> À couvrir</small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Statistiques complémentaires -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-gift fs-2 text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Dons reçus</h6>
                                        <h3 class="fw-bold text-info mb-0" id="totalDons">
                                            <?= number_format($recap['total_dons'] ?? 0, 0, ',', ' ') ?>
                                        </h3>
                                        <small class="text-muted">Ariary</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-bag fs-2 text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Achats effectués</h6>
                                        <h3 class="fw-bold text-warning mb-0" id="totalAchats">
                                            <?= number_format($recap['total_achats'] ?? 0, 0, ',', ' ') ?>
                                        </h3>
                                        <small class="text-muted">Ariary (TTC)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-dark bg-opacity-10 p-3 me-3">
                                <i class="bi bi-speedometer2 fs-3 text-dark"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Progression de couverture des besoins</h5>
                                <small class="text-muted">Objectif : 100% des besoins couverts</small>
                            </div>
                        </div>
                        
                        <?php
                        $total = ($recap['besoins_totaux'] ?? 0);
                        $satisfaits = ($recap['besoins_satisfaits'] ?? 0);
                        $pourcentage = $total > 0 ? round(($satisfaits / $total) * 100, 1) : 0;
                        ?>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium">Couverture actuelle</span>
                                <strong class="text-primary" id="pourcentageCouverture"><?= $pourcentage ?>%</strong>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                     id="progressBar"
                                     role="progressbar" 
                                     style="width: <?= $pourcentage ?>%;" 
                                     aria-valuenow="<?= $pourcentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span class="fw-bold" id="progressText"><?= $pourcentage ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <span class="badge bg-success mb-2">Satisfaits</span>
                                    <h4 class="text-success mb-0" id="satisfaitsMontant">
                                        <?= number_format($satisfaits, 0, ',', ' ') ?>
                                    </h4>
                                    <small class="text-muted">Ariary</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <span class="badge bg-danger mb-2">Restants</span>
                                    <h4 class="text-danger mb-0" id="restantsMontant">
                                        <?= number_format($total - $satisfaits, 0, ',', ' ') ?>
                                    </h4>
                                    <small class="text-muted">Ariary</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="row g-3 text-center">
                    <div class="col-md-3 col-6">
                        <a href="<?= htmlspecialchars($toUrl('/besoin/saisie')) ?>" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                            <span>Nouveau besoin</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= htmlspecialchars($toUrl('/don/saisie')) ?>" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-gift fs-4 d-block mb-2"></i>
                            <span>Nouveau don</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-bag fs-4 d-block mb-2"></i>
                            <span>Nouvel achat</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= htmlspecialchars($toUrl('/don/simulation')) ?>" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-play-circle fs-4 d-block mb-2"></i>
                            <span>Simulation FIFO</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php
$pageJs = <<<JS
<script>
    async function actualiserRecap() {
        const btn = document.getElementById('btnActualiser');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualisation...';

        try {
            const response = await fetch('/api/recap');
            const data = await response.json();

            // Mettre à jour les valeurs avec animation
            const elements = [
                'besoinsTotaux', 'besoinsSatisfaits', 'besoinsRestants',
                'totalDons', 'totalAchats', 'satisfaitsMontant', 'restantsMontant'
            ];
            
            elements.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.add('animate-pulse');
                    setTimeout(() => el.classList.remove('animate-pulse'), 500);
                }
            });

            document.getElementById('besoinsTotaux').textContent = formatNumber(data.besoins_totaux);
            document.getElementById('besoinsSatisfaits').textContent = formatNumber(data.besoins_satisfaits);
            document.getElementById('besoinsRestants').textContent = formatNumber(data.besoins_restants);
            document.getElementById('totalDons').textContent = formatNumber(data.total_dons);
            document.getElementById('totalAchats').textContent = formatNumber(data.total_achats);

            // Mettre à jour la barre de progression
            const pourcentage = data.besoins_totaux > 0 
                ? Math.round((data.besoins_satisfaits / data.besoins_totaux) * 1000) / 10 
                : 0;
                
            document.getElementById('pourcentageCouverture').textContent = pourcentage + '%';
            document.getElementById('progressBar').style.width = pourcentage + '%';
            document.getElementById('progressBar').setAttribute('aria-valuenow', pourcentage);
            document.getElementById('progressText').textContent = pourcentage + '%';
            document.getElementById('satisfaitsMontant').textContent = formatNumber(data.besoins_satisfaits);
            document.getElementById('restantsMontant').textContent = formatNumber(data.besoins_restants);

            showToast('Données actualisées avec succès', 'success');

        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors de l\'actualisation', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    
    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }
</script>
JS;

include __DIR__ . '/../layouts/footer.php';
?>