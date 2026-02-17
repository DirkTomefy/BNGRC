<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BNGRC - Gestion des besoins et dons') ?></title>
    <?php
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
    ?>
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= htmlspecialchars($toUrl('assets/bootstrap/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= htmlspecialchars($toUrl('assets/bootstrap-icons/font/bootstrap-icons.css')) ?>">
    <!-- Layout CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars($toUrl('assets/css/layout.css')) ?>">
    
    <?php if (!empty($pageCss)): ?>
        <?php foreach ((array)$pageCss as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($toUrl($css)) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-navbar fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= htmlspecialchars($toUrl('/')) ?>">
                <i class="bi bi-shield-check fs-4 me-2"></i>
                <span class="fw-bold">BNGRC</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                    aria-controls="navbarMain" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= htmlspecialchars($toUrl('/dashboard')) ?>">
                            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'besoin' ? 'active' : '' ?>" href="<?= htmlspecialchars($toUrl('/besoin/saisie')) ?>">
                            <i class="bi bi-plus-circle me-1"></i>Saisie besoin
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($currentPage ?? '') === 'don' ? 'active' : '' ?>" href="#" 
                           id="donDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gift me-1"></i>Dons
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="donDropdown">
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($toUrl('/don/saisie')) ?>"><i class="bi bi-plus-circle me-2"></i>Saisie don</a></li>
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($toUrl('/don/simulation')) ?>"><i class="bi bi-play-circle me-2"></i>Simulation FIFO</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($currentPage ?? '') === 'achat' ? 'active' : '' ?>" href="#" 
                           id="achatDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bag me-1"></i>Achats
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="achatDropdown">
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($toUrl('/achat/saisie')) ?>"><i class="bi bi-plus-circle me-2"></i>Nouvel achat</a></li>
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($toUrl('/achat/liste')) ?>"><i class="bi bi-list-ul me-2"></i>Liste des achats</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'recap' ? 'active' : '' ?>" href="<?= htmlspecialchars($toUrl('/recap')) ?>">
                            <i class="bi bi-bar-chart me-1"></i>Récap
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-2">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal (avec marge pour la navbar fixe) -->
    <main class="main-content">
