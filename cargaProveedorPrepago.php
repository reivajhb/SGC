<?php
include "seguridad.php";
include "conexion.php";

$identificacion = $_POST["identificacion"];
$nombre = $_POST["nombre"];
$email_proveedor = $_POST["email_proveedor"];

// Consulta preparada para verificar si el proveedor ya existe
$verificar_dato_query = "SELECT * FROM tbl_proveedores_ocasionales WHERE identificacion = ?";
$stmt = mysqli_prepare($conn, $verificar_dato_query);
mysqli_stmt_bind_param($stmt, "s", $identificacion);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si existe algún resultado
if (mysqli_num_rows($resultado) > 0) {
    // Si existe el proveedor
    echo '<script>
          alert("El proveedor ya existe");
          window.location = "RegistroProveedoresPrepago.php";
          </script>';
} else {
    // Si no existe el proveedor, insertarlo en la base de datos
    $insertar_query = "INSERT INTO tbl_proveedores_ocasionales (identificacion, nombre, email_proveedor) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertar_query);
    mysqli_stmt_bind_param($stmt, "sss", $identificacion, $nombre, $email_proveedor);

    if (mysqli_stmt_execute($stmt)) {
        // Si la inserción es exitosa
        echo '<script>
              alert("Proveedor cargado con éxito");
              window.location = "buscarProveedorPrepago.php";
              </script>';
    } else {
        // Mostrar si hay algún error al insertar el registro
        echo "Error al insertar el proveedor: " . mysqli_error($conn);
    }
}

// Cerrar la conexión
mysqli_close($conn);
?>
