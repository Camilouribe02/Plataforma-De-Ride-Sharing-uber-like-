CREATE DATABASE IF NOT EXISTS rideshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rideshare;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    rol ENUM('pasajero','conductor','admin') NOT NULL DEFAULT 'pasajero',
    tipo_vehiculo ENUM('moto','carro') DEFAULT NULL,
    placa VARCHAR(10) DEFAULT NULL UNIQUE,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    token_recuperacion VARCHAR(255) DEFAULT NULL,
    token_expira DATETIME DEFAULT NULL,
    codigo_recuperacion VARCHAR(64) DEFAULT NULL,
    codigo_expira DATETIME DEFAULT NULL,
    intentos_codigo TINYINT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Usuario de prueba:
-- correo: demo@rideshare.com
-- contraseña: 12345678
-- Para crear usuarios reales usa registro.php.

-- Si ya creaste la tabla usuarios anteriormente, ejecuta una sola vez:
-- ALTER TABLE usuarios ADD COLUMN tipo_vehiculo ENUM('moto','carro') DEFAULT NULL AFTER rol;
-- ALTER TABLE usuarios ADD COLUMN placa VARCHAR(10) DEFAULT NULL UNIQUE AFTER tipo_vehiculo;
