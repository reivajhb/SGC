<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario logueado, redirige al login de facturación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /facturacion/index.php');
    exit;
}

// Si no es administrador, deniega acceso
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 1) {
    echo "
    <script>
        alert('No tienes permisos para acceder a este módulo.');
        window.location.href = '/facturacion/paneladmin.php';
    </script>
    ";
    exit;
}
?>