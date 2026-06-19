<?php
include('../facturacion/config/conexion.php');

// Realizamos la consulta para obtener los escaneos con la descripción de la campaña y el tipo de QR
$query = "
    SELECT 
        e.qr_id, 
        c.descripcion, 
        c.url_tracking, 
        c.tipo_qr,
        COUNT(*) AS total_escaneos
    FROM tbl_escaneos e
    LEFT JOIN tbl_campañas c ON e.qr_id = c.qr_id
    GROUP BY e.qr_id, c.descripcion, c.url_tracking, c.tipo_qr
    ORDER BY total_escaneos DESC;
";

$result = $conn->query($query);

$escaneos = [];
while ($row = $result->fetch_assoc()) {
    $escaneos[] = $row;
}

echo json_encode($escaneos);
$conn->close();
?>
