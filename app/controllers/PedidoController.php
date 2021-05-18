<?php
require_once './models/Pedido.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre_cliente = $parametros['nombre_cliente'];
    $foto = $parametros['foto'];
    // $tiempo_entrega = $parametros['tiempo_entrega'];
    // $estado = $parametros['estado'];
    $tiempo_entrega = '';
    $estado = '';

    $pedido = new Pedido();
    $pedido->nombre_cliente = $nombre_cliente;
    $pedido->codigo = GenerateRandomToken::getToken(5);
    $pedido->estado = $estado;
    $pedido->tiempo_entrega = $tiempo_entrega;
    $pedido->foto = $foto;
    $pedido->crearPedido();

    $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos pedido por codigo
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

    $codigo = $args['codigo'];
    $estado = $parametros['nuevoestado'];

    Pedido::modificarPedido($estado, $codigo);

    $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $pedidoId = $parametros['pedidoId'];
    Pedido::borrarPedido($pedidoId);

    $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
