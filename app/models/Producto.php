<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'id';
    protected $table = 'productos';

    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_baja';

    protected $fillable = [
        'nombre', 'tipo', 'precio'
    ];

    public static function crearProducto($nombre,$tipo, $precio)
    {
        $producto = new Producto();
        $producto->nombre = $nombre;
        $producto->tipo = $tipo;
        $producto->precio = $precio;
        $producto->save();
        
        return $producto->id;
    }
}
