<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'usuarios';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'usuario', 'clave', 'rol'
    ];

    public static function crearUsuario($usuario, $clave, $rol)
    {
        $Ausuario = new Usuario();
        $Ausuario->usuario = $usuario;
        $Ausuario->clave = $clave;
        $Ausuario->rol = $rol;
        $Ausuario->save();
        
        return $Ausuario->id;
    }
}