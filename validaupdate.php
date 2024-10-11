<?php 
include "seguridad.php"
?>
<?php
include('conexion.php');

// Seleccionar todos los usuarios con contraseñas en texto plano
$sql = "SELECT id_usuario, contraseña FROM tbl_usuarios";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $id_usuario = $row['id_usuario'];
    $plain_password = $row['contraseña'];

    // Encriptar la contraseña
    $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

    // Actualizar la contraseña en la base de datos
    $update_sql = "UPDATE tbl_usuarios SET contraseña = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $hashed_password, $id_usuario);
    $stmt->execute();
    $stmt->close();
}

mysqli_close($conn);
?>
