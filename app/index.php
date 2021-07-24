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
$app->post('/encuesta', \ClienteController::class . ':EncuestaCliente');

$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{id}', \UsuarioController::class . ':TraerUno');
  $group->post('/nuevo', \UsuarioController::class . ':CargarUno');
  $group->post('/actualizar-usuario', \UsuarioController::class . ':ModificarUno');
  $group->post('/borrar', \UsuarioController::class . ':BorrarUno');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoController::class . ':TraerUno');
  $group->post('/nuevo', \PedidoController::class . ':CargarUno')->add(\MWPermisos::class . ':VerificarUsuarioMozo');
  $group->post('/actualizar-estado', \PedidoController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerificarEmpleadoDePedido');
  $group->get('/estados/visualizar', \PedidoController::class . ':TraerEstados')->add(\MWPermisos::class . ':VerificarSoloSocios');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->get('/tipo/{tipo}', \ProductoController::class . ':TraerProductosPorTipo');
  $group->post('/nuevo', \ProductoController::class . ':CargarUno');
})->add(\MWAutentificar::class . ':VerificarTokenExpire');

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{id}', \MesaController::class . ':TraerUno');
  $group->post('/nuevo', \MesaController::class . ':CargarUno');
  $group->get('/archivos/guardar-csv', \MesaController::class . ':GuardarMesasEnCSV');
  $group->get('/archivos/leer-csv', \MesaController::class . ':LeerMesasEnCSV');
  $group->get('/archivos/descargar-pdf', \MesaController::class . ':GenerarPdf');
  $group->post('/actualizar-estado', \MesaController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerificarCambioEstadoMesa');
});

$app->group('/registros', function (RouteCollectorProxy $group) {
  $group->get('/mesas', \MesaController::class . ':TraerRegistroMesas');
  $group->get('/pedidos', \PedidoController::class . ':TraerRegistroPedidos');
});

$app->group('/consultas', function (RouteCollectorProxy $group) {
  $group->post('/mesas', \MesaController::class . ':EstadisticaMesas');
  $group->get('/usuarios/alta-usuarios', \UsuarioController::class . ':Ingresos');
  $group->get('/usuarios/logueo-usuarios', \UsuarioController::class . ':Logueos');
  $group->get('/usuarios/operaciones-usuarios', \UsuarioController::class . ':CantidadOperaciones');
  $group->get('/usuarios/operaciones-sectores', \UsuarioController::class . ':OperacionesPorSector');
  $group->get('/usuarios/operaciones-empleados', \UsuarioController::class . ':OperacionesPorEmpleado');
  $group->get('/mesas/mas-usada', \MesaController::class . ':MesaMasUsada');
  $group->get('/mesas/menos-usada', \MesaController::class . ':MesaMenosUsada');
  $group->get('/mesas/mejores-comentarios', \MesaController::class . ':MesaMejoresComentarios');
  $group->get('/mesas/peores-comentarios', \MesaController::class . ':MesaPeoresComentarios');
});

$app->get('[/]', function (Request $request, Response $response) {
  
  $response->getBody()->write("Slim Framework 4 PHP");

  return $response;
});

$app->run();
