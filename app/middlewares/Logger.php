<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Logger
{
    public static function LogOperacion(Request $request, RequestHandler $handler)
    {
        $response = $handler->handle($request);
        $algo = new stdClass();
        $algo->nombre = "asd";
        $algo->apellido = "123";
        $persona = ['nombre' => 'asd', 'apellido' => '123'];
        $data = AutentificadorJWT::CrearToken($persona);

        if ($request->getMethod() == 'GET') {
            $datos = AutentificadorJWT::ObtenerData($data);
            $respuesta = $datos->apellido;

        } else if ($request->getMethod() == 'POST') {
            $parametros = $request->getParsedBody();

            $usuario = $parametros['usuario'];
            $clave = $parametros['clave'];
            $codigo_pedido = $parametros['codigo_pedido'];

            $respuesta = "Esto es POST codigo: " . $codigo_pedido;
        }

        $response->getBody()->write("{$respuesta}");

        return $response;
    }
}
