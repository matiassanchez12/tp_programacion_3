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

require_once './db/AccesoDatos.php';
require_once './middlewares/MWAutentificar.php';

require_once './controllers/ProductoController.php';
require_once './controllers/ProductoDePedidoController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/MesaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

$app->setBasePath("/laComanda");
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

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

$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\MWAutentificar::class . ':VerificarUsuarioEmpleado');
  $group->get('/{id}', \UsuarioController::class . ':TraerUno');
  $group->get('/roles/{rol}', \UsuarioController::class . ':TraerUsuariosPorRol');
  $group->post('/nuevo', \UsuarioController::class . ':CargarUno');
  $group->post('/actualizar-usuario', \UsuarioController::class . ':ModificarUno');
  $group->post('/borrar', \UsuarioController::class . ':BorrarUno');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoController::class . ':TraerUno');
  $group->post('/nuevo', \PedidoController::class . ':CargarUno')->add(\MWAutentificar::class . ':VerificarUsuarioMozo');
  $group->post('/actualizar-estado', \PedidoController::class . ':ModificarUno');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->get('/tipo/{tipo}', \ProductoController::class . ':TraerProductosPorTipo');
  $group->post('/nuevo', \ProductoController::class . ':CargarUno');
});

$app->group('/productos-pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoDePedidoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoDePedidoController::class . ':TraerUno');
  $group->get('/empleados/{id}', \ProductoDePedidoController::class . ':TraerPedidosDeEmpleado');
  $group->post('/nuevo', \ProductoDePedidoController::class . ':CargarUno');
  $group->post('/actualizar-estado', \ProductoDePedidoController::class . ':ModificarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{id}', \MesaController::class . ':TraerUno');
  $group->post('/nuevo', \MesaController::class . ':CargarUno');
  $group->post('/actualizar-estado', \MesaController::class . ':ModificarUno');
});

$app->group('/registros', function (RouteCollectorProxy $group) {
  //listar detalles
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->get('[/]', function (Request $request, Response $response) {
  
  $response->getBody()->write("Slim Framework 4 PHP");
  return $response;
});

$app->run();
