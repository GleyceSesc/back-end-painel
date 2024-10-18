<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->post('login', 'Acesso::Login', ['filter' => 'log_acesso']);
$routes->get('login', 'Acesso::Login', ['filter' => 'log_acesso']);

$routes->get('logout', 'Acesso::logout');

$routes->group('cliente', function (RouteCollection $routes) {
    $routes->get('index', 'Cliente::index');
    $routes->get('show/(:num)', 'Cliente::show/$1');
    $routes->get('receitaWS/(:num)', 'Cliente::ReceitaWs/$1');
    $routes->post('cadastrar/upload', 'Cliente::Upload');
    $routes->post('cadastrar', 'Cliente::create');
    $routes->delete('(:num)', 'Cliente::excluir/$1');
    $routes->put('editar', 'Cliente::editar');
});

$routes->resource('menu');
$routes->resource('modulo');
$routes->get('validar/usuario/(:any)', 'Usuario::ValidarUsuario/$1');

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->resource('usuario');
    $routes->resource('unidade');
});

$routes->group('cidade', function (RouteCollection $routes) {
    $routes->get('listar', 'Unidade::showCities');
    $routes->get('unidade/(:num)', 'Unidade::showUnities/$1');
});

$routes->resource('perfil');
$routes->resource('sub_categoria');
$routes->get('categoria', 'sub_categoria::categoria');

$routes->get('teste', 'Teste::group');