<?php
// Evitar caché en el navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Destruir sesión
    session_destroy();
    // Redirigir al login
    header("Location: index.php");
    exit();
}
?>

