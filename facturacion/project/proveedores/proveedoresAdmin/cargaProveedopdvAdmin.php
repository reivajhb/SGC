<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

$nit = $_POST["nit_identificacion"] ?? '';
$nombre = $_POST["nombre"] ?? '';
$email_contabilidad = $_POST["email_contabilidad"] ?? '';
$email_cartera = $_POST["email_cartera"] ?? '';
$tipo_proveedor = $_POST["tipo_proveedor"] ?? '';

try {
    $insertar_query = "
        INSERT INTO tbl_proveedores 
        (nit_identificacion, nombre, tipo_proveedor, email_contabilidad, email_cartera) 
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $insertar_query);

    mysqli_stmt_bind_param(
        $stmt,
        "issss",
        $nit,
        $nombre,
        $tipo_proveedor,
        $email_contabilidad,
        $email_cartera
    );

    if (mysqli_stmt_execute($stmt)) {
        echo '<script>
                alert("Proveedor cargado con éxito");
                window.location = "buscarProveedor.php";
              </script>';
    } else {
        echo "Error al insertar el proveedor: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) { // Código de error para entrada duplicada
        echo '<script>
                alert("Error: El NIT del proveedor ya existe.");
                window.location = "RegistroProveedoresAdministrativos.php";
              </script>';
    } else {
        // Otros errores de SQL
        echo "Error al insertar el proveedor: " . $e->getMessage();
    }
} finally {
    mysqli_close($conn);
}
?>
