<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// Verificar si el usuario es administrador o usuario especial con ID 8
if ((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8)) {
    if ($_SESSION['id_rol'] == 1) {
        include "../../facturacion/config/sidebar3.php"; // Admin
    } else {
        include "../../facturacion/config/sidebar.php";  // Usuario especial
    }
    include "../../facturacion/config/boton_volver.php";
} else {
    echo "<script>alert('Acceso denegado.'); window.location.href = '../../buscarProveedor.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <!-- SOLO Bootstrap 4 (coherente con el resto del sistema) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- jQuery ya fue cargado por sidebar3.php, no duplicar -->
    <!-- Tu JS -->
    <script src="peticion.js"></script>
</head>

<body class="bg-dark text-light">

<div class="container my-4">

    <!-- ================== ANTICIPOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS ANTICIPOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart1"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart2"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart3"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart4"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== ADMINISTRATIVOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS ADMINISTRATIVOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart5"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart6"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart7"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart8"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== TURÍSTICOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS PROVEEDORES TURISTICOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart9"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart10"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart11"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart12"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== PENDIENTES ANTICIPOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS PENDIENTES ANTICIPOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart13"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart14"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart15"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart16"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== PENDIENTES ADMINISTRATIVOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS PENDIENTES ADMINISTRATIVOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart17"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart18"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart19"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart20"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== PENDIENTES TURÍSTICOS ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">INFORME DE PAGOS PENDIENTES PROVEEDORES TURISTICOS</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart21"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart22"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart23"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart24"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== HOTELES MÁS PAGOS POR MES ================== -->
    <div class="row">
        <div class="col-12">
            <h4 class="text-center my-4">HOTELES MÁS PAGOS POR MES</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart25"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart26"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart27"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="chart28"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== ALERTAS RT ================== -->
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="graficoAlertas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <canvas id="graficoAlertas1"></canvas>
                </div>
            </div>
        </div>
    </div>

</div> <!-- /container principal -->

<script>
    // ================== CONFIGURACIONES GENERALES ==================

    const baseColors = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(199, 199, 199, 0.2)'
    ];

    const baseBorderColors = [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(199, 199, 199, 1)'
    ];

    function createSimpleChart(ctxId, type, labels, values, label) {
        const ctx = document.getElementById(ctxId).getContext('2d');
        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: baseColors,
                    borderColor: baseBorderColors,
                    borderWidth: 1
                }]
            },
            options: (type === 'bar' || type === 'line') ? {
                scales: { y: { beginAtZero: true } }
            } : {}
        });
    }

    /**
     * Carga datos desde un endpoint y dibuja 4 gráficos (bar, line, pie, doughnut)
     * @param {string} url - Endpoint que devuelve {labels:[], values:[]}
     * @param {string} idBar
     * @param {string} idLine
     * @param {string} idPie
     * @param {string} idDoughnut
     * @param {string} label
     */
    function loadChartsFromEndpoint(url, idBar, idLine, idPie, idDoughnut, label) {
        fetch(url)
            .then(res => res.json())
            .then(data => {
                const labels = data.labels || [];
                const values = data.values || [];

                createSimpleChart(idBar, 'bar', labels, values, label);
                createSimpleChart(idLine, 'line', labels, values, label);
                createSimpleChart(idPie, 'pie', labels, values, label);
                createSimpleChart(idDoughnut, 'doughnut', labels, values, label);
            })
            .catch(err => console.error('Error cargando ' + url, err));
    }

    // ================== CARGA DE GRÁFICAS ==================

    // Anticipos
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeAnticipos.php',
        'chart1', 'chart2', 'chart3', 'chart4',
        'Anticipos por mes'
    );

    // Administrativos
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeAdministrativos.php',
        'chart5', 'chart6', 'chart7', 'chart8',
        'Pagos administrativos por mes'
    );

    // Turísticos
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeTuristicos.php',
        'chart9', 'chart10', 'chart11', 'chart12',
        'Pagos turísticos por mes'
    );

    // Anticipos Pendientes
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeAnticiposPendiente.php',
        'chart13', 'chart14', 'chart15', 'chart16',
        'Anticipos pendientes por mes'
    );

    // Administrativos Pendientes
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeAdministrativosPendiente.php',
        'chart17', 'chart18', 'chart19', 'chart20',
        'Administrativos pendientes por mes'
    );

    // Turísticos Pendientes
    loadChartsFromEndpoint(
        '/facturacion/informes/graficaschartjs/InformeTuristicosPendiente.php',
        'chart21', 'chart22', 'chart23', 'chart24',
        'Turísticos pendientes por mes'
    );

    // ================== HOTELES MÁS PAGOS POR MES ==================

    fetch('/facturacion/informes/graficaschartjs/InformeRecordTuristicos.php')
        .then(response => response.json())
        .then(data => {
            const months = Object.keys(data);
            const providersData = {};

            // Construir estructura proveedor → [valores por mes]
            months.forEach((month, monthIndex) => {
                data[month].forEach(entry => {
                    if (!providersData[entry.proveedor]) {
                        providersData[entry.proveedor] = Array(months.length).fill(0);
                    }
                    providersData[entry.proveedor][monthIndex] = entry.total_pagos;
                });
            });

            const colors = [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(199, 199, 199, 0.2)',
                'rgba(201, 203, 207, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)'
            ];

            const borderColors = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(199, 199, 199, 1)',
                'rgba(201, 203, 207, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ];

            const datasets = Object.keys(providersData).map((proveedor, index) => {
                return {
                    label: proveedor,
                    data: providersData[proveedor],
                    backgroundColor: colors[index % colors.length],
                    borderColor: borderColors[index % borderColors.length],
                    borderWidth: 1
                };
            });

            // Bar
            new Chart(document.getElementById('chart25').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: datasets
                },
                options: {
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Line
            new Chart(document.getElementById('chart26').getContext('2d'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: datasets
                },
                options: {
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Pie & Doughnut: total por proveedor
            const pieLabels = Object.keys(providersData);
            const pieValues = pieLabels.map(p => providersData[p].reduce((acc, v) => acc + v, 0));

            const pieData = {
                labels: pieLabels,
                datasets: [{
                    label: 'Total pagos',
                    data: pieValues,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            };

            new Chart(document.getElementById('chart27').getContext('2d'), {
                type: 'pie',
                data: pieData
            });

            new Chart(document.getElementById('chart28').getContext('2d'), {
                type: 'doughnut',
                data: pieData
            });
        })
        .catch(error => console.error('Error InformeRecordTuristicos.php:', error));

    // ================== ALERTAS RT ==================

    fetch('/facturacion/informes/informesTesoreria/consultaProveedoresTiemposRT.php?modo=json')
        .then(response => response.json())
        .then(data => {
            const etiquetas = Object.keys(data);
            const valores = Object.values(data);

            const colores = ['#36a2eb', '#ff6384', '#ffce56', '#4bc0c0'];

            // Barras
            new Chart(document.getElementById('graficoAlertas').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Cantidad',
                        data: valores,
                        backgroundColor: colores,
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Resumen de Alertas por Tipo' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Pie
            new Chart(document.getElementById('graficoAlertas1').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Cantidad',
                        data: valores,
                        backgroundColor: colores,
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Resumen de Alertas por Tipo' }
                    }
                }
            });
        })
        .catch(error => console.error('Error consultaProveedoresTiemposRT.php:', error));

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
