<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $tipo = $parametros['tipo'];

    $auxUsuario = new Usuario();
    $auxUsuario->usuario = $usuario;
    $auxUsuario->clave = password_hash($clave, PASSWORD_DEFAULT);
    $auxUsuario->tipo = $tipo;

    if ($tipo == 'Empleado' && isset($tipo) && !empty($tipo)) {
      $rol = $parametros['rol'];
      $auxUsuario->rol = $rol;
    }
    $auxUsuario->save();

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $usuario = $args['usuario'];
    $usuario = Usuario::where('usuario', $usuario)->first();
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

    // Usuario::modificarUsuario($usuario, $id);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuarioId = $parametros['usuarioId'];

    $usuario = Usuario::where('id', $usuarioId)->first();
    // Borramos
    $usuario->delete();

    $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
