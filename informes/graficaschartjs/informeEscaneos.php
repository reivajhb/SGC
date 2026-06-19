<?php
include('../../facturacion/config/conexion.php');

$tipoQR = isset($_GET['tipo_qr']) ? $_GET['tipo_qr'] : '';

$query = "
    SELECT 
        c.descripcion, 
        COUNT(*) AS total_escaneos
    FROM tbl_escaneos e
    LEFT JOIN tbl_campañas c ON e.qr_id = c.qr_id
    WHERE ('$tipoQR' = '' OR c.tipo_qr = ?)
    GROUP BY c.descripcion
    ORDER BY total_escaneos DESC
";

$stmt = $conn->prepare($query);

if ($tipoQR !== '') {
    $stmt->bind_param("s", $tipoQR);
}

$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$raw_values = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['descripcion'];
    $raw_values[] = (int)$row['total_escaneos'];
    $total += (int)$row['total_escaneos'];
}

$percentages = [];
foreach ($raw_values as $value) {
    $percentages[] = $total > 0 ? round(($value / $total) * 100, 2) : 0;
}

echo json_encode([
    'labels' => $labels,
    'raw_values' => $raw_values,
    'values' => $percentages
]);

$conn->close();
?>
