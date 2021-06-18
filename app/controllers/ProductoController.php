<?php
require_once './models/Producto.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
require_once './models/RegistroDeAcciones.php';

use \App\Models\Producto as Producto;
use \App\Models\Pedido as Pedido;
use \App\Models\RegistroDeAcciones as RegistroDeAcciones;

class ProductoController
{
  public function CargarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $tipo = $parametros['tipo'];
    $precio = $parametros['precio'];


    $producto = new Producto();
    $producto->nombre = $nombre;
    $producto->tipo = $tipo;
    $producto->precio = $precio;
    $producto->save();

    RegistroDeAcciones::crearRegistro(ProductoController::obtenerIdUsuario($jwtHeader), 'Alta de producto');

    $payload = json_encode(array("mensaje" => "Producto creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = Producto::all();
    $payload = json_encode(array("listaProductos" => $lista));

    RegistroDeAcciones::crearRegistro(ProductoController::obtenerIdUsuario($jwtHeader), 'Listado de todos los productos');

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerProductosPorTipo($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $tipo = $args['tipo'];

    $lista = Producto::where('tipo', $tipo)->get();

    RegistroDeAcciones::crearRegistro(ProductoController::obtenerIdUsuario($jwtHeader), 'Listado de productos por tipo');

    $payload = json_encode(array("lista productos de tipo '$tipo'" => $lista));

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
