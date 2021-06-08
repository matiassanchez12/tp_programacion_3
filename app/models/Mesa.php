<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'mesas';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente', 'codigo'
    ];

    public static function crearMesa($id_cliente, $codigo)
    {
        $mesa = new Mesa();

        $verificar_mesa_vacia = $mesa->where('id_cliente', $id_cliente)->first();
        
        if ($verificar_mesa_vacia != null) {
            return $verificar_mesa_vacia->id;
        }

        $mesa->id_cliente = $id_cliente;
        $mesa->codigo = $codigo;
        $mesa->save();

        return $mesa->id;
    }
}
