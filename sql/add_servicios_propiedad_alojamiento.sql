-- ============================================
-- Script: Agregar servicios de propiedad
-- Descripcion: Agrega campos nuevos para la seccion
--              SERVICIOS DE LA PROPIEDAD del hotel
-- ============================================

SET @db_name = DATABASE();
SET @table_name = 'tbl_alojamiento_servicios';

-- parqueadero
SET @column_name = 'parqueadero';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Parqueadero" AFTER recepcion_24_hrs'),
    'SELECT "La columna parqueadero ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- minibar
SET @column_name = 'minibar';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Minibar" AFTER parqueadero'),
    'SELECT "La columna minibar ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- con_cocina
SET @column_name = 'con_cocina';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Con cocina" AFTER minibar'),
    'SELECT "La columna con_cocina ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- cafetera_cortesia
SET @column_name = 'cafetera_cortesia';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Cafetera de cortesia" AFTER con_cocina'),
    'SELECT "La columna cafetera_cortesia ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- servicio_habitacion
SET @column_name = 'servicio_habitacion';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Servicio a la habitacion" AFTER cafetera_cortesia'),
    'SELECT "La columna servicio_habitacion ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- servicio_habitacion_24_hrs
SET @column_name = 'servicio_habitacion_24_hrs';
SELECT COUNT(*) INTO @column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = @table_name
  AND COLUMN_NAME = @column_name;

SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Servicio a la habitacion 24 hrs" AFTER servicio_habitacion'),
    'SELECT "La columna servicio_habitacion_24_hrs ya existe" AS mensaje'
);
PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Servicios de propiedad verificados' AS resultado;
