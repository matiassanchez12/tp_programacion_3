<?php
require_once './models/Pedido.php';
require_once './models/Cliente.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
require_once './models/RegistroDeAcciones.php';
require_once './models/DetalleEstadoPedido.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Cliente as Cliente;
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
    $id_mesa = $parametros['id_mesa'];
    $id_producto = $parametros['id_producto'];
    $codigo = $parametros['codigo'];
    $foto = $parametros['foto']; //se guarda con el id del cliente

    try {
      $id_mozo = PedidoController::obtenerIdUsuario($jwtHeader); //Busco el mozo
      $id_cliente = Cliente::crearCliente($nombre_cliente); //Creo un Cliente
      $producto = Producto::buscarProducto($id_producto);
      $id_empleado = Usuario::buscarEmpleadoPorRol($producto->area_preparacion);
      $id_pedido = Pedido::crearPedido($id_mesa, $id_cliente, $id_mozo, $id_empleado, $id_producto, $codigo); //Creo el pedido

      DetalleEstadoPedido::crearDetallePedido($id_pedido, $id_mozo, 'Pendiente'); //Creo el registro del pedido
      RegistroDeAcciones::crearRegistro($id_mozo, 'Alta de pedido'); //Creo el registro del usuario

      $payload = json_encode(array("mensaje" => "Pedido y productos encargados con exito"));
    } catch (Exception $e) {

      $payload = json_encode(array("mensaje Error" => "Ocurrio un error" . $e->getMessage()));
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

    if($this->validarEstado($nuevo_estado)){
      if (isset($parametros['tiempo_estimado'])) {
      
        $tiempo_estimado = $parametros['tiempo_estimado'];
  
        Pedido::actualizarTiempoEstimado($id_pedido, $this->ConvertIntToDate($tiempo_estimado));
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

  public function validarEstado($estado)
  {
    $estados_disponibles = ['en preparacion', 'listo para servir'];
    if (in_array($estado, $estados_disponibles)) {
      return true;
    }
    return false;
  }

  public static function ConvertIntToDate($minutes)
  {
    $fecha = new DateTime();
    // $fecha = new DateTime('H:i:s');
    // $fecha + $minutes
    return $fecha->setTime(0, $minutes)->format('H:i:s');
  }

  public static function obtenerIdUsuario($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }
}
