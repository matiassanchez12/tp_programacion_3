<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroDeAcciones extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'registro_acciones';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_usuario', 'accion'
    ];

    public static function crearRegistro($id_usuario, $accion)
    {
        $registro_acciones = new RegistroDeAcciones();
        $registro_acciones->id_usuario = $id_usuario;
        $registro_acciones->accion = $accion;
        $registro_acciones->save();
    }
}
?>