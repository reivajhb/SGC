<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "facturacion";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consulta SQL para obtener los datos agregados por mes
$sql = "
    SELECT 
        DATE_FORMAT(fecha, '%M') as mes, 
        SUM(ValorTotalApagar) as total_pagos 
    FROM 
        tbl_anticipos 
         WHERE 
        YEAR(fecha) = 2024 
        AND (estado = 'Pendiente')
    GROUP BY 
        mes 
    ORDER BY 
        FIELD(mes, 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre')";

$result = $conn->query($sql);

$labels = [];
$values = [];

if ($result->num_rows > 0) {
    // Obtener datos
    while($row = $result->fetch_assoc()) {
        $labels[] = $row['mes'];
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
