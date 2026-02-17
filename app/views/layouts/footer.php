</main>

    <?php
        if (isset($toUrl) === false) {
            $baseUrl = '';
            if (class_exists('Flight')) {
                $baseUrl = Flight::get('flight.base_url');
            }
            $baseUrl = rtrim((string)($baseUrl ?? ''), '/');
            $toUrl = static function ($path) use ($baseUrl) {
                $path = (string)$path;
                if ($path === '' || preg_match('#^(https?:)?//#', $path) === 1) {
                    return $path;
                }
                if ($path[0] === '/') {
                    return $baseUrl . $path;
                }
                return $baseUrl . '/' . $path;
            };
        }
    ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row py-5">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-check fs-2 me-3 text-warning"></i>
                        <div>
                            <h5 class="fw-bold text-white mb-0">BNGRC</h5>
                            <small class="text-white-50">Bureau National de Gestion des Risques et Catastrophes</small>
                        </div>
                    </div>
                    <p class="text-white-50 small mb-0">
                        Suivi en temps réel des aides humanitaires à Madagascar. 
                        Transparence et efficacité dans la gestion des dons et besoins.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-primary me-2">ETU003911</span>
                        <span class="badge bg-primary me-2">ETU003948</span>
                        <span class="badge bg-primary">ETU004130</span>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h6 class="fw-bold text-white mb-3">
                        <i class="bi bi-link-45deg me-2 text-warning"></i>Liens rapides
                    </h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= htmlspecialchars($toUrl('/dashboard')) ?>" class="text-white-50 text-decoration-none"><i class="bi bi-chevron-right me-1 small"></i>Tableau de bord</a></li>
                        <li class="mb-2"><a href="<?= htmlspecialchars($toUrl('/besoin/saisie')) ?>" class="text-white-50 text-decoration-none"><i class="bi bi-chevron-right me-1 small"></i>Saisie des besoins</a></li>
                        <li class="mb-2"><a href="<?= htmlspecialchars($toUrl('/don/saisie')) ?>" class="text-white-50 text-decoration-none"><i class="bi bi-chevron-right me-1 small"></i>Saisie des dons</a></li>
                        <li class="mb-2"><a href="<?= htmlspecialchars($toUrl('/don/simulation')) ?>" class="text-white-50 text-decoration-none"><i class="bi bi-chevron-right me-1 small"></i>Simulation FIFO</a></li>
                        <li class="mb-2"><a href="<?= htmlspecialchars($toUrl('/recap')) ?>" class="text-white-50 text-decoration-none"><i class="bi bi-chevron-right me-1 small"></i>Récapitulatif</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <h6 class="fw-bold text-white mb-3">
                        <i class="bi bi-info-circle me-2 text-warning"></i>Informations
                    </h6>
                    <ul class="list-unstyled">
                        <li class="mb-2 text-white-50">
                            <i class="bi bi-geo-alt me-2"></i> Antananarivo, Madagascar
                        </li>
                        <li class="mb-2 text-white-50">
                            <i class="bi bi-envelope me-2"></i> contact@bngrc.mg
                        </li>
                        <li class="mb-2 text-white-50">
                            <i class="bi bi-telephone me-2"></i> +261 20 22 123 45
                        </li>
                        <li class="mb-2 text-white-50">
                            <i class="bi bi-clock me-2"></i> Lun-Ven: 08h00 - 17h00
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold text-white mb-3">
                        <i class="bi bi-newspaper me-2 text-warning"></i>Actualités
                    </h6>
                    <p class="text-white-50 small mb-2">
                        <i class="bi bi-dot me-1"></i>Nouveau système de distribution FIFO
                    </p>
                    <p class="text-white-50 small mb-2">
                        <i class="bi bi-dot me-1"></i>Suivi des achats en temps réel
                    </p>
                    <p class="text-white-50 small mb-3">
                        <i class="bi bi-dot me-1"></i>Dashboard amélioré
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="text-white-50"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="border-white-10 my-0">
            
            <div class="row py-4">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-white-50">
                        <i class="bi bi-c-circle me-1"></i> <?= date('Y') ?> BNGRC - Tous droits réservés
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="text-white-50">
                        <i class="bi bi-code-slash me-1"></i>Version 2.0 - Propulsé par FlightPHP
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toast notification system -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    
    <script>
        // Toast notification function
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
            
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }
        
        // Format number function
        function formatNumber(num) {
            return new Intl.NumberFormat('fr-FR').format(num);
        }
        
        // Loading button function
        function setLoading(button, isLoading, text = null) {
            if (isLoading) {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || text || button.innerHTML;
            }
        }
    </script>
    
    <?php if (!empty($pageJs)): ?>
        <?php if (is_array($pageJs)): ?>
            <?php foreach ($pageJs as $js): ?>
                <script src="<?= htmlspecialchars($toUrl($js)) ?>"></script>
            <?php endforeach; ?>
        <?php elseif (is_string($pageJs) && strpos(trim($pageJs), '<script') !== false): ?>
            <?= $pageJs ?>
        <?php elseif (is_string($pageJs)): ?>
            <script src="<?= htmlspecialchars($toUrl($pageJs)) ?>"></script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>