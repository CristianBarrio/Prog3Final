<?php

class AccesoDatos
{
    private static AccesoDatos $objAccesoDatos;
    private PDO $objPDO;

    private function __construct()
    {
        try {
            $usuario = 'root';
            $clave = '';

            $this->objPDO = new PDO('mysql:host=localhost;dbname=kiosco_bd;charset=utf8', $usuario, $clave);
        } catch (PDOException $e) {
            print "Error!!!<br>" . $e->getMessage();
            die();
        }
    }

    public function retornarConsulta(string $sql)
    {
        return $this->objPDO->prepare($sql);
    }

    public function retornarUltimoIdInsertado()
    { 
        return $this->objPDO->lastInsertId(); 
    }

    public static function obtenerObjetoAccesoDatos(): AccesoDatos
    {
        if (!isset(self::$objAccesoDatos)) {
            self::$objAccesoDatos = new AccesoDatos();
        }
        return self::$objAccesoDatos;
    }

    public function __clone()
    {
        trigger_error('La clonacion de este objeto no esta permitida!!!', E_USER_ERROR);
    }
}
