-- ============================================
-- Script: Agregar columna numero_cuenta
-- Descripción: Agrega el campo numero_cuenta para almacenar
--              el número de cuenta bancaria del hotel
-- ============================================

-- Verificar si la columna ya existe antes de agregarla
SET @db_name = DATABASE();
SET @table_name = 'tbl_alojamiento_general';
SET @column_name = 'numero_cuenta';

SET @query = CONCAT(
    'SELECT COUNT(*) INTO @column_exists ',
    'FROM information_schema.COLUMNS ',
    'WHERE TABLE_SCHEMA = "', @db_name, '" ',
    'AND TABLE_NAME = "', @table_name, '" ',
    'AND COLUMN_NAME = "', @column_name, '"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Si la columna no existe, agregarla
SET @alter_query = IF(
    @column_exists = 0,
    CONCAT('ALTER TABLE `', @table_name, '` ADD COLUMN `', @column_name, '` VARCHAR(50) NULL DEFAULT NULL COMMENT "Número de cuenta bancaria" AFTER retefuente'),
    'SELECT "La columna numero_cuenta ya existe" AS mensaje'
);

PREPARE stmt FROM @alter_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mensaje final
SELECT 
    CASE 
        WHEN @column_exists = 0 THEN '✅ Columna numero_cuenta agregada exitosamente'
        ELSE '✅ La columna numero_cuenta ya existía'
    END AS resultado;
