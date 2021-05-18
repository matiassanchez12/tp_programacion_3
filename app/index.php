<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/ProductoController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EmpleadoController.php';
// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);


// Routes Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
  $group->post('[/]', \UsuarioController::class . ':CargarUno');
  $group->post('/baja', \UsuarioController::class . ':BorrarUno');
});

// Routes Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{codigo}', \PedidoController::class . ':TraerUno');
  $group->post('/altapedido', \PedidoController::class . ':CargarUno');
  $group->post('/{codigo}/nuevoestado', \PedidoController::class . ':ModificarUno');
  // $group->post('/{codigo}/altaproducto', \ProductoController::class . ':CargarUno');
  $group->post('/baja', \PedidoController::class . ':BorrarUno');
});

$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
  $group->get('/{nombre}', \EmpleadoController::class . ':TraerUno');
  $group->get('/{id}/pedidosempleado', \EmpleadoController::class . ':TraerPedidosDeEmpleado');
  $group->post('/altaempleado', \EmpleadoController::class . ':CargarUno');
  $group->post('/baja', \EmpleadoController::class . ':BorrarUno');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->post('/{idpedido}/altaproducto', \ProductoController::class . ':CargarUno');
  // $group->post('/altaproducto', \ProductoController::class . ':CargarUno');
  $group->post('/baja', \ProductoController::class . ':BorrarUno');
});

$app->get('[/]', function (Request $request, Response $response) {
  
  $response->getBody()->write("Slim Framework 4 PHP");
  return $response;
});

$app->run();
