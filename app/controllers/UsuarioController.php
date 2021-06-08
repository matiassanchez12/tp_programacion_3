<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutenticadorJWT.php';

use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];

    Usuario::crearUsuario($usuario, password_hash($clave, PASSWORD_DEFAULT), $rol);

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $id = $args['id'];
    $usuario = Usuario::where('id', $id)->first();
    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUsuariosPorRol($request, $response, $args)
  {
    $rol = $args['rol'];
    $usuarios = Usuario::where('rol', $rol)->get();
    $payload = json_encode(array("lista usuarios con rol '$rol'", $usuarios));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::all();
    $payload = json_encode(array("listaUsuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $id = $parametros['id'];

    $rows_affected = Usuario::where('id', $id)->update(['usuario' => $usuario]);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];

    $usuario = Usuario::where('id', $id)->first();

    $usuario->delete();

    $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

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
}
