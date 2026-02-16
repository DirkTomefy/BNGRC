<?php

$recap = $recap ?? [];

$pageTitle = 'Récapitulatif - BNGRC';
$currentPage = 'recap';
$pageCss = ['/assets/css/besoin/saisie.css'];
include __DIR__ . '/../layouts/header.php';
?>

    <!-- En-tête -->
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold header-title">
                    <i class="bi bi-bar-chart-fill text-primary"></i> Récapitulatif
                </h1>
                <p class="lead text-secondary">
                    Vue d'ensemble des besoins et dons
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Bouton Actualiser -->
                <div class="text-end mb-4">
                    <button type="button" class="btn btn-primary" id="btnActualiser" onclick="actualiserRecap()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Actualiser
                    </button>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-5" id="cardsContainer">
                    
                    <!-- Besoins Totaux -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-box-seam me-2"></i>Besoins Totaux
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="bi bi-clipboard-data display-3 text-primary"></i>
                                </div>
                                <h2 class="display-5 fw-bold text-primary" id="besoinsTotaux">
                                    <?= number_format($recap['besoins_totaux'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="lead text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant total des besoins déclarés</small>
                            </div>
                        </div>
                    </div>

                    <!-- Besoins Satisfaits -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-check-circle me-2"></i>Besoins Satisfaits
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="bi bi-check2-all display-3 text-success"></i>
                                </div>
                                <h2 class="display-5 fw-bold text-success" id="besoinsSatisfaits">
                                    <?= number_format($recap['besoins_satisfaits'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="lead text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant des besoins couverts par des dons</small>
                            </div>
                        </div>
                    </div>

                    <!-- Besoins Restants -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Besoins Restants
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="bi bi-hourglass-split display-3 text-danger"></i>
                                </div>
                                <h2 class="display-5 fw-bold text-danger" id="besoinsRestants">
                                    <?= number_format($recap['besoins_restants'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="lead text-muted mb-0">Ariary</p>
                                <small class="text-muted">Montant des besoins non couverts</small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Statistiques complémentaires -->
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-gift me-2"></i>Dons reçus
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="display-5 fw-bold text-info" id="totalDons">
                                    <?= number_format($recap['total_dons'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="lead text-muted mb-0">Ariary</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="bi bi-bag me-2"></i>Achats effectués
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="display-5 fw-bold text-warning" id="totalAchats">
                                    <?= number_format($recap['total_achats'] ?? 0, 0, ',', ' ') ?>
                                </h2>
                                <p class="lead text-muted mb-0">Ariary (TTC)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="card mb-5">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Progression de couverture des besoins
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $total = ($recap['besoins_totaux'] ?? 0);
                        $satisfaits = ($recap['besoins_satisfaits'] ?? 0);
                        $pourcentage = $total > 0 ? round(($satisfaits / $total) * 100, 1) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Couverture des besoins</span>
                                <strong id="pourcentageCouverture"><?= $pourcentage ?>%</strong>
                            </div>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                     id="progressBar"
                                     role="progressbar" 
                                     style="width: <?= $pourcentage ?>%;" 
                                     aria-valuenow="<?= $pourcentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span id="progressText"><?= $pourcentage ?>%</span>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <span class="badge bg-success">
                                    <i class="bi bi-check me-1"></i>Satisfaits: <span id="satisfaitsMontant"><?= number_format($satisfaits, 0, ',', ' ') ?></span> Ar
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-danger">
                                    <i class="bi bi-x me-1"></i>Restants: <span id="restantsMontant"><?= number_format($total - $satisfaits, 0, ',', ' ') ?></span> Ar
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="/besoin/saisie" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-plus-circle me-2"></i>Nouveau besoin
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/don/saisie" class="btn btn-outline-success btn-lg w-100">
                            <i class="bi bi-gift me-2"></i>Nouveau don
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/achat/saisie" class="btn btn-outline-warning btn-lg w-100">
                            <i class="bi bi-bag me-2"></i>Nouvel achat
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/don/simulation" class="btn btn-outline-info btn-lg w-100">
                            <i class="bi bi-play-circle me-2"></i>Simulation FIFO
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php
$pageJs = <<<JS
<script>
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    async function actualiserRecap() {
        const btn = document.getElementById('btnActualiser');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';

        try {
            const response = await fetch('/api/recap');
            const data = await response.json();

            // Mettre à jour les valeurs
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

            // Animation flash
            document.getElementById('cardsContainer').classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                document.getElementById('cardsContainer').classList.remove('animate__animated', 'animate__pulse');
            }, 500);

        } catch (error) {
            console.error('Erreur lors de l\'actualisation:', error);
            alert('Erreur lors de l\'actualisation des données');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Actualiser';
        }
    }
</script>
JS;

include __DIR__ . '/../layouts/footer.php';
?>
