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

/** 
 * @var Router $router 
 * @var Engine $app
 */

// Admin auth
$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'handleLogin']);
$router->get('/admin/logout', [AdminAuthController::class, 'logout']);
$router->get('/admin', [AdminAuthController::class, 'dashboard']);


$router->get('/admin/categories', [CategorieController::class, 'showCategories']);
$router->get('/api/categories', [CategorieController::class, 'getAll']);
$router->post('/api/categories', [CategorieController::class, 'create']);
$router->put('/api/categories/@id', [CategorieController::class, 'update']);
$router->delete('/api/categories/@id', [CategorieController::class, 'delete']);

$router->get('/register', [UserController::class, 'showRegister']);
$router->post('/register', [UserController::class, 'handleRegister']);

// User login
$router->get('/login', [UserController::class, 'showLogin']);
$router->post('/login', [UserController::class, 'handleLogin']);

// Objets
$router->get('/objet', [ObjetController::class, 'index']);
$router->get('/objet/create', [ObjetController::class, 'showCreate']);
$router->get('/objet/find', [ObjetController::class, 'showFind']);
$router->post('/objet/create', [ObjetController::class, 'handleCreate']);
$router->get('/objet/@id/edit', [ObjetController::class, 'showEdit']);
$router->post('/objet/@id/edit', [ObjetController::class, 'handleEdit']);
$router->post('/objet/@id/delete', [ObjetController::class, 'handleDelete']);
$router->get('/objet/explore', [ObjetController::class, 'explore']);
$router->get('/objet/detail/@id', [ObjetController::class, 'detail']);
$router->get('/objet/@id/propose', [ObjetController::class, 'proposeExchange']);
$router->post('/objet/@id/propose', [ObjetController::class, 'handleProposal']);
$router->get('/objet/propositions', [ObjetController::class, 'showMyProposals']);

// Echange (template)
$router->get('/echange', [EchangeController::class, 'index']);

// Statistique (admin)
$router->get('/admin/statistique', [StatistiqueController::class, 'admin']);


//$router->group('', function (Router $router) use ($app) {
//
//	
//}, [SecurityHeadersMiddleware::class]);
