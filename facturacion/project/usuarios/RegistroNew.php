<?php
include "../../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los campos no estén vacíos
    if (empty($_POST['usuario']) || empty($_POST['contraseña']) || empty($_POST['id_rol']) || empty($_POST['nombre'])) {
        echo '<script>
                  alert("Los campos usuario, nombre, contraseña y rol son obligatorios.");
                  window.location = "RegistroUser.php";
              </script>';
        exit();
    }

    // Escapar valores y obtener datos
    $usuario    = trim($_POST['usuario']);
    $nombre     = trim($_POST['nombre']);
    $contraseña = $_POST['contraseña'];
    $id_rol     = intval($_POST['id_rol']);
    $correo     = trim($_POST['correo'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $direccion  = trim($_POST['direccion'] ?? '');
    $estado     = 1;

    // Verificar si el usuario ya existe
    $sql_check = "SELECT usuario FROM tbl_usuarios WHERE usuario = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    
    if ($stmt_check) {
        mysqli_stmt_bind_param($stmt_check, "s", $usuario);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            // El usuario ya existe
            mysqli_stmt_close($stmt_check);
            echo '<script>
                      alert("El usuario ya existe. Intenta con otro nombre.");
                      window.location = "RegistroUser.php";
                  </script>';
            exit();
        }
        mysqli_stmt_close($stmt_check);
    }

    // Encriptar la contraseña
    $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);

    // Insertar el nuevo usuario con el estado
    $sql = "INSERT INTO tbl_usuarios (usuario, nombre, contraseña, id_rol, estado, correo, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssiisss", $usuario, $nombre, $hashed_password, $id_rol, $estado, $correo, $telefono, $direccion);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($resultado) {
            echo '<script>
                      alert("Registro Exitoso");
                      window.location = "index.php";
                  </script>';
        } else {
            echo '<script>
                      alert("Error al registrar usuario. Inténtalo de nuevo.");
                      window.location = "RegistroUser.php";
                  </script>';
        }
    } else {
        echo '<script>
                  alert("Error en la base de datos.");
                  window.location = "RegistroUser.php";
              </script>';
    }
} else {
    echo '<script>
              alert("Acceso no autorizado.");
              window.location = "index.php";
          </script>';
}
?>
