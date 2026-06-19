<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "facturacion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consulta SQL: mes como número (para orden correcto)
$sql = "
    SELECT 
        DATE_FORMAT(fecha, '%m') as mes_num, 
        SUM(cop) as total_pagos 
    FROM 
        tbl_proveedores_turtisticos 
    WHERE 
        YEAR(fecha) = 2025 
        AND estado = 'Pendiente'
    GROUP BY 
        mes_num 
    ORDER BY 
        mes_num";

$result = $conn->query($sql);

$meses_es = [
    '01' => 'enero',
    '02' => 'febrero',
    '03' => 'marzo',
    '04' => 'abril',
    '05' => 'mayo',
    '06' => 'junio',
    '07' => 'julio',
    '08' => 'agosto',
    '09' => 'septiembre',
    '10' => 'octubre',
    '11' => 'noviembre',
    '12' => 'diciembre'
];

$labels = [];
$values = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $mes_nombre = $meses_es[$row['mes_num']];
        $labels[] = $mes_nombre;
        $values[] = $row['total_pagos'];
    }
} else {
    echo "0 results";
}
$conn->close();

$data = [
    'labels' => $labels,
    'values' => $values
];

header('Content-Type: application/json');
echo json_encode($data);
?>
