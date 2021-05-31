<?php 

class Pedido
{
    public $id;
    public $id_mesa;
    public $id_usuario;
    public $id_mozo;
    public $imagen_mesa;
    public $codigo;
    public $estado;
        
    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_mesa, id_usuario, id_mozo, imagen_mesa, estado, codigo) VALUES (:id_mesa,:id_usuario,:id_mozo, :imagen_mesa, :estado, :codigo)");
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':id_mozo', $this->id_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':imagen_mesa', $this->imagen_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($estado, $id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    // public static function borrarPedido($estado, $id)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");
    //     $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
    //     $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    //     $consulta->execute();
    // }
}
