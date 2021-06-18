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

class UsuarioController implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];

    Usuario::crearUsuario($usuario, password_hash($clave, PASSWORD_DEFAULT), $rol);
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

    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Listado de un usuario');

    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUsuariosPorRol($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $rol = $args['rol'];
    $usuarios = Usuario::where('rol', $rol)->get();

    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Listado de usuarios por rol');

    $payload = json_encode(array("lista usuarios con rol '$rol'", $usuarios));

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

    DetalleEstadoUsuario::crearDetalleUsuario($id_usuario, UsuarioController::obtenerIdUsuario($jwtHeader), $nuevo_estado);
    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Cambio de estado del usuario con ID: '.$id_usuario);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();
    
    $id = $parametros['id'];

    $usuario = Usuario::find($id);

    $usuario->delete();
    RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Borrado de un usuario con ID: ' . $id);

    $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PedidosPendientes($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $id = $args['id'];

    $producto = Producto::traerPedidosPendientes($id);

    $payload = json_encode(array("mensaje" => "El empleado no tiene pedidos pendientes"));

    if($producto != null){
      
      RegistroDeAcciones::crearRegistro(UsuarioController::obtenerIdUsuario($jwtHeader), 'Borrado de un usuario con ID: ' . $id);

      $payload = json_encode(array("mensaje" => "Cambio de estado de pedido pendiente"));
    }


    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function Login($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

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

        RegistroDeAcciones::crearRegistro($datos_usuario->id, 'Inicio de sesion del usuario: ' . $usuario);
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
}
