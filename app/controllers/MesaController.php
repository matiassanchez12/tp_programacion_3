<?php
require_once './models/Mesa.php';
require_once './models/DetalleEstadoMesa.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa as Mesa;
use \App\Models\DetalleEstadoMesa as DetalleEstadoMesa;

class MesaController
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $id_cliente = $parametros['id_cliente'];

    $id_mesa = Mesa::crearMesa($id_cliente,GenerateRandomToken::getToken(5));
    
    DetalleEstadoMesa::crearDetalleMesa($id_mesa , 'Iniciando');

    $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function TraerTodos($request, $response, $args)
  {
    $lista = Mesa::all();
    $payload = json_encode(array("listaMesas" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $id = $args['id'];

    $mesa = Mesa::find($id);

    $payload = json_encode($mesa);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_mesa = $parametros['id_mesa'];
    $nuevo_estado = $parametros['nuevo_estado'];

    DetalleEstadoMesa::crearDetalleMesa($id_mesa, $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
