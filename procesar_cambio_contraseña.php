<?php 
include "seguridad.php"

 ?>

<?php


session_start();
include('conexion.php');

// Obtener datos del formulario
$usuario = $_SESSION['usuario'];
$contraseña_actual = $_POST['contraseña_actual'];
$nueva_contraseña = $_POST['nueva_contraseña'];
$confirmar_contraseña = $_POST['confirmar_contraseña'];

// Consulta para obtener la contraseña actual del usuario
$consultaUser = "SELECT contraseña FROM tbl_usuarios WHERE usuario = ?";
$stmt = $conn->prepare($consultaUser);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $contraseña_hash = $fila['contraseña'];
    
    // Verificar si la contraseña actual coincide con la almacenada en la base de datos
    if (password_verify($contraseña_actual, $contraseña_hash)) {
        // Verificar si las contraseñas nuevas coinciden
        if ($nueva_contraseña === $confirmar_contraseña) {
            // Encriptar la nueva contraseña
            $hashed_password = password_hash($nueva_contraseña, PASSWORD_BCRYPT);
            
            // Actualizar la contraseña en la base de datos
            $update_sql = "UPDATE tbl_usuarios SET contraseña = ? WHERE usuario = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ss", $hashed_password, $usuario);
            $stmt->execute();
            
            echo '<script>
                    alert("Contraseña actualizada exitosamente");
                    window.location = "micuenta.php"; // Redireccionar a la página de perfil u otra página
                  </script>';
        } else {
            echo '<script>
                    alert("Las contraseñas nuevas no coinciden");
                    window.location = "RecuperarPass.php"; // Redireccionar de vuelta al formulario
                  </script>';
        }
    } else {
        echo '<script>
                alert("Contraseña actual incorrecta");
                window.location = "RecuperarPass.php"; // Redireccionar de vuelta al formulario
              </script>';
    }
} else {
    echo '<script>
            alert("Error: Usuario no encontrado");
            window.location = "RecuperarPass.php"; // Redireccionar de vuelta al formulario
          </script>';
}

$stmt->close();
$conn->close();
?>
