<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class MWAutentificar
{
    public static function VerificarTokenExpire(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        try {
            AutentificadorJWT::VerificarToken($jwtHeader);

            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            $response = $handler->handle($request);

            $response->getBody()->write('El usuario esta logeado');

            return $response;
        } catch (Exception $e) {
            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }

    public static function VerificarUsuarioEmpleado(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        try {
            $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            if($user->rol == 'Bartender'){
                throw new Exception("Este usuario no puede acceder al contenido.");
            }

            $response = $handler->handle($request);

            $response->getBody()->write('Rol: '. $user->id);

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
            $data_usuario = AutentificadorJWT::ObtenerData($jwtHeader);

            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            if($user->rol !== 'Mozo'){
                throw new Exception("Este usuario no puede acceder al contenido.");
            }

            $response = $handler->handle($request);

            // $response->getBody()->write('Rol: '. $user->id);

            return $response;
        } catch (Exception $e) {

            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }
}
