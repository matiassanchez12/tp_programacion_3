<?php 
// Si al mozo le hacen un pedido de un vino, una cerveza y unas empanadas, deberían los
// empleados correspondientes ver estos pedidos en su listado de “pendientes”, con la opción de
// tomar una foto de la mesa con sus integrantes y relacionarlo con el pedido.
// producto : id, id_pedido, nombre, tipo

class Producto
{
    private $id;
    private $id_pedido;
    private $nombre;
    private $tipo;

    public function crearCliente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (id_pedido, nombre, tipo) VALUES (:id_pedido, :nombre, :tipo)");
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}



?>