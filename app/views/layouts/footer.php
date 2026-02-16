    </main>

    <!-- Footer -->
    <footer class="footer-custom mt-5">
        <div class="container">
            <div class="row py-4">
                <div class="col-md-4 mb-3 mb-md-0">
                <h6>ETU003911 - ETU003948 - ETU004130</h6>    
                <h6 class="fw-bold text-white">
                        <i class="bi bi-shield-check me-2"></i>BNGRC  
                    </h6>
                    <p class="text-light small mb-0">
                        Bureau National de Gestion des Risques et Catastrophes.<br>
                        Suivi en temps réel des aides humanitaires à Madagascar.
                    </p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="fw-bold text-white">
                        <i class="bi bi-link-45deg me-2"></i>Liens rapides
                    </h6>
                    <ul class="list-unstyled small mb-0">
                        <li><a href="/dashboard" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Tableau de bord</a></li>
                        <li><a href="/besoin/saisie" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Saisie des besoins</a></li>
                        <li><a href="/don/saisie" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Saisie des dons</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Informations
                    </h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="text-light"><i class="bi bi-geo-alt me-1"></i> Antananarivo, Madagascar</li>
                        <li class="text-light"><i class="bi bi-envelope me-1"></i> contact@bngrc.mg</li>
                        <li class="text-light"><i class="bi bi-telephone me-1"></i> +261 20 22 xxx xx</li>
                    </ul>
                </div>
            </div>
            <hr class="border-light my-0">
            <div class="row py-3">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-light">&copy; <?= date('Y') ?> BNGRC — Tous droits réservés</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="text-light">
                        <i class="bi bi-code-slash me-1"></i>Propulsé par FlightPHP
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <?php if (!empty($pageJs)): ?>
        <?php foreach ((array)$pageJs as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
