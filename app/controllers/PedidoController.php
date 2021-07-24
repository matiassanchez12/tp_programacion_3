<?php
require_once './models/Pedido.php';
require_once './models/Cliente.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Usuario.php';
require_once './models/RegistroDeAcciones.php';
require_once './models/DetalleEstadoPedido.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Cliente as Cliente;
use \App\Models\Mesa as Mesa;
use App\Models\Producto as Producto;
use App\Models\Usuario as Usuario;
use \App\Models\DetalleEstadoPedido as DetalleEstadoPedido;
use \App\Models\RegistroDeAcciones as RegistroDeAcciones;

class PedidoController
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $jwtHeader = $request->getHeaderLine('Authorization');

    $nombre_cliente = $parametros['nombre_cliente'];
    $codigo_mesa = $parametros['codigo_mesa'];
    $id_producto = $parametros['id_producto'];
    $codigo_pedido = $parametros['codigo_pedido'];

    try {
      $id_mozo = PedidoController::obtenerIdUsuario($jwtHeader); //Busco el mozo
      $id_cliente = Cliente::crearCliente($nombre_cliente); //Creo un Cliente
      $id_mesa = Mesa::BuscarMesaPorCodigo($codigo_mesa);
      $producto = Producto::buscarProducto($id_producto);
      $id_empleado = Usuario::buscarEmpleadoPorRol($producto->area_preparacion);
      $empleado_encargado = Usuario::find($id_empleado);

      $id_pedido = Pedido::crearPedido($id_mesa, $id_cliente, $id_mozo, $id_empleado, $id_producto, $codigo_pedido); //Creo el pedido

      DetalleEstadoPedido::crearDetallePedido($id_pedido, $id_mozo, 'Pendiente'); //Creo el registro del pedido

      RegistroDeAcciones::crearRegistro($id_mozo, 'Alta de pedido'); //Creo el registro del usuario

      if(isset($_FILES['foto'])){
        
        $foto = $_FILES['foto'];

        Pedido::GuardarImagen($foto, $codigo_pedido);
      }

      $payload = json_encode(array("mensaje" => "Pedido encargado con exito", "Empleado del pedido" => "$empleado_encargado->nombre(ID: $empleado_encargado->id)"));
    } catch (Exception $e) {

      $payload = json_encode(array("mensaje Error" => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function ModificarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $parametros = $request->getParsedBody();

    $id_pedido = $parametros['id_pedido'];
    $nuevo_estado = $parametros['nuevo_estado'];

    $payload = json_encode(array("mensaje error" => "Ingresar un estado valido"));

    if ($this->validarEstado($nuevo_estado)) {

      switch ($nuevo_estado) {
        case 'en preparacion':
          Pedido::actualizarTiempoEstimado($id_pedido, $this->ConvertIntToDate($parametros['tiempo_estimado']));
          break;
        case 'listo para servir':
          Mesa::CambiarEstado(Pedido::find($id_pedido)->id_mesa, Pedido::find($id_pedido)->id_mozo, "con cliente comiendo");
          
          Usuario::actualizarDisponible($this->obtenerIdUsuario($jwtHeader));
          break;
        case 'cancelado':
          Usuario::actualizarDisponible($this->obtenerIdUsuario($jwtHeader));
          break;
      }

      DetalleEstadoPedido::crearDetallePedido($id_pedido, PedidoController::obtenerIdUsuario($jwtHeader), $nuevo_estado);

      RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Modificar estado de pedido');

      $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public function TraerUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $id = $args['id'];
    $pedido = Pedido::find($id);


    RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Listar un pedido');

    $payload = json_encode($pedido);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $lista = Pedido::all();

    RegistroDeAcciones::crearRegistro(PedidoController::obtenerIdUsuario($jwtHeader), 'Listar todos los pedidos');

    $payload = json_encode(array("listaPedido" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerRegistroPedidos($request, $response, $args)
  {
    // $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = DetalleEstadoPedido::all();

    $payload = json_encode(array("listaEstadosDePedidos" => $lista));

    // RegistroDeAcciones::crearRegistro(MesaController::obtenerIdUsuario($jwtHeader), 'Listado de registros de pedidos');

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function validarEstado($estado)
  {
    $estados_disponibles = ['en preparacion', 'listo para servir', 'cancelado'];
    if (in_array($estado, $estados_disponibles)) {
      return true;
    }
    return false;
  }

  public static function ConvertIntToDate($minutes)
  {
    $fecha = new DateTime();

    $fecha->modify("+$minutes minutes");

    return $fecha->format('Y-m-d H:i:s');
  }

  public static function obtenerIdUsuario($jwtHeader)
  {
    $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

    return $data_usuario->id;
  }

  public function ProductosMasVendidos($request, $response, $args)
  {
    $pedidos = Pedido::LoMasPedido();

    $payload = json_encode(array("3 Productos Mas vendidos" => $pedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ProductosMenosVendidos($request, $response, $args)
  {
    $pedidos = Pedido::LoMenosPedido();

    $payload = json_encode(array("3 Productos Menos vendidos" => $pedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function FueraDeTiempo($request, $response, $args)
  {
    $pedidos = Pedido::PedidosFueraDeTiempo();

    $payload = json_encode(array("3 pedidos que mas veces fueron entregados con demora" => $pedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function Cancelados($request, $response, $args)
  {
    $pedidos = Pedido::PedidosCancelados();

    $payload = json_encode(array("3 pedidos mas cancelados" =>  $pedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerEstados($request, $response, $args)
  {
    $estados = Pedido::BuscarEstadosDePedidos();

    $payload = json_encode(array("Estado de los pedidos" =>  $estados));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
