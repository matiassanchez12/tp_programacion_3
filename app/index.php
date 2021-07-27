<?php
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set("America/Argentina/Buenos_Aires");

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

require_once './middlewares/MWAutentificar.php';
require_once './middlewares/MWPermisos.php';

require_once './controllers/ProductoController.php';
require_once './controllers/ClienteController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/MesaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->setBasePath("/laComanda");
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Add error middleware


// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$app->post('/login', \UsuarioController::class . ':Login');
$app->post('/clientes', \ClienteController::class . ':LoginCliente');

$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{id}', \UsuarioController::class . ':TraerUno');
  $group->post('/nuevo', \UsuarioController::class . ':CargarUno')->add(\MWPermisos::class . ':VerificarSoloSocios');
  $group->post('/actualizar-usuario', \UsuarioController::class . ':ModificarUno');
  $group->post('/borrar', \UsuarioController::class . ':BorrarUno');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoController::class . ':TraerUno');
  $group->post('/nuevo', \PedidoController::class . ':CargarUno')->add(\MWPermisos::class . ':VerificarUsuarioMozo');
  $group->post('/actualizar-estado', \PedidoController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerificarEmpleadoDePedido');
  $group->get('/estados/todos', \PedidoController::class . ':TraerEstados')->add(\MWPermisos::class . ':VerificarSoloSocios');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->get('/tipo/{tipo}', \ProductoController::class . ':TraerProductosPorTipo');
  $group->post('/nuevo', \ProductoController::class . ':CargarUno')->add(\MWPermisos::class . ':VerificarSoloSocios');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{id}', \MesaController::class . ':TraerUno');
  $group->post('/nuevo', \MesaController::class . ':CargarUno')->add(\MWPermisos::class . ':VerificarSoloSocios');
  $group->post('/actualizar-estado', \MesaController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerificarCambioEstadoMesa');
 
  $group->get('/archivos/guardar-csv', \MesaController::class . ':GuardarMesasEnCSV');
  $group->get('/archivos/leer-csv', \MesaController::class . ':LeerMesasEnCSV');
  $group->get('/archivos/descargar-pdf', \MesaController::class . ':GenerarPdf');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/consultas', function (RouteCollectorProxy $group) {
  $group->post('/mesas', \MesaController::class . ':EstadisticaMesas');
  $group->post('/usuarios', \UsuarioController::class . ':EstadisticasUsuarios');
  $group->post('/pedidos', \PedidoController::class . ':EstadisticasPedidos');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->get('[/]', function (Request $request, Response $response) {
  
  $response->getBody()->write("Slim Framework 4 PHP");

  return $response;
});

$app->run();
