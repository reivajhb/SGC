<?php
include "../../config/conexion.php"; // Conexión a la base de datos

// Verificar si se ha recibido el ID del usuario y el nuevo estado
if (isset($_POST['id']) && isset($_POST['estado'])) {
    $id_usuario = $_POST['id'];
    $estado = $_POST['estado'];

    // Actualizar el estado del usuario (0 = Bloqueado, 1 = Activo)
    $query = "UPDATE tbl_usuarios SET estado = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $estado, $id_usuario);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>alert('Estado del usuario actualizado con éxito'); window.location.href = 'paneladmin.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el estado del usuario.'); window.history.back();</script>";
    }

    // Cerrar la sentencia
    $stmt->close();
} else {
    echo "<script>alert('ID de usuario o estado no proporcionado.'); window.history.back();</script>";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
