<?php
require_once './models/Pedido.php';
require_once './models/Cliente.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Usuario.php';
require_once './models/RegistroDeAcciones.php';
require_once './models/DetalleEstadoPedido.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Cliente as Cliente;
use \App\Models\Mesa as Mesa;
use App\Models\Producto as Producto;
use App\Models\Usuario as Usuario;
use \App\Models\DetalleEstadoPedido as DetalleEstadoPedido;
use \App\Models\RegistroDeAcciones as RegistroDeAcciones;

class PedidoController
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $jwtHeader = $request->getHeaderLine('Authorization');

    $nombre_cliente = $parametros['nombre_cliente'];
    $codigo_mesa = $parametros['codigo_mesa'];
    $id_producto = $parametros['id_producto'];
    $codigo_pedido = $parametros['codigo_pedido'];

    try {
      $id_empleado = Usuario::buscarEmpleadoPorRol(Producto::buscarProducto($id_producto)->area_preparacion);

      $id_pedido = Pedido::crearPedido(
        Mesa::BuscarMesaPorCodigo($codigo_mesa),
        Cliente::crearCliente($nombre_cliente),
        PedidoController::obtenerIdUsuario($jwtHeader),
        $id_empleado,
        $id_producto,
        $codigo_pedido,
        $_FILES['foto']
      ); //Creo el pedido

      DetalleEstadoPedido::crearDetallePedido($id_pedido,  PedidoController::obtenerIdUsuario($jwtHeader), 'Pendiente'); //Creo el registro del pedido

      RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Alta de pedido'); //Creo el registro del usuario

      $payload = json_encode(
        [
          "mensaje" => "Pedido encargado con exito",
          "Empleado del pedido" =>  Usuario::find($id_empleado)->usuario . "(ID:" . $id_empleado . ")"
        ]
      );
    } catch (Exception $e) {

      $payload = json_encode(array("mensaje Error" => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function ModificarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $parametros = $request->getParsedBody();

    $id_pedido = $parametros['id_pedido'];
    $nuevo_estado = $parametros['nuevo_estado'];

    $payload = json_encode(array("mensaje error" => "Ingresar un estado valido"));

    if ($this->validarEstado($nuevo_estado)) {

      switch ($nuevo_estado) {
        case 'en preparacion':
          Pedido::actualizarTiempoEstimado($id_pedido, $this->ConvertIntToDate($parametros['tiempo_estimado']));
          break;
        case 'listo para servir':
          Mesa::CambiarEstado(Pedido::find($id_pedido)->id_mesa, Pedido::find($id_pedido)->id_mozo, "con cliente comiendo");

          Usuario::actualizarDisponible($this->obtenerIdUsuario($jwtHeader));
          break;
        case 'cancelado':
          Usuario::actualizarDisponible($this->obtenerIdUsuario($jwtHeader));
          break;
      }

      DetalleEstadoPedido::crearDetallePedido($id_pedido, PedidoController::obtenerIdUsuario($jwtHeader), $nuevo_estado);

      RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Modificar estado de pedido');

      $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $id = $args['id'];
    $pedido = Pedido::find($id);

    RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Listar un pedido');

    $payload = json_encode($pedido);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $lista = Pedido::all();

    RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Listar todos los pedidos');

    $payload = json_encode(array("listaPedido" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerRegistroPedidos($request, $response, $args)
  {
    $lista = DetalleEstadoPedido::all();

    $payload = json_encode(array("listaEstadosDePedidos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function validarEstado($estado)
  {
    $estados_disponibles = ['en preparacion', 'listo para servir', 'cancelado'];
    if (in_array($estado, $estados_disponibles)) {
      return true;
    }
    return false;
  }

  public static function obtenerIdUsuario($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }

  public function EstadisticasPedidos($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $consigna = $parametros['consigna'];
    $desde = $parametros['desde'];
    $hasta = $parametros['hasta'];

    switch ($consigna) {
      case 'LoMasPedido':
        $pedidos = ["3 Productos Mas vendidos" => Pedido::LoMasPedido($desde, $hasta)];
        break;
      case 'LoMenosPedido':
        $pedidos = ["3 Productos Menos vendidos" => Pedido::LoMenosPedido($desde, $hasta)];
        break;
      case 'PedidosFueraDeTiempo':
        $pedidos = ["3 pedidos entregados con demora" => Pedido::PedidosFueraDeTiempo($desde, $hasta)];
        break;
      case 'PedidosCancelados':
        $pedidos = ["3  pedidos mas cancelados" => Pedido::PedidosCancelados($desde, $hasta)];
        break;
      default:
        $pedidos = ["Consigna invalida"];
        break;
    }

    $payload = json_encode(array("Estadistica" => $pedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerEstados($request, $response, $args)
  {
    $estados = Pedido::TraerEstadosDePedidos();

    $payload = json_encode(array("Estado de los pedidos" =>  $estados));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function ConvertIntToDate($minutes)
  {
    $fecha = new DateTime();

    $fecha->modify("+$minutes minutes");

    return $fecha->format('Y-m-d H:i:s');
  }
}
