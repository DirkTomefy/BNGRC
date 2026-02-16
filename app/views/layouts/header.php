<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BNGRC - Gestion des besoins et dons') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="assets/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Layout CSS -->
    <link rel="stylesheet" href="assets/css/layout.css">
    
    <?php if (!empty($pageCss)): ?>
        <?php foreach ((array)$pageCss as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-navbar fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
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
                        <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/dashboard">
                            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'besoin' ? 'active' : '' ?>" href="/besoin/saisie">
                            <i class="bi bi-plus-circle me-1"></i>Saisie besoin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'don' ? 'active' : '' ?>" href="/don/saisie">
                            <i class="bi bi-gift me-1"></i>Saisie don
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
