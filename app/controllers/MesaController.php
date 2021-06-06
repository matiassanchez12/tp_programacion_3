<?php
require_once './models/Mesa.php';
require_once './models/DetalleEstadoMesa.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $id_cliente = $parametros['id_cliente'];
    // Poner un estado por defecto para iniciar
    $estado = $parametros['estado'];

    $mesa = new Mesa();
    $mesa->codigo = GenerateRandomToken::getToken(5);
    $mesa->id_cliente = $id_cliente;
    $mesa->estado = $estado;
    $id_mesa = $mesa->crearMesa();

    $fecha = new DateTime(date("d-m-Y H:i:s"));

    DetalleEstadoMesa::crearDetalleMesa($id_mesa, date_format($fecha, 'Y-m-d H:i:s'), $estado);

    $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function TraerTodos($request, $response, $args)
  {
    $lista = Mesa::obtenerTodos();
    $payload = json_encode(array("listaMesas" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $id_mesa = $parametros['id_mesa'];

    $mesa = Mesa::obtenerMesa($id_mesa);

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

    Mesa::modificarMesa($nuevo_estado, $id_mesa);

    $fecha = new DateTime(date("Y-m-d H:i:s"));
    
    DetalleEstadoMesa::crearDetalleMesa($id_mesa, date_format($fecha, 'Y-m-d H:i:s'), $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_mesa = $parametros['id_mesa'];
    $nuevo_estado = $parametros['nuevo_estado'];

    Mesa::modificarMesa($nuevo_estado, $id_mesa);

    $fecha = new DateTime(date("Y-m-d H:i:s"));
    
    DetalleEstadoMesa::crearDetalleMesa($id_mesa, date_format($fecha, 'Y-m-d H:i:s'), $nuevo_estado);

    $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
