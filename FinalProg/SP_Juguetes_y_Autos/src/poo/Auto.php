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

class Auto
{
    public int $id;
    public string $color;
    public string $marca;
    public float $precio;
    public string $modelo;

    public function Agregar(){
        $retorno = false;
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();    
        $consulta =$objetoAccesoDato->RetornarConsulta ("INSERT INTO `autos`(`color`, `marca`, `precio`, `modelo`)
        VALUES (:color, :marca, :precio, :modelo)");   
        $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);       
        $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
        $consulta->execute();     
        if ($consulta->rowCount()>0) {
            $retorno = true;
        }  
        return $retorno;
    }

    public static function TraerTodos(){
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM autos");
        $consulta->execute();
        $auto = $consulta->fetchAll(PDO::FETCH_CLASS, "Auto");  
        return $auto;
    }

    public static function Eliminar($id){
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM autos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();      
        $retorno = false;
        if ($consulta->rowCount() > 0) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function Modificar($id, $color, $marca, $precio, $modelo)
    {
      $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
      $consulta = $objetoAccesoDato->retornarConsulta("UPDATE autos SET color = :color, marca = :marca, precio = :precio, modelo = :modelo WHERE id = :id");
      $consulta->bindValue(':id', $id, PDO::PARAM_INT);
      $consulta->bindValue(':color', $color, PDO::PARAM_STR);
      $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
      $consulta->bindValue(':precio', $precio, PDO::PARAM_STR);
      $consulta->bindValue(':modelo', $modelo, PDO::PARAM_STR);
      $consulta->execute();

      $retorno = false;
      if ($consulta->rowCount() > 0) 
      {
        $retorno = true;
      }
      return $retorno;
    }

    public static function TraerUno($id)
    {
        $objetoAccesoDato = AccesoDatos::obtenerObjetoAccesoDatos();
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM autos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $juguete = $consulta->fetchObject('Auto');

        return $juguete;
    }
}