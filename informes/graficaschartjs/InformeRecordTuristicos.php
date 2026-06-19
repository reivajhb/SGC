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

// Obtener todos los pagos del año 2025 agrupados por proveedor y mes
$sql = "
    SELECT 
        MONTH(fecha) AS mes_num,
        DATE_FORMAT(fecha, '%M') AS mes_nombre,
        proveedor,
        SUM(cop) AS total_pagos
    FROM tbl_proveedores_turtisticos
    WHERE YEAR(fecha) = 2025
      AND estado = 'pagado'
    GROUP BY mes_num, mes_nombre, proveedor
    ORDER BY mes_num ASC, total_pagos DESC";

$result = $conn->query($sql);

$data_por_mes = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $mes = $row['mes_nombre'];
        $proveedor = $row['proveedor'];
        $total = $row['total_pagos'];

        // Agrupar por mes
        if (!isset($data_por_mes[$mes])) {
            $data_por_mes[$mes] = [];
        }

        $data_por_mes[$mes][] = [
            'proveedor' => $proveedor,
            'total_pagos' => $total
        ];
    }
}

$conn->close();

// Limitar a 10 proveedores por mes
foreach ($data_por_mes as $mes => $proveedores) {
    usort($proveedores, function($a, $b) {
        return $b['total_pagos'] - $a['total_pagos'];
    });
    $data_por_mes[$mes] = array_slice($proveedores, 0, 10);
}

header('Content-Type: application/json');
echo json_encode($data_por_mes);
?>
