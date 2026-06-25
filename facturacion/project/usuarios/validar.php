<?php
// 1. Iniciar sesión y LIMPIARLA inmediatamente antes de validar al nuevo usuario
session_start();
session_unset(); // Borra todas las variables de sesión
session_destroy(); // Destruye la sesión físicamente
session_start(); // Inicia una sesión nueva y limpia para el nuevo usuario

include "../../config/conexion.php";

// Validar y sanitizar las entradas
$usuario = htmlspecialchars(trim($_POST['usuario']), ENT_QUOTES, 'UTF-8');
$contraseña = $_POST['contraseña'];

// Consulta para obtener datos del usuario
$consultaUser = "SELECT id_usuario, usuario, contraseña, id_rol, estado, correo FROM tbl_usuarios WHERE usuario = ?";
$stmt = $conn->prepare($consultaUser);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $filas = $resultado->fetch_assoc();
    $hashed_password = $filas['contraseña'];

    // Verificar si el usuario está bloqueado
    if ($filas['estado'] == 0) {
        echo "<script>alert('Tu cuenta está bloqueada. Por favor, contacta con el administrador.'); window.location.href = 'index.php';</script>";
        exit();
    }

    // Verificar la contraseña
    if (password_verify($contraseña, $hashed_password)) {

        // 2. REGENERAR ID DE SESIÓN (Crucial para evitar que se hereden datos por ID de sesión)
        session_regenerate_id(true);

        // Almacenar datos en la sesión nueva y limpia
        $_SESSION['id_usuario'] = $filas['id_usuario'];
        $_SESSION['usuario'] = $filas['usuario'];
        $_SESSION['id_rol'] = $filas['id_rol'];
        $_SESSION['correo'] = $filas['correo'];

        // Redireccionar según el rol del usuario
        switch ($filas['id_rol']) {
            case 1:
                header("Location: paneladmin.php");
                break;
            case 2:
                header("Location: ../proveedores/proveedoresTur/buscarProveedor.php");
                break;
            case 3:
                header("Location: ../../views/indexFacturacion.php");
                break;
            case 4:
                header("Location: ../../../proveedores/vista/formularioIncripHotel.php");
                break;
            case 5:
            case 8:
                header("Location: ../proveedores/proveedoresPrepago/buscarProveedorPrepago.php");
                break;
            case 7:
                header("Location: ../../../proveedores/vista/headercadena.php");
                break;
            case 9:
                header("Location: ../contabilidad/proveedores/consultaFichaProveedores.php");
                break;
            case 10:
                header("Location: ../contabilidad/proveedores/consultaFichaProveedores.php");
                break;

            default:
                echo "<script>alert('Rol no válido.'); window.location.href = 'index.php';</script>";
                break;
        }
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }
} else {
    header("Location: index.php?error=1");
    exit();
}

$stmt->close();
$conn->close();
?>