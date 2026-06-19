-- Script para agregar la columna juniper_id a la tabla tbl_alojamiento_general
-- Ejecuta este script en tu base de datos antes de usar la funcionalidad

ALTER TABLE `tbl_alojamiento_general` 
ADD COLUMN `juniper_id` VARCHAR(50) NULL DEFAULT NULL COMMENT 'ID del proveedor en Juniper' 
AFTER `nit_consecutivo`;

-- Opcional: Agregar índice para búsquedas más rápidas
ALTER TABLE `tbl_alojamiento_general` 
ADD INDEX `idx_juniper_id` (`juniper_id`);
