<?php 
include "../../config/seguridad.php";  // Verificación de sesión y permisos
include "../../config/conexion.php";   // Incluir la conexión a la base de datos

// Verificar si los datos llegaron a través del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id_usuario = $_POST['id'];
    $usuario = $_POST['usuario'];
    $id_rol = $_POST['id_rol'];
    $contraseña = isset($_POST['password']) ? $_POST['password'] : '';  // Solo obtener la contraseña si se ha ingresado

    // Validar que no haya campos vacíos
    if (empty($usuario) || empty($id_rol)) {
        echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
        exit;
    }

    // Preparar la consulta para actualizar los datos
    if (!empty($contraseña)) {
        // Si se ingresó una nueva contraseña, encriptarla
        $contraseña_encriptada = password_hash($contraseña, PASSWORD_BCRYPT);
        $query = "UPDATE tbl_usuarios SET usuario = ?, id_rol = ?, contraseña = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisi", $usuario, $id_rol, $contraseña_encriptada, $id_usuario);
    } else {
        // Si no se ingresó una nueva contraseña, no se actualiza el campo de la contraseña
        $query = "UPDATE tbl_usuarios SET usuario = ?, id_rol = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sis", $usuario, $id_rol, $id_usuario);
    }

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir después de la actualización exitosa
        echo "<script>alert('Usuario actualizado con éxito'); window.location.href = 'paneladmin.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el usuario.'); window.history.back();</script>";
    }

    // Cerrar la sentencia
    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
