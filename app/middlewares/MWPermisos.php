<?php

require_once './models/Pedido.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Pedido as Pedido;

class MWPermisos
{
    public static function VerificarEmpleadoDePedido(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $parametros = $request->getParsedBody();

        $id_pedido = $parametros['id_pedido'];

        try {
            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            $pedido = Pedido::buscarPedido($id_pedido);

            if ($user->id != $pedido->id_empleado || $pedido == null) {
                throw new Exception("A este usuario no le corresponde el pedido ingresado.");
            }
            
            $response = $handler->handle($request);
            
            return $response;
        } catch (Exception $e) {

            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }

    public static function VerificarUsuarioMozo(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        try {
            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            if ($user->rol !== 'Mozo') {
                throw new Exception("Este usuario no puede acceder al contenido.");
            }

            $response = $handler->handle($request);

            // $response->getBody()->write($name);

            return $response;
        } catch (Exception $e) {

            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }

    public static function VerificarCambioEstadoMesa(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $parametros = $request->getParsedBody();

        $nuevo_estado = $parametros['nuevo_estado'];

        try {
            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            if($nuevo_estado !== 'cerrada' && $user->rol !== 'Mozo'){
                throw new Exception("Este usuario no puede cambiar el estado de las mesas.");
            }

            if ($nuevo_estado === 'cerrada' && $user->rol !== 'Socio' ) {
                throw new Exception("Este usuario no puede cerrar la mesa.");
            }

            $response = $handler->handle($request);

            return $response;
        } catch (Exception $e) {

            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }
}
