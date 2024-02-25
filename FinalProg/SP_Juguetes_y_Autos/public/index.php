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


//JUGUETERÍA

// $app->get('/', \Manejadora::class . ':mostrarUsuarios');

// $app->post('/', \Manejadora::class . ':crearJuguete');

// $app->get('/juguetes', \Manejadora::class . ':mostrarJuguetes');

// $app->post('/login[/]', \Manejadora::class . ':loginCorreoYClave')
//     ->add(\MW::class . ':verificarCredencialesEnBd')
//     ->add(\MW::class . ':verificarCamposCorreoYClave');

// $app->get('/login[/]', \Manejadora::class . ':loginToken');


// $app->group('/toys', function (RouteCollectorProxy $grupo) {       
//     $grupo->delete('/{id_juguete}', \Manejadora::class . ':borrarJuguete');
//     $grupo->post('/', \Manejadora::class . ':modificarJuguete');
// })->add(\MW::class . ':verificarjwt');


// $app->group('/tablas', function (RouteCollectorProxy $grupo) {       
//     $grupo->get('/usuarios', \Manejadora::class . ':mostrarUsuarios')
//     ->add(\MW::class . ':TablaUsuariosSinClave');
//     $grupo->post('/usuarios', \Manejadora::class . ':mostrarUsuarios')
//     ->add(\MW::class . ':TablaUsuariosProp')
//     ->add(\MW::class . ':verificarjwt');
//     $grupo->get('/juguetes',  \Manejadora::class . ':mostrarJuguetes')
//     ->add(\MW::class . ':TablaJuguetes');
// });

// $app->post('/usuarios', \Manejadora::class . ':crearUsuario')
//     ->add(\MW::class .':verificarCorreoExistente')
//     ->add(\MW::class .':verificarCamposCorreoYClave');
// //    ->add(\MW::class .':verificarjwt');




//CONCESIONARIA
$app->post('/usuarios', \Manejadora::class . ':crearUsuario')
->add(\MW::class . ':verificarCorreoExistenteAuto')
->add(\MW::class . ':verificarVacio')
->add(\MW::class . ':verificarCorreoClave');

$app->get('/', \Manejadora::class . ':mostrarUsuarios')
->add(\MW::class . ':mostrarUsuariosPropietario')
->add(\MW::class . ':mostrarUsuariosEmpleado')
->add(\MW::class . ':mostrarUsuariosEncargado')
->add(\MW::class . ':verificarjwt');

$app->post('/', \Manejadora::class . ':crearAuto')
->add(\MW::class . ':verificarAuto');

$app->get('/autos', \Manejadora::class . ':mostrarAutos')
->add(\MW::class . ':mostrarAutosPropietario')
->add(\MW::class . ':mostrarAutosEmpleado')
->add(\MW::class . ':mostrarAutosEncargado')
->add(\MW::class . ':verificarjwt');

$app->post('/login', \Manejadora::class . ':loginCorreoYClave')
->add(\MW::class . ':verificarCredencialesEnBdAutos')
->add(\MW::class . ':verificarVacio')
->add(\MW::class . ':verificarCorreoClave');

$app->get('/login', \Manejadora::class . ':loginToken');

$app->put('/', \Manejadora::class . ':modificarAuto')
->add(\MW::class . ':verificarEncargado')
->add(\MW::class . ':verificarjwt');

$app->delete('/', \Manejadora::class . ':borrarAuto')
->add(\MW::class . ':verificarPropietario')
->add(\MW::class . ':verificarjwt');



//KIOSCO

// $app->get('/', \Manejadora::class . ':mostrarArticulos');
// $app->post('/', \Manejadora::class . ':crearArticulo');

// $app->group('/articulo', function (RouteCollectorProxy $grupo) {       
//     $grupo->post('/', \Manejadora::class . ':modificarArticulo');
//     $grupo->delete('/{codigo_barra}', \Manejadora::class . ':borrarArticulo');
// });

// $app->post('/login', \Manejadora::class . ':loginCorreoYClave');
// $app->get('/login', \Manejadora::class . ':loginTokenBearer');

// $app->group('/mw', function (RouteCollectorProxy $grupo){
//     $grupo->post('/login_mw', \Manejadora::class . ':loginCorreoYClave')
//     ->add(\MW::class . ':verificarLegajoClaveVacios');
//     $grupo->post('/crud', \Manejadora::class . ':crearArticulo')
//     ->add(\MW::class . ':verificarCodigoExistente')
//     ->add(\MW::class . ':verificarjwtBearer');
//     $grupo->post('/crud_mw', \Manejadora::class . ':modificarArticulo')
//     ->add(\MW::class . ':verificarjwtBearer');
//     $grupo->delete('/crud_mw/{codigo_barra}', \Manejadora::class . ':borrarArticulo')
//     ->add(\MW::class . ':verificarjwtBearer');
// });

// $app->get('/pdf', \Manejadora::class . ':mostrarListadoPdf')
// ->add(\MW::class . ':verificarjwtBearer');

$app->run();