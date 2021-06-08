<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEstadoProducto extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'detalle_estado_producto';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_producto', 'estado'
    ];

    public static function crearDetalleProducto($id_producto, $estado)
    {
        $detalle_producto = new DetalleEstadoProducto();
        $detalle_producto->id_producto = $id_producto;
        $detalle_producto->estado = $estado;
        $detalle_producto->save();
    }
}
?>