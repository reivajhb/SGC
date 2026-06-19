<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// =========================================================================
// LÓGICA DE BACKEND (Mantenida íntegra)
// =========================================================================

function formatFecha($fecha) {
    return (empty($fecha) || $fecha === '0000-00-00' || $fecha === '0000-00-00 00:00:00') ? '---' : date('d/m/Y', strtotime($fecha));
}

function generarFilaTabla($row) {
    $fechaInicio = $row['fecha'];
    $fechaRetencion = $row['fecha_Retencion'];
    $fechaSoporte  = $row['fecha_Soporte'];
    $fechaFin      = $row['fecha_pago'];
    $fechaIngreso  = $row['fecha_ingreso'];
    $fechaSalida   = $row['fecha_salida'];
    $tiempo = "";
    $dias = $horas = 0;

    if ($fechaInicio !== "0000-00-00 00:00:00" && $fechaFin !== "0000-00-00 00:00:00") {
        $diff = strtotime($fechaFin) - strtotime($fechaInicio);
        $dias = floor($diff / (60 * 60 * 24));
        $horas = floor(($diff % (60 * 60 * 24)) / (60 * 60));
        $minutos = floor(($diff % (60 * 60)) / 60);
        $tiempo = "{$dias}d {$horas}h {$minutos}m";
    } elseif ($fechaInicio !== "0000-00-00 00:00:00" && $row['estado'] === 'Pendiente') {
        $diff = time() - strtotime($fechaInicio);
        $dias = floor($diff / (60 * 60 * 24));
        $tiempo = "Pendiente ({$dias}d)";
    }

    if ($fechaRetencion == "0000-00-00 00:00:00") { $alerta = "Sin Retenciones"; $claseAlerta = "badge-critica"; } 
    elseif ($fechaFin == "0000-00-00 00:00:00") { $alerta = "Sin Soporte"; $claseAlerta = "badge-critica"; } 
    elseif ($dias >= 2 || $horas >= 36) { $alerta = "+36 Horas"; $claseAlerta = "badge-warning"; } 
    else { $alerta = "A tiempo"; $claseAlerta = "badge-ok"; }

    $valor = number_format($row['ValorTotalApagar'], 0, ",", ".");
    $claseEstado = (strtolower($row['estado']) === 'pendiente') ? 'table-warning' : '';

    return "<tr class='{$claseEstado}'>
                <td><span class='status-pill'>{$row['estado']}</span></td>
                <td style='min-width: 110px;'><span class='badge-custom {$claseAlerta}'>{$alerta}</span></td>
                <td class='fw-bold'>{$row['identificacion']}</td>
                <td class='text-nowrap'>".formatFecha($fechaInicio)."</td>
                <td class='text-nowrap'>".formatFecha($fechaRetencion)."</td>
                <td class='text-nowrap'>".formatFecha($fechaSoporte)."</td>
                <td class='text-nowrap'>".formatFecha($fechaFin)."</td>
                <td class='text-nowrap'>".formatFecha($fechaIngreso)."</td>
                <td class='text-nowrap'>".formatFecha($fechaSalida)."</td>
                <td class='text-nowrap text-muted' style='font-size: 0.75rem;'>{$tiempo}</td>
                <td class='text-truncate' style='max-width: 140px;' title='{$row['proveedor']}'>{$row['proveedor']}</td>
                <td><span class='badge bg-light text-dark border'>{$row['localizador']}</span></td>
                <td class='fw-bold text-success text-end'>$$valor</td>
              </tr>";
}

if (!((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8))) {
    exit("<script>alert('Acceso denegado.'); window.location.href = '../../buscarProveedor.php';</script>");
}

$sidebar = ($_SESSION['id_rol'] == 1) ? "../../facturacion/config/sidebar3.php" : "../../facturacion/config/sidebar.php";
include $sidebar;
include "../../facturacion/config/boton_volver.php";

$resPagado = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(ValorTotalApagar) as total FROM tbl_anticipos WHERE estado IN ('Pagado','Soporte enviado y pagado')"));
$totalPagado = number_format((float)($resPagado['total'] ?? 0), 0, ",", ".");
$resPendiente = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(ValorTotalApagar) as total FROM tbl_anticipos WHERE estado = 'Pendiente'"));
$totalPendiente = number_format((float)($resPendiente['total'] ?? 0), 0, ",", ".");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Anticipos</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #0f172a; }
        .main-header { background: white; padding: 1.25rem 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem; }
        
        .stat-card { border: none; border-radius: 12px; padding: 1.25rem; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .bg-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

        /* CONTENEDOR MAESTRO DE LA TABLA - Esto soluciona el desborde */
        .content-card { 
            background: white; 
            border-radius: 12px; 
            padding: 1rem; 
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            max-width: 100%;
        }

        /* AJUSTES DE TABLA */
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table { font-size: 0.82rem; margin-bottom: 0; vertical-align: middle; }
        .table thead th { 
            background: #f8fafc; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: #64748b; 
            padding: 12px 8px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        
        .badge-custom { 
            padding: 4px 8px; 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 0.65rem; 
            display: inline-block;
            text-align: center;
        }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-critica { background: #fee2e2; color: #991b1b; }
        
        .status-pill { color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; display: inline-block; width: 100%; text-align: center;}
        
        /* Forzar que las columnas de fechas no se amontonen */
        .text-nowrap { white-space: nowrap !important; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <h1 class="h4 m-0 fw-bold">Panel de Anticipos</h1>
        <a href="../../facturacion/genexcel/ExcelTiemposAnticipos.php" class="btn btn-dark btn-sm px-3">
            Exportar Reporte
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download"
                viewBox="0 0 16 16">
                <path
                    d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                <path
                    d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
            </svg>
        </a>
    </div>
</header>

<div class="container-fluid px-4">
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card bg-green text-start">
                <div class="small opacity-75 fw-medium">Total Pagado</div>
                <div class="h2 fw-bold m-0">$<?php echo $totalPagado ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card bg-red text-start">
                <div class="small opacity-75 fw-medium">Total Pendiente</div>
                <div class="h2 fw-bold m-0">$<?php echo $totalPendiente ?></div>
            </div>
        </div>
    </div>

    <div class="content-card shadow-sm">
        <div class="table-responsive-custom">
            <table id="miTabla" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 100px;">Estado</th>
                        <th>Alerta</th>
                        <th>ID</th>
                        <th>Fecha Inicio</th>
                        <th>F. Retención</th>
                        <th>F. Soporte</th>
                        <th>F. Pago</th>
                        <th>F. Ent. Pasajeros</th>
                        <th>F. Sal. Pasajeros</th>
                        <th>Tiempo</th>
                        <th>Proveedor</th>
                        <th>Localizador</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result_total = mysqli_query($conn, "SELECT * FROM tbl_anticipos WHERE fecha >= '2025-08-26' ORDER BY fecha DESC");
                    while ($row = mysqli_fetch_assoc($result_total)) {
                        echo generarFilaTabla($row);
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#miTabla').DataTable({
        "scrollX": false, // Desactivado para usar el contenedor CSS que es más estable con Bootstrap
        "pageLength": 10,
        "order": [[3, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros"
        },
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
    });
});
</script>

</body>
</html>