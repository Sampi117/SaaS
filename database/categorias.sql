-- Primero, eliminamos las tablas si existen (en orden inverso por las claves foráneas)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `categoria_materiales`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `tb_materiales_indirectos`;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabla de Materiales Indirectos
CREATE TABLE `tb_materiales_indirectos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `costo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cantidad` int NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Categorías
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `costos_indirectos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `costos_financieros` decimal(10,2) NOT NULL DEFAULT '0.00',
  `costos_distribucion` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('Activo','Inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación entre Categorías y Materiales Indirectos
CREATE TABLE `categoria_materiales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_categoria` int NOT NULL,
  `id_material` int NOT NULL,
  `cantidad` decimal(10,3) NOT NULL DEFAULT '1.000',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_material_categoria` (`id_categoria`,`id_material`),
  KEY `id_material` (`id_material`),
  CONSTRAINT `categoria_materiales_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categoria_materiales_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `tb_materiales_indirectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunos materiales indirectos de ejemplo (opcional)
INSERT INTO `tb_materiales_indirectos` (`nombre`, `unidad_medida`, `costo`, `cantidad`) VALUES
('Tornillos', 'Unidad', 0.50, 100),
('Pegamento', 'Litro', 15.00, 10),
('Lija', 'Hoja', 2.50, 50),
('Barniz', 'Litro', 25.00, 5),
('Cinta métrica', 'Unidad', 8.00, 20);

-- Insertar algunas categorías de ejemplo (opcional)
INSERT INTO `categorias` (`nombre`, `costos_indirectos`, `costos_financieros`, `costos_distribucion`, `estado`) VALUES
('Muebles de Oficina', 1500.00, 800.00, 1200.00, 'Activo'),
('Muebles de Hogar', 2000.00, 1000.00, 1500.00, 'Activo'),
('Muebles de Exteriores', 1800.00, 900.00, 1300.00, 'Activo');

-- Insertar materiales a categorías (ejemplo)
-- NOTA: Asegúrate de que los IDs coincidan con los materiales y categorías insertados
INSERT INTO `categoria_materiales` (`id_categoria`, `id_material`, `cantidad`) VALUES
(1, 1, 20.000),  -- 20 tornillos para Muebles de Oficina
(1, 2, 0.500),   -- 0.5 litros de pegamento para Muebles de Oficina
(2, 1, 30.000),  -- 30 tornillos para Muebles de Hogar
(2, 3, 5.000),   -- 5 hojas de lija para Muebles de Hogar
(3, 4, 2.000),   -- 2 litros de barniz para Muebles de Exteriores
(3, 5, 2.000);   -- 2 cintas métricas para Muebles de Exteriores
