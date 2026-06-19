<?php
include "../../../../config/seguridad.php";
include "../../../../config/conexion.php";

$id_proveedor = $_POST["id_proveedor"] ?? null;
$nit_identificacion = $_POST["nit_identificacion"] ?? null;
$nombre = $_POST["nombre"] ?? null;
$email_contabilidad = $_POST["email_contabilidad"] ?? null;
$email_cartera = $_POST["email_cartera"] ?? null;
$tipo_proveedor = $_POST["tipo_proveedor"] ?? null;
$limite_credito = $_POST["limite_credito"] ?? null;
$saldo_deuda = $_POST["saldo_deuda"] ?? null;

if (is_null($id_proveedor)) {
    echo '<script>alert("Error: ID de proveedor no proporcionado."); window.location = "consultaProveedores.php";</script>';
    exit;
}
// Consulta preparada para la actualización de datos
$editarProveedor = "UPDATE tbl_proveedores SET nit_identificacion=?, nombre=?, email_contabilidad=?, email_cartera=?, tipo_proveedor=?, limite_credito=? , saldo_deuda=?  WHERE id_proveedor=?";
$stmt = mysqli_prepare($conn, $editarProveedor);

// Vincular los parámetros con el nuevo ID
mysqli_stmt_bind_param(
    $stmt,
    "sssssiii",
    $nit_identificacion,
    $nombre,
    $email_contabilidad,
    $email_cartera,
    $tipo_proveedor,
    $limite_credito,
    $saldo_deuda,
    $id_proveedor
);

// Ejecutar la declaración
$resultado = mysqli_stmt_execute($stmt);

if ($resultado) {
    echo '<script>
        alert("Proveedor editado con éxito");
        window.location = "../consultaProveedoresTuristicosCs.php";
    </script>';
} else {
    echo '<script>alert("Error al editar el registro: ' . mysqli_error($conn) . '")</script>';
}

// Cerrar la declaración y la conexión a la base de datos
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
