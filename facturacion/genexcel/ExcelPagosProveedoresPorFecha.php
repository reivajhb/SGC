<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=InformePagosProveedoresTuristicos.xls");

include '../config/seguridad.php';
include '../config/conexion.php';

function formatoMoneda($valor) {
    return number_format($valor, 2, ",", ".");
}

$from_date     = !empty($_GET['from_date'])      ? mysqli_real_escape_string($conn, $_GET['from_date'])      : null;
$to_date       = !empty($_GET['to_date'])        ? mysqli_real_escape_string($conn, $_GET['to_date'])        : null;
$id_proveedor  = !empty($_GET['id_proveedor_fo']) ? mysqli_real_escape_string($conn, $_GET['id_proveedor_fo']) : '';

$query = "
    SELECT
        p.id_pago,
        p.valor_pagado,
        p.total_pagar,
        p.novedad,
        p.fecha_pago,
        p.estado,
        p.archivo_factura,
        p.archivo_soporte,
        prov.nit_identificacion AS nit,
        prov.nombre AS proveedor
    FROM tbl_pagos p
    JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
    WHERE prov.tipo_proveedor = 'Turístico'
";

$params = [];
$types  = '';

if ($from_date && $to_date) {
    $query   .= " AND p.fecha_pago BETWEEN ? AND ?";
    $types   .= 'ss';
    $params[] = $from_date;
    $params[] = $to_date;
}

if ($id_proveedor !== '') {
    $query   .= " AND p.id_proveedor = ?";
    $types   .= 's';
    $params[] = $id_proveedor;
}

$query .= " ORDER BY p.fecha_pago DESC";

$stmt = mysqli_prepare($conn, $query);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>
<table>
    <thead>
        <tr>
            <th>Id Pago</th>
            <th>NIT</th>
            <th>Proveedor</th>
            <th>Valor Pagado</th>
            <th>Total a Pagar</th>
            <th>Novedad</th>
            <th>Fecha Pago</th>
            <th>Archivo Factura</th>
            <th>Estado</th>
            <th>Soporte</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
        <tr>
            <td><?php echo $row['id_pago']; ?></td>
            <td><?php echo $row['nit']; ?></td>
            <td><?php echo $row['proveedor']; ?></td>
            <td><?php echo formatoMoneda($row['valor_pagado']); ?></td>
            <td><?php echo formatoMoneda($row['total_pagar']); ?></td>
            <td><?php echo $row['novedad']; ?></td>
            <td><?php echo $row['fecha_pago']; ?></td>
            <td><?php echo $row['archivo_factura']; ?></td>
            <td><?php echo $row['estado']; ?></td>
            <td><?php echo $row['archivo_soporte']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
