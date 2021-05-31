<?php
require_once './models/Pedido.php';
require_once './models/DetalleEstadoPedido.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_mesa = $parametros['id_mesa'];
    $id_usuario = $parametros['id_usuario'];
    $id_mozo = $parametros['id_mozo'];
    $imagen_mesa = $parametros['imagen_mesa'];
    $estado = $parametros['estado'];

    $pedido = new Pedido();
    $pedido->id_mesa = $id_mesa;
    $pedido->id_usuario = $id_usuario;
    $pedido->id_mozo = $id_mozo;
    $pedido->imagen_mesa = $imagen_mesa;
    $pedido->codigo = GenerateRandomToken::getToken(5);
    $pedido->estado = $estado;

    $id_pedido = $pedido->crearPedido();

    $fecha = new DateTime(date("d-m-Y H:i:s"));
    
    DetalleEstadoPedido::crearDetallePedido($id_pedido, date_format($fecha, 'Y-m-d H:i:s'), $estado);

    $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscar pedido por codigo
    $codigo = $args['codigo'];
    $pedido = Pedido::obtenerPedido($codigo);
    $payload = json_encode($pedido);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::obtenerTodos();
    $payload = json_encode(array("listaPedido" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_pedido = $parametros['id_pedido'];
    $nuevo_estado = $parametros['nuevo_estado'];

    Pedido::modificarPedido($nuevo_estado, $id_pedido);

    $fecha = new DateTime(date("d-m-Y H:i:s"));
    
    DetalleEstadoPedido::crearDetallePedido($id_pedido, date_format($fecha, 'Y-m-d H:i:s'), $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_pedido = $parametros['id_pedido'];
    $nuevo_estado = $parametros['nuevo_estado'];

    Pedido::modificarPedido($nuevo_estado, $id_pedido);
    
    $fecha = new DateTime(date("d-m-Y "));

    DetalleEstadoPedido::crearDetallePedido($id_pedido, date_format($fecha, 'Y-m-d H:i:s'), $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function validaciones($datos)
  {
    $ret = false;
    return $ret;
  }
}
