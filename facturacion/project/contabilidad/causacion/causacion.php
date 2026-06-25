<?php
include_once "../../../config/seguridad.php";
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

// Lógica de filtrado
$fechaInicio = $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? '';
$fechaFin = $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? '';
$nit_proveedor_busqueda = $_POST['nit_proveedor'] ?? $_GET['nit_proveedor'] ?? '';
$buscar = trim($_POST['buscar'] ?? $_GET['buscar'] ?? '');

function formatoMoneda($valor) {
    return number_format((float)$valor, 0, ",", ".");
}

// === CONFIGURACIÓN DE LA PAGINACIÓN ===
$resultados_por_pagina = 100;
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $resultados_por_pagina;

$consulta_base = "
    SELECT c.*, p.nit_identificacion, p.nombre AS nombre_proveedor, p.tipo_proveedor, p.email_contabilidad, p.email_cartera, p.id_proveedor
    FROM tbl_causacion c
    JOIN tbl_proveedores p ON c.id_proveedor = p.id_proveedor";

$parametros = [];
$tipos_datos = "";
$clausula_where = [];

if (!empty($fechaInicio) && !empty($fechaFin)) {
    $clausula_where[] = "c.fecha_emision BETWEEN ? AND ?";
    $parametros[] = $fechaInicio; $parametros[] = $fechaFin;
    $tipos_datos .= "ss";
}
if (!empty($nit_proveedor_busqueda)) {
    $clausula_where[] = "p.nit_identificacion = ?";
    $parametros[] = $nit_proveedor_busqueda;
    $tipos_datos .= "s";
}
if (!empty($buscar)) {
    $termino = '%' . $buscar . '%';
    $clausula_where[] = "(p.nit_identificacion LIKE ? OR p.nombre LIKE ? OR c.localizador LIKE ? OR c.numero_factura LIKE ? OR c.estado LIKE ?)";
    $parametros[] = $termino; $parametros[] = $termino; $parametros[] = $termino; $parametros[] = $termino; $parametros[] = $termino;
    $tipos_datos .= "sssss";
}

$where_sql = !empty($clausula_where) ? " WHERE " . implode(' AND ', $clausula_where) : "";

// Contar Total
$stmt_total = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM tbl_causacion c JOIN tbl_proveedores p ON c.id_proveedor = p.id_proveedor" . $where_sql);
if (!empty($parametros)) mysqli_stmt_bind_param($stmt_total, $tipos_datos, ...$parametros);
mysqli_stmt_execute($stmt_total);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_paginas = ceil($total_registros / $resultados_por_pagina);
mysqli_stmt_close($stmt_total);

// Consulta Final
$stmt = mysqli_prepare($conn, $consulta_base . $where_sql . " LIMIT ? OFFSET ?");
$params_final = array_merge($parametros, [$resultados_por_pagina, $offset]);
mysqli_stmt_bind_param($stmt, $tipos_datos . "ii", ...$params_final);
mysqli_stmt_execute($stmt);
$ejecutar = mysqli_stmt_get_result($stmt);

$tipo_proveedor_actual = null;
if ($ejecutar && mysqli_num_rows($ejecutar) > 0) {
    $fila_temp = mysqli_fetch_assoc($ejecutar);
    $tipo_proveedor_actual = $fila_temp['tipo_proveedor'];
    mysqli_data_seek($ejecutar, 0);
}

