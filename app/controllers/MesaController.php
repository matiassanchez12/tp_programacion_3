<?php
require_once './models/Mesa.php';
require_once './models/DetalleEstadoMesa.php';
require_once './models/RegistroDeAcciones.php';
require_once './librarys/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';
require_once './librarys/Archivos.php';

use \App\Models\Mesa as Mesa;
use \App\Models\DetalleEstadoMesa as DetalleEstadoMesa;
use \App\Models\RegistroDeAcciones as RegistroDeAcciones;

class MesaController
{
  public function CargarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $mesa = new Mesa();
    $mesa->codigo = GenerateRandomToken::getToken(5);
    $mesa->save();

    RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Alta de mesa');

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = Mesa::all();
    $payload = json_encode(array("listaMesas" => $lista));
    RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Listado completo de mesas');

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $id = $args['id'];

    $mesa = Mesa::find($id);
    RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Listar una mesa');

    $payload = json_encode($mesa);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $parametros = $request->getParsedBody();

    $id_mesa = $parametros['id_mesa'];
    $nuevo_estado = $parametros['nuevo_estado'];

    $payload = json_encode(array("mensaje Error" => "Ingresar estado valido: con cliente esperando,con cliente comiendo,con cliente pagando,cerrada"));

    if (isset($nuevo_estado) && $this->ValidarEstado($nuevo_estado)) {

      Mesa::CambiarEstado($id_mesa, $nuevo_estado); //Habilito la mesa
      DetalleEstadoMesa::crearDetalleMesa($id_mesa, MesaController::obtenerIdUsuario($jwtHeader), $nuevo_estado); //Creo el registro de la mesa
      RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Cambia estado de mesa');

      $payload = json_encode(array("mensaje" => "Estado Mesa modificado con exito"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerRegistroMesas($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = DetalleEstadoMesa::all();

    $payload = json_encode(array("listaDetalleDeMesas" => $lista));

    RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Listado de registros de mesas');

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function obtenerIdUsuario($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }

  public function ValidarEstado($estado)
  {
    $estados_disponibles = ['con cliente esperando', 'con cliente comiendo', 'con cliente pagando', 'cerrada'];
    if (in_array($estado, $estados_disponibles)) {
      return true;
    }
    return false;
  }

  public function GuardarMesasEnCSV($request, $response, $args)
  {
    $array = Mesa::all();

    $flag = 1;
    
    foreach ($array as $mesa) {
      
      $mesa_string =  implode(",", $mesa->getAttributes());

      Archivos::GuardarTxt('mesas.csv', $mesa_string, $flag);

      $flag = 0;
    }
    
    $payload = json_encode(array("mensaje" => "Mesas guardadas en CSV"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function LeerMesasEnCSV($request, $response, $args)
  {
    $array_mesas = Archivos::CargarTxt('mesas.csv');

    $nuevoArray = [];

    foreach ($array_mesas as $mesa) {
      $atributos = explode(",", $mesa);

      $auxMesa = new stdClass();
      $auxMesa->id = $atributos[0];
      $auxMesa->estado_actual = $atributos[1];
      $auxMesa->codigo = $atributos[2];
      
      array_push($nuevoArray, $auxMesa);
    }

    $payload = json_encode(array("Mesas" => $nuevoArray));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
