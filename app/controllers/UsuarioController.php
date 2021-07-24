<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';

require_once './models/RegistroDeAcciones.php';
require_once './models/DetalleEstadoUsuario.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutenticadorJWT.php';

use \App\Models\Usuario as Usuario;
use \App\Models\Producto as Producto;

use \App\Models\DetalleEstadoUsuario as DetalleEstadoUsuario;
use \App\Models\RegistroDeAcciones as RegistroDeAcciones;

class UsuarioController
{
  public function CargarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];
    $sector = $parametros['sector'];

    $id_usuario = Usuario::crearUsuario($usuario, password_hash($clave, PASSWORD_DEFAULT), $rol, $sector);

    DetalleEstadoUsuario::crearDetalleUsuario($id_usuario, UsuarioController::obtenerIdUsuario($jwtHeader), 'Alta');

    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Alta de usuario');

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $id = $args['id'];
    $usuario = Usuario::where('id', $id)->first();

    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = Usuario::all();

    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Listado de todos los usuarios');

    $payload = json_encode(array("listaUsuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $id_usuario = $parametros['id_usuario'];
    $nuevo_estado = $parametros['nuevo_estado'];

    try {
      switch ($nuevo_estado) {
        case 'Suspender':
          Usuario::find($id_usuario)->delete();
          break;
        case 'Alta':
          Usuario::withTrashed()->find($id_usuario)->restore();
          break;
        case 'Eliminar':
          Usuario::find($id_usuario)->delete();
          break;
        default:
          throw new Exception("Ingresar estados: Suspender, Alta, Eliminar");
          break;
      }

      DetalleEstadoUsuario::crearDetalleUsuario($id_usuario, UsuarioController::obtenerIdUsuario($jwtHeader), $nuevo_estado);

      RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Cambio de estado del usuario con ID: ' . $id_usuario);

      $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    } catch (Exception $th) {
      $payload = json_encode(array("mensaje error" => $th->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function Login($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $usuario =  $parametros['usuario'];
    $clave =  $parametros['clave'];

    if (isset($usuario) && isset($clave)) {
      $datos_usuario = Usuario::where('usuario', $usuario)->first();

      if (!empty($datos_usuario) && password_verify($clave, $datos_usuario->clave)) {

        $jwt = AutentificadorJWT::CrearToken($datos_usuario);

        $message = [
          'Autorizacion' => $jwt,
          'Estado' => 'Logueado.'
        ];

        RegistroDeAcciones::crearRegistro($datos_usuario->id, 'Inicio de sesion del usuario');
      } else {
        $message = [
          'Autorizacion' => 'Denegate',
          'Estado' => 'Error, el usuario no existe.'
        ];
      }
    }

    $payload = json_encode($message);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function obtenerIdUsuario($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }

  public function Ingresos($request, $response, $args)
  {
    $lista = Usuario::ingresosUsuarios();

    $payload = json_encode(array("Ingreso de usuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function Logueos($request, $response, $args)
  {
    $lista = Usuario::logueoUsuarios();

    $payload = json_encode(array("Logueo de usuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CantidadOperaciones($request, $response, $args)
  {
    $lista = Usuario::cantidadOperacionesPorUsuario();

    $payload = json_encode(array("Operaciones de usuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function OperacionesPorSector($request, $response, $args)
  {
    $lista = Usuario::operacionesPorSector();

    $payload = json_encode(array("Operaciones Por Sector" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function OperacionesPorEmpleado($request, $response, $args)
  {
    $lista = Usuario::operacionesPorEmpleado();

    $payload = json_encode(array("Operaciones Por Empleado" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
