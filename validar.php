<?php 
session_start();
include('conexion.php');

// Validar y sanitizar las entradas
$usuario = filter_var($_POST['usuario'], FILTER_SANITIZE_STRING);
$contraseña = $_POST['contraseña']; // La contraseña se procesa sin cambios por razones de seguridad

// Almacenar el nombre de usuario en la sesión
$_SESSION['usuario'] = $usuario;

// Consulta para obtener la contraseña encriptada y el rol del usuario
$consultaUser = "SELECT id_usuario, usuario, contraseña, id_rol FROM tbl_usuarios WHERE usuario = ?";
$stmt = $conn->prepare($consultaUser);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $filas = $resultado->fetch_assoc();
    $hashed_password = $filas['contraseña'];

    // Verificar la contraseña ingresada con la contraseña encriptada
    if (password_verify($contraseña, $hashed_password)) {
        // Redireccionar según el rol del usuario
        switch ($filas['id_rol']) {
            case 1:
            case 2:
                header("Location: buscarProveedor.php");
                break;
            case 3:
                header("Location: indexFacturacion.php");
                break;
            case 4:
                header("Location: consultaFacturasRecepPublic.php");
                break;
            case 5:
                header("Location: buscarProveedorPrepago.php");
                break;
            default:
                // Manejo de rol no válido
                header("Location: index.php?error=invalid_role");
                break;
        }
        exit(); // Asegura que el script se detenga después de la redirección
    } else {
        // Contraseña incorrecta
        header("Location: index.php?error=wrong_password");
        exit();
    }
} else {
    // Usuario no encontrado
    header("Location: index.php?error=user_not_found");
    exit();
}

$stmt->close();
$conn->close();
?>
