<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'pedidos';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_mesa', 'id_cliente', 'id_mozo', 'imagen_mesa', 'codigo'
    ];

    public static function crearPedido($id_mesa,$id_cliente, $id_mozo, $imagen_mesa, $codigo)
    {
        $pedido = new Pedido();
        $pedido->id_mesa = $id_mesa;
        $pedido->id_cliente = $id_cliente;
        $pedido->id_mozo = $id_mozo;
        $pedido->imagen_mesa = $imagen_mesa;
        $pedido->codigo = $codigo;
        $pedido->save();
        
        return $pedido->id;
    }

}
