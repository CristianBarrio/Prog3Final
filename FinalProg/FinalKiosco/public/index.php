<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseMW;
use \Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/poo/Manejadora.php';
require_once __DIR__ . '/../src/poo/MW.php';

$app = AppFactory::create();

$app->get('/', \Manejadora::class . ':mostrarArticulos');
$app->post('/', \Manejadora::class . ':crearArticulo');

$app->group('/articulo', function (RouteCollectorProxy $grupo) {       
    $grupo->post('/', \Manejadora::class . ':modificarArticulo');
    $grupo->delete('/{codigo_barra}', \Manejadora::class . ':borrarArticulo');
});

$app->post('/login', \Manejadora::class . ':loginCorreoYClave');
$app->get('/login', \Manejadora::class . ':loginTokenBearer');

$app->group('/mw', function (RouteCollectorProxy $grupo){
    $grupo->post('/login_mw', \Manejadora::class . ':loginCorreoYClave')
    ->add(\MW::class . ':verificarLegajoClaveVacios');
    $grupo->post('/crud', \Manejadora::class . ':crearArticulo')
    ->add(\MW::class . ':verificarCodigoExistente')
    ->add(\MW::class . ':verificarjwtBearer');
    $grupo->post('/crud_mw', \Manejadora::class . ':modificarArticulo')
    ->add(\MW::class . ':verificarjwtBearer');
    $grupo->delete('/crud_mw/{codigo_barra}', \Manejadora::class . ':borrarArticulo')
    ->add(\MW::class . ':verificarjwtBearer');
});

$app->get('/pdf', \Manejadora::class . ':mostrarListadoPdf')
->add(\MW::class . ':verificarjwtBearer');

$app->run();