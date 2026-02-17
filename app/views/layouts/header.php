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
    <link href="<?php echo $toUrl('assets/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?php echo $toUrl('assets/bootstrap-icons/font/bootstrap-icons.css'); ?>">
 
    
    <style>
        :root {
            --primary: #00796B;
            --primary-light: #4DD0E1;
            --success: #AED581;
            --text-dark: #263238;
            --bg-white: #FFFFFF;
            --bg-light: #ECEFF1;
            --warning: #FFB300;
            --danger: #D32F2F;
            
            --primary-rgb: 0, 121, 107;
            --primary-light-rgb: 77, 208, 225;
            --success-rgb: 174, 213, 129;
            --warning-rgb: 255, 179, 0;
            --danger-rgb: 211, 47, 47;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-white);
            color: var(--text-dark);
            padding-top: 80px;
            overflow-x: hidden;
        }
        
        /* Navbar ultra moderne */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, #00897b 100%);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid var(--primary-light);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -1px;
            color: white !important;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .navbar-brand i {
            color: var(--primary-light);
            margin-right: 0.5rem;
            font-size: 2rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600;
            padding: 0.7rem 1.2rem !important;
            margin: 0 0.2rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .navbar-nav .nav-link:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
            background: rgba(255,255,255,0.1);
        }
        
        .navbar-nav .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 3px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { width: 0; opacity: 0; }
            to { width: 30px; opacity: 1; }
        }
        
        /* Dropdown stylisé */
        .dropdown-menu {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 0.8rem;
            margin-top: 0.8rem;
            border: 1px solid var(--bg-light);
            animation: dropdownFade 0.3s ease;
        }
        
        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-item {
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            font-weight: 500;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        
        .dropdown-item i {
            width: 1.8rem;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, #b2ebf2 100%);
            color: var(--text-dark);
            transform: translateX(8px);
            padding-left: 2rem;
        }
        
        .dropdown-item:hover i {
            color: var(--primary);
        }
        
        /* Bouton reset stylisé */
        .btn-reset {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .btn-reset:hover {
            background: var(--danger);
            border-color: var(--danger);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(211,47,47,0.4);
        }
        
        /* Date badge */
        .date-badge {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 500;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        /* Page header */
        .page-header {
            background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
            padding: 4rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0,121,107,0.03) 0%, transparent 70%);
            border-radius: 50%;
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .page-header h1 {
            color: var(--primary);
            font-weight: 800;
            font-size: 3rem;
            letter-spacing: -1px;
            position: relative;
            display: inline-block;
        }
        
        .page-header h1 i {
            color: var(--primary-light);
            margin-right: 1rem;
        }
        
        .page-header .lead {
            color: var(--text-dark);
            font-size: 1.3rem;
            font-weight: 300;
            opacity: 0.8;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
            background: white;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,121,107,0.15);
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card-header {
            border-bottom: none;
            padding: 1.5rem;
            font-weight: 600;
        }
        
        .card-header.bg-primary { background: linear-gradient(135deg, var(--primary), #00897b) !important; }
        .card-header.bg-success { background: linear-gradient(135deg, var(--success), #9ccc65) !important; }
        .card-header.bg-warning { background: linear-gradient(135deg, var(--warning), #ffc107) !important; }
        .card-header.bg-danger { background: linear-gradient(135deg, var(--danger), #c62828) !important; }
        .card-header.bg-info { background: linear-gradient(135deg, var(--primary-light), #80deea) !important; }
        
        /* Badges */
        .badge {
            padding: 0.6rem 1rem;
            font-weight: 600;
            border-radius: 50px;
            letter-spacing: 0.3px;
        }
        
        .badge.bg-success { background: var(--success) !important; color: var(--text-dark) !important; }
        .badge.bg-warning { background: var(--warning) !important; color: var(--text-dark) !important; }
        .badge.bg-danger { background: var(--danger) !important; }
        .badge.bg-info { background: var(--primary-light) !important; color: var(--text-dark) !important; }
        
        /* Buttons */
        .btn {
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::after {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #00897b);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #9ccc65);
            color: var(--text-dark);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #ffc107);
            color: var(--text-dark);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c62828);
            color: white;
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Progress bars */
        .progress {
            border-radius: 50px;
            background-color: var(--bg-light);
            overflow: hidden;
            height: 12px;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--bg-light);
            color: var(--text-dark);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1.2rem 1rem;
            border-bottom: 3px solid var(--primary);
        }
        
        .table tbody td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--bg-light);
        }
        
        .table-hover tbody tr:hover {
            background: rgba(0,121,107,0.02);
            transition: background 0.3s ease;
        }
        
        /* Form elements */
        .form-control, .form-select {
            border-radius: 16px;
            border: 2px solid var(--bg-light);
            padding: 0.8rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(77,208,225,0.2);
        }
        
        .form-control-lg {
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        
        /* Alertes */
        .alert {
            border-radius: 20px;
            border: none;
            padding: 1.2rem 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, var(--success), #c5e1a5);
            color: var(--text-dark);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, var(--danger), #ef5350);
            color: white;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, var(--warning), #ffd54f);
            color: var(--text-dark);
        }
        
        .alert-info {
            background: linear-gradient(135deg, var(--primary-light), #b3e5fc);
            color: var(--text-dark);
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--text-dark), #2c3e50);
            color: white;
            margin-top: 5rem;
            position: relative;
            overflow: hidden;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0,121,107,0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: rotate 40s linear infinite;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .footer a:hover {
            color: var(--primary-light);
            padding-left: 8px;
        }
        
        /* Animations */
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-pulse-slow {
            animation: pulseSlow 3s infinite;
        }
        
        @keyframes pulseSlow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Toast notifications */
        .toast {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .toast.bg-success { background: linear-gradient(135deg, var(--success), #9ccc65) !important; }
        .toast.bg-danger { background: linear-gradient(135deg, var(--danger), #c62828) !important; }
        .toast.bg-info { background: linear-gradient(135deg, var(--primary-light), #80deea) !important; }
        
        /* Loading spinner */
        .spinner-border {
            border-width: 0.3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.6rem 1.5rem;
            }
        }
        
        /* Glassmorphism effect */
        .glass-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

    <!-- Navbar ultra moderne -->
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
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'besoin' ? 'active' : '' ?>" href="<?= htmlspecialchars($toUrl('/besoin/saisie')) ?>">
                            <i class="bi bi-plus-circle me-1"></i>Besoins
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
                
                <div class="d-flex align-items-center gap-2">
                    <form method="post" action="<?= htmlspecialchars($toUrl('/reset-all')) ?>" class="m-0" onsubmit="return confirm('Confirmer la réinitialisation ? Cette action va remettre les besoins et les dons à l\'état initial, et vider les achats et distributions.');">
                        <button type="submit" class="btn-reset">
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
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($resetSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($resetError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($resetError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <main class="main-content">