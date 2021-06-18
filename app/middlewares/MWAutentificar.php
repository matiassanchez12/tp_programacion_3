<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class MWAutentificar
{
    public static function VerificarTokenExpire(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        try {
            AutentificadorJWT::VerificarToken($jwtHeader);

            $user = AutentificadorJWT::ObtenerData($jwtHeader);

            $response = $handler->handle($request);

            return $response;
        } catch (Exception $e) {
            $response = new Response();

            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response;
        }
    }

   
}
