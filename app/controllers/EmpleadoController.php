<?php
require_once './models/Empleado.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class EmpleadoController extends Empleado implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];
    $fecha = new DateTime(date("d-m-Y H:i:s"));

    $empleado = new Empleado();
    $empleado->usuario = $usuario;
    $empleado->clave = $clave;
    $empleado->rol = $rol;
    $empleado->fecha_ingreso =  date_format($fecha, 'Y-m-d H:i:s');
    
    $empleado->crearEmpleado();

    $payload = json_encode(array("mensaje" => "Empleado creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $nombre = $args['nombre'];
    $empleado = Empleado::obtenerEmpleado($nombre);
    $payload = json_encode($empleado);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerPedidosDeEmpleado($request, $response, $args)
  {
    $id = $args['id'];
    $productos = Producto::obtenerPedidosDeEmpleado($id);
    $payload = json_encode(array("listaEmpleados" => $productos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ListarPorRol($request, $response, $args)
  {
    $rol = $args['rol'];
    $empleados = Empleado::obtenerEmpleadosPorRol($rol);
    $payload = json_encode(array("listaEmpleados" => $empleados));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Empleado::obtenerTodos();
    $payload = json_encode(array("listaEmpleados" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $id_usuario = $parametros['id_usuario'];
    $id = $parametros['id'];

    Empleado::modificarEmpleado($id_usuario, $nombre, $id);

    $payload = json_encode(array("mensaje" => "Empleado modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $empleadoId = $parametros['empleadoId'];
    Empleado::borrarEmpleado($empleadoId);

    $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
