-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 06:33:30
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
-- Base de datos: `yess_sweet_garden`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `usuario`, `password`, `fecha_registro`) VALUES
(2, 'Yess', '$2y$10$z84dbUvpODKRsUcmDFCgv.g3YGghfFS.1AblBJlnGou3vFdhb/YSq', '2026-11-15 22:47:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id`, `usuario_id`, `fecha_creacion`) VALUES
(1, 1, '2026-11-16 01:36:40'),
(2, 2, '2026-11-16 02:10:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_detalles`
--

CREATE TABLE `carrito_detalles` (
  `id` int(11) NOT NULL,
  `carrito_id` int(11) NOT NULL,
  `prenda_talla_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(30) DEFAULT 'Completada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `usuario_id`, `total`, `fecha_compra`, `estado`) VALUES
(1, 1, 250.00, '2026-11-16 02:03:11', 'Completada'),
(2, 1, 250.00, '2026-11-16 02:04:39', 'Completada'),
(3, 1, 250.00, '2026-11-16 02:05:31', 'Completada'),
(4, 2, 900.00, '2026-11-16 02:11:43', 'Completada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_detalles`
--

CREATE TABLE `compra_detalles` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `prenda_id` int(11) NOT NULL,
  `nombre_prenda` varchar(150) NOT NULL,
  `talla` varchar(30) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_ruta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compra_detalles`
--

INSERT INTO `compra_detalles` (`id`, `compra_id`, `prenda_id`, `nombre_prenda`, `talla`, `cantidad`, `precio`, `imagen_ruta`) VALUES
(1, 1, 14, 'Falda Jardín', 'XS', 1, 250.00, '7e889da0832818b0d8fc512c7a53699d.jpg'),
(2, 2, 14, 'Falda Jardín', 'XS', 1, 250.00, '7e889da0832818b0d8fc512c7a53699d.jpg'),
(3, 3, 14, 'Falda Jardín', 'XS', 1, 250.00, '7e889da0832818b0d8fc512c7a53699d.jpg'),
(4, 4, 31, 'Bufanda Winter Bloom', 'XS', 1, 200.00, 'd25bb155ad01fbb5f782b13f033ea237.jpg'),
(5, 4, 26, 'Abrigo Olive', 'L', 1, 700.00, '00b164df216ea3ccbba00571c95e3f8c.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `prenda_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `favoritos`
--

INSERT INTO `favoritos` (`id`, `usuario_id`, `prenda_id`, `fecha_registro`) VALUES
(2, 1, 14, '2026-11-16 01:36:59'),
(3, 2, 26, '2026-11-16 02:10:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prendas`
--

CREATE TABLE `prendas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `temporada` enum('Primavera','Verano','Otoño','Invierno') NOT NULL,
  `talla` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagen` mediumblob DEFAULT NULL,
  `imagen_ruta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prendas`
--

INSERT INTO `prendas` (`id`, `nombre`, `descripcion`, `categoria`, `precio`, `temporada`, `talla`, `color`, `stock`, `fecha_registro`, `imagen`, `imagen_ruta`) VALUES
(12, 'Corset Primaveral', 'Corset floral con diseño romántico y detalles delicados.', 'Blusa', 350.00, 'Primavera', 'XS', 'Rosa Pastel', 26, '2026-11-15 23:24:41', NULL, '7380a2f470801890191b37293819aae6.jpg'),
(13, 'Pantalón Brisa', 'Pantalón de corte amplio y tela ligera para días cálidos.', 'Pantalón', 420.00, 'Primavera', 'XS', 'Beige', 22, '2026-11-15 23:49:55', NULL, '9a33ac404f376a055ccfc56f1e94b0af.jpg'),
(14, 'Falda Jardín', 'Falda midi con estampado de flores pequeñas y caída ligera.', 'Falda', 250.00, 'Primavera', 'XS', 'Verde', 19, '2026-11-15 23:50:48', NULL, '7e889da0832818b0d8fc512c7a53699d.jpg'),
(15, 'Blusa Flor de Abril', 'Blusa de manga corta abullonada con estampado primaveral.', 'Blusa', 200.00, 'Primavera', 'XS', 'Blanco', 16, '2026-11-15 23:53:13', NULL, '21b655ac964f1285cb7f3301199da0ba.jpg'),
(16, 'Vestido Rosé', 'Vestido corto de corte fluido con estampado de rosas.', 'Vestido', 350.00, 'Primavera', 'M', 'Cafe', 31, '2026-11-15 23:54:08', NULL, '0ba501e4573b6b7191a693ee5b36b0a1.jpg'),
(17, 'Top Sol', 'Top ligero sin mangas ideal para combinar con prendas', 'Blusa', 200.00, 'Verano', 'S', 'Amarillo', 13, '2026-11-15 23:56:19', NULL, '5337565ac69205276f18b2fdd736959c.jpg'),
(18, 'Short Blue Sky', 'Short de mezclilla con diseño casual y acabado deslavado.', 'Pantalón', 150.00, 'Verano', 'M', 'Azul Claro', 12, '2026-11-15 23:57:05', NULL, '86c946ce159b37f046c06425fdfbb486.jpg'),
(19, 'Vestido Coral', 'Vestido sin mangas de tela fresca y silueta cómoda.', 'Vestido', 400.00, 'Verano', 'M', 'Coral', 8, '2026-11-15 23:57:40', NULL, '2433dff086af09fbc20c572f40bf06e6.jpg'),
(20, 'Falda Lavanda', 'Falda plisada de corte corto con estilo juvenil.', 'Falda', 200.00, 'Verano', 'XS', 'Lila', 14, '2026-11-15 23:58:22', NULL, '299f714f3276050504087940d8c3b6bd.jpg'),
(21, 'Blusa Sunshine', 'Blusa ligera de manga corta.', 'Blusa', 200.00, 'Verano', 'XS', 'Amarillo', 16, '2026-11-15 23:59:14', NULL, '170bb1bb774e88313c3544494aa80007.jpg'),
(22, 'Suéter Autumn', 'Suéter tejido de corte holgado para días frescos.', 'Suéter', 500.00, 'Otoño', 'XS', 'Cafe', 9, '2026-11-16 00:00:01', NULL, '72d468d07d1e1930846cd9e43fa5828c.jpg'),
(23, 'Pantalón Canela', 'Pantalón de pana con corte recto y estilo vintage.', 'Pantalón', 400.00, 'Otoño', 'L', 'Cafe', 8, '2026-11-16 00:02:10', NULL, '1e45432e8125fa8a684d741bc9917359.jpg'),
(24, 'Falda Maple', 'Falda midi con estampado de cuadros en tonos otoñales.', 'Falda', 300.00, 'Otoño', 'S', 'Café', 10, '2026-11-16 00:03:07', NULL, '41fb514f61b1ebec6ce0d22f06a94e5d.jpg'),
(25, 'Cardigan Cozy', 'Cardigan tejido con botones frontales y textura suave.', 'Suéter', 400.00, 'Otoño', 'S', 'Crema', 15, '2026-11-16 00:03:40', NULL, 'bc0c629f9386af8ae0fc1bbddcf64887.jpg'),
(26, 'Abrigo Olive', 'Abrigo ligero de corte clásico para complementar atuendos otoñales.', 'Abrigo', 700.00, 'Otoño', 'XS', 'Verde olivo', 17, '2026-11-16 00:04:20', NULL, '00b164df216ea3ccbba00571c95e3f8c.jpg'),
(27, 'Suéter Pink Cloud', 'Suéter de tejido grueso y corte holgado para mantener el calor.', 'Suéter', 400.00, 'Invierno', 'XS', 'Rosa', 49, '2026-11-16 00:05:03', NULL, '4aa8fe9a3d05924fd0362a2d16d92f75.jpg'),
(28, 'Abrigo Midnight', 'Abrigo largo de estilo elegante con botones frontales.', 'Abrigo', 900.00, 'Invierno', 'XS', 'Negro', 35, '2026-11-16 00:05:45', NULL, '60b5d8ba08ccf6d1ad278b5d88044165.jpg'),
(29, 'Suéter Burgundy', 'Suéter de cuello alto con tejido suave y diseño clásico.', 'Suéter', 300.00, 'Invierno', 'XS', 'Vino', 34, '2026-11-16 00:06:22', NULL, '05d5f21fb4bb643c67608a28af444457.jpg'),
(30, 'Vestido Snow', 'Vestido tejido de manga larga con silueta cómoda y elegante.', 'Vestido', 600.00, 'Invierno', 'XS', 'Blanco', 27, '2026-11-16 00:06:49', NULL, '9185ce4777a7b95dcf3eb4368d20d41d.jpg'),
(31, 'Bufanda Winter Bloom', 'Bufanda tejida de textura suave para complementar atuendos invernales.', 'Otro', 200.00, 'Invierno', 'XS', 'Gris', 35, '2026-11-16 00:07:24', NULL, 'd25bb155ad01fbb5f782b13f033ea237.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prenda_tallas`
--

CREATE TABLE `prenda_tallas` (
  `id` int(11) NOT NULL,
  `prenda_id` int(11) NOT NULL,
  `talla` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prenda_tallas`
--

INSERT INTO `prenda_tallas` (`id`, `prenda_id`, `talla`, `stock`) VALUES
(12, 12, 'S', 8),
(13, 13, 'L', 10),
(14, 14, 'M', 15),
(15, 15, 'L', 8),
(16, 16, 'L', 20),
(17, 17, 'L', 10),
(18, 18, 'M', 9),
(19, 19, 'L', 4),
(20, 20, 'M', 8),
(21, 21, 'S', 14),
(22, 22, 'XS', 6),
(23, 23, 'XL', 5),
(24, 24, 'S', 7),
(25, 25, 'L', 9),
(26, 27, 'Unitalla', 0),
(27, 28, 'L', 10),
(28, 29, 'S', 5),
(29, 30, 'M', 5),
(30, 31, 'Unitalla', 0),
(48, 31, 'XS', 9),
(49, 31, 'S', 2),
(50, 31, 'M', 8),
(51, 31, 'L', 7),
(52, 31, 'XL', 9),
(53, 27, 'XS', 10),
(54, 27, 'S', 11),
(55, 27, 'M', 20),
(56, 27, 'L', 8),
(57, 27, 'XL', 0),
(58, 30, 'XS', 8),
(59, 30, 'S', 7),
(61, 30, 'L', 5),
(62, 30, 'XL', 2),
(63, 29, 'XS', 7),
(65, 29, 'M', 5),
(66, 29, 'L', 10),
(67, 29, 'XL', 7),
(68, 28, 'XS', 3),
(69, 28, 'S', 6),
(70, 28, 'M', 9),
(72, 28, 'XL', 7),
(73, 26, 'XS', 7),
(74, 26, 'S', 3),
(75, 26, 'M', 3),
(76, 26, 'L', 2),
(77, 26, 'XL', 2),
(78, 12, 'XS', 7),
(80, 12, 'M', 4),
(81, 12, 'L', 4),
(82, 12, 'XL', 3),
(83, 13, 'XS', 3),
(84, 13, 'S', 3),
(85, 13, 'M', 3),
(87, 13, 'XL', 3),
(93, 14, 'XS', 0),
(95, 14, 'L', 2),
(96, 14, 'XL', 2),
(97, 25, 'S', 3),
(98, 25, 'M', 3),
(101, 24, 'M', 3),
(102, 23, 'L', 3),
(104, 20, 'XS', 3),
(105, 20, 'S', 3),
(108, 22, 'XL', 3),
(109, 21, 'XS', 2),
(111, 19, 'M', 4),
(114, 18, 'XL', 3),
(115, 17, 'S', 3),
(117, 15, 'XS', 4),
(118, 15, 'S', 4),
(120, 16, 'M', 3),
(122, 16, 'XL', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `fecha_registro`) VALUES
(1, 'Yessenia Gausin', 'yesseniagausin7@gmail.com', '$2y$10$utHlBgyuKutG/4RsIAEwWedgJK0p2.Cti2kYMxoB7E2XaDpC0R6R6', '2026-11-16 01:19:43'),
(2, 'Elena Ramirez', 'elenarmz@gmail.com', '$2y$10$qxzASbeJIky1CLl5xcfbw.g3JH.B0dVfVFGPZZsgxr2/OAzBPWjdG', '2026-11-16 02:09:51');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `carrito_detalles`
--
ALTER TABLE `carrito_detalles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_talla_carrito` (`carrito_id`,`prenda_talla_id`),
  ADD KEY `fk_detalles_tallas` (`prenda_talla_id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_compras_usuario` (`usuario_id`);

--
-- Indices de la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalles_compra` (`compra_id`),
  ADD KEY `fk_detalles_prenda` (`prenda_id`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `favorito_unico` (`usuario_id`,`prenda_id`),
  ADD KEY `fk_favoritos_prendas` (`prenda_id`);

--
-- Indices de la tabla `prendas`
--
ALTER TABLE `prendas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prenda_tallas`
--
ALTER TABLE `prenda_tallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unica_talla_prenda` (`prenda_id`,`talla`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `carrito_detalles`
--
ALTER TABLE `carrito_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `prendas`
--
ALTER TABLE `prendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `prenda_tallas`
--
ALTER TABLE `prenda_tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `fk_carrito_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carrito_detalles`
--
ALTER TABLE `carrito_detalles`
  ADD CONSTRAINT `fk_detalles_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carrito` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalles_tallas` FOREIGN KEY (`prenda_talla_id`) REFERENCES `prenda_tallas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  ADD CONSTRAINT `fk_detalles_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalles_prenda` FOREIGN KEY (`prenda_id`) REFERENCES `prendas` (`id`);

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `fk_favoritos_prendas` FOREIGN KEY (`prenda_id`) REFERENCES `prendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favoritos_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prenda_tallas`
--
ALTER TABLE `prenda_tallas`
  ADD CONSTRAINT `fk_tallas_prendas` FOREIGN KEY (`prenda_id`) REFERENCES `prendas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
