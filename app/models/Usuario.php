<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'usuarios';

    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_baja';

    protected $fillable = [
        'usuario', 'clave', 'rol', 'disponible', 'sector'
    ];

    public static function crearUsuario($usuario, $clave, $rol, $sector)
    {
        $Ausuario = new Usuario();
        $Ausuario->usuario = $usuario;
        $Ausuario->clave = $clave;
        $Ausuario->sector = $sector;
        $Ausuario->rol = $rol;
        $Ausuario->save();

        return $Ausuario->id;
    }

    public static function buscarEmpleadoPorRol($rol)
    {
        $usuario_encontrado = Usuario::where(['rol' => $rol, 'disponible' => 1])->first();

        if ($usuario_encontrado == null) {

            throw new Exception("No hay un empleado disponible con el rol: " . $rol, 1);
        }

        $usuario_encontrado->update(['disponible' => 0]);

        return $usuario_encontrado->id;
    }

    public static function actualizarDisponible($id)
    {
        Usuario::find($id)->update(['disponible' => 1]);
    }

    public static function ingresosUsuarios()
    {
        return Usuario::select('usuarios.id', 'detalle_estado_usuario.fecha_creacion')

            ->join('detalle_estado_usuario', 'detalle_estado_usuario.id_usuario', '=', 'usuarios.id')

            ->where('detalle_estado_usuario.estado', 'Alta')

            ->get();
    }

    public static function logueoUsuarios()
    {
        return RegistroDeAcciones::select('registro_acciones.id_usuario', 'registro_acciones.fecha_creacion')

            ->where('registro_acciones.accion', 'Inicio de sesion del usuario')

            ->get();
    }

    public static function cantidadOperacionesPorUsuario()
    {
        return RegistroDeAcciones::select('registro_acciones.id_usuario', Usuario::raw('COUNT(registro_acciones.id_usuario) as operaciones_realizadas'))

            ->groupBy('registro_acciones.id_usuario')

            ->orderBy('registro_acciones.id_usuario', 'DESC')

            ->get();
    }

    public static function OperacionesPorSector()
    {
        return Usuario::select('usuarios.sector', Usuario::raw('COUNT(registro_acciones.id_usuario) as total'))

            ->join('registro_acciones', 'registro_acciones.id_usuario', '=', 'usuarios.id')

            ->groupBy('usuarios.sector')

            ->orderBy('total', 'DESC')

            ->get();
    }

    public static function OperacionesPorEmpleado()
    {
        return Usuario::select('usuarios.id', 'usuarios.sector', Usuario::raw('COUNT(registro_acciones.id_usuario) as total'))

            ->join('registro_acciones', 'registro_acciones.id_usuario', '=', 'usuarios.id')

            ->groupBy('usuarios.id')

            ->orderBy('id', 'ASC')

            ->get();
    }
}
