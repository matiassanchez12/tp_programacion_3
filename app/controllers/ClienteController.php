<?php
require_once './models/Cliente.php';
require_once './interfaces/IApiUsable.php';

class ClienteController extends Cliente implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $codigo_pedido = $parametros['codigo_pedido'];
    $tiempo_espera = $parametros['tiempo_espera'];
    $fecha = new DateTime(date("d-m-Y H:i:s"));

    $cliente = new Cliente();
    $cliente->usuario = $usuario;
    $cliente->clave = $clave;
    $cliente->codigo_pedido = $codigo_pedido;
    $cliente->tiempo_espera = $tiempo_espera;
    $cliente->fecha_ingreso =  date_format($fecha, 'Y-m-d H:i:s');
    
    $cliente->crearCliente();

    $payload = json_encode(array("mensaje" => "Cliente creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por nombre
    // $usr = $args['usuario'];
    // $usuario = Usuario::obtenerUsuario($usr);
    // $payload = json_encode($usuario);

    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Cliente::obtenerTodos();
    $payload = json_encode(array("listaClientes" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
  //   $parametros = $request->getParsedBody();

  //   $nombre = $parametros['nombre'];
  //   Usuario::modificarUsuario($nombre);

    // $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    // $response->getBody()->write($payload);
    // return $response
      // ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    // $parametros = $request->getParsedBody();

    // $usuarioId = $parametros['usuarioId'];
    // Usuario::borrarUsuario($usuarioId);

    // $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
}
