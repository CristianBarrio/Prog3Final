<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once "autentificadora.php";
require_once "Usuario.php";
require_once "Juguete.php";

class MW
{
    public function verificarCamposCorreoYClave(Request $request, RequestHandler $handler) : ResponseMW{

        $arrayDeParametros = $request->getParsedBody();
        if (!isset($arrayDeParametros["user"])) {
            $data = [
                'mensaje' => 'No se recibió el campo "usuario"',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }
        $array = json_decode($arrayDeParametros["user"]); 

        if ($array === null){
            $data = [
                'mensaje' => 'El campo "usuario" no es un JSON válido',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $correo = $array->correo ?? '';
        $clave = $array->clave ?? '';

        if (empty($correo) || empty($clave)) {
            $data = [
                'mensaje' => 'Faltan el correo y/o la clave',
            ];
            $responseMW = new ResponseMW();
            $responseMW = $responseMW->withStatus(409, "ERROR");            
            $responseMW->getBody()->write(json_encode($data));
            return $responseMW;
        }else{
            $response = $handler->handle($request);            
            $contenidoAPI = (string) $response->getBody();
        }

   
        $response = new ResponseMW(200);
        $response->getBody()->write("$contenidoAPI");
        return $response;
    } 

    public static function verificarCredencialesEnBd(Request $request, RequestHandler $handler): ResponseMW {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["user"]);         
        $correo =  $array->correo;
        $clave = $array->clave;

        if (!Usuario::ValidarUsuario($correo,$clave)) {
            $data = [
                'mensaje' => 'Credenciales invalidas, no estan en la base',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }

    public function verificarjwt(Request $request, RequestHandler $handler) : ResponseMW{
        $token = $request->getHeaderLine('token');
        $obj_rta = Autentificadora::verificarJWT($token);
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

    // public function ListarTablaSinClave(Request $request, RequestHandler $handler): ResponseMW{
    //     $contenidoAPI = "";

    //     if (isset($request->getHeader("token")[0])) {
    //         $token = $request->getHeader("token")[0];
    //         $datos_token = Autentificadora::obtenerPayLoad($token);            
    //         $usuario_token = $datos_token->payload->data; 
    //         //$perfil_usuario = $usuario_token->perfil;

    //         $response = $handler->handle($request);
    //         $contenidoAPI = (string) $response->getBody();

    //         $api_respuesta = json_decode($contenidoAPI);
    //         $array_usuarios = json_decode($api_respuesta->tabla);

    //         foreach ($array_usuarios as $usuario) {
    //             unset($usuario->clave);
    //         }

    //         $contenidoAPI = MW::ArmarTablaSinClave($array_usuarios);
    //     }

    //     $response = new ResponseMW();
    //     $response = $response->withStatus(200);
    //     $response->getBody()->write($contenidoAPI);
    //     return $response;
    // }

    // public function ListarTablaSinClavePROP(Request $request, RequestHandler $handler): ResponseMW{
    //     $contenidoAPI = "";

    //     if (isset($request->getHeader("token")[0])) {
    //         $token = $request->getHeader("token")[0];
    //         $datos_token = Autentificadora::obtenerPayLoad($token);            
    //         $usuario_token = $datos_token->payload->data; 
    //         $perfil_usuario = $usuario_token->perfil;

    //         if($perfil_usuario == "propietario"){
    //             $response = $handler->handle($request);
    //             $contenidoAPI = (string) $response->getBody();
    
    //             $api_respuesta = json_decode($contenidoAPI);
    //             $array_usuarios = json_decode($api_respuesta->tabla);
    
    //             foreach ($array_usuarios as $usuario) {
    //                 unset($usuario->clave);
    //                 unset($usuario->id);
    //                 unset($usuario->perfil);
    //                 unset($usuario->foto);
    //             }
    
    //             $contenidoAPI = MW::ArmarTablaSinClave($array_usuarios);
    //             return $contenidoAPI;

    //         }else{
    //             $response = new ResponseMW();
    //             $std = new stdClass();
    //             $std->éxito = false;
    //             $std->mensaje = 'perfil no valido';
    //             $response = $response->withStatus(403);
    //             $response->getBody()->write(json_encode($std));
    //             return $response;
    //         }
           
    //     }
    // }

    // private static function ArmarTablaSinClave($listado): string
    // {
    //     $tabla = "<table><thead><tr>";
    //     foreach ($listado[0] as $key => $value) {
    //         if ($key != "clave") {
    //             $tabla .= "<th>{$key}</th>";
    //         }
    //     }
    //     $tabla .= "</tr></thead><tbody>";

    //     foreach ($listado as $item) {
    //         $tabla .= "<tr>";
    //         foreach ($item as $key => $value) {
    //             if ($key == "foto") {
    //                 $tabla .= "<td><img src='{$value}' width=25px></td>";
    //             } else {
    //                 if ($key != "clave") {
    //                     $tabla .= "<td>{$value}</td>";
    //                 }
    //             }
    //         }
    //         $tabla .= "</tr>";
    //     }
    //     $tabla .= "</tbody></table> <br>";
    //     return $tabla;
    // }

    // public function ListarTablaJuguetes(Request $request, RequestHandler $handler): ResponseMW
    // {
    //     $contenidoAPI = "";

    //     if (isset($request->getHeader("token")[0])) {
    //         $token = $request->getHeader("token")[0];

    //         $datos_token = Autentificadora::obtenerPayLoad($token);
    //         $usuario_token = $datos_token->payload->data;

    //         $response = $handler->handle($request);
    //         $contenidoAPI = (string) $response->getBody();

    //         $api_respuesta = json_decode($contenidoAPI);
    //         $array_juguetes = json_decode($api_respuesta->tabla);

    //         $contenidoAPI = MW::ArmarTablaJuguetes($array_juguetes);
    //     }

    //     $response = new ResponseMW();
    //     $response = $response->withStatus(200);
    //     $response->getBody()->write($contenidoAPI);
    //     return $response;
    // }

    // private static function ArmarTablaJuguetes($listado): string
    // {
    //     $tabla = "<table><thead><tr>";
    //     foreach ($listado[0] as $key => $value) {
    //         $tabla .= "<th>{$key}</th>";
    //     }
    //     $tabla .= "</tr></thead><tbody>";

    //     foreach ($listado as $item) {
    //         $tabla .= "<tr>";
    //         foreach ($item as $key => $value) {
    //             if ($key == "path_foto") {
    //                 $tabla .= "<td><img src='{$value}' width=25px></td>";
    //             } else {
    //                 $tabla .= "<td>{$value}</td>";
    //             }
    //         }
    //         $tabla .= "</tr>";
    //     }
    //     $tabla .= "</tbody></table> <br>";
    //     return $tabla;
    // }

    public function TablaUsuariosSinClave(Request $request, RequestHandler $handler): ResponseMW
    {

        $response = $handler->handle($request);
        $contenidoAPI = (string) $response->getBody();

        $api_respuesta = json_decode($contenidoAPI);
        $usuarios = json_decode($api_respuesta->tabla);

        $tabla = '<table class="table" border="1" align="center">
                    <thead>
                        <tr>
                            <th> ID   </th>
                            <th> CORREO     </th>
                            <th> NOMBRE    </th>
                            <th> APELLIDO    </th>
                            <th> FOTO      </th>
                            <th> PERFIL      </th>
                        </tr>
                    </thead>';

        foreach ($usuarios as $usuario){

            $tabla .= "<tr>
                    <td>".$usuario->id."</td>
                    <td>".$usuario->correo."</td>
                    <td>".$usuario->nombre."</td>
                    <td>".$usuario->apellido."</td>
                    <td><img src='".$usuario->foto."' width='100px' height='100px'/></td>
                    <td>".$usuario->perfil."</td>
                    </tr>";
    
        }

        $tabla .= '</table>';
    
        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($tabla);
        return $response;
    }

    public function TablaUsuariosProp(Request $request, RequestHandler $handler): ResponseMW
    {
        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "propietario"){
               
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();

                $api_respuesta = json_decode($contenidoAPI);
                $usuarios = json_decode($api_respuesta->tabla);

                $tabla = '<table class="table" border="1" align="center">
                            <thead>
                                <tr>
                                    <th> CORREO     </th>
                                    <th> NOMBRE    </th>
                                    <th> APELLIDO    </th>
                                </tr>
                            </thead>';

                foreach ($usuarios as $usuario){

                    $tabla .= "<tr>
                        <td>".$usuario->correo."</td>
                        <td>".$usuario->nombre."</td>
                        <td>".$usuario->apellido."</td>
                        </tr>";

                }

                $tabla .= '</table>';

                $response = new ResponseMW();
                $response = $response->withStatus(200);
                $response->getBody()->write($tabla);
                return $response;

            }else{
                $response = new ResponseMW();
                $std = new stdClass();
                $std->éxito = false;
                $std->mensaje = 'perfil no valido';
                $response = $response->withStatus(403);
                $response->getBody()->write(json_encode($std));
                return $response;
            }

        }

    }

    public function TablaJuguetes(Request $request, RequestHandler $handler): ResponseMW
    {

        $response = $handler->handle($request);
        $contenidoAPI = (string) $response->getBody();

        $api_respuesta = json_decode($contenidoAPI);
        $juguetes = json_decode($api_respuesta->tabla);

        $tabla = '<table class="table" border="1" align="center">
                    <thead>
                        <tr>
                            <th> ID   </th>
                            <th> MARCA     </th>
                            <th> PRECIO    </th>
                            <th> FOTO      </th>
                        </tr>
                    </thead>';

        foreach ($juguetes as $juguete){

            if($juguete->id % 2 != 0){
                $tabla .= "<tr>
                                <td>".$juguete->id."</td>
                                <td>".$juguete->marca."</td>
                                <td>".$juguete->precio."</td>
                                <td><img src='"."../src/fotos/".$juguete->path_foto."' width='100px' height='100px'/></td>
                                </tr>";
            }
        }

        $tabla .= '</table>';
    
        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($tabla);
        return $response;
    }

    public static function verificarCorreoExistente(Request $request, RequestHandler $handler): ResponseMW {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["user"]);         
        $correo =  $array->correo;
        $clave = $array->clave;

        if (Usuario::ValidarCorreoExiste($correo)) {
            $data = [
                'mensaje' => 'CORREO YA EXISTE',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }





    //CONCESIONARIA/////////////////////////////////////////

    public function verificarCorreoClave(Request $request, RequestHandler $handler) : ResponseMW {
    
        $arrayParametros = $request->getParsedBody();
        $usuario = json_decode($arrayParametros["user"]);
        
        $obj_datos = new stdClass();
        $obj_datos->exito = FALSE;
        $obj_datos->mensaje = "";

        if(isset($usuario->clave) && isset($usuario->correo))
        {
            $response = $handler->handle($request);
            $obj_datos =json_decode($response->getBody());
            $status = 200;
        }
        else{
    
            $obj_datos->mensaje = "clave o correo no pasados";
            $status = 403;
        } 
    
        $response = new ResponseMW($status);
    
        $response->getBody()->write(json_encode($obj_datos));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarVacio(Request $request, RequestHandler $handler) : ResponseMW {
    
        $arrayParametros = $request->getParsedBody();
        $usuario = json_decode($arrayParametros["user"]);
        
        $obj_datos = new stdClass();
        $obj_datos->exito = FALSE;
        $obj_datos->mensaje = "";

        if($usuario->correo != ""  && $usuario->clave != "")
        {
            $response = $handler->handle($request);
            $obj_datos =json_decode($response->getBody());
            
            $status = 200;
        }
        else{
    
            $obj_datos->mensaje = "correo o clave vacio";
            $status = 409;
        } 
    
        $response = new ResponseMW($status);
    
        $response->getBody()->write(json_encode($obj_datos));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarCredencialesEnBdAutos(Request $request, RequestHandler $handler): ResponseMW {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["user"]);         
        $correo =  $array->correo;
        $clave = $array->clave;

        if (!Usuario::ValidarUsuario($correo,$clave)) {
            $data = [
                'mensaje' => 'Credenciales invalidas, no estan en la base',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }

    public static function verificarCorreoExistenteAuto(Request $request, RequestHandler $handler): ResponseMW {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["user"]);         
        $correo =  $array->correo;

        if (Usuario::ValidarCorreoExiste($correo)) {
            $data = [
                'mensaje' => 'CORREO YA EXISTENTE',
            ];
            $response = new ResponseMW();
            $response = $response->withStatus(403, "ERROR");
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }

    public function verificarAuto(Request $request, RequestHandler $handler) : ResponseMW {
    
        $arrayParametros = $request->getParsedBody();
        $auto = json_decode($arrayParametros["auto"]);
        
        $obj_datos = new stdClass();
        $obj_datos->mensaje = "El auto es de color azul o el precio no esta entre los valores válidos ($50.000 - $600.000)";
        $obj_datos->exito = false;

        if(($auto->precio >=50000 &&  $auto->precio <=600000)  && $auto->color != "azul")
        {
            $response = $handler->handle($request);
            $objeto =json_decode($response->getBody());
            $obj_datos->mensaje = $objeto->mensaje;
            $obj_datos->exito = $objeto->exito;
            $status = 200;
        }
        else{
            $status = 409;
        } 
    
        $response = new ResponseMW($status);
    
        $response->getBody()->write(json_encode($obj_datos));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function verificarPropietario(Request $request, RequestHandler $handler) : ResponseMW {
    
        $token = $request->getHeader("token")[0];
        
        $obj = Autentificadora::obtenerPayLoad($token); 
        $usuario = $obj->payload->data;

        $obj_datos = new stdClass();
        $obj_datos->mensaje = "No es propietario, no puede eliminar";
        $obj_datos->exito = false;
        if($usuario->perfil == "Propietario" || $usuario->perfil =="propietario")
        {
            $response = $handler->handle($request);
            $objeto =json_decode($response->getBody());
            $obj_datos->mensaje = $objeto->mensaje;
            $obj_datos->exito = $objeto->exito;
            $status = 200;
        }
        else{
            $status = 409;
        } 
    
        $response = new ResponseMW($status);
    
        $response->getBody()->write(json_encode($obj_datos));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function verificarencargado(Request $request, RequestHandler $handler) : ResponseMW {
    
        $token = $request->getHeader("token")[0];
        
        $obj = Autentificadora ::obtenerPayLoad($token); 
        $usuario = $obj->payload->data;

        $obj_datos = new stdClass();
        $obj_datos->mensaje = "No es encargado, no puede modificar";
        $obj_datos->exito = false;
        if($usuario->perfil == "encargado" || $usuario->perfil == "Encargado")
        {
            $response = $handler->handle($request);
            $objeto =json_decode($response->getBody());
            $obj_datos->mensaje = $objeto->mensaje;
            $obj_datos->exito = $objeto->exito;
            $status = 200;
        }
        else{
            $status = 409;
        } 
    
        $response = new ResponseMW($status);
    
        $response->getBody()->write(json_encode($obj_datos));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function mostrarAutosEncargado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "encargado"){
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
    
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->tabla);
    
                foreach ($array_autos as $auto) {
                    unset($auto->id);
                }
                
                $api_respuesta->tabla = json_encode($array_autos);

                $response = new ResponseMW;
                $response->getBody()->write(json_encode($api_respuesta));
                return $response;

            }

        }

        return $handler->handle($request);
        
    }

    public function mostrarAutosEmpleado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "empleado"){
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
    
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->tabla);
    
                $colores_distintos = count(array_unique(array_column($array_autos, 'color')));

                $api_respuesta->cantidad_colores_distintos = $colores_distintos;
                $response->getBody()->write(json_encode($api_respuesta));                
                
                return $response;
            }
           
        }

        return $handler->handle($request);
    }

    public static function mostrarAutosPropietario(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "propietario"){

                $id_auto = $request->getQueryParams()['id'] ?? null;

                if($id_auto !== null)
                {
                    $auto = Auto::TraerUno($id_auto);

                    if($auto !== null)
                    {
                        $std = new stdClass();
                        $std->exito = true;
                        $std->mensaje = "Auto obtenido";
                        $std->auto = $auto;
                        $response = new ResponseMW();
                        $response->getBody()->write(json_encode($std));
                        return $response;
                    } else {
                        $response = new ResponseMW();
                        $std = new stdClass();
                        $std->exito = false;
                        $std->mensaje = "Auto no encontrado";
                        $response->getBody()->write(json_encode($std));
                        return $response->withStatus(404);
                    }
                }else
                {
                    $response = $handler->handle($request);
                    $contenidoAPI = (string) $response->getBody();
        
                    $api_respuesta = json_decode($contenidoAPI);
                    $array_autos = json_decode($api_respuesta->tabla);
            
                    $response->getBody()->write(json_encode($array_autos));                
                    
                    return $response;
                }
            }
           
        }
        
        return $handler->handle($request);
    }
    
