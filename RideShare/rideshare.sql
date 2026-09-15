-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-09-2026 a las 01:34:17
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
(9, 'daniela', 'diaz herrera', 'dulcepecado075@gmail.com', '3018127062', '$2y$10$t68HmvZCLCk9tSPPkjhA3OpOWBPOfRLfHiaa3QyMJlFtJSl6YqKMa', 'conductor', 'carro', 'CAR985', 'negra', 'perfil_f7904e3e2b89b96596ed0a9b.jpg', 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-15 14:18:47', '2026-09-15 14:18:47'),
(10, 'Cesar', 'Moreno', 'andres321963@hotmail.com', '3008532973', '$2y$10$rTD91qU3mnXYTkgRMcuPSuhEgtzvLaOkGDIsQI1l6D11EQ8ZmdAX6', 'pasajero', NULL, NULL, NULL, NULL, 0, 'activo', NULL, NULL, NULL, NULL, 0, '2026-09-15 17:58:02', '2026-09-15 17:58:02');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `viajes`
--
ALTER TABLE `viajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
