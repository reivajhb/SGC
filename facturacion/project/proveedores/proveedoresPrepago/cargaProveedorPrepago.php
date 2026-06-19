<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

$nit = $_POST["nit_identificacion"] ?? '';
$nombre = $_POST["nombre"] ?? '';
$email_contabilidad = $_POST["email_contabilidad"] ?? '';
$email_cartera = $_POST["email_cartera"] ?? '';
$tipo_proveedor = $_POST["tipo_proveedor"] ?? ''; // Ahora viene del select

$insertar_query = "
    INSERT INTO tbl_proveedores 
    (nit_identificacion, nombre, tipo_proveedor, email_contabilidad, email_cartera) 
    VALUES (?, ?, ?, ?, ?)
";

$stmt = mysqli_prepare($conn, $insertar_query);

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $nit,
    $nombre,
    $tipo_proveedor,
    $email_contabilidad,
    $email_cartera
);

if (mysqli_stmt_execute($stmt)) {
    echo '<script>
            alert("Proveedor cargado con éxito");
            window.location = "buscarProveedorPrepago.php";
          </script>';
} else {
    if (mysqli_errno($conn) == 1062) {
        echo '<script>
                alert("Error: El NIT del proveedor ya existe.");
                window.location = "RegistroProveedoresPrepago.php";
              </script>';
    } else {
        echo "Error al insertar el proveedor: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>