<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once "autentificadora.php";
require_once "Usuario.php";

class MW
{

    public static function verificarLegajoClaveVacios(Request $request, RequestHandler $handler) : ResponseMW {
        
        $arrayParametros = $request->getParsedBody();
        $usuario = json_decode($arrayParametros["user"]);
        
        $obj_datos = new stdClass();
        $obj_datos->exito = FALSE;
        $obj_datos->mensaje = "";

        if($usuario->legajo != ""  && $usuario->clave != "")
        {
            $response = $handler->handle($request);
            $obj_datos =json_decode($response->getBody());
            
            $status = 200;
        }
        else{

            $obj_datos->mensaje = "legajo o clave vacio";
            $status = 409;
        } 

        $response = new ResponseMW($status);

        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarCodigoExistente(Request $request, RequestHandler $handler): ResponseMW {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["articulo_json"]);         
        $codigo_barra =  $array->codigo_barra;

        if (Articulo::ValidarCodigoExiste($codigo_barra)) {
            $data = [
                'mensaje' => 'CODIGO EXISTENTE',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }

    public function verificarjwtBearer(Request $request, RequestHandler $handler) : ResponseMW{
        $bearer = null;
        if (isset($_SERVER['Authorization'])) {
            $bearer = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { 
            $bearer = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            if (isset($requestHeaders['Authorization'])) {
                $bearer = trim($requestHeaders['Authorization']);
            }
        }

        $token = str_replace("Bearer", "", $bearer);
        $tokenSinEspacios = trim($token);

        $obj_rta = Autentificadora::verificarJWT($tokenSinEspacios);

        if (!$obj_rta->verificado) {
            $std = new stdClass();
            $std->éxito = false;
            $std->mensaje = 'Token inválido';

            $response = new ResponseMW();
            $response->withStatus(403);
            $response->getBody()->write(json_encode($std));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

}