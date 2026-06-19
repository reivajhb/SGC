<?php 
session_start();
include('../../facturacion/config/conexion.php');

// Validar y sanitizar las entradas
$usuario = trim(filter_var($_POST['usuario'], FILTER_SANITIZE_STRING));
$contraseña = trim($_POST['contraseña']); // La contraseña se procesa sin cambios por seguridad

// Almacenar el nombre de usuario en la sesión
$_SESSION['usuario'] = $usuario;

// Consulta para obtener la contraseña encriptada, el rol del usuario y su estado
$consultaUser = "SELECT id_usuario, usuario, contraseña, id_rol, estado FROM tbl_usuarios WHERE usuario = ?";
$stmt = $conn->prepare($consultaUser);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $filas = $resultado->fetch_assoc();
    $hashed_password = $filas['contraseña'];

    // Verificar si el usuario está bloqueado
    if ($filas['estado'] == 0) {
        echo "<script>alert('Tu cuenta está bloqueada. Por favor, contacta con el administrador.'); window.location.href = '../vista/index-proveedores.php';</script>";
        exit();
    }

    // Verificar la contraseña ingresada con la contraseña encriptada
    if (password_verify($contraseña, $hashed_password)) {
        // Redireccionar solo si el rol es 6 o 7
        if ($filas['id_rol'] == 6 || $filas['id_rol'] == 7 ) {
            $_SESSION['id_rol'] = $filas['id_rol'];
            $_SESSION['id_usuario'] = $filas['id_usuario'];
            $_SESSION['PROV_AUTH'] = true;
            
            // Si es ROL 6 (Hotel individual): buscar su hotel y redirigir a edición
          if ($filas['id_rol'] == 6) {
            $consultaHotel = "SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro FROM tbl_alojamiento_general WHERE id_usuario_creacion = ? ORDER BY id_hotel DESC LIMIT 1";
            $stmtHotel = $conn->prepare($consultaHotel);
            $stmtHotel->bind_param("i", $filas['id_usuario']);
            $stmtHotel->execute();
            $resultHotel = $stmtHotel->get_result();

            if ($resultHotel->num_rows > 0) {
                $hotelData = $resultHotel->fetch_assoc();
                $stmtHotel->close();

                if (strtoupper(trim((string) $hotelData['estado_registro'])) === 'BORRADOR') {
                    header("Location: ../vista/formularioIncripHotel.php?id=" . $hotelData['id_hotel']);
                } else {
                    header("Location: ../vista/consultaHotel.php?id=" . $hotelData['id_hotel']);
                }
                exit();
            } else {
                $stmtHotel->close();

                header("Location: ../vista/formularioIncripHotel.php");
                exit();
            }
        } else {
                // ROL 7 (Cadena): ir a listado de hoteles o formulario
                header("Location: ../vista/listadoHotelesCadena.php");
                exit();
            }
        } else {
            echo "<script>alert('Acceso denegado: no tienes permisos para acceder a esta página.'); window.location.href = '../vista/index-proveedores.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Contraseña incorrecta.'); window.location.href = '../vista/index-proveedores.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Usuario no encontrado.'); window.location.href = '../vista/index-proveedores.php';</script>";
    exit();
}

$stmt->close();
$conn->close();
?>
