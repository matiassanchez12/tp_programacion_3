<?php
require_once './models/Pedido.php';
require_once './models/Cliente.php';
require_once './models/Mesa.php';
require_once './models/DetalleEstadoPedido.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Cliente as Cliente;
use \App\Models\Mesa as Mesa;
use \App\Models\DetalleEstadoPedido as DetalleEstadoPedido;

class PedidoController
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $jwtHeader = $request->getHeaderLine('Authorization');
    
    $nombre_cliente = $parametros['nombre_cliente'];
    $foto = $parametros['foto'];
    $productos = $parametros['productos'];//separados por coma
    
    $id_mozo = PedidoController::obtenerIdMozo($jwtHeader);
    $id_cliente = Cliente::crearCliente($nombre_cliente);
    $id_mesa = Mesa::crearMesa($id_cliente, GenerateRandomToken::getToken(5));

    $id_pedido = Pedido::crearPedido($id_mesa, $id_cliente, $id_mozo, $foto, GenerateRandomToken::getToken(5));

    DetalleEstadoPedido::crearDetallePedido($id_pedido, 'Creando');

    $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $id = $args['id'];
    $pedido = Pedido::find($id);

    $payload = json_encode($pedido);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::all();
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

    DetalleEstadoPedido::crearDetallePedido($id_pedido, $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function validaciones($datos)
  {
    $ret = false;
    return $ret;
  }
public function encargarproductos( $var = null)
{
  # code...
}
  public static function obtenerIdMozo($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }
}
