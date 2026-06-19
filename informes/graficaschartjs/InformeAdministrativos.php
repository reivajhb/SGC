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

// Forzar idioma español para los nombres de los meses
$conn->query("SET lc_time_names = 'es_ES'");

// Consulta SQL
$sql = "
    SELECT 
        DATE_FORMAT(fecha, '%M') AS mes, 
        SUM(valor) AS total_pagos 
    FROM 
        tbl_pagos_administrativos 
    WHERE 
        YEAR(fecha) = 2025 
        AND (estado = 'Pagado' OR estado = 'Soporte enviado y pagado')
    GROUP BY 
        mes 
    ORDER BY 
        FIELD(mes, 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre')
";

$result = $conn->query($sql);

$labels = [];
$values = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = ucfirst($row['mes']); // Capitalizar la primera letra
        $values[] = (float)$row['total_pagos'];
    }
}

$conn->close();

$data = [
    'labels' => $labels,
    'values' => $values
];

header('Content-Type: application/json');
echo json_encode($data);
?>
