<?php 
// Headers para exportar a Excel
header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=InformePagosProveedoresAdmin.xls");

include '../config/seguridad.php';
include '../config/conexion.php'; 

// Función para formatear moneda
function formatoMoneda($valor) {
    return number_format($valor, 2, ",", ".");
}

// === CONSTRUCCIÓN DE LA CONSULTA DINÁMICA ===
$consulta_base = "SELECT 
    p.id_pago,
    p.valor_pagado,
    p.novedad,
    p.fecha_pago,
    p.estado,
    p.archivo_factura,
    p.archivo_soporte,
    prov.nit_identificacion AS nit,
    prov.nombre AS proveedor
FROM 
    tbl_pagos p
JOIN 
    tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
WHERE 
    prov.tipo_proveedor = 'Administrativo'
";

$ejecutar = mysqli_query($conn, $consulta_base);
?>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Id pago</th>
            <th scope="col">Nit</th>
            <th scope="col">Proveedor</th>
            <th scope="col">Valor pagado</th>
            <th scope="col">Novedad</th>
            <th scope="col">Fecha</th>
            <th scope="col">Archivo Factura</th>
            <th scope="col">Estado</th>
            <th scope="col">Soporte Proveedor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
            ?>
            <tr>
                <td><?php echo $mostrarProveedor['id_pago']; ?></td>
                <td><?php echo $mostrarProveedor['nit']; ?></td>
                <td><?php echo $mostrarProveedor['proveedor']; ?></td>
                <td>$<?php echo formatoMoneda($mostrarProveedor['valor_pagado']); ?></td>
                <td><?php echo $mostrarProveedor['novedad']; ?></td>
                <td><?php echo $mostrarProveedor['fecha_pago']; ?></td>
                <td><?php echo $mostrarProveedor['archivo_factura']; ?></td>
                <td><?php echo $mostrarProveedor['estado']; ?></td>
                <td><?php echo $mostrarProveedor['archivo_soporte']; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
