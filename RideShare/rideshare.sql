CREATE database rideshare;
USE rideshare;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2026 a las 16:23:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rideshare`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL,
  `viaje_id` int(11) NOT NULL,
  `pasajero_id` int(11) NOT NULL,
  `conductor_id` int(11) NOT NULL,
  `estrellas` tinyint(4) NOT NULL,
  `comentario` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id`, `viaje_id`, `pasajero_id`, `conductor_id`, `estrellas`, `comentario`, `creado_en`) VALUES
(1, 9, 1, 2, 5, 'muy bien', '2026-09-13 23:51:48'),
(2, 10, 1, 2, 4, 'muy bien', '2026-09-14 00:01:25'),
(3, 12, 3, 4, 5, 'muy bien', '2026-09-14 00:15:04'),
(4, 16, 6, 5, 5, 'muy bien', '2026-09-14 00:32:03'),
(5, 17, 6, 5, 5, 'execelente', '2026-09-14 00:37:52'),
(6, 18, 6, 5, 5, 'muy bien', '2026-09-14 00:56:32'),
(7, 19, 6, 5, 1, 'se paso todos los semaforos', '2026-09-14 01:15:08'),
(8, 20, 6, 7, 5, 'el condcutor esta muy bueno', '2026-09-14 01:23:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `viaje_id` int(11) NOT NULL,
  `emisor_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `viaje_id`, `emisor_id`, `mensaje`, `creado_en`) VALUES
(1, 2, 1, 'hola mi amor', '2026-09-13 22:35:56'),
(2, 2, 2, 'hola bubu', '2026-09-13 22:36:09'),
(3, 5, 1, 'hola te estoy esperando', '2026-09-13 23:14:19'),
(4, 6, 1, 'hola', '2026-09-13 23:19:40'),
(5, 6, 1, 'mi mama esta cansona', '2026-09-13 23:20:01'),
(6, 7, 2, 'hola', '2026-09-13 23:30:44'),
(7, 7, 2, 'donde estas', '2026-09-13 23:30:48'),
(8, 7, 1, 'ya llegue estoy afuera', '2026-09-13 23:30:59'),
(9, 19, 5, 'compa donde anda ya estoy aca', '2026-09-14 01:13:00'),
(10, 19, 5, 'que paso', '2026-09-14 01:13:02'),
(11, 19, 6, 'ya voy dame 5 minutos', '2026-09-14 01:13:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('pasajero','conductor','admin') NOT NULL DEFAULT 'pasajero',
  `tipo_vehiculo` enum('moto','carro') DEFAULT NULL,
  `placa` varchar(10) DEFAULT NULL,
  `color_vehiculo` varchar(40) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `pico_placa` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `codigo_recuperacion` varchar(64) DEFAULT NULL,
  `codigo_expira` datetime DEFAULT NULL,
  `intentos_codigo` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `rol`, `tipo_vehiculo`, `placa`, `color_vehiculo`, `foto_perfil`, `pico_placa`, `estado`, `token_recuperacion`, `token_expira`, `codigo_recuperacion`, `codigo_expira`, `intentos_codigo`, `creado_en`, `actualizado_en`) VALUES
(5, 'gustavo', 'rodriguez', 'gustiuribe885@gmail.com', '3105847572', '$2y$10$ps5iNcIwoz1S9nwmxRZ9SOdhDsaCTkBobH4xye9tzy9ZcIQQGRaK2', 'conductor', 'moto', 'RKB44C', 'negra', NULL, 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-14 00:28:19', '2026-09-14 00:28:19'),
(6, 'camilo', 'uribe diaz', 'juancamilo.uribediaz0210@gmail.com', '3154208659', '$2y$10$mbEbqKo63ZrAcuSaQrOBNu3.98O2xzwKF7L2DEo9MLh86LEGAOMWK', 'pasajero', NULL, NULL, NULL, NULL, 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-14 00:28:55', '2026-09-14 00:28:55'),
(7, 'Carlos', 'sanchez', 'gusta34.ta@gmail.com', '3013231348', '$2y$10$cEZ/HTtEXS8Seylfm/evQuktSsUq/idF97ZDfp3ZUekKUC5MXszMe', 'conductor', 'carro', 'VUS138', 'blanco', 'perfil_951ebdfdf3aa805b87477e60.jpg', 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-14 01:17:35', '2026-09-14 01:19:05'),
(9, 'daniela', 'diaz herrera', 'dulcepecado075@gmail.com', '3018127062', '$2y$10$t68HmvZCLCk9tSPPkjhA3OpOWBPOfRLfHiaa3QyMJlFtJSl6YqKMa', 'conductor', 'carro', 'CAR985', 'negra', 'perfil_f7904e3e2b89b96596ed0a9b.jpg', 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-15 14:18:47', '2026-09-15 14:18:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viajes`
--

CREATE TABLE `viajes` (
  `id` int(11) NOT NULL,
  `pasajero_id` int(11) NOT NULL,
  `conductor_id` int(11) DEFAULT NULL,
  `tipo_vehiculo` enum('moto','carro') NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `origen_lat` decimal(10,7) NOT NULL,
  `origen_lng` decimal(10,7) NOT NULL,
  `destino_lat` decimal(10,7) NOT NULL,
  `destino_lng` decimal(10,7) NOT NULL,
  `medio_pago` enum('efectivo','tarjeta','nequi') NOT NULL DEFAULT 'efectivo',
  `precio` int(11) NOT NULL DEFAULT 0,
  `estado` enum('solicitado','aceptado','en_curso','finalizado','cancelado','rechazado') NOT NULL DEFAULT 'solicitado',
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `aceptado_en` datetime DEFAULT NULL,
  `iniciado_en` datetime DEFAULT NULL,
  `finalizado_en` datetime DEFAULT NULL,
  `cancelado_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `viajes`
--

INSERT INTO `viajes` (`id`, `pasajero_id`, `conductor_id`, `tipo_vehiculo`, `origen`, `destino`, `origen_lat`, `origen_lng`, `destino_lat`, `destino_lng`, `medio_pago`, `precio`, `estado`, `motivo_cancelacion`, `creado_en`, `aceptado_en`, `iniciado_en`, `finalizado_en`, `cancelado_en`) VALUES
(2, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159031, -73.1408177, 7.1051164, -73.1235240, 'efectivo', 13814, 'finalizado', NULL, '2026-09-13 22:35:35', '2026-09-13 17:35:40', '2026-09-13 17:38:08', '2026-09-13 17:40:04', NULL),
(3, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Clínica Bucaramanga, Calle 53, Campestre, Comuna 12 - Cabecera del Llano, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680003, Colombia', 7.1159669, -73.1408425, 7.1115599, -73.1096781, 'efectivo', 16456, 'cancelado', 'El conductor está tardando', '2026-09-13 22:51:55', '2026-09-13 17:51:58', NULL, NULL, '2026-09-13 18:05:17'),
(4, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1158861, -73.1407932, 7.1051164, -73.1235240, 'efectivo', 13808, 'finalizado', NULL, '2026-09-13 23:05:48', '2026-09-13 18:05:52', '2026-09-13 18:06:07', '2026-09-13 18:07:50', NULL),
(5, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159683, -73.1408421, 7.1051164, -73.1235240, 'efectivo', 13825, 'finalizado', NULL, '2026-09-13 23:13:59', '2026-09-13 18:14:04', '2026-09-13 18:14:27', '2026-09-13 18:15:27', NULL),
(6, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159421, -73.1408032, 7.1051164, -73.1235240, 'efectivo', 13816, 'finalizado', NULL, '2026-09-13 23:19:17', '2026-09-13 18:19:20', '2026-09-13 18:20:14', '2026-09-13 18:23:17', NULL),
(7, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159282, -73.1408460, 7.1051164, -73.1235240, 'efectivo', 13821, 'finalizado', NULL, '2026-09-13 23:30:35', '2026-09-13 18:30:37', '2026-09-13 18:31:09', '2026-09-13 18:34:34', NULL),
(8, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159677, -73.1408299, 7.1051164, -73.1235240, 'efectivo', 13823, 'finalizado', NULL, '2026-09-13 23:39:30', '2026-09-13 18:39:33', '2026-09-13 18:40:48', '2026-09-13 18:42:31', NULL),
(9, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159458, -73.1408096, 7.1051164, -73.1235240, 'efectivo', 13817, 'finalizado', NULL, '2026-09-13 23:45:06', '2026-09-13 18:45:09', '2026-09-13 18:49:19', '2026-09-13 18:51:32', NULL),
(10, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159739, -73.1408400, 7.1051164, -73.1235240, 'efectivo', 13825, 'finalizado', NULL, '2026-09-13 23:59:19', '2026-09-13 18:59:23', '2026-09-13 18:59:25', '2026-09-13 19:01:17', NULL),
(11, 1, 2, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1158918, -73.1408141, 7.1051164, -73.1235240, 'efectivo', 13812, 'cancelado', 'El conductor está tardando', '2026-09-14 00:06:34', '2026-09-13 19:06:37', NULL, NULL, '2026-09-13 19:09:22'),
(12, 3, 4, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159225, -73.1408402, 7.1051164, -73.1235240, 'efectivo', 13820, 'finalizado', NULL, '2026-09-14 00:13:52', '2026-09-13 19:13:54', '2026-09-13 19:14:05', '2026-09-13 19:14:49', NULL),
(13, 3, 4, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Clinica Chicamocha - Sede Puerta del Sol, Calle 63, Mercedes, Comuna 12 - Cabecera del Llano, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159510, -73.1408136, 7.1070085, -73.1124252, 'efectivo', 14252, 'cancelado', 'Cancelado por conductor', '2026-09-14 00:18:21', '2026-09-13 19:18:24', NULL, NULL, '2026-09-13 19:19:03'),
(14, 3, 4, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159509, -73.1408175, 7.1051164, -73.1235240, 'efectivo', 13819, 'cancelado', 'El conductor está tardando', '2026-09-14 00:21:03', '2026-09-13 19:21:06', NULL, NULL, '2026-09-13 19:21:28'),
(15, 3, 4, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Clinica Chicamocha - Sede Puerta del Sol, Calle 63, Mercedes, Comuna 12 - Cabecera del Llano, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159737, -73.1408401, 7.1070085, -73.1124252, 'efectivo', 14259, 'cancelado', 'Cancelado por conductor', '2026-09-14 00:25:02', '2026-09-13 19:25:06', NULL, NULL, '2026-09-13 19:25:10'),
(16, 6, 5, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Iglesia Pentecostal Unida de Colombia, 54 - 19, Diagonal 17, Comuna Caldas - El Reposo, Comuna 4 Caldas-Reposo, Perímetro Urbano de Floridablanca, Floridablanca, Metropolitana, Santander, RAP Gran Santander, 006547, Colombia', 7.1159233, -73.1408454, 7.0894797, -73.0940375, 'efectivo', 20182, 'finalizado', NULL, '2026-09-14 00:30:01', '2026-09-13 19:30:04', '2026-09-13 19:30:10', '2026-09-13 19:31:20', NULL),
(17, 6, 5, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159192, -73.1408397, 7.1051164, -73.1235240, 'efectivo', 13819, 'finalizado', NULL, '2026-09-14 00:36:00', '2026-09-13 19:36:02', '2026-09-13 19:36:32', '2026-09-13 19:37:44', NULL),
(18, 6, 5, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1157915, -73.1407292, 7.1051164, -73.1235240, 'efectivo', 13787, 'finalizado', NULL, '2026-09-14 00:55:15', '2026-09-13 19:55:18', '2026-09-13 19:55:22', '2026-09-13 19:56:24', NULL),
(19, 6, 5, 'moto', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Clinica Chicamocha - Sede Puerta del Sol, Calle 63, Mercedes, Comuna 12 - Cabecera del Llano, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1158821, -73.1408145, 7.1070085, -73.1124252, 'efectivo', 14245, 'finalizado', NULL, '2026-09-14 01:12:35', '2026-09-13 20:12:46', '2026-09-13 20:13:30', '2026-09-13 20:14:53', NULL),
(20, 6, 7, 'carro', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Parque de La Salud, Comuna Bosque - Molinos, Comuna 5 Bosque-Molinos, Perímetro Urbano de Floridablanca, Floridablanca, Metropolitana, Santander, RAP Gran Santander, Colombia', 7.1159324, -73.1407973, 7.0721545, -73.1097896, 'nequi', 28160, 'finalizado', NULL, '2026-09-14 01:21:11', '2026-09-13 20:21:15', '2026-09-13 20:22:26', '2026-09-13 20:23:27', NULL),
(21, 6, 8, 'carro', 'Carrera 8 Oeste, Santander, Comuna 4 - Occidental, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680006, Colombia', 'Unidades Tecnológicas de Santander - UTS, 12, Calle de Los Estudiantes, Ciudadela Real de Minas, Comuna 7 - La Ciudadela, Perímetro Urbano Bucaramanga, Bucaramanga, Metropolitana, Santander, RAP Gran Santander, 680005, Colombia', 7.1159686, -73.1408394, 7.1051164, -73.1235240, 'efectivo', 18766, 'en_curso', NULL, '2026-09-15 14:16:41', '2026-09-15 09:16:44', '2026-09-15 09:17:00', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `viaje_id` (`viaje_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `viaje_id` (`viaje_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- Indices de la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasajero_id` (`pasajero_id`),
  ADD KEY `conductor_id` (`conductor_id`),
  ADD KEY `estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `viajes`
--
ALTER TABLE `viajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
