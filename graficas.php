<?php 
include "seguridad.php"
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gráfica de columnas PHP y MySQL de estados y valores diferentes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
	<div class="mx-auto" style="width: 500px;" >
    <canvas id="chartCanvas"></canvas>
   </div> 
    <script>
    // Obtener los datos de MySQL utilizando PHP para cada estado y valor
    <?php
    // Conexión a la base de datos MySQL
    include 'conexion.php';
    // Consulta SQL para obtener los datos de los estados y valores
    $sql = "SELECT proveedor, SUM(cop) as 'total', estado  FROM tbl_proveedores_turtisticos where id_proveedor_fo = '2'  GROUP BY estado";
    $result = $conn->query($sql);

    // Arreglos para almacenar los datos de cada estado y valor
    $estados = array();
    $valores = array();

    if ($result->num_rows > 0) {
        // Almacenar los datos en los arreglos correspondientes
        while($row = $result->fetch_assoc()) {
            $estados[] = $row["estado"];
            $valores[] = $row["total"];
        }
    }

    $conn->close();
    ?>

    // Generar la estructura de datos necesaria para la gráfica en JavaScript
    var data = {
        labels: <?php echo json_encode($estados); ?>,
        datasets: [{
            label: 'Valores',
            data: <?php echo json_encode($valores); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    // Configuración de la gráfica
    var chartConfig = {
        type: 'bar',
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Crear la instancia de la gráfica
    var myChart = new Chart(document.getElementById('chartCanvas'), chartConfig);
    </script>
</body>
</html>
