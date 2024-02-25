<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once __DIR__ . "/autentificadora.php";
require_once __DIR__ . "/accesoDatos.php";
require_once __DIR__ . "/articulo.php";
require_once __DIR__ . "/usuario.php";
require_once "../vendor/autoload.php";

class Manejadora{

    public function loginCorreoYClave(Request $request, Response $response, array $args) : Response {

        $arrayDeParametros = $request->getParsedBody();
        $array = json_decode($arrayDeParametros["user"]); 
       
        $usuario = Usuario::ValidarUsuario($array->legajo,$array->clave);
        
        if($usuario){
            $usuariopayload= new stdclass();    
            $usuariopayload->id = $usuario->id;
            $usuariopayload->legajo = $usuario->legajo;
            $usuariopayload->clave = $usuario->clave;
            $usuariopayload->nombre = $usuario->nombre;
            $usuariopayload->apellido = $usuario->apellido;
            $usuariopayload->rol = $usuario->rol;
    
            $token = Autentificadora::crearJWT($usuariopayload, 120);
            $std= new stdclass();                 
            $std->exito = true;
            $std->status = 200;
            $std->jwt = $token;
            $newResponse = $response->withStatus(200);
            $newResponse->getBody()->write(json_encode($std));     
        }else{
            $std= new stdclass();                 
            $std->exito = false;
            $std->status = 403;
            $std->jwt = json_encode("false");
            $newResponse = $response->withStatus(403);
            $newResponse->getBody()->write(json_encode($std));   
        }     
    
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function mostrarArticulos(Request $request, Response $response, array $args) : Response {
        $articulo = Articulo::TraerTodos();
        $std= new stdclass();
        if($articulo){
            $std->exito = true;
            $std->mensaje = "articulos obtenidos!";
            $std->dato = json_encode($articulo);
            $std->status = 200;
            $newResponse = $response->withStatus(200);
            $newResponse->getBody()->write(json_encode($std));            
            return $newResponse->withHeader('Content-Type', 'application/json');
        }else{
            $std->exito = false;
            $std->mensaje = "articulo NO obtenidos!";
            $std->dato = "";
            $std->status = 424;
            $newResponse = $response->withStatus(424);
            $newResponse->getBody()->write(json_encode($std));            
            return $newResponse->withHeader('Content-Type', 'application/json');
        }
    }

    public function crearArticulo(Request $request, Response $response, array $args):Response{
        $array = $request->getParsedBody();
        $recibido = json_decode($array["articulo_json"]);        
        
        $articulo = new Articulo();
        $articulo->codigo_barra = $recibido->codigo_barra;
        $articulo->nombre = $recibido->nombre;
        $articulo->precio = $recibido->precio;    

        $archivos = $request->getUploadedFiles(); 
        $destino = "../src/fotos/";
        $extension = explode(".", $archivos['foto']->getClientFilename()); 
        $path =  $articulo->codigo_barra . "_" . $articulo->nombre . "." . $extension[1];

        $articulo->foto = $path;    
        
        $articulo->id = -1;     
        $std= new stdclass();
        
        if($articulo->Agregar()){            
            $archivos['foto']->moveTo($destino . $path);
            $std->exito = true;
            $std->mensaje = "articulo  agregado!";
            $std->status = 200;
            $newResponse = $response->withStatus(200);
            $newResponse->getBody()->write(json_encode($std));            
            return $newResponse->withHeader('Content-Type', 'application/json');
        }else{
            $std->exito = false;
            $std->mensaje = "ERROR! articulo no agregado"; 
            $std->status = 418;
            $newResponse = $response->withStatus(418);
            $newResponse->getBody()->write(json_encode($std));            
            return $newResponse->withHeader('Content-Type', 'application/json');
        }
    }

    public function borrarArticulo(Request $request, Response $response, array $args) : Response {       
        
        $codigo_barra = $args['codigo_barra'];
        
        if(Articulo::Eliminar($codigo_barra)){
            $std = new stdClass();
            $std->exito = true;
            $std->mensaje = 'Articulo borrado exitosamente';
            $std->status = 200;
            $newResponse = new ResponseMW();
            $newResponse->withStatus(200);
            $newResponse->getBody()->write(json_encode($std));
        }else{
            $std = new stdClass();
            $std->exito = false;
            $std->mensaje = 'Articulo NO existe ';
            $std->status = 418;
            $newResponse = new ResponseMW();
            $newResponse->withStatus(418);
            $newResponse->getBody()->write(json_encode($std));
        }           
    
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function modificarArticulo(Request $request, Response $response, array $args) : Response {

        $array = $request->getParsedBody();
        $articulo = json_decode($array["articulo_json"]);

        if ($articulo && isset($articulo->codigo_barra) && isset($articulo->precio) && isset($articulo->nombre)) {
            //$id = $articulo->id;
            $codigo_barra = $articulo->codigo_barra;
            $nombre = $articulo->nombre;
            $precio = $articulo->precio;

            $archivos = $request->getUploadedFiles(); 
            $destino = "../src/fotos/";
            $extension = explode(".", $archivos['foto']->getClientFilename()); 
            $path = $articulo->codigo_barra . "_" . $articulo->nombre . "." . $extension[1];            
            
            $articuloViejo  = Articulo::TraerUno($codigo_barra);

            if(Articulo::Modificar($codigo_barra,$nombre,$precio,$path))
            {    
                $rutaFoto = '../src/fotos/' . $articuloViejo->foto;
                if (file_exists($rutaFoto)) {
                    unlink($rutaFoto);
                }                
                $archivos['foto']->moveTo($destino . $path);
                $std = new stdClass();
                $std->exito = true;
                $std->mensaje = 'Articulo modificado exitosamente';
                $std->status = 200;
                $newResponse = new ResponseMW();
                $newResponse->withStatus(200);
                $newResponse->getBody()->write(json_encode($std));
            }    
            else{
                $std = new stdClass();
                $std->exito = false;
                $std->mensaje = 'Hubo un error.';
                $std->status = 418;
                $newResponse = new ResponseMW();
                $newResponse->withStatus(418);
                $newResponse->getBody()->write(json_encode($std));
            }
               
        } else {
            $std = new stdClass();
            $std->exito = false;
            $std->mensaje = 'Datos de articulo invalidos';
            $std->status = 418;
            $newResponse = new ResponseMW();
            $newResponse->withStatus(418);
            $newResponse->getBody()->write(json_encode($std));
        }

        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function loginTokenBearer(Request $request, Response $response, array $args) : Response {

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

        $status = $obj_rta->verificado ? 200 : 403;

        $newResponse = $response->withStatus($status);

        $newResponse->getBody()->write(json_encode($obj_rta));
        
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function mostrarListadoPdf(Request $request, Response $response, array $args) : Response {
        
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

        $datos_token = Autentificadora::obtenerPayload($tokenSinEspacios);
        
        $usuario_token = $datos_token->payload->data; 
        $rol = $usuario_token->rol;
        $apellido = $usuario_token->apellido;
        $clave = $usuario_token->clave;
        $legajo = $usuario_token->legajo;

        $clavePdf = "";

        switch($rol)
        {
            case "empleado":
                $clavePdf = $apellido;
                break;
            case "supervisor":
                $clavePdf = $clave;
                break;
            case "administrador":
                $clavePdf = $legajo;
                break;
        }

        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'P',
            'pagenumPrefix' => 'Pagina nro. ',
            'pagenumSuffrix' => ' - ',
            'nbpgPrefix' => ' de ',
            'nbpgSuffix' => ' páginas'
        ]);
        

        $mpdf->SetHeader('Barrio Cristian||{PAGENO}{nbpg}');
        $mpdf->SetTitle("Listado de Articulos");

        $articulos = Articulo::TraerTodos();

        $tabla = '<table class="table" border="1" align="center">
                    <thead>
                        <tr>
                            <th> ID   </th>
                            <th> CODIGO_BARRA     </th>
                            <th> NOMBRE     </th>
                            <th> PRECIO    </th>
                            <th> FOTO      </th>
                        </tr>
                    </thead>';

        foreach ($articulos as $articulo) {
            $tabla .= "<tr>
                            <td>".$articulo->id."</td>
                            <td>".$articulo->codigo_barra."</td>
                            <td>".$articulo->nombre."</td>
                            <td>".$articulo->precio."</td>
                            <td><img src='"."../src/fotos/".$articulo->foto."' width='100px' height='100px'/></td>
                            </tr>";
        }

        $tabla .= '</table>';

        $mpdf->WriteHTML($tabla);

        $mpdf->SetFooter('|{DATE j-m-Y}|');

        $clavePdf = "";

        switch($rol)
        {
            case "empleado":
                $clavePdf = $apellido;
                break;
            case "supervisor":
                $clavePdf = $clave;
                break;
            case "administrador":
                $clavePdf = $legajo;
                break;
        }

        $mpdf->SetProtection([],$clavePdf,$clavePdf);

        $contenidoPDF = $mpdf->Output('', 'S');

        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'inline; filename="listado_articulos.pdf"')
                             ->withHeader('Content-Length', strlen($contenidoPDF))
                             ->withBody(new \Slim\Psr7\Stream(fopen('php://temp', 'r+')));
        $response->getBody()->write($contenidoPDF);
    
        return $response;
    }

}