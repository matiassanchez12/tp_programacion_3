<?php
require_once './models/Cliente.php';
require_once './models/Mesa.php';
require_once './models/Encuesta.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
require_once './models/RegistroDeAcciones.php';

use \App\Models\Mesa as Mesa;
use \App\Models\Encuesta as Encuesta;
use \App\Models\Pedido as Pedido;
use \App\Models\Cliente as Cliente;

class ClienteController
{
    public function LoginCliente($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo_mesa =  $parametros['codigo_mesa'];

        try {
            $estado = Mesa::buscarEstadoMesa($codigo_mesa);
            
            if ($estado[0]->estado_actual == 'con cliente pagando') {

                $message = ClienteController::EncuestaCliente($codigo_mesa, $parametros['puntaje_mesa'], $parametros['puntaje_restaurante'], $parametros['puntaje_mozo'], $parametros['puntaje_cocinero'], $parametros['comentarios']);
            } else {
                $codigo_pedido =  $parametros['codigo_pedido'];

                $message = [
                    'Tiempo restante de su pedido' => Pedido::BuscarTiempo($codigo_pedido)
                ];
            }
        } catch (Exception $e) {

            $message = [
                'Ocurrio un error' => $e->getMessage()
            ];
        }

        $payload = json_encode($message);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Cliente::all();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function EncuestaCliente($codigo_mesa, $punt_mesa, $punt_restaurante, $punt_mozo, $punt_cocinero, $comentarios)
    {
        $message = 'Encuesta registrada. Gracias por su tiempo';

        if (
            self::ValidarPuntajes($punt_mesa) && self::ValidarPuntajes($punt_restaurante)
            && self::ValidarPuntajes($punt_mozo) && self::ValidarPuntajes($punt_cocinero)
        ) {
            if (strlen($comentarios) <= 66) {
                Encuesta::crearEncuesta($codigo_mesa, $punt_mesa, $punt_restaurante, $punt_mozo, $punt_cocinero, $comentarios);
            } else {
                $message = 'Error. Los comentarios deben contener 66 digitos';
            }
        } else {
            $message = 'Error. Ingresar solo numeros del 1 al 10';
        }

        return array("Mensaje" => $message);
    }

    public static function ValidarPuntajes($puntaje)
    {
        if ($puntaje > 0 && $puntaje < 11) {
            return true;
        }
        return false;
    }
}
