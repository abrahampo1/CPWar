-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 15-06-2021 a las 01:20:07
-- Versión del servidor: 10.4.17-MariaDB
-- Versión de PHP: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cpwar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `vida` int(11) NOT NULL DEFAULT 1000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id`, `nombre`, `lat`, `lng`, `vida`) VALUES
(1, 'Vigo', 42.2261, -8.74294, 1000),
(2, 'Madrid', 40.4379, -3.74958, 5000),
(3, 'Pontevedra', 42.4339, -8.6481, 1000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gente`
--

CREATE TABLE `gente` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `pais` text NOT NULL,
  `clave` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `gente`
--

INSERT INTO `gente` (`id`, `nombre`, `pais`, `clave`) VALUES
(0, 'Nadie', '', ''),
(2, 'Abraham', 'CPCorp', 'clave');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `ciudades` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ciudades`)),
  `log` text NOT NULL,
  `actividad` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `partidas`
--

INSERT INTO `partidas` (`id`, `nombre`, `ciudades`, `log`, `actividad`) VALUES
(1, '1', '{\"ciudades\":[null,{\"nombre\":\"Vigo\",\"owner\":2,\"libre\":false,\"tropas\":[]},{\"nombre\":\"Madrid\",\"owner\":0,\"libre\":true,\"tropas\":[]},{\"nombre\":\"Pontevedra\",\"owner\":0,\"libre\":true,\"tropas\":[]}]}', '{\"gente\":{\"0\":{\"nombre\":\"Nadie\",\"pais\":\"\",\"dinero\":1000,\"propiedades\":[]},\"2\":{\"nombre\":\"Abraham\",\"pais\":\"CPCorp\",\"dinero\":1000,\"propiedades\":{\"1\":\"1\"}}}}', '<p>Abraham ha empezado en Vigo</p><p>Abraham ha empezado en Vigo</p><p>Abraham ha empezado en Pontevedra</p><p>Abraham ha empezado en Pontevedra</p><p>Abraham ha empezado en Vigo</p><p>Abraham ha empezado en Pontevedra</p><p>Abraham ha empezado en Vigo</p><p>Abraham ha empezado en Vigo</p><p>Abraham ha empezado en Pontevedra</p><p>Abraham ha empezado en Vigo</p>');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_tropas`
--

CREATE TABLE `tipo_tropas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `efectivo` text NOT NULL,
  `debil` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_tropas`
--

INSERT INTO `tipo_tropas` (`id`, `nombre`, `efectivo`, `debil`) VALUES
(1, 'tierra', '', 'aire;tanque'),
(2, 'aire', 'tierra', 'antiaire'),
(3, 'tanque', 'tierra', 'bomba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tropas`
--

CREATE TABLE `tropas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `descripcion` text NOT NULL,
  `tipo` int(11) NOT NULL,
  `costo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tropas`
--

INSERT INTO `tropas` (`id`, `nombre`, `descripcion`, `tipo`, `costo`, `cantidad`) VALUES
(1, 'Soldado', 'Soldados de las fuerzas armadas terrestres.', 1, 1000, 1000);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `gente`
--
ALTER TABLE `gente`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `tipo_tropas`
--
ALTER TABLE `tipo_tropas`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `tropas`
--
ALTER TABLE `tropas`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `gente`
--
ALTER TABLE `gente`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_tropas`
--
ALTER TABLE `tipo_tropas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tropas`
--
ALTER TABLE `tropas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
