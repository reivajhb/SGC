<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="InformePagosProveedoresAdministrativos.xls"');
header('Cache-Control: max-age=0');

include '../config/seguridad.php';
include '../config/conexion.php';

$from_date        = !empty($_GET['from_date'])            ? mysqli_real_escape_string($conn, $_GET['from_date'])            : null;
$to_date          = !empty($_GET['to_date'])              ? mysqli_real_escape_string($conn, $_GET['to_date'])              : null;
$id_proveedor_adm = !empty($_GET['id_proveedoradmin_fo']) ? mysqli_real_escape_string($conn, $_GET['id_proveedoradmin_fo']) : null;

if (!$from_date || !$to_date) {
    die('Parámetros de fecha requeridos.');
}

$sql = "
    SELECT
        p.id_pago,
        prov.nit,
        p.locacion AS proveedor,
        p.identificacion,
        p.valor_pagado,
        p.total_pagar,
        p.novedad,
        p.fecha_pago,
        p.archivo_factura,
        p.estado,
        p.archivo_soporte
    FROM tbl_pagos p
    JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
    WHERE prov.tipo_proveedor = 'Administrativo'
      AND p.fecha_pago BETWEEN '$from_date' AND '$to_date'
";

if ($id_proveedor_adm) {
    $sql .= " AND p.id_proveedor = '$id_proveedor_adm'";
}

$sql .= " ORDER BY p.fecha_pago DESC";

$result = mysqli_query($conn, $sql);
?>
<table border="1">
    <thead>
        <tr>
            <th>Id Pago</th>
            <th>NIT</th>
            <th>Proveedor / Locación</th>
            <th>Identificación</th>
            <th>Valor Pagado</th>
            <th>Total a Pagar</th>
            <th>Novedad</th>
            <th>Fecha Pago</th>
            <th>Archivo Factura</th>
            <th>Estado</th>
            <th>Soporte de Pago</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id_pago'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['nit'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['proveedor'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['identificacion'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['valor_pagado'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['total_pagar'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['novedad'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['fecha_pago'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['archivo_factura'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['estado'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['archivo_soporte'] ?? ''); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php
mysqli_close($conn);
?>
