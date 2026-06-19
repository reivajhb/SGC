<?php
include('../facturacion/config/conexion.php');
include "../facturacion/config/seguridad.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo "<script>alert('Acceso denegado.'); window.location.href = 'buscarProveedor.php';</script>";
    exit();
}

$qr_id = isset($_GET['qr_id']) ? intval($_GET['qr_id']) : 0;

include $_SESSION['id_rol'] == 1 ? "../facturacion/config/sidebar3.php" : "../facturacion/config/sidebar.php";

$query = "SELECT c.descripcion FROM tbl_campañas c WHERE c.qr_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $qr_id);
$stmt->execute();
$stmt->bind_result($descripcion);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Escaneos - <?= htmlspecialchars($descripcion) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../facturacion/img/favicon.jpg">
    <link rel="stylesheet" href="../estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        .modulo-detalles {
            font-family: 'Inter', sans-serif;
            margin-top: 2.5rem;
            color: #334155;
        }

        .modulo-detalles .custom-header {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .modulo-detalles .btn-back {
            background-color: #f1f5f9;
            color: #64748b;
            border: none;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .modulo-detalles .btn-back:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        .modulo-detalles .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
        }

        .modulo-detalles .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1.2rem;
        }

        /* AJUSTE PARA MOSTRAR TODO EL TEXTO */
        .modulo-detalles .user-agent-text {
            word-break: break-word; /* Rompe palabras largas si es necesario */
            overflow-wrap: break-word;
            white-space: normal;    /* Permite saltos de línea */
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.4;
            max-width: 100%;        /* Usa todo el espacio de la celda */
        }

        .modulo-detalles .ip-badge {
            background-color: #f0f9ff;
            color: #0369a1;
            font-family: monospace;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .modulo-detalles .page-link {
            border: none;
            margin: 0 3px;
            border-radius: 8px !important;
            color: #64748b;
        }

        .modulo-detalles .page-item.active .page-link {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>

<body class="bg-light">
    <div class="modulo-detalles container-fluid px-4">
        
        <div class="custom-header d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="conteo_escaneos.php" class="text-decoration-none">Reporte General</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Escaneos: <span class="text-primary"><?= htmlspecialchars($descripcion) ?></span></h2>
            </div>
            <a href="conteo_escaneos.php" class="btn btn-back">← Volver</a>
        </div>

        <div class="table-container shadow-sm">
            <table class="table table-hover mb-0" id="tablaDetalles">
                <thead>
                    <tr>
                        <th style="width: 15%;">IP del Visitante</th>
                        <th style="width: 65%;">Navegador / Dispositivo (User Agent)</th>
                        <th style="width: 20%;">Fecha y Hora del Acceso</th>
                    </tr>
                </thead>
                <tbody id="bodyDetalles">
                    <?php
                    $stmt = $conn->prepare("SELECT ip, user_agent, fecha_hora FROM tbl_escaneos WHERE qr_id = ? ORDER BY fecha_hora DESC");
                    $stmt->bind_param("i", $qr_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $datos = [];
                    while ($row = $result->fetch_assoc()) {
                        $datos[] = $row;
                    }
                    $stmt->close();
                    $conn->close();

                    foreach ($datos as $row): ?>
                        <tr class="fila-dato">
                            <td><span class="ip-badge"><?= htmlspecialchars($row['ip']) ?></span></td>
                            <td>
                                <div class="user-agent-text">
                                    <?= htmlspecialchars($row['user_agent']) ?>
                                </div>
                            </td>
                            <td class="text-muted fw-medium"><?= htmlspecialchars($row['fecha_hora']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($datos)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">No se registran escaneos para este código aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <div id="infoConteo" class="text-muted small fw-medium"></div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginadorDetalles"></ul>
            </nav>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const filasPorPagina = 15;
            const $filas = $('.fila-dato');
            const totalFilas = $filas.length;
            const totalPaginas = Math.ceil(totalFilas / filasPorPagina);
            let paginaActual = 1;

            function mostrarPagina(numPagina) {
                paginaActual = numPagina;
                const inicio = (numPagina - 1) * filasPorPagina;
                const fin = inicio + filasPorPagina;

                $filas.hide();
                $filas.slice(inicio, fin).show();

                actualizarPaginador();
                
                const hasta = Math.min(fin, totalFilas);
                $('#infoConteo').text(`Mostrando ${totalFilas === 0 ? 0 : inicio + 1} - ${hasta} de ${totalFilas} registros`);
            }

            function actualizarPaginador() {
                const $paginador = $('#paginadorDetalles');
                $paginador.empty();

                if (totalPaginas <= 1) return;

                $paginador.append(`<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${paginaActual - 1}">Ant.</a>
                </li>`);

                for (let i = 1; i <= totalPaginas; i++) {
                    if (i === 1 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)) {
                        $paginador.append(`<li class="page-item ${i === paginaActual ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`);
                    } else if (i === 2 || i === totalPaginas - 1) {
                        $paginador.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                $paginador.append(`<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${paginaActual + 1}">Sig.</a>
                </li>`);
            }

            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const num = $(this).data('page');
                if (num && num >= 1 && num <= totalPaginas) {
                    mostrarPagina(num);
                }
            });

            mostrarPagina(1);
        });
    </script>
</body>
</html>