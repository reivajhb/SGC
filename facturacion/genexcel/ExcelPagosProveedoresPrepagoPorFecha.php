<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="InformeAnticiposProveedoresTuristicos.xls"');
header('Cache-Control: max-age=0');

include '../config/seguridad.php';
include '../config/conexion.php';

$from_date     = !empty($_GET['from_date'])     ? mysqli_real_escape_string($conn, $_GET['from_date'])     : null;
$to_date       = !empty($_GET['to_date'])       ? mysqli_real_escape_string($conn, $_GET['to_date'])       : null;
$nit_proveedor = !empty($_GET['nit_proveedor']) ? mysqli_real_escape_string($conn, $_GET['nit_proveedor']) : null;

if (!$from_date || !$to_date) {
    die('Parámetros de fecha requeridos.');
}

$sql = "
    SELECT
        identificacion,
        fecha,
        proveedor,
        descripcion,
        moneda,
        localizador,
        ValorTotalApagar,
        soportePrepago,
        estado
    FROM tbl_anticipos
    WHERE fecha BETWEEN '$from_date' AND '$to_date'
";

if ($nit_proveedor) {
    $sql .= " AND identificacion = '$nit_proveedor'";
}

$sql .= " ORDER BY fecha DESC";

$result = mysqli_query($conn, $sql);
?>
<table border="1">
    <thead>
        <tr>
            <th>NIT</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Descripción</th>
            <th>Moneda</th>
            <th>Localizador</th>
            <th>Valor a Pagar</th>
            <th>Soporte de Pago</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['identificacion'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['fecha'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['proveedor'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['descripcion'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['moneda'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['localizador'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['ValorTotalApagar'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['soportePrepago'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['estado'] ?? ''); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php
mysqli_close($conn);
?>
?>
