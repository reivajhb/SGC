<?php
/**
 * Archivo de seguridad para el módulo de proveedores
 * Verifica que el usuario esté autenticado como proveedor (rol 6 o 7) 
 * O como usuario administrativo (rol 1, 2, 8, 9)
 * Si no está autenticado, redirige al login correspondiente
 */

// Evitar caché en el navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir roles permitidos
$rolesProveedores = [6, 7]; // Hotel individual, Cadena hotelera
$rolesAdministrativos = [1, 2, 8, 9]; // Admin, Analista Contable, Rol 8, Gestoras
$rolesPermitidos = array_merge($rolesProveedores, $rolesAdministrativos);

// Verificar autenticación
$tieneUsuario = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
$idRol = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : 0;
$esProveedorAutenticado = isset($_SESSION['PROV_AUTH']) && $_SESSION['PROV_AUTH'] === true;

// Verificar si el usuario tiene un rol permitido
$tieneRolPermitido = in_array($idRol, $rolesPermitidos);

// Si no tiene sesión activa, redirigir al login de proveedores
if (!$tieneUsuario) {
    header("Location: /facturacion/proveedores/vista/index-proveedores.php");
    exit;
}

// Si tiene sesión pero no tiene un rol permitido, denegar acceso
if (!$tieneRolPermitido) {
    http_response_code(403);
    echo '<h1>Acceso denegado</h1>';
    echo '<p>No tienes permisos para acceder a esta sección.</p>';
    exit;
}

// Si es proveedor (rol 6 o 7), verificar que tenga PROV_AUTH
if (in_array($idRol, $rolesProveedores) && !$esProveedorAutenticado) {
    // Es proveedor pero no tiene PROV_AUTH, redirigir a login de proveedores
    header("Location: /facturacion/proveedores/vista/index-proveedores.php");
    exit;
}

// Si llegó hasta aquí, el usuario tiene acceso permitido
// (Es proveedor autenticado O es usuario administrativo con sesión válida)
?>
