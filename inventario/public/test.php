<?php
// public/test.php

// Verifica rutas
echo 'Ruta Absoluta del Directorio Actual: ' . __DIR__ . '<br>';
echo 'Ruta del Archivo Inventory.php: ' . __DIR__ . '/../app/models/Inventory.php' . '<br>';
echo 'Ruta del Archivo success.php: ' . __DIR__ . '/../app/views/inventory/success.php' . '<br>';

// Verifica existencia de archivos
echo file_exists(__DIR__ . '/../app/models/Inventory.php') ? 'Inventory.php existe.' : 'Inventory.php no existe.' . '<br>';
echo file_exists(__DIR__ . '/../app/views/inventory/success.php') ? 'success.php existe.' : 'success.php no existe.' . '<br>';
?>
