<?php
require_once './models/ProductoDePedido.php';
require_once './models/DetalleEstadoProducto.php';
require_once './interfaces/IApiUsable.php';

class ProductoDePedidoController extends ProductoDePedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
       
        $id_producto = $parametros['id_producto'];
        $id_pedido = $parametros['id_pedido'];
        $id_empleado = $parametros['id_empleado'];
        $tiempo_entrega = $parametros['tiempo_entrega'];
        $estado = $parametros['estado'];
        
        // Creamos el usuario
        $producto = new ProductoDePedido();
        $producto->id_producto = $id_producto;
        $producto->id_pedido = $id_pedido;
        $producto->id_empleado = $id_empleado;
        $producto->tiempo_entrega = $tiempo_entrega;
        $producto->estado = $estado;
        $id_producto = $producto->crearProductoDePedido();

        $fecha = new DateTime(date("Y-m-d H:i:s"));
    
        DetalleEstadoProducto::crearDetalleProducto($id_producto, date_format($fecha, 'Y-m-d H:i:s'), $estado);

        $payload = json_encode(array("mensaje" => "Producto de pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos pedido por codigo
        $id = $args['id'];
        $producto = ProductoDePedido::obtenerProducto($id);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = ProductoDePedido::obtenerTodos();
        $payload = json_encode(array("listaProductosPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_producto = $parametros['id_producto'];
        $nuevo_estado = $parametros['nuevo_estado'];

        $mensaje = "El id no existe, intentar nuevamente";
        
        if(ProductoDePedido::modificarProducto($id_producto, $nuevo_estado) > 0){
          $fecha = new DateTime(date("Y-m-d H:i:s"));
          
          DetalleEstadoProducto::crearDetalleProducto($id_producto, date_format($fecha, 'Y-m-d H:i:s'), $nuevo_estado); 
          
          $mensaje = "Producto modificado con exito";
        }


        $payload = json_encode(array("mensaje" => $mensaje));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        
        $mensaje = "Producto modificado con exito";
        
        if(Producto::borrarProducto($id) < 1){
          $mensaje = "El id no existe, intentar nuevamente";
        }

        $payload = json_encode(array("mensaje" => $mensaje));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
