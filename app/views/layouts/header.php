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
      ?>
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= htmlspecialchars($toUrl('assets/bootstrap/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= htmlspecialchars($toUrl('assets/bootstrap-icons/font/bootstrap-icons.css')) ?>">
    <!-- Layout CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars($toUrl('assets/css/layout.css')) ?>">

    <!-- Bootstrap 5 CSS -->

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            padding-top: 76px;
        }
        
        .navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.75rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .navbar-brand i {
            color: #ffc107;
            margin-right: 0.5rem;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.125rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .navbar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.15);
            color: white !important;
            transform: translateY(-1px);
        }
        
        .navbar-nav .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white !important;
            position: relative;
        }
        
        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -0.75rem;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background-color: #ffc107;
            border-radius: 3px;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
            animation: fadeInDown 0.2s;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-item {
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .dropdown-item i {
            width: 1.5rem;
            color: #0d6efd;
        }
        
        .dropdown-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        
        .btn-reset {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-reset:hover {
            background-color: #dc3545;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220,53,69,0.3);
        }
        
        .date-badge {
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            margin-left: 1rem;
        }
        
        .alert {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .main-content {
            min-height: calc(100vh - 300px);
        }
        
        /* Page headers */
        .page-header {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            color: #0d6efd;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-header .lead {
            color: #6c757d;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-header {
            font-weight: 600;
            border-bottom: none;
            padding: 1rem 1.5rem;
        }
        
        .card-header.bg-primary { background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important; }
        .card-header.bg-success { background: linear-gradient(135deg, #198754, #157347) !important; }
        .card-header.bg-danger { background: linear-gradient(135deg, #dc3545, #bb2d3b) !important; }
        .card-header.bg-warning { background: linear-gradient(135deg, #ffc107, #ffca2c) !important; }
        .card-header.bg-info { background: linear-gradient(135deg, #0dcaf0, #31d2f2) !important; }
        .card-header.bg-dark { background: linear-gradient(135deg, #212529, #1a1e21) !important; }
        
        /* Buttons */
        .btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-lg {
            padding: 0.75rem 2rem;
            border-radius: 3rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #198754, #157347);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            border: none;
            color: #212529;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #0dcaf0, #31d2f2);
            border: none;
            color: #212529;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        
        /* Badges */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            border-radius: 2rem;
        }
        
        /* Progress bars */
        .progress {
            border-radius: 1rem;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            transition: width 0.5s ease;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #212529 0%, #1a1e21 100%);
            color: white;
            margin-top: 4rem;
            padding-top: 2rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .footer a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .footer hr {
            border-color: rgba(255,255,255,0.1);
        }
        
        /* Animations */
        .animate-pulse {
            animation: pulse 0.5s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
    
    <?php if (!empty($pageCss)): ?>
        <?php foreach ((array)$pageCss as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($toUrl($css)) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

    <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $resetSuccess = '';
        $resetError = '';

        if (!empty($_SESSION['reset_success'])) {
            $resetSuccess = (string)$_SESSION['reset_success'];
            unset($_SESSION['reset_success']);
        }
        if (!empty($_SESSION['reset_error'])) {
            $resetError = (string)$_SESSION['reset_error'];
            unset($_SESSION['reset_error']);
        }
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?= htmlspecialchars($toUrl('/')) ?>">
                <i class="bi bi-shield-check"></i>
                BNGRC
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
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
                    <form method="post" action="<?= htmlspecialchars($toUrl('/reset-all')) ?>" class="me-2" onsubmit="return confirm('Confirmer la réinitialisation ? Cette action va remettre les besoins et les dons à l\'état initial, et vider les achats et distributions.');">
                        <button type="submit" class="btn btn-reset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                        </button>
                    </form>
                    <span class="date-badge">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <?php if (!empty($resetSuccess) || !empty($resetError)): ?>
        <div class="container mt-3">
            <?php if (!empty($resetSuccess)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($resetSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($resetError)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($resetError) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <main class="main-content">