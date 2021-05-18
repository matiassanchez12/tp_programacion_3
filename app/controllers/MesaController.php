<?php
require_once './models/Mesa.php';
require_once './models/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $foto = $parametros['foto'];

    $mesa = new Mesa();
    $mesa->codigo_cliente = GenerateRandomToken::getToken(5);
    $mesa->foto = $foto;
    $mesa->crearMesa();

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
    
  }

  public function ModificarUno($request, $response, $args)
  {
     
  }

  public function BorrarUno($request, $response, $args)
  {
     
  }
}
