<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'clientes';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'nombre'
    ];

    public static function crearCliente($nombre)
    {
        $cliente = new Cliente();

        $verificar_cliente_existente = $cliente->where('nombre', $nombre)->first();

        if ($verificar_cliente_existente != null) {
            return $verificar_cliente_existente->id;
        }

        $cliente->nombre = $nombre;
        $cliente->save();

        return $cliente->id;
    }

   
}
