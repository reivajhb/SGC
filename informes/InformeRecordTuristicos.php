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

// Consulta SQL para obtener los 10 proveedores más pagados por mes
$sql = "
    SELECT 
        DATE_FORMAT(fecha, '%M') as mes, 
        proveedor , 
        SUM(cop) as total_pagos 
    FROM 
        tbl_proveedores_turtisticos
    WHERE 
        YEAR(fecha) = 2024 
        AND (estado = 'pagado')
    GROUP BY 
        mes, proveedor 
    ORDER BY 
        total_pagos DESC
    LIMIT 10";

$result = $conn->query($sql);

$records = [];

if ($result->num_rows > 0) {
    // Obtener datos
    while($row = $result->fetch_assoc()) {
        $records[] = [
            'mes' => $row['mes'],
            'proveedor' => $row['proveedor'],
            'total_pagos' => $row['total_pagos']
        ];
    }
} else {
    echo json_encode([]);
    exit;
}
$conn->close();

// Agrupar por mes para facilitar el procesamiento posterior
$data = [];
foreach ($records as $record) {
    $mes = $record['mes'];
    if (!isset($data[$mes])) {
        $data[$mes] = [];
    }
    $data[$mes][] = [
        'proveedor' => $record['proveedor'],
        'total_pagos' => $record['total_pagos']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
