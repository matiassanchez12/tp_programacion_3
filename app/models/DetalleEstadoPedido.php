<?php 

class DetalleEstadoPedido
{
    public $id;
    public $id_pedido;
    public $fecha_modificacion;
    public $estado;

    public static function crearDetallePedido($id_pedido, $fecha_modificacion, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO detalle_estado_pedido (id_pedido, fecha_modificacion, estado) VALUES (:id_pedido, :fecha_modificacion, :estado)");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_modificacion', $fecha_modificacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}


?>