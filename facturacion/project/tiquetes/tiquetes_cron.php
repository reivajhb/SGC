<?php
// Evita que se ejecute si no es desde la tarea programada (CLI)
if (php_sapi_name() !== 'cli') {
    die("Acceso no permitido");
}

// El código de tiquetes.php sigue aquí
file_put_contents("C:\\xampp\\htdocs\\Facturacion\\logs\\log_tiquetes.txt", "Ejecutado en: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>
