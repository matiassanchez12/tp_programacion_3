<?php

class Cliente extends Usuario
{
    public $codigo_pedido;
    public $tiempo_espera;

    public function crearCliente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO clientes (usuario, clave, fecha_ingreso, codigo_pedido, tiempo_espera) VALUES (:usuario, :clave, :fecha_ingreso, :codigo_pedido, :tiempo_espera)");
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_espera', $this->tiempo_espera, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_ingreso', $this->fecha_ingreso, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM clientes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');
    }

    public static function obtenerEmpleado($nombre)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function modificarEmpleado($nombre, $id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET nombre = :nombre WHERE id = :id");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarEmpleado($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}