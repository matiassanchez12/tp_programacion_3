<?php
require_once './models/Mesa.php';
require_once './models/DetalleEstadoMesa.php';
require_once './models/RegistroDeAcciones.php';
require_once './librarys/GenerateRandomToken.php';
require_once './interfaces/IApiUsable.php';
require_once './librarys/Archivos.php';
require_once './librarys/fpdf/fpdf.php';

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

      Mesa::CambiarEstado($id_mesa, MesaController::obtenerIdUsuario($jwtHeader),  $nuevo_estado);

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

    $payload = json_encode(array("listaEstadosDeMesas" => $lista));

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

  public function GenerarPdf($request, $response, $args)
  {
    $pdf = new \FPDF('P', 'mm', 'letter');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);
    $pdf->Ln(5);
    $pdf->SetTitle('Mesas');
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Image("images/local.jpg", 10, 5, 25);

    $pdf->Cell(0, 5, utf8_decode('Reporte de mesas'), 0, 1, 'C');

    $pdf->Ln(15);

    $pdf->Cell(40, 5, utf8_decode('ID'), 1, 0, 'C');
    $pdf->Cell(80, 5, utf8_decode('Estado actual'), 1, 0, 'C');
    $pdf->Cell(80, 5, utf8_decode('Codigo'), 1, 1, 'C');

    $mesas = Mesa::all();

    $pdf->SetFont('Arial', 'I', 10);

    foreach ($mesas as $mesa) {
      $pdf->Cell(40, 5, $mesa['id'], 1, 0, 'C');
      $pdf->Cell(80, 5, $mesa['estado_actual'], 1, 0, 'C');
      $pdf->Cell(80, 5, $mesa['codigo'], 1, 1, 'C');
    }

    $pdf->Output('I', 'filename.pdf');

    return $response->withHeader('Content-Type', 'application/pdf');
  }

  public function EstadisticaMesas($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    //le paso la consigna por la cual va a devolver una estadistica
    $consigna = $parametros['consigna'];

    switch ($consigna) {
      case 'MesaMasUsada':
        $lista = Mesa::MesaMasUsada();
        break;
      case 'MesaMenosUsada':
        $lista = Mesa::MesaMenosUsada();
        break;
      case 'MesaMejoresComentarios':
        $lista = Mesa::MesaMejoresComentarios();
        break;
      case 'MesaPeoresComentarios':
        $lista = Mesa::MesaPeoresComentarios();
        break;
      case 'MasFacturo':
        $lista = Mesa::MasFacturo();
        break;
        case 'MenosFacturo':
          $lista = Mesa::MenosFacturo();
          break;
      default:
        $lista = "Error, ingresar valor valido";
        break;
    }

    $payload = json_encode(array("Estadisticas" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
