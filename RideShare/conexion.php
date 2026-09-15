<?php
class Conexion {
    private static ?Conexion $instancia = null;
    private mysqli $conexion;
    private function __construct() {
        $this->conexion = new mysqli('localhost','root','','rideshare');
        if ($this->conexion->connect_error) die('Error de conexión con la base de datos: ' . $this->conexion->connect_error);
        $this->conexion->set_charset('utf8mb4');
        $this->prepararEsquema();
    }
    private function prepararEsquema(): void {
        $db=$this->conexion;
        $cols=$db->query("SHOW COLUMNS FROM usuarios");
        $exist=[]; if($cols) while($r=$cols->fetch_assoc()) $exist[$r['Field']]=true;
        $adds=[
          'foto_perfil'=>"ALTER TABLE usuarios ADD foto_perfil VARCHAR(255) DEFAULT NULL",
          'color_vehiculo'=>"ALTER TABLE usuarios ADD color_vehiculo VARCHAR(40) DEFAULT NULL",
          'pico_placa'=>"ALTER TABLE usuarios ADD pico_placa TINYINT(1) NOT NULL DEFAULT 0"
        ];
        foreach($adds as $c=>$sql) if(!isset($exist[$c])) @$db->query($sql);
        $db->query("CREATE TABLE IF NOT EXISTS viajes (
          id INT AUTO_INCREMENT PRIMARY KEY,pasajero_id INT NOT NULL,conductor_id INT NULL,tipo_vehiculo ENUM('moto','carro') NOT NULL,
          origen VARCHAR(255) NOT NULL,destino VARCHAR(255) NOT NULL,origen_lat DECIMAL(10,7) NOT NULL,origen_lng DECIMAL(10,7) NOT NULL,
          destino_lat DECIMAL(10,7) NOT NULL,destino_lng DECIMAL(10,7) NOT NULL,medio_pago ENUM('efectivo','tarjeta','nequi') NOT NULL DEFAULT 'efectivo',
          precio INT NOT NULL DEFAULT 0,estado ENUM('solicitado','aceptado','en_curso','finalizado','cancelado','rechazado') NOT NULL DEFAULT 'solicitado',
          motivo_cancelacion VARCHAR(255) DEFAULT NULL,creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,aceptado_en DATETIME NULL,iniciado_en DATETIME NULL,finalizado_en DATETIME NULL,cancelado_en DATETIME NULL,
          INDEX(pasajero_id),INDEX(conductor_id),INDEX(estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->query("CREATE TABLE IF NOT EXISTS mensajes (id INT AUTO_INCREMENT PRIMARY KEY,viaje_id INT NOT NULL,emisor_id INT NOT NULL,mensaje TEXT NOT NULL,creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(viaje_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->query("CREATE TABLE IF NOT EXISTS calificaciones (id INT AUTO_INCREMENT PRIMARY KEY,viaje_id INT NOT NULL UNIQUE,pasajero_id INT NOT NULL,conductor_id INT NOT NULL,estrellas TINYINT NOT NULL,comentario TEXT,creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    private function __clone() {}
    public static function obtenerInstancia(): Conexion { if (self::$instancia===null) self::$instancia=new self(); return self::$instancia; }
    public function obtenerConexion(): mysqli { return $this->conexion; }
}
$conexion=Conexion::obtenerInstancia()->obtenerConexion();
