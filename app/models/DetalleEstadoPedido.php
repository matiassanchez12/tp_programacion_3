<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEstadoPedido extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'detalle_estado_pedido';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_pedido', 'estado'
    ];

    public static function crearDetallePedido($id_pedido, $estado)
    {
        $detalle_pedido = new DetalleEstadoPedido();
        $detalle_pedido->id_pedido = $id_pedido;
        $detalle_pedido->estado = $estado;
        $detalle_pedido->save();
    }
}
?>