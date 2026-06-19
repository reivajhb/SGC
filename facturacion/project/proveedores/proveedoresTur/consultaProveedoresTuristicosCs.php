<?php
include "../../../config/seguridad.php";
include_once "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
} else {
    include "../../../config/sidebar.php";
}
include "../../../config/boton_volver.php";
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGC | Información Proveedores</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fc; padding-top: 20px; }

        /* --- TÍTULO --- */
        .custom-header {
            background-color: #007bff !important;
            border-radius: 12px;
            padding: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            margin-bottom: 25px;
        }
        .custom-header h2 { font-size: 1.4rem !important; font-weight: 700 !important; text-transform: uppercase; margin: 0; }

        /* --- BARRA DE FILTRO --- */
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        /* --- TABLA CON SCROLL Y HEADER FIJO --- */
        .table-responsive-scroll {
            max-height: 500px; /* Altura del scroll */
            overflow: auto !important;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .table { width: 100%; margin-bottom: 0; font-size: 0.82rem; }

        .table thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
            background-color: #1a3a5c !important; /* Header oscuro para contraste */
            color: white !important;
            padding: 14px 12px !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            border: none;
        }

        .table td { 
            vertical-align: middle; 
            padding: 10px 12px !important; 
            border-bottom: 1px solid #f3f4f6; 
            white-space: nowrap; 
        }

        /* --- PAGINACIÓN Y LÁPIZ --- */
        .page-jump-input {
            width: 45px; height: 30px; text-align: center;
            border: 1px solid #dee2e6; border-radius: 4px; 
            margin: 0 5px; font-size: 0.85rem;
        }

        /* --- LOADER --- */
        .overlay {
            display: none; position: fixed; z-index: 3000;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.7);
            justify-content: center; align-items: center;
        }
        .loader {
            border: 5px solid #f3f3f3; border-top: 5px solid #28a745;
            border-radius: 50%; width: 50px; height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>

<body>

    <div class="container-fluid px-4 mt-4" style="padding: 5% !important; padding-top: 0px !important;">
        <div class="custom-header text-uppercase">
            <h2>Información Proveedores Turísticos</h2>
        </div>

        <div class="filter-card d-flex align-items-center gap-3">
            <label for="myInput3" class="fw-bold text-secondary text-uppercase small mb-0">Buscar:</label>
            <input class="form-control" id="myInput3" type="text" placeholder="Buscar proveedor, nit..." style="max-width: 320px;">
            <div class="ms-auto">
                <a href="../../../genexcel/ExcelProveedores.php" 
                   class="btn btn-success shadow-sm rounded-pill" style="background-color:#10b981; border:none;">
                    <i class="fas fa-file-excel me-2"></i>Excel
                </a>
            </div>
        </div>


        <div id="overlay" class="overlay">
            <div class="loader"></div>
        </div>

        <div class="table-responsive-scroll">
            <table class="table table-hover" id="tablaProveedores">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Editar</th>
                        <th>Nit</th>
                        <th>Proveedor</th>
                        <th>Tipo Proveedor</th>
                        <th>Límite Crédito</th>
                        <th>Saldo Facturado</th>
                        <th>Saldo Pagado</th>
                        <th>Saldo Disponible</th>
                    </tr>
                </thead>
                <tbody id="tabla_resultados">
                    </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5 px-2">
            <span id="paginacion-info" class="text-muted small fw-medium"></span>
            <nav aria-label="Paginación">
                <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
            </nav>
        </div>
    </div>

    <script>
        var LIMIT = 30;
        var currentPage = 1;
        var totalRecords = 0;

        function cargarTabla(page) {
            var search = $('#myInput3').val();
            $('#overlay').css('display', 'flex');

            $.ajax({
                url: 'tablaproveedoresturisticosCs.php',
                type: 'GET',
                data: { page: page, limit: LIMIT, search: search },
                dataType: 'json',
                success: function(data) {
                    $('#overlay').hide();
                    $('#tabla_resultados').html(data.html);
                    totalRecords = data.total;
                    currentPage = page;
                    renderPaginacion();
                },
                error: function() {
                    $('#overlay').hide();
                    $('#tabla_resultados').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos.</td></tr>');
                }
            });
        }

        function renderPaginacion() {
            var totalPages = Math.ceil(totalRecords / LIMIT);
            var container = $('#paginacion');
            $('#paginacion-info').text('Mostrando registros de ' + totalRecords);
            
            container.empty();
            if (totalPages <= 1) return;

            function crearItem(label, page, disabled, active) {
                var li = $('<li class="page-item ' + (disabled ? 'disabled' : '') + (active ? 'active' : '') + '"></li>');
                var a = $('<a class="page-link" href="#">' + label + '</a>');
                if (!disabled) {
                    a.click(function(e) { 
                        e.preventDefault(); 
                        cargarTabla(page); 
                        $('.table-responsive-scroll').scrollTop(0);
                    });
                }
                return li.append(a);
            }

            // Botón anterior
            container.append(crearItem('«', currentPage - 1, currentPage === 1, false));

            // Lógica de números y lápiz (integrado)
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                container.append(crearItem('1', 1, false, false));
                if (startPage > 2) {
                    var liInput = $('<li class="page-item d-flex align-items-center"></li>');
                    var input = $('<input class="page-jump-input" placeholder="✏">');
                    input.on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            var p = parseInt($(this).val());
                            if (p >= 1 && p <= totalPages) cargarTabla(p);
                        }
                    });
                    container.append(liInput.append(input));
                }
            }

            for (var i = startPage; i <= endPage; i++) {
                container.append(crearItem(i, i, false, i === currentPage));
            }

            if (endPage < totalPages) {
                container.append(crearItem('...', 0, true, false));
                container.append(crearItem(totalPages, totalPages, false, false));
            }

            // Botón siguiente
            container.append(crearItem('»', currentPage + 1, currentPage === totalPages, false));
        }

        $(document).ready(function() {
            cargarTabla(1);
            $('#myInput3').on('keyup', function() {
                clearTimeout(window.searchTimer);
                window.searchTimer = setTimeout(function() { cargarTabla(1); }, 400);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>