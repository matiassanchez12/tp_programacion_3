<?php 

class DetalleEstadoMesa
{
    public static function crearDetalleMesa($id_mesa, $fecha_modificacion, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO detalle_estado_mesa (id_mesa, fecha_modificacion, estado) VALUES (:id_mesa, :fecha_modificacion, :estado)");
        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_modificacion', $fecha_modificacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}


?>