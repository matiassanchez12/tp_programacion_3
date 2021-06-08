<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoDePedido extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'productos_pedido';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_producto', 'id_pedido', 'id_empleado', 'tiempo_entrega'
    ];

    public static function crearProductoDePedido($id_producto, $id_pedido, $id_empleado, $tiempo_entrega)
    {
        $producto_pedido = new ProductoDePedido();
        $producto_pedido->id_producto = $id_producto;
        $producto_pedido->id_pedido = $id_pedido;
        $producto_pedido->id_empleado = $id_empleado;
        $producto_pedido->tiempo_entrega = $tiempo_entrega;
        $producto_pedido->save();
        
        return $producto_pedido->id;
    }

    public function VerificarProductoExistente($id_producto)
    {
        $productos = new Producto();

        $producto = $productos->find($id_producto);
        if($producto != null){
            return $producto->tipo;
        }

        return null;
    }

    public static function obtenerPedidosDeEmpleado($id_empleado)
    {
        return ProductoDePedido::where('id_empleado', $id_empleado)->get();
    }
}