    public function mostrarUsuariosEncargado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "encargado"){
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
    
                $api_respuesta = json_decode($contenidoAPI);
                $array_usuarios = json_decode($api_respuesta->tabla);
    
                foreach ($array_usuarios as $usuario) {
                    unset($usuario->id);
                    unset($usuario->clave);
                }
                
                $api_respuesta->tabla = json_encode($array_usuarios);

                $response = new ResponseMW;
                $response->getBody()->write(json_encode($api_respuesta));
                return $response;

            }

        }

        return $handler->handle($request);
        
    }

    public function mostrarUsuariosEmpleado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "empleado"){
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
    
                $api_respuesta = json_decode($contenidoAPI);
                $array_usuarios = json_decode($api_respuesta->tabla);
    
                foreach ($array_usuarios as $usuario) {
                    unset($usuario->id);
                    unset($usuario->clave);
                    unset($usuario->correo);
                    unset($usuario->perfil);
                }
                
                $api_respuesta->tabla = json_encode($array_usuarios);

                $response = new ResponseMW;
                $response->getBody()->write(json_encode($api_respuesta));
                return $response;

            }

        }

        return $handler->handle($request);
        
    }

    public static function mostrarUsuariosPropietario(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];
            $datos_token = Autentificadora::obtenerPayLoad($token);            
            $usuario_token = $datos_token->payload->data; 
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "propietario"){

                $apellido = $request->getQueryParams()['apellido'] ?? null;
                
                if($apellido !== null)
                {
                    $cantidadUsuarios = Usuario::contarUsuariosPorApellido($apellido);
                    $std = new stdClass();
                    $std->exito = true;
                    $std->mensaje = "Numero de usuarios de apellido $apellido: $cantidadUsuarios";
                    $response = new ResponseMW();
                    $response->getBody()->write(json_encode($std));
                    return $response;
                }else
                {
                    $apellidos = Usuario::contarApellidosUsuarios();
                    $response = new ResponseMW();
                    $std = new stdClass();
                    $std->exito = true;
                    $std->mensaje = "Cantidad de usuarios por apellido:";
                    $std->apellidos = $apellidos;
                    $response->getBody()->write(json_encode($std));
                    return $response->withStatus(200);
                }     
            }
           
        }
        
        return $handler->handle($request);
    }






    /////KIOSCO//////////////////////////////////////

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