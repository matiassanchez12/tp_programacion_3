<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'productos';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'tipo', 'precio', 'area_preparacion'
    ];

    public static function crearProducto($nombre, $tipo, $precio, $area_preparacion)
    {
        $producto = new Producto();
        $producto->nombre = $nombre;
        $producto->tipo = $tipo;
        $producto->precio = $precio;
        $producto->area_preparacion = $area_preparacion;
        $producto->save();
        
        return $producto->id;
    }

    public static function buscarProducto($id_producto)
    {
        $producto = new Producto();
        return $producto->find($id_producto);
    }
}
