<?php

namespace App\Models;

use Exception;
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

    //Retorna el id si lo encontro sino 0
    public static function buscarEmpleadoPorRol($rol)
    {
        $Ausuario = new Usuario();
        $usuario_encontrado = $Ausuario->where(['rol' => $rol, 'disponible' => 1])->first();
        if($usuario_encontrado == null){
            
            throw new Exception("No hay un empleado disponible en el rol: ". $rol, 1);
        }

        $usuario_encontrado->update(['disponible' => 0]);

        return $usuario_encontrado->id;
    }
}