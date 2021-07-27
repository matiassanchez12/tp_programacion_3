<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'pedidos';

    public $incrementing = true;
    public $timestamps = true;

    const UPDATED_AT = null;
    const CREATED_AT = 'fecha_creacion';

    protected $fillable = [
        'id_mesa', 'id_cliente', 'id_empleado', 'id_producto', 'id_mozo', 'tiempo_estimado', 'codigo'
    ];

    public static function crearPedido($id_mesa, $id_cliente, $id_mozo, $id_empleado, $id_producto, $codigo, $imagen, $tiempo_estimado = null)
    {
        $pedido = new Pedido();
        $pedido->id_mesa = $id_mesa;
        $pedido->id_cliente = $id_cliente;
        $pedido->id_empleado = $id_empleado;
        $pedido->id_producto = $id_producto;
        $pedido->id_mozo = $id_mozo;
        $pedido->tiempo_estimado = $tiempo_estimado;
        $pedido->codigo = $codigo;
        $pedido->save();

        if (isset($imagen)) {

            Pedido::GuardarImagen($imagen, $codigo);
        }

        Mesa::CambiarEstado($id_mesa, $id_mozo, 'con cliente esperando'); //Cambio el estado de la mesa

        return $pedido->id;
    }

    public static function buscarPedido($id)
    {
        return Pedido::find($id);
    }

    public static function BuscarPedidoPorCodigo($codigo)
    {
        $pedidos_encontrados = Pedido::where('codigo', $codigo)->get();

        if (!isset($pedidos_encontrados)) {
            throw new Exception("El pedido con el codigo: " . $codigo . " no existe", 1);
        }

        return Pedido::BuscarTiempoDePedido($pedidos_encontrados);
    }

    public static function actualizarTiempoEstimado($id, $nuevo_tiempo)
    {
        Pedido::find($id)->update(['tiempo_estimado' => $nuevo_tiempo]);
    }

    public static function BuscarTiempo($codigo)
    {
        $tiempo_encontrado = Pedido::select('pedidos.id', 'pedidos.tiempo_estimado as tiempo')

            ->join("detalle_estado_pedido", "detalle_estado_pedido.id_pedido", "=", "pedidos.id")

            ->where('pedidos.codigo', $codigo)

            ->where('detalle_estado_pedido.estado', 'en preparacion')

            ->orderBy('tiempo', 'DESC')

            ->take(1)

            ->get();

        $ret = "El pedido esta con demora";

        if(!isset($tiempo_encontrado[0]->tiempo)){
            throw new Exception("Error codigo invalido", 1);
        }

        if (strtotime(date('Y:m:d H:i:s')) < strtotime($tiempo_encontrado[0]->tiempo)) {

            $ret = Pedido::CalcularDiffDates(date('Y:m:d H:i:s'), $tiempo_encontrado[0]->tiempo);
        }

        return $ret;
    }

    public static function CalcularDiffDates($date1, $date2)
    {
        $diff = abs(strtotime($date2) - strtotime($date1));

        $years   = floor($diff / (365 * 60 * 60 * 24));
        $months  = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days    = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        $hours   = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

        $minuts  = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);

        $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));

        return "$minuts: $seconds";
    }

    public static function LoMasPedido($desde, $hasta)
    {
        return Producto::select("productos.nombre", Producto::raw('COUNT(pedidos.id_producto) as total'))

            ->join("pedidos", "pedidos.id_producto", "=", "productos.id")

            ->groupBy('productos.id')

            ->whereBetween('pedidos.fecha_creacion', [$desde, $hasta])

            ->orderBy('total', 'DESC')

            ->take(3)

            ->get();
    }

    public static function LoMenosPedido($desde, $hasta)
    {
        return Producto::select("productos.nombre", Producto::raw('COUNT(pedidos.id_producto) as total'))

            ->join("pedidos", "pedidos.id_producto", "=", "productos.id")

            ->whereBetween('pedidos.fecha_creacion', [$desde, $hasta])

            ->groupBy('productos.id')

            ->orderBy('total', 'ASC')

            ->take(3)

            ->get();
    }

    public static function PedidosFueraDeTiempo($desde, $hasta)
    {
        return Pedido::select("pedidos.id_producto", "productos.nombre", Producto::raw('COUNT(pedidos.id_producto) as total'))

            ->join("detalle_estado_pedido", "detalle_estado_pedido.id_pedido", "=", "pedidos.id")

            ->join("productos", "pedidos.id_producto", "=", "productos.id")

            ->where('detalle_estado_pedido.estado', 'listo para servir')

            ->whereBetween('pedidos.fecha_creacion', [$desde, $hasta])

            ->whereRaw("TIME(detalle_estado_pedido.fecha_creacion) >= TIME(pedidos.tiempo_estimado)")

            ->groupBy("pedidos.id_producto")

            ->orderBy('total', 'DESC')

            ->take(3)

            ->get();
    }

    public static function PedidosCancelados($desde, $hasta)
    {
        return Pedido::select("productos.nombre", Producto::raw('COUNT(pedidos.id_producto) as total'))

            ->join("productos", "productos.id", "=", "pedidos.id_producto")

            ->join("detalle_estado_pedido", "detalle_estado_pedido.id_pedido", "=", "pedidos.id")

            ->whereBetween('pedidos.fecha_creacion', [$desde, $hasta])

            ->where('detalle_estado_pedido.estado', 'cancelado')

            ->groupBy("productos.id")

            ->get();
    }

    public static function GuardarImagen($file, $codigo_pedido)
    {
        if (!file_exists("$codigo_pedido.jpg")) {
            $name = "$codigo_pedido";

            $destino = "images/" . $name;

            $tipo = explode(".", $file["name"]);

            $destino .= "." . $tipo[1];

            move_uploaded_file($file["tmp_name"], $destino);
        }
    }

    public static function TraerEstadosDePedidos()
    {
        return DetalleEstadoPedido::selectRaw('pedidos.id, productos.nombre as nombre_producto, usuarios.usuario as empleado_encargado, max(fecha_creacion) as hora, detalle_estado_pedido.estado')

            ->join('pedidos', 'pedidos.id', '=', 'detalle_estado_pedido.id_pedido')

            ->join('productos', 'productos.id', '=', 'pedidos.id_producto')

            ->join('usuarios', 'usuarios.id', '=', 'pedidos.id_empleado')

            ->whereRaw('detalle_estado_pedido.fecha_creacion = (select max(`fecha_creacion`) from detalle_estado_pedido where detalle_estado_pedido.id_pedido = pedidos.id)')

            ->groupBy('detalle_estado_pedido.id_pedido', 'detalle_estado_pedido.estado')

            ->orderBy('detalle_estado_pedido.id_pedido', 'ASC')

            ->get();
    }
}