$url_excel = '../../../genexcel/ExcelPagosCausacion.php?' . http_build_query(['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'nit_proveedor' => $nit_proveedor_busqueda, 'buscar' => $buscar]);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Facturación Tiquetes | SGC ERP</title>   
    <style>
        body { background-color: #f7f9fc; padding-top: 10px; }

        .custom-header {
            background-color: #007bff !important;
            border-radius: 12px; padding: 20px; color: white; text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2); margin-bottom: 25px;
        }

        .filter-card {
            background: white; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px;
        }

        .select2-hidden-accessible { border: 0 !important; clip: rect(0 0 0 0) !important; height: 1px !important; margin: -1px !important; overflow: hidden !important; padding: 0 !important; position: absolute !important; width: 1px !important; }

        .pagination .page-link { color: #1a3a5c !important; border: 1px solid #dee2e6; margin: 0 3px; border-radius: 6px !important; background-color: #ffffff; }
        .pagination .page-item.active .page-link { background-color: #1a3a5c !important; border-color: #1a3a5c !important; color: #ffffff !important; }

        .page-jump-input { width: 45px; height: 31px; border: 1px solid #dee2e6; background-color: #fff; color: #1a3a5c; text-align: center; margin: 0 3px; border-radius: 6px !important; outline: none; }

        /* --- AJUSTES DE TABLA PARA EVITAR DESBORDE --- */
        .table-responsive-scroll {
            max-height: 550px; overflow: auto; border-radius: 12px;
            border: 1px solid #dee2e6; background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); position: relative;
        }

        .table { 
            width: 100%; 
            margin-bottom: 0; 
            table-layout: fixed; /* Clave para controlar anchos */
        }

        .table thead th {
            position: sticky; top: 0; z-index: 10;
            background-color: #1a3a5c !important; color: white !important;
            padding: 12px 8px; text-transform: uppercase; font-size: 0.7rem; border: none;
        }

        /* Definición de anchos de columna */
        .col-check { width: 35px; text-align: center; }
        .col-edit  { width: 45px; text-align: center; }
        .col-pdf { width: 45px; text-align: center; }
        .col-nit   { width: 90px; }
        .col-nombre { width: 180px; }
        .col-tipo  { width: 80px; }
        .col-factura { width: 100px; }
        .col-prefijo { width: 60px; }
        .col-fecha { width: 90px; }
        .col-loc   { width: 100px; }
        .col-mon   { width: 60px; }
        .col-val   { width: 100px; }

        .table td { 
            vertical-align: middle; 
            font-size: 0.78rem; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; /* Poner ... si el texto es muy largo */
            padding: 8px 8px !important; 
        }
    </style>
</head>
<body>

<div class="container-fluid px-4 mt-4" style="padding: 5% !important; padding-top: 0px !important;"> <div class="custom-header text-uppercase">
        <h2 class="fw-bold mb-0">Información Facturas Proveedores</h2>
    </div>

    <div class="filter-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold small">FECHA INICIO</label>
                <input class="form-control" type="date" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">FECHA FIN</label>
                <input class="form-control" type="date" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small">PROVEEDOR</label>
                <select class="form-select select2" name="nit_proveedor">
                    <option value="">Todos los proveedores</option>
                    <?php 
                    $prov_res = mysqli_query($conn, "SELECT nit_identificacion, nombre FROM tbl_proveedores ORDER BY nombre ASC");
                    while ($p = mysqli_fetch_assoc($prov_res)): ?>
                        <option value="<?= htmlspecialchars($p['nit_identificacion']) ?>" 
                            <?= ($p['nit_identificacion'] == $nit_proveedor_busqueda) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nit_identificacion'] . " - " . $p['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small">BUSCAR (NIT, nombre, localizador, factura...)</label>
                <input class="form-control" type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar en todos los registros...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 fw-bold" type="submit">CONSULTAR</button>
            </div>
        </form>
    </div>

    <div class="mb-4">
        <nav>
            <ul class="pagination justify-content-center pagination-sm align-items-center">
                <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?>&fecha_inicio=<?= urlencode($fechaInicio) ?>&fecha_fin=<?= urlencode($fechaFin) ?>&nit_proveedor=<?= urlencode($nit_proveedor_busqueda) ?>&buscar=<?= urlencode($buscar) ?>">Anterior</a>
                </li>
                <?php 
                $rango = 2; $inicio = max(1, $pagina_actual - $rango); $fin = min($total_paginas, $pagina_actual + $rango);
                if ($inicio > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?pagina=1&fecha_inicio='.urlencode($fechaInicio).'&fecha_fin='.urlencode($fechaFin).'&nit_proveedor='.urlencode($nit_proveedor_busqueda).'&buscar='.urlencode($buscar).'">1</a></li>';
                    if ($inicio > 2) echo '<li><input type="number" placeholder="&#xf303;" style="font-family: \'Inter\', \'Font Awesome 6 Free\'; font-weight: 900;" class="page-jump-input" onkeydown="irAPagina(this,event,'.$total_paginas.')"></li>';
                }
                for ($i = $inicio; $i <= $fin; $i++): ?>
                    <li class="page-item <?= ($pagina_actual == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>&fecha_inicio=<?= urlencode($fechaInicio) ?>&fecha_fin=<?= urlencode($fechaFin) ?>&nit_proveedor=<?= urlencode($nit_proveedor_busqueda) ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; 
                if ($fin < $total_paginas) {
                    if ($fin < $total_paginas - 1) echo '<li><input type="number" placeholder="&#xf303;" style="font-family: \'Inter\', \'Font Awesome 6 Free\'; font-weight: 900;" class="page-jump-input" onkeydown="irAPagina(this,event,'.$total_paginas.')"></li>';
                    echo '<li class="page-item"><a class="page-link" href="?pagina='.$total_paginas.'&fecha_inicio='.urlencode($fechaInicio).'&fecha_fin='.urlencode($fechaFin).'&nit_proveedor='.urlencode($nit_proveedor_busqueda).'&buscar='.urlencode($buscar).'">'. $total_paginas.'</a></li>';
                }
                ?>
                <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?>&fecha_inicio=<?= urlencode($fechaInicio) ?>&fecha_fin=<?= urlencode($fechaFin) ?>&nit_proveedor=<?= urlencode($nit_proveedor_busqueda) ?>&buscar=<?= urlencode($buscar) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small"><?= number_format($total_registros, 0, ',', '.') ?> registro(s) encontrado(s)<?= !empty($buscar) ? ' para "' . htmlspecialchars($buscar) . '"' : '' ?></span>
        <div class="d-flex gap-2">
            <?php if (in_array($tipo_proveedor_actual, ['Administrativo', 'Turístico'])): ?>
                <button id="unirPagosBtn" class="btn btn-primary shadow-sm rounded-pill"><i class="fas fa-link me-2"></i>Unir Pagos</button>
            <?php endif; ?>
            <a class="btn btn-success shadow-sm rounded-pill" style="background-color: #10b981; border:none;" href="<?= $url_excel ?>">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
        </div>
    </div>

    <div class="table-responsive-scroll shadow-sm">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" id="checkAll"></th>
                    <th class="col-edit">EDIT</th>
                    <th class="col-pdf">PDF</th>
                    <th class="col-nit">NIT</th>
                    <th class="col-nombre">NOMBRE</th>
                    <th class="col-tipo">TIPO</th>
                    <th class="col-factura">FACTURA</th>
                    <th class="col-prefijo">PREF</th>
                    <th class="col-fecha">EMISIÓN</th>
                    <th class="col-fecha">VENCE</th>
                    <th class="col-loc">LOCALIZADOR</th>
                    <th class="col-mon">MON</th>
                    <th class="col-val">VALOR</th>
                    <th class="col-tipo">ESTADO</th>
                </tr>
            </thead>
            <tbody id="myTable2">
                <?php while ($fila = mysqli_fetch_assoc($ejecutar)): ?>
                    <tr data-id-causacion="<?= $fila['id_causacion'] ?>" data-valorpagar="<?= $fila['valorpagar'] ?>" data-nit="<?= $fila['nit_identificacion'] ?>" data-nombre="<?= $fila['nombre_proveedor'] ?>" data-idproveedor="<?= $fila['id_proveedor'] ?>">
                        <td class="col-check"><input type="checkbox" name="seleccionar[]" value="<?= $fila['id_causacion'] ?>"></td>
                        <td class="col-edit">
                            <a href="modificarCausacion.php?id_causacion=<?= $fila['id_causacion'] ?>" class="btn btn-sm btn-success rounded-circle"><i class="fas fa-pen" style="font-size: 0.7rem;"></i></a>
                        </td>
                        <td class="col-pdf">
                            <a href="verPdfDescom.php?id_causacion=<?= urlencode($fila['id_causacion']) ?>" class="btn btn-sm btn-outline-danger rounded-circle" target="_blank" title="Ver PDF">
                                <i class="fas fa-file-pdf" style="font-size: 0.7rem;"></i>
                            </a>
                        </td>
                        <td class="col-nit"><?= $fila['nit_identificacion'] ?></td>
                        <td class="col-nombre" title="<?= $fila['nombre_proveedor'] ?>"><?= $fila['nombre_proveedor'] ?></td>
                        <td class="col-tipo"><span class="badge bg-light text-dark border" style="font-size: 0.65rem;"><?= $fila['tipo_proveedor'] ?></span></td>
                        <td class="col-factura" title="<?= $fila['numero_factura'] ?>"><?= $fila['numero_factura'] ?></td>
                        <td class="col-prefijo"><?= $fila['prefijo'] ?></td>
                        <td class="col-fecha"><?= $fila['fecha_emision'] ?></td>
                        <td class="col-fecha"><?= $fila['fecha_vencimiento'] ?></td>
                        <td class="col-loc fw-bold text-primary"><?= $fila['localizador'] ?></td>
                        <td class="col-mon"><?= $fila['tipo_moneda'] ?></td>
                        <td class="col-val fw-bold">$<?= formatoMoneda($fila['valorpagar']) ?></td>
                        <td class="col-tipo"><?= $fila['estado'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="py-5"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function irAPagina(input, e, totalPaginas) {
        if (e.key === 'Enter') {
            var pg = parseInt(input.value);
            if (pg >= 1 && pg <= totalPaginas) {
                var params = new URLSearchParams(window.location.search);
                params.set('pagina', pg);
                window.location.href = '?' + params.toString();
            }
            input.value = '';
        }
    }
   $(document).ready(function() {

    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Todos los proveedores',
        allowClear: true
    });



    $("#checkAll").click(function() {
        $("#myTable2 input[type=checkbox]").prop('checked', $(this).prop('checked'));
    });

    });
</script>
</body>
</html>