<?php
require_once './models/ProductoDePedido.php';
require_once './models/DetalleEstadoProducto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\ProductoDePedido as ProductoDePedido;
use \App\Models\DetalleEstadoProducto as DetalleEstadoProducto;

class ProductoDePedidoController
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $jwtHeader = $request->getHeaderLine('Authorization');

    $id_producto = $parametros['id_producto'];
    $id_pedido = $parametros['id_pedido'];
    $tiempo_entrega = $parametros['tiempo_entrega'];
    
    $id_empleado = ProductoDePedidoController::obtenerIdEmpleado($jwtHeader);
    $id_producto = ProductoDePedido::crearProductoDePedido($id_producto, $id_pedido, $id_empleado, $tiempo_entrega);

    DetalleEstadoProducto::crearDetalleProducto($id_producto, 'Encargado');

    $payload = json_encode(array("mensaje" => "Producto de pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $id = $args['id'];
    $producto = ProductoDePedido::find($id);
    $payload = json_encode($producto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = ProductoDePedido::all();
    $payload = json_encode(array("listaProductosPedidos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_producto = $parametros['id_producto'];
    $nuevo_estado = $parametros['nuevo_estado'];

    $mensaje = "El id no existe, intentar nuevamente";

    if (ProductoDePedido::find($id_producto) != null) {

      DetalleEstadoProducto::crearDetalleProducto($id_producto, $nuevo_estado);

      $mensaje = "Producto modificado con exito";
    }

    $payload = json_encode(array("mensaje" => $mensaje));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerPedidosDeEmpleado($request, $response, $args)
  {
    $id = $args['id'];

    $lista = ProductoDePedido::obtenerPedidosDeEmpleado($id);

    $payload = json_encode(array("listaProductosPedidos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function obtenerIdEmpleado($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }
}
