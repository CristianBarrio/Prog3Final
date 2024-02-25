<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
use Mpdf\Mpdf;

require_once "AccesoDatos.php";
require_once "Usuario.php";
require_once __DIR__ . "/autentificadora.php";

class Articulo
{
    public int $id;
    public string $codigo_barra;
    public string $nombre;
    public float $precio;
    public string $foto;

    public function Agregar(){
        $retorno = false;
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();    
        $consulta =$objetoAccesoDato->RetornarConsulta ("INSERT INTO `articulos`(`codigo_barra`, `nombre`, `precio`, `foto`)
        VALUES (:codigo_barra, :nombre, :precio, :foto)");   
        $consulta->bindValue(':codigo_barra', $this->codigo_barra, PDO::PARAM_STR);       
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();     
        if ($consulta->rowCount()>0) {
            $retorno = true;
        }  
        return $retorno;
    }

    public static function TraerTodos(){
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM articulos");
        $consulta->execute();
        $auto = $consulta->fetchAll(PDO::FETCH_CLASS, "Articulo");  
        return $auto;
    }

    public static function Eliminar($codigo_barra){
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM articulos WHERE codigo_barra = :codigo_barra");
        $consulta->bindValue(':codigo_barra', $codigo_barra, PDO::PARAM_INT);
        $consulta->execute();      
        $retorno = false;
        if ($consulta->rowCount() > 0) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function Modificar($codigo_barra, $nombre, $precio, $foto)
    {
      $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
      $consulta = $objetoAccesoDato->retornarConsulta("UPDATE articulos SET nombre = :nombre, precio = :precio, foto = :foto WHERE codigo_barra = :codigo_barra");
      $consulta->bindValue(':codigo_barra', $codigo_barra, PDO::PARAM_STR);    
      $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
      $consulta->bindValue(':precio', $precio, PDO::PARAM_INT);
      $consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
      $consulta->execute();

      $retorno = false;
      if ($consulta->rowCount() > 0) 
      {
        $retorno = true;
      }
      return $retorno;
    }

    public static function TraerUno($codigo_barra)
    {
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM articulos WHERE codigo_barra = :codigo_barra");
        $consulta->bindValue(':codigo_barra', $codigo_barra, PDO::PARAM_STR);
        $consulta->execute();

        $juguete = $consulta->fetchObject('Articulo');

        return $juguete;
    }

    public static function ValidarCodigoExiste($codigo_barra)
    {
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM articulos WHERE codigo_barra=:codigo_barra");
  
        $consulta->bindValue(':codigo_barra', $codigo_barra, PDO::PARAM_STR);
        $consulta->execute();
  
        $usuario = false;
  
        if ($consulta->rowCount()>0) {
            $usuario= $consulta->fetchObject('Articulo');
        }
  
        return $usuario;
    }
}