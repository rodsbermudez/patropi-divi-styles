<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Login routes
$routes->get('/', 'Home::index');
$routes->post('/', 'AuthController::login');
$routes->get('/login', 'Home::index');
$routes->get('/users', 'UsersController::index');
$routes->post('/users', 'UsersController::store');
$routes->post('/users/(:num)', 'UsersController::update/$1');
$routes->delete('/users/(:num)', 'UsersController::delete/$1');
$routes->get('/auth/login', 'AuthController::login');
$routes->post('/auth/login', 'AuthController::login');
$routes->get('/auth/logout', 'AuthController::logout');

// Dashboard
$routes->get('/dashboard', 'Home::index');
$routes->get('/styles', 'StylesController::index');
$routes->get('/sync', 'SyncController::index');
$routes->get('/sync/download-plugin', 'SyncController::downloadPlugin');
$routes->post('/sync/send', 'SyncController::send');

// Websites
$routes->get('/websites', 'WebsitesController::index');
$routes->post('/websites', 'WebsitesController::store');
$routes->delete('/websites/(:num)', 'WebsitesController::delete/$1');