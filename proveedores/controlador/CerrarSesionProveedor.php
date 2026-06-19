<?php
session_start();

// 1. ELIMINAR TODAS LAS VARIABLES DE SESIÓN DEL PROVEEDOR
// Estas son las variables que establece validarProveedores.php y que usa el área de proveedores
if (isset($_SESSION['usuario'])) {
    unset($_SESSION['usuario']);
}

if (isset($_SESSION['id_rol'])) {
    unset($_SESSION['id_rol']);
}

if (isset($_SESSION['contraseña'])) {
    unset($_SESSION['contraseña']);
}

// Eliminar otras variables específicas del área de proveedores si existen
if (isset($_SESSION['PROV_AUTH'])) {
    unset($_SESSION['PROV_AUTH']);
}

if (isset($_SESSION['PROV_ID'])) {
    unset($_SESSION['PROV_ID']);
}

// 2. REDIRECCIÓN A LA PÁGINA DE LOGIN DE PROVEEDORES
header("Location: ../vista/index-proveedores.php");
exit(); // Siempre termina el script después de una redirección
?>