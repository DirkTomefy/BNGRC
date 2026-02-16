<?php

use app\controllers\ApiExampleController;
use app\controllers\ApiSalaireChauffeurControler;
use app\controllers\AdminAuthController;

use app\controllers\CategorieController;
use app\controllers\UserController;
use app\controllers\ObjetController;
use app\controllers\EchangeController;
use app\controllers\StatistiqueController;

use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;
use app\controllers\ApiTrajetController;
use app\controllers\ApiVehiculeController;
use app\controllers\ApiHomeController;

use app\controller\DashboardController;
use app\controller\BesoinController;
use app\controller\DonController;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/region/@id', [DashboardController::class, 'parRegion']);
$router->get('/dashboard/ville/@id', [DashboardController::class, 'parVille']);

$router->get('/besoin/saisie', [BesoinController::class, 'saisie']);
$router->post('/besoin/saisie', [BesoinController::class, 'saisie']);

$router->get('/don/saisie', [DonController::class, 'saisie']);
$router->post('/don/saisie', [DonController::class, 'saisie']);
$router->post('/don/supprimer', [DonController::class, 'supprimerDuPanier']);
$router->post('/don/vider', [DonController::class, 'viderPanier']);
$router->post('/don/distribuer', [DonController::class, 'distribuer']);


//$router->group('', function (Router $router) use ($app) {
//
//	
//}, [SecurityHeadersMiddleware::class]);
