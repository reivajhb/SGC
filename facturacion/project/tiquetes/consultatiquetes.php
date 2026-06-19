<?php
include "../../config/seguridad.php";
include "../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../config/sidebar3.php";
    include "../../config/boton_volver.php";
} else {
    include "../../config/sidebar.php";
    include "../../config/boton_volver.php";
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facturación Tiquetes | SGC ERP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc;
        }

        /* --- CABECERA PRINCIPAL --- */
        .page-header-box {
            background-color: #1a3a5c;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 15px -3px rgba(26, 58, 92, 0.2);
            border: none;
            margin-bottom: 30px;
        }

        /* --- CONTENEDOR DE TABLA CON SCROLL --- */
        .table-responsive-scroll {
            max-height: 550px;
            overflow-y: auto !important;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background-color: white;
            position: relative;
        }

        .table-responsive-scroll table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
        }

        /* Encabezado Sticky Refinado */
        .table-responsive-scroll thead th {
            position: sticky;
            top: -1px;
            z-index: 10;
            background-color: #1a3a5c !important;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 12px;
            border-bottom: 2px solid #142d4a;
            white-space: nowrap;
        }

        /* Celdas de la tabla */
        .table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 0.88rem;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* --- ESTILOS DE FILTROS --- */
        .filter-label {
            font-weight: 700;
            color: #4b5563;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #1a3a5c;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }

        /* --- BOTÓN DESCARGA --- */
        .btn-success-custom {
            background-color: #10b981;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s;
        }

        .btn-success-custom:hover {
            background-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        /* --- PAGINACIÓN CON LÁPIZ --- */
        .pagination .page-link {
            color: #1a3a5c;
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #e5e7eb;
            font-weight: 500;
        }

        .pagination .page-item.active .page-link {
            background-color: #1a3a5c;
            border-color: #1a3a5c;
        }

        .page-jump-input {
            width: 45px;
            height: 31px;
            text-align: center;
            padding: 0;
            border: 1px solid #dee2e6;
            margin: 0 4px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        /* --- LOADER --- */
        .overlay {
            display: none; position: fixed; z-index: 2000;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.7);
            justify-content: center; align-items: center;
        }
        .loader {
            border: 5px solid #f3f3f3; border-top: 5px solid #1a3a5c;
            border-radius: 50%; width: 50px; height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="page-header-box text-center">
                    <h2 class="text-white mb-0 fw-bold">INFORMACIÓN TIQUETES</h2>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 align-items-center bg-white p-3 rounded-3 shadow-sm mx-0">
            <div class="col-auto">
                <label for="myInput2" class="filter-label">Filtro:</label>
            </div>
            <div class="col-auto">
                <input class="form-control" id="myInput2" type="text" placeholder="Buscar por vendedor, localizador...">
            </div>
            <div class="col-auto ms-auto">
                <a class="btn btn-success-custom text-white d-flex align-items-center gap-2" href="../../genexcel/ExcelTiquetes.php">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                </a>
            </div>
        </div>

        <div id="overlay" class="overlay">
            <div class="loader"></div>
        </div>

        <div class="table-responsive-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center;">Acciones</th>
                        <th>Tipo trato</th>
                        <th>Vendedor</th>
                        <th>Importe</th>
                        <th>Fecha cierre</th>
                        <th>Nombre trato</th>
                        <th>Agencia/Cliente</th>
                        <th>Moneda</th>
                        <th>Servicio</th>
                        <th>Canal</th>
                        <th>Localizador</th>
                    </tr>
                </thead>
                <tbody id="tabla_resultados">
                    </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <span id="paginacion-info" class="text-muted small fw-medium"></span>
            <nav aria-label="Paginación">
                <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
            </nav>
        </div>
    </div>

    <script>
        var LIMIT = 25;
        var currentPage = 1;
        var totalRecords = 0;

        document.addEventListener('DOMContentLoaded', function () {
            cargarTabla(1);
            var searchTimer;
            document.getElementById('myInput2').addEventListener('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    currentPage = 1;
                    cargarTabla(1);
                }, 400);
            });
        });

        function cargarTabla(page) {
            var search  = document.getElementById('myInput2').value;
            var overlay = document.getElementById('overlay');
            overlay.style.display = 'flex';

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4) {
                    overlay.style.display = 'none';
                    if (this.status == 200) {
                        try {
                            var data = JSON.parse(this.responseText);
                            document.getElementById('tabla_resultados').innerHTML = data.html;
                            totalRecords = data.total;
                            currentPage  = page;
                            renderPaginacion();
                        } catch (e) {
                            document.getElementById('tabla_resultados').innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error al procesar datos.</td></tr>';
                        }
                    }
                }
            };
            xhttp.open('GET', 'tablatiquetes.php?page=' + page + '&limit=' + LIMIT + '&search=' + encodeURIComponent(search), true);
            xhttp.send();
        }

        function renderPaginacion() {
            var totalPages = Math.ceil(totalRecords / LIMIT);
            var container = document.getElementById('paginacion');
            var info = document.getElementById('paginacion-info');

            var start = totalRecords === 0 ? 0 : (currentPage - 1) * LIMIT + 1;
            var end = Math.min(currentPage * LIMIT, totalRecords);
            info.textContent = 'Mostrando ' + start + ' - ' + end + ' de ' + totalRecords + ' registros';

            container.innerHTML = '';
            if (totalPages <= 1) return;

            function crearItem(label, page, disabled, active) {
                var li = document.createElement('li');
                li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                var a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = label;
                if (!disabled) {
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        cargarTabla(page);
                        document.querySelector('.table-responsive-scroll').scrollTop = 0;
                    });
                }
                li.appendChild(a);
                return li;
            }

            function crearInputPagina(totalPages) {
                var li = document.createElement('li');
                li.className = 'page-item d-flex align-items-center';
                var input = document.createElement('input');
                input.type = 'number';
                input.min = 1;
                input.max = totalPages;
                input.placeholder = '✏';
                input.className = 'page-jump-input';
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        var pg = parseInt(input.value);
                        if (pg >= 1 && pg <= totalPages) {
                            cargarTabla(pg);
                            document.querySelector('.table-responsive-scroll').scrollTop = 0;
                        }
                        input.value = '';
                    }
                });
                li.appendChild(input);
                return li;
            }

            container.appendChild(crearItem('«', currentPage - 1, currentPage === 1, false));
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                container.appendChild(crearItem('1', 1, false, false));
                if (startPage > 2) container.appendChild(crearInputPagina(totalPages));
            }
            for (var i = startPage; i <= endPage; i++) {
                container.appendChild(crearItem(i, i, false, i === currentPage));
            }
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) container.appendChild(crearInputPagina(totalPages));
                container.appendChild(crearItem(totalPages, totalPages, false, false));
            }
            container.appendChild(crearItem('»', currentPage + 1, currentPage === totalPages, false));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>