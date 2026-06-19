<?php
include('../facturacion/config/conexion.php');
include "../facturacion/config/seguridad.php";

// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    // Incluir el sidebar para el administrador
    include "../facturacion/config/sidebar3.php";
    include "../facturacion/config/boton_volver.php";
} else {
    // Incluir el sidebar normal para usuarios no administradores
    include "../facturacion/config/sidebar.php";
    include "../facturacion/config/boton_volver.php";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conteo de Escaneos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="icon" type="image/x-icon" href="img/favicon.jpg">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../estilos/estilos.css">
</head>

<body>
    <div class="container-fluid mt-2">
        <h4 class="text-center mb-4">INFORME ESCANEOS</h4>

        <div class="row mb-3">
            <div class="col-md-6 offset-md-3">
                <label for="tipoQR" class="form-label">Seleccione el tipo de código QR:</label>
                <select id="tipoQR" class="form-select">
                    <option value="CONDUCTOR">CONDUCTOR</option>
                    <option value="CAMPAÑA">CAMPAÑA</option>
                    <option value="HOTEL">HOTEL</option>
                    <option value="TOOLKIT">TOOLKIT</option>
                    <option value="GUIAS">GUÍAS</option>
                    <option value="COMENTARIOS">COMENTARIOS</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chartqr1"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chartqr2"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        let chartPie = null;
        let chartBar = null;

        async function fetchData(tipoQR) {
            const response = await fetch(`/facturacion/informes/graficaschartjs/informeEscaneos.php?tipo_qr=${encodeURIComponent(tipoQR)}`);
            return await response.json();
        }

        async function renderCharts(tipoQR) {
            const data = await fetchData(tipoQR);

            const pieCtx = document.getElementById('chartqr1').getContext('2d');
            const barCtx = document.getElementById('chartqr2').getContext('2d');

            // Destruir si existen
            if (chartPie) chartPie.destroy();
            if (chartBar) chartBar.destroy();

            // PIE CHART
            chartPie = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.raw_values,
                        backgroundColor: data.labels.map((_, i) =>
                            `hsl(${i * 40 % 360}, 70%, 70%)`
                        ),
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const index = context.dataIndex;
                                    const value = data.raw_values[index];         // Total real
                                    const porcentaje = data.values[index];        // Porcentaje
                                    return `${context.label}: ${value} (${porcentaje}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // BAR CHART
            chartBar = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Total de Escaneos',
                        data: data.raw_values, // <- estos deben venir del PHP
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad'
                            }
                        }
                    }
                }
            });
        }

        document.getElementById('tipoQR').addEventListener('change', function () {
            renderCharts(this.value);
        });

        // Inicial
        renderCharts(document.getElementById('tipoQR').value);
    </script>


</body>

</html>