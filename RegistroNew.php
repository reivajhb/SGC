<?php
include("conexion.php");

$usuario = $_POST['usuario'];
$contraseña = $_POST['contraseña'];
$id_rol = $_POST['id_rol'];

// Encriptar la contraseña
$hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);

$InsertarUser = "INSERT INTO tbl_usuarios (usuario, contraseña, id_rol) VALUES ('$usuario','$hashed_password','$id_rol')";
$resultado = mysqli_query($conn, $InsertarUser);

if ($resultado) {
    echo '<script>
              alert("Registro Exitoso");
              window.location = "index.php";
          </script>';
} else {
    echo '<script>
              alert("Error en los datos Ingresados");
              window.location = "RegistroUser.php";
          </script>';
}
?>