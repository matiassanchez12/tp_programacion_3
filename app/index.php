<?php
error_reporting(-1);
ini_set('display_errors', 1);

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/Logger.php';

require_once './controllers/ProductoController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ClienteController.php';

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

// Routes Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
  $group->post('[/]', \UsuarioController::class . ':CargarUno');
  $group->delete('/baja', \UsuarioController::class . ':BorrarUno');
})->add(\Logger::class . ':LogOperacion');

// Routes Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{codigo}', \PedidoController::class . ':TraerUno');
  $group->post('/altapedido', \PedidoController::class . ':CargarUno');
  $group->put('/{codigo}/nuevoestado', \PedidoController::class . ':ModificarUno');
  $group->delete('/baja', \PedidoController::class . ':BorrarUno');
});

$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
  $group->get('/{nombre}', \EmpleadoController::class . ':TraerUno');
  $group->get('/roles/{rol}', \EmpleadoController::class . ':ListarPorRol');
  $group->get('/{id}/pedidosempleado', \EmpleadoController::class . ':TraerPedidosDeEmpleado');
  $group->post('/altaempleado', \EmpleadoController::class . ':CargarUno');
  $group->post('/baja', \EmpleadoController::class . ':BorrarUno');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->post('/{idpedido}/altaproducto', \ProductoController::class . ':CargarUno');
  $group->post('/baja', \ProductoController::class . ':BorrarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->post('/altamesa', \MesaController::class . ':CargarUno');
});


$app->group('/clientes', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ClienteController::class . ':TraerTodos');
  $group->post('/alta', \ClienteController::class . ':CargarUno');
})->add(\Logger::class . ':LogOperacion');

$app->get('[/]', function (Request $request, Response $response) {
  
  $response->getBody()->write("Slim Framework 4 PHP");
  return $response;
});

$app->run();
