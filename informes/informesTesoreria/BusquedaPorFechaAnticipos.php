<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// =========================================================================
// CONTROL DE ACCESO
// =========================================================================
if ((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8)) {
    $sidebar = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) ? "../../facturacion/config/sidebar3.php" : "../../facturacion/config/sidebar.php";
    include $sidebar;
    include "../../facturacion/config/boton_volver.php";
} else {
    echo "<script>alert('Acceso denegado.'); window.location.href = '../../buscarProveedor.php';</script>";
    exit();
}

// =========================================================================
// LÓGICA DE FILTROS
// =========================================================================
$from_date = !empty($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : null;
$to_date = !empty($_GET['to_date']) ? mysqli_real_escape_string($conn, $_GET['to_date']) : null;
$nit_proveedor = !empty($_GET['nit_proveedor']) ? mysqli_real_escape_string($conn, $_GET['nit_proveedor']) : null;

$busqueda_activa = ($from_date && $to_date) || $nit_proveedor;

$where_clause = "WHERE 1=1"; 
if ($from_date && $to_date) { $where_clause .= " AND fecha BETWEEN '$from_date' AND '$to_date'"; }
if ($nit_proveedor) { $where_clause .= " AND identificacion = '$nit_proveedor'"; }

// Consultas para Totales
$consultaSum = "SELECT SUM(ValorTotalApagar) as total FROM tbl_anticipos {$where_clause} AND (estado = 'Pagado' OR estado = 'Soporte enviado y pagado')";
$mostrarSum = mysqli_fetch_array(mysqli_query($conn, $consultaSum));
$numero = number_format($mostrarSum['total'] ?? 0, 0, ",", ".");

$consultaSum2 = "SELECT SUM(ValorTotalApagar) as total FROM tbl_anticipos {$where_clause} AND estado = 'Pendiente'";
$mostrarSum2 = mysqli_fetch_array(mysqli_query($conn, $consultaSum2));
$numero2 = number_format($mostrarSum2['total'] ?? 0, 0, ",", ".");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Consulta Pagos de Anticipos</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #0f172a; }
        .main-header { background: white; padding: 1.5rem 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
        
        .stat-card { border: none; border-radius: 12px; padding: 1.5rem; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .bg-green { background-color: #198754; } 
        .bg-red { background-color: #dc3545; }   
        .card-title-custom { font-size: 1.2rem; font-weight: 500; margin-bottom: 0.5rem; }
        .card-value-custom { font-size: 2.2rem; font-weight: 700; }

        /* Contenedor principal de la tabla */
        .content-card { 
            background: white; 
            border-radius: 12px; 
            padding: 1.5rem; 
            border: 1px solid #e2e8f0; 
            margin-bottom: 1.5rem;
            max-width: 100%;
        }

        /* Corregir el desborde horizontal de la tabla */
        .table-responsive-container {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
        }
        
        .table thead th { 
            background: #3b82f6 !important; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: white !important; 
            padding: 12px; 
            text-align: center; 
            border: none;
            white-space: nowrap;
        }

        .table { font-size: 0.78rem; vertical-align: middle; width: 100% !important; margin-top: 10px !important; }
        .status-pill { color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; }
        
        /* Ajustes Select2 */
        .select2-container--bootstrap4 .select2-selection { border-radius: 6px; height: 38px; }
        .btn-custom { height: 38px; font-weight: 600; border-radius: 6px; padding: 0 20px; }

        /* Estilo para los botones de acción para que no se vean amontonados */
        .action-btn-group { display: flex; gap: 4px; justify-content: center; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container-fluid px-4">
        <h1 class="h3 m-0 fw-bold text-dark">Consulta pagos Anticipos de Proveedores Turísticos Por fecha</h1>
    </div>
</header>

<div class="container-fluid px-4">
    
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card bg-green">
                <div class="card-title-custom">Total Pagado:</div>
                <div class="card-value-custom">$<?php echo $numero ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card bg-red">
                <div class="card-title-custom">Pendiente por pagar:</div>
                <div class="card-value-custom">$<?php echo $numero2 ?></div>
            </div>
        </div>
    </div>

    <div class="content-card shadow-sm">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold text-secondary small">Del Día</label>
                <input type="date" name="from_date" value="<?php echo $_GET['from_date'] ?? ''; ?>" class="form-control shadow-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold text-secondary small">Hasta el Día</label>
                <input type="date" name="to_date" value="<?php echo $_GET['to_date'] ?? ''; ?>" class="form-control shadow-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-secondary small">NIT o Nombre del proveedor:</label>
                <select class="form-control select2" id="select_proveedor" name="nit_proveedor">
                    <option value="">-- Selecciona o busca un proveedor --</option>
                    <?php
                    $ejec_prov = mysqli_query($conn, "SELECT DISTINCT identificacion, proveedor FROM tbl_anticipos ORDER BY proveedor ASC");
                    while ($prov = mysqli_fetch_assoc($ejec_prov)): ?>
                        <option value="<?php echo $prov['identificacion']; ?>" <?php echo (isset($_GET['nit_proveedor']) && $_GET['nit_proveedor'] == $prov['identificacion']) ? 'selected' : ''; ?>>
                            <?php echo $prov['identificacion'] . " - " . $prov['proveedor']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger btn-custom flex-grow-1 shadow-sm">Buscar</button>
                <a href="../../facturacion/genexcel/ExcelPagosProveedoresPrepagoPorFecha.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&nit_proveedor=<?php echo $nit_proveedor; ?>" 
                   class="btn btn-success btn-custom flex-grow-1 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    Excel <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                </a>
            </div>
        </form>
    </div>

    <?php if ($busqueda_activa): ?>
        <div class="content-card shadow-sm">
            <div class="table-responsive-container">
                <table class="table table-hover" id="table_id">
                    <thead>
                        <tr>
                            <th>Editar</th>
                            <th>Eliminar</th>
                            <th>Email</th>
                            <th>Nit</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Descripción</th>
                            <th>Moneda</th>
                            <th>Localizador</th>
                            <th>Valor</th>
                            <th>Soporte</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM tbl_anticipos {$where_clause} ORDER BY fecha DESC";
                        $query_run = mysqli_query($conn, $query);
                        foreach ($query_run as $row) { ?>
                            <tr>
                                <td class="text-center">
                                    <a href='../../modificarProveedoresPrepago.php?id_anticipo=<?php echo $row['id_anticipo']; ?>' class="btn btn-outline-success btn-sm border-0"><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'><path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/></svg></a>
                                </td>
                                <td class="text-center">
                                    <a href='../../EliminarProveedoresPrepago.php?id_anticipo=<?php echo $row['id_anticipo']; ?>' class="btn btn-outline-danger btn-sm border-0"><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'><path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/></svg></a>
                                </td>
                                <td class="text-center">
                                    <a href='../../formularioenviocorreoproveedorPrepago.php?id_anticipo=<?php echo $row['id_anticipo']; ?>' class="btn btn-outline-primary btn-sm border-0"><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'><path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/><path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/></svg></a>
                                </td>
                                <td class="text-nowrap"><?php echo $row['identificacion']; ?></td>
                                <td class="text-nowrap"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                <td class="text-truncate" style="max-width: 150px;"><?php echo $row['proveedor']; ?></td>
                                <td class="small text-muted"><?php echo $row['descripcion']; ?></td>
                                <td class="text-center"><?php echo $row['moneda']; ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $row['localizador']; ?></span></td>
                                <td class="fw-bold text-success text-end">$<?php echo number_format($row['ValorTotalApagar'], 0, ",", "."); ?></td>
                                <td class="text-center"><a href="<?php echo $row['soportePrepago']; ?>"><img width="24" height="24" src="/facturacion/img/factura.png" /></a></td>
                                <td class="text-center"><span class="status-pill"><?php echo $row['estado']; ?></span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="content-card text-center py-5 shadow-sm">
            <h6 class="text-muted opacity-50">Ingrese los filtros anteriores para visualizar los resultados detallados</h6>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "",
            allowClear: true,
            width: '100%'
        });

        $('#table_id').DataTable({
            "paging": false, 
            "info": false,
            "order": [[4, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                "search": "Filtrar en resultados:"
            },
            "dom": '<"d-flex justify-content-end mb-2"f>rt'
        });
    });
</script>
</body>
</html>