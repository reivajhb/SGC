<?php 
// Headers para exportar a Excel
header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=InformeProveedores.xls");

include '../config/seguridad.php';
include '../config/conexion.php'; 

// Función para formatear moneda
function formatoMoneda($valor) {
    return number_format($valor, 2, ",", ".");
}

// === CONSTRUCCIÓN DE LA CONSULTA ===
// La consulta ahora selecciona todas las columnas relevantes de la tabla `tbl_proveedores`
$consulta = "SELECT 
    id_proveedor, 
    nit_identificacion, 
    nombre, 
    email_contabilidad, 
    email_cartera, 
    tipo_proveedor 
FROM 
    tbl_proveedores
ORDER BY 
    nombre ASC";

$ejecutar = mysqli_query($conn, $consulta);
?>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Id Proveedor</th>
            <th scope="col">Nit</th>
            <th scope="col">Proveedor</th>
            <th scope="col">Correo Contabilidad</th>
            <th scope="col">Correo Cartera</th>
            <th scope="col">Tipo Proveedor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
            ?>
            <tr>
                <td><?php echo $mostrarProveedor['id_proveedor']; ?></td>
                <td><?php echo $mostrarProveedor['nit_identificacion']; ?></td>
                <td><?php echo $mostrarProveedor['nombre']; ?></td>
                <td><?php echo $mostrarProveedor['email_contabilidad']; ?></td>
                <td><?php echo $mostrarProveedor['email_cartera']; ?></td>
                <td><?php echo $mostrarProveedor['tipo_proveedor']; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
