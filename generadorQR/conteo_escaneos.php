<?php
include('../facturacion/config/conexion.php');
include "../facturacion/config/seguridad.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../facturacion/config/sidebar3.php";
    include "../facturacion/config/boton_volver.php";
} else {
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        /* ESTILOS ORIGINALES CONSERVADOS */
        .modulo-reportes {
            font-family: 'Inter', sans-serif;
            margin-top: 2rem;
            color: #334155;
        }

        .modulo-reportes .card-stats {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .modulo-reportes .custom-title {
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .modulo-reportes .search-container {
            position: relative;
            max-width: 400px;
        }

        .modulo-reportes .form-control-custom {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background-color: white;
            transition: all 0.3s ease;
        }

        .modulo-reportes .form-control-custom:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            border-color: #3b82f6;
        }

        .modulo-reportes .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
        }

        /* --- CAMBIO: CONTENEDOR DE SCROLL --- */
        .table-responsive-scroll {
            max-height: 500px; /* Altura de la tabla antes de hacer scroll */
            overflow-y: auto;
        }

        /* --- CAMBIO: CABECERA FIJA --- */
        .modulo-reportes .table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.2rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .modulo-reportes .table tbody td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.9rem;
        }

        .modulo-reportes .badge-qr {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            background-color: #eff6ff;
            color: #3b82f6;
        }

        .modulo-reportes .total-escaneos-bubble {
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            display: inline-block;
        }

        .modulo-reportes .qr-thumb {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: white;
            width: 70px;             
            height: 70px;
            object-fit: contain;
            cursor: zoom-in;
        }

        #qr-preview-floating {
            display: none;
            position: fixed;
            z-index: 99999;
            pointer-events: none;
            background: white;
            border-radius: 14px;
            padding: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border: 1px solid #e2e8f0;
            animation: qrPopIn 0.18s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }

        #qr-preview-floating img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            display: block;
        }

        @keyframes qrPopIn {
            from { opacity: 0; transform: scale(0.7); }
            to   { opacity: 1; transform: scale(1); }
        }

        .modulo-reportes .btn-action {
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .modulo-reportes .page-link {
            border: none;
            margin: 0 3px;
            border-radius: 8px !important;
            color: #64748b;
            font-weight: 500;
        }

        .modulo-reportes .page-item.active .page-link {
            background-color: #3b82f6;
            color: white;
        }

        .loading {
            display: none;
            height: 3px;
            width: 100%;
            background: linear-gradient(to right, #3b82f6, #818cf8);
            position: fixed;
            top: 0; left: 0; z-index: 9999;
            animation: loadingBar 2s infinite;
        }

        @keyframes loadingBar {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>

<body class="bg-light">
    <div id="loading" class="loading"></div>
    <div id="qr-preview-floating"><img src="" alt="QR Preview"></div>

    <div class="modulo-reportes container-fluid px-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="custom-title mb-1">Métricas de Escaneo</h2>
                <p class="text-muted small mb-0">Monitoreo en tiempo real de tus campañas QR</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="search-container ms-md-auto">
                    <input type="text" id="buscadorTabla" class="form-control-custom form-control" placeholder="🔍 Buscar por ID, descripción o tipo...">
                </div>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <div class="table-responsive-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>QR ID</th>
                            <th>Información General</th>
                            <th>Tipo</th>
                            <th>URL Tracking</th>
                            <th class="text-center">Escaneos</th>
                            <th>Código</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaConteo">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="paginacion" class="d-flex justify-content-between align-items-center mt-4">
            <span id="infoPagina" class="text-muted small fw-medium"></span>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="controlesPagina"></ul>
            </nav>
        </div>
    </div>

    <script>
        var todosLosRegistros = [];
        var paginaActual = 1;
        var porPagina = 25;

        function renderTabla(registros) {
            var tbody = $('#tablaConteo');
            tbody.empty();
            var total = registros.length;
            var totalPaginas = Math.ceil(total / porPagina) || 1;
            if (paginaActual > totalPaginas) paginaActual = totalPaginas;

            var inicio = (paginaActual - 1) * porPagina;
            var fin = inicio + porPagina;
            var pagina = registros.slice(inicio, fin);

            if (pagina.length === 0) {
                tbody.append('<tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron registros activos</td></tr>');
            }

            pagina.forEach(function (row) {
                var tablaHTML = `
                <tr id="qr_${row.qr_id}">
                    <td class="fw-bold text-primary">#${row.qr_id}</td>
                    <td>
                        <div class="fw-semibold text-dark">${row.descripcion || 'Sin descripción'}</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">ID Unico Tracking</div>
                    </td>
                    <td><span class="badge-qr">${row.tipo_qr || 'No definido'}</span></td>
                    <td>
                        <code class="small text-truncate d-inline-block" style="max-width: 150px;">${row.url_tracking}</code>
                    </td>
                    <td class="text-center">
                        <span class="total-escaneos-bubble">${row.total_escaneos}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="qr_codes/${row.qr_id}.png" alt="QR" class="qr-thumb me-2">
                            <a href="qr_codes/${row.qr_id}.png" download="${row.qr_id}.png" class="btn btn-light btn-sm p-1" title="Descargar">
                                📥
                            </a>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="ver_escaneos_qr.php?qr_id=${row.qr_id}" class="btn btn-primary btn-sm btn-action">
                            Detalles
                        </a>
                    </td>
                </tr>`;
                tbody.append(tablaHTML);
            });

            var desde = total === 0 ? 0 : inicio + 1;
            var hasta = Math.min(fin, total);
            $('#infoPagina').text('Viendo ' + desde + ' - ' + hasta + ' de ' + total + ' registros');

            var controles = $('#controlesPagina');
            controles.empty();
            controles.append('<li class="page-item ' + (paginaActual === 1 ? 'disabled' : '') + '"><a class="page-link" href="#" data-pagina="' + (paginaActual - 1) + '">Ant.</a></li>');
            
            for (var i = 1; i <= totalPaginas; i++) {
                if(i <= 3 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)) {
                    controles.append('<li class="page-item ' + (i === paginaActual ? 'active' : '') + '"><a class="page-link" href="#" data-pagina="' + i + '">' + i + '</a></li>');
                } else if (i === 4 && totalPaginas > 5) {
                    controles.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }

            controles.append('<li class="page-item ' + (paginaActual === totalPaginas ? 'disabled' : '') + '"><a class="page-link" href="#" data-pagina="' + (paginaActual + 1) + '">Sig.</a></li>');
        }

        function registrosFiltrados() {
            var valorBusqueda = $('#buscadorTabla').val().toLowerCase();
            if (!valorBusqueda) return todosLosRegistros;
            return todosLosRegistros.filter(function (row) {
                return [
                    row.qr_id, row.descripcion, row.tipo_qr, row.url_tracking, row.total_escaneos
                ].join(' ').toLowerCase().indexOf(valorBusqueda) > -1;
            });
        }

        function cargarConteo() {
            $('#loading').show();
            $.ajax({
                url: "fetch_conescaneos.php",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    todosLosRegistros = data;
                    renderTabla(registrosFiltrados());
                },
                error: function (xhr, status, error) {
                    console.error("Error al cargar el conteo:", error);
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        $(document).on('click', '#controlesPagina .page-link', function (e) {
            e.preventDefault();
            var p = parseInt($(this).data('pagina'));
            if (p) {
                paginaActual = p;
                renderTabla(registrosFiltrados());
                // Reset del scroll al cambiar página
                $('.table-responsive-scroll').scrollTop(0);
            }
        });

        $('#buscadorTabla').on('keyup', function () {
            paginaActual = 1;
            renderTabla(registrosFiltrados());
        });

        cargarConteo();
        setInterval(cargarConteo, 10000);

        // QR floating preview
        var $preview = $('#qr-preview-floating');
        var $previewImg = $preview.find('img');

        $(document).on('mouseenter', '.qr-thumb', function(e) {
            var src = $(this).attr('src');
            $previewImg.attr('src', src);
            $preview.stop(true).css('display', 'block');
            posicionarPreview(e);
        });

        $(document).on('mousemove', '.qr-thumb', function(e) {
            posicionarPreview(e);
        });

        $(document).on('mouseleave', '.qr-thumb', function() {
            $preview.css('display', 'none');
        });

        function posicionarPreview(e) {
            var pw = 220, ph = 220;
            var vw = $(window).width(), vh = $(window).height();
            var x = e.clientX + 18;
            var y = e.clientY + 18;
            if (x + pw > vw) x = e.clientX - pw - 18;
            if (y + ph > vh) y = e.clientY - ph - 18;
            $preview.css({ left: x + 'px', top: y + 'px' });
        }
    </script>
</body>

</html>