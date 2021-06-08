<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEstadoMesa extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'detalle_estado_mesa';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_mesa', 'estado'
    ];

    public static function crearDetalleMesa($id_mesa, $estado)
    {
        $detalle_mesa = new DetalleEstadoMesa();
        $detalle_mesa->id_mesa = $id_mesa;
        $detalle_mesa->estado = $estado;
        $detalle_mesa->save();
    }
}


?>