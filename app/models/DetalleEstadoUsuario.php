<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEstadoUsuario extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'detalle_estado_usuario';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_usuario', 'id_empleado', 'estado'
    ];

    public static function crearDetalleUsuario($id_usuario, $id_empleado, $estado)
    {
        $detalle_usuario = new DetalleEstadoUsuario();
        $detalle_usuario->id_usuario = $id_usuario;
        $detalle_usuario->id_empleado = $id_empleado;
        $detalle_usuario->estado = $estado;
        $detalle_usuario->save();
    }
}


?>