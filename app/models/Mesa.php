<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'mesas';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'estado_actual', 'codigo'
    ];

    public static function CambiarEstado($id, $id_empleado, $nuevo_estado)
    {
        $mesa = new Mesa();
        $mesa_encontrada = $mesa->find($id);

        if ($mesa_encontrada == null) {
            throw new Exception("No existe una mesa con el ID: " . $id, 1);
        }

        DetalleEstadoMesa::crearDetalleMesa($mesa_encontrada->id, $id_empleado, $nuevo_estado);
        $mesa_encontrada->update(['estado_actual' => $nuevo_estado]);
    }

    public static function BuscarMesaPorCodigo($codigo, $verificacionDoble = true)
    {
        $mesa_encontrada = Mesa::where('codigo', $codigo)->first();

        if (!isset($mesa_encontrada)) {
            throw new Exception("La mesa con el codigo: " . $codigo . " no existe", 1);
        }

        return $mesa_encontrada->id;
    }

    public static function MesaMasUsada()
    {
        return Mesa::select('mesas.id', Mesa::raw('COUNT(detalle_estado_mesa.id_mesa) as total'))

            ->join('detalle_estado_mesa', 'detalle_estado_mesa.id_mesa', '=', 'mesas.id')

            ->where('detalle_estado_mesa.estado', '=', 'con cliente esperando pedido')

            ->groupBy('mesas.id')

            ->orderBy('total', 'DESC')

            ->take(1)

            ->get();
    }

    public static function MesaMenosUsada()
    {
        return Mesa::select('mesas.id', Mesa::raw('COUNT(detalle_estado_mesa.id_mesa) as total'))

            ->join('detalle_estado_mesa', 'detalle_estado_mesa.id_mesa', '=', 'mesas.id')

            ->where('detalle_estado_mesa.estado', '=', 'con cliente esperando')

            ->groupBy('mesas.id')

            ->orderBy('total', 'ASC')

            ->take(1)

            ->get();
    }

    public static function MasFacturo()
    {
        return Mesa::select('mesas.id', Producto::raw('SUM(productos.precio) as total'))

        ->join('pedidos', 'pedidos.id_mesa', '=', 'mesas.id')

        ->join('productos', 'productos.id', '=', 'pedidos.id_producto')

        ->groupBy('pedidos.id_mesa')

        ->orderBy('total', 'DESC')

        ->take(1)

        ->get();
    }

    public static function MenosFacturo()
    {
        return Mesa::select('mesas.id', Producto::raw('SUM(productos.precio) as total'))

            ->join('pedidos', 'pedidos.id_mesa', '=', 'mesas.id')

            ->join('productos', 'productos.id', '=', 'pedidos.id_producto')

            ->groupBy('pedidos.id_mesa')

            ->orderBy('total', 'ASC')

            ->take(1)

            ->get();
    }

    public static function MayorImporte()
    {
        $mayor =  Mesa::select('pedidos.codigo as codigo', Mesa::raw('SUM(productos.precio) as total'))

            ->join('pedidos', 'pedidos.id_mesa', '=', 'mesas.id')

            ->join('productos', 'productos.id', '=', 'pedidos.id_producto')

            ->groupBy('pedidos.codigo')

            ->orderBy('total', 'DESC')

            ->take(1)

            ->get()

            ->toArray();

        return [
            'id mesa:' => Pedido::where('codigo', $mayor[0]['codigo'])->first()->id_mesa,
            'total importe factura: $' => $mayor[0]['total']
        ];
    }

    public static function MenorImporte()
    {
        # code...
    }

    public static function MesaMejoresComentarios()
    {
        return Mesa::select('mesas.id', Mesa::raw('SUM(encuesta.punt_mesa) as total'))

            ->join('encuesta', 'encuesta.codigo_mesa', '=', 'mesas.codigo')

            ->groupBy('mesas.id')

            ->orderBy('total', 'DESC')

            ->take(1)

            ->get();
    }


    public static function MesaPeoresComentarios()
    {
        return Mesa::select('mesas.id', Mesa::raw('SUM(encuesta.punt_mesa) as total'))

            ->join('encuesta', 'encuesta.codigo_mesa', '=', 'mesas.codigo')

            ->groupBy('mesas.id')

            ->orderBy('total', 'ASC')

            ->take(1)

            ->get();
    }
}
