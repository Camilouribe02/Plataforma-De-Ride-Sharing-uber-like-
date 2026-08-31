<?php
class Conexion {
    private static ?Conexion $instancia = null;
    private mysqli $conexion;
    private function __construct() {
        $this->conexion = new mysqli('localhost','root','','rideshare');
        if ($this->conexion->connect_error) die('Error de conexión con la base de datos: ' . $this->conexion->connect_error);
        $this->conexion->set_charset('utf8mb4');
    }
    private function __clone() {}
    public static function obtenerInstancia(): Conexion {
        if (self::$instancia === null) self::$instancia = new self();
        return self::$instancia;
    }
    public function obtenerConexion(): mysqli { return $this->conexion; }
}
$conexion = Conexion::obtenerInstancia()->obtenerConexion();
