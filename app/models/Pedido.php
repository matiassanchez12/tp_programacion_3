<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'pedidos';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_mesa', 'id_cliente','id_empleado','id_producto', 'id_mozo', 'tiempo_estimado', 'codigo'
    ];

    public static function crearPedido($id_mesa, $id_cliente, $id_mozo, $id_empleado, $id_producto, $codigo, $tiempo_estimado = null)
    {
        $pedido = new Pedido();
        $pedido->id_mesa = $id_mesa;
        $pedido->id_cliente = $id_cliente;
        $pedido->id_empleado = $id_empleado;
        $pedido->id_producto = $id_producto;
        $pedido->id_mozo = $id_mozo;
        $pedido->tiempo_estimado = $tiempo_estimado;
        $pedido->codigo = $codigo;
        $pedido->save();

        return $pedido->id;
    }

    public static function buscarPedido($id)
    {
        $pedido = new Pedido();
        return $pedido->find($id);
    }

    public static function actualizarTiempoEstimado($id, $nuevo_tiempo)
    {
        $pedido = new Pedido();
        $pedido_encontrado = $pedido->find($id);
        $pedido_encontrado->update(['tiempo_estimado' => $nuevo_tiempo]);
    }
}
