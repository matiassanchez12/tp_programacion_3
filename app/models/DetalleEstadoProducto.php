<?php 

class DetalleEstadoProducto
{
    public static function crearDetalleProducto($id_producto, $fecha_modificacion, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO detalle_estado_producto (id_producto, fecha_modificacion, estado) VALUES (:id_producto, :fecha_modificacion, :estado)");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_modificacion', $fecha_modificacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}


?>