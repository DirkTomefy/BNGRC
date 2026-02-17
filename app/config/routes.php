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
use app\controller\AchatController;
use app\controller\RecapController;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// Dashboard
$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/region/@id', [DashboardController::class, 'parRegion']);
$router->get('/dashboard/ville/@id', [DashboardController::class, 'parVille']);

// Besoin
$router->get('/besoin/saisie', [BesoinController::class, 'saisie']);
$router->post('/besoin/saisie', [BesoinController::class, 'saisie']);

// Don
$router->get('/don/saisie', [DonController::class, 'saisie']);
$router->post('/don/saisie', [DonController::class, 'saisie']);
$router->post('/don/supprimer', [DonController::class, 'supprimerDuPanier']);
$router->post('/don/vider', [DonController::class, 'viderPanier']);
$router->post('/don/ajouter-stock', [DonController::class, 'ajouterAuStock']);

// Simulation (Distribution depuis le Stock)
$router->get('/don/simulation', [DonController::class, 'simulation']);
$router->post('/don/simuler', [DonController::class, 'simuler']);
$router->post('/don/valider', [DonController::class, 'valider']);
$router->post('/don/distribuer-auto', [DonController::class, 'distribuerAuto']);

// Achat
$router->get('/achat/saisie', [AchatController::class, 'saisie']);
$router->post('/achat/saisie', [AchatController::class, 'saisie']);
$router->post('/achat/supprimer', [AchatController::class, 'supprimerDuPanier']);
$router->post('/achat/vider', [AchatController::class, 'viderPanier']);
$router->post('/achat/valider', [AchatController::class, 'validerAchats']);
$router->get('/achat/liste', [AchatController::class, 'liste']);

// Récapitulation
$router->get('/recap', [RecapController::class, 'index']);
$router->get('/api/recap', [RecapController::class, 'apiRecap']);


//$router->group('', function (Router $router) use ($app) {
//
//	
//}, [SecurityHeadersMiddleware::class]);
