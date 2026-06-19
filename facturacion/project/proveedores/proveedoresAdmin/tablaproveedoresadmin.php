<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);
include "../../../config/conexion.php";
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$limit  = max(1, intval($_GET['limit'] ?? 30));
$page   = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $limit;

function formatoMoneda($valor) {
    return number_format($valor, 0, ",", ".");
}

$whereSearch = '';
$params = [];
$types  = '';

if ($search !== '') {
    $whereSearch = "AND (prov.nit_identificacion LIKE ? OR prov.nombre LIKE ? OR p.estado LIKE ? OR p.novedad LIKE ? OR p.locacion LIKE ? OR p.identificacion LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like, $like, $like];
    $types  = 'ssssss';
}

// Total registros
$countSql = "
    SELECT COUNT(*) AS total
    FROM tbl_pagos p
    JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
    WHERE prov.tipo_proveedor = 'Administrativo'
    $whereSearch
";
$stmtCount = mysqli_prepare($conn, $countSql);
if ($types) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];
mysqli_stmt_close($stmtCount);

// Consulta paginada
$consulta = "
    SELECT
        p.id_pago,
        p.valor_pagado AS valor,
        p.novedad,
        p.fecha_pago AS fecha,
        p.estado,
        p.archivo_factura AS archivo,
        p.archivo_soporte AS soporteAdmin,
        p.locacion,
        p.identificacion,
        prov.email_contabilidad AS email_contabilidad,
        p.total_pagar
    FROM tbl_pagos p
    JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
    WHERE prov.tipo_proveedor = 'Administrativo'
    $whereSearch
    ORDER BY p.fecha_pago DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conn, $consulta);
if ($types) {
    $allTypes  = $types . 'ii';
    $allParams = array_merge($params, [$limit, $offset]);
    mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
}
mysqli_stmt_execute($stmt);
$ejecutar = mysqli_stmt_get_result($stmt);

ob_start();
if (mysqli_num_rows($ejecutar) > 0) {
    while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {
        $id_pago_ad = $mostrarPaAdmin['id_pago'];
        $valorApagar = $mostrarPaAdmin['valor'];
        $numero = number_format($valorApagar, 0, ",", ".");


        echo "<td>
        <div class='d-flex flex-column align-items-center'>
            <!-- Botón Editar -->
            <a href='modificarPagosAdministrativos.php?id_pago={$id_pago_ad}'>
                <button type='button' class='btn btn-success btn-sm mb-2' title='Editar'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
                        <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
                    </svg>
                </button>
            </a>
            
            <!-- Botón Eliminar con modal -->
            <button type='button' class='btn btn-danger btn-sm mb-2' data-toggle='modal' data-target='#modalEliminar{$id_pago_ad}' title='Eliminar'>
                <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'>
                    <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
                </svg>
            </button>
            
            <!-- Botón Enviar Correo -->
            <a href='formularioenviopagosadministrativos.php?id_pago={$id_pago_ad}&email={$mostrarPaAdmin['email_contabilidad']}'>
                <button type='button' class='btn btn-primary btn-sm mb-2' title='Enviar correo'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
                        <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/><path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/></svg>
                </button>
            </a>
        </div>
        <!-- Modal Eliminar -->
        <div class='modal fade' id='modalEliminar{$id_pago_ad}' tabindex='-1' role='dialog' aria-labelledby='modalEliminarLabel{$id_pago_ad}' aria-hidden='true'>
            <div class='modal-dialog modal-dialog-centered' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>¿Estás seguro de que deseas eliminar este registro?</h5>
                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>
                    <div class='modal-body'>
                        <table class='table'>
                            <thead>
                                <tr><th>Id Pago</th><th>Proveedor</th><th>Valor</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>".htmlspecialchars($mostrarPaAdmin['id_pago'])."</td>
                                    <td>".htmlspecialchars($mostrarPaAdmin['locacion'])."</td>
                                    <td>$".htmlspecialchars($numero)."</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
                        <a href='EliminarPagosAdministrativos.php?id_pago=".htmlspecialchars($id_pago_ad)."'><button type='button' class='btn btn-danger'>Eliminar</button></a>
                    </div>
                </div>
            </div>
        </div>
    </td>";
    echo '<td>' . htmlspecialchars($mostrarPaAdmin['identificacion'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($mostrarPaAdmin['locacion'] ?? 'N/A') . '</td>';
    echo '<td>$' . htmlspecialchars($numero) . '</td>';
    echo '<td>$' . number_format($mostrarPaAdmin['total_pagar'] ?? 0, 0, ",", ".") . '</td>';
    echo '<td>' . htmlspecialchars($mostrarPaAdmin['novedad'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($mostrarPaAdmin['fecha'] ?? 'N/A') . '</td>';
    echo '<td><a href="' . htmlspecialchars($mostrarPaAdmin['archivo'] ?? '#') . '" target="_blank"><img width="50" height="50" src="/facturacion2/img/factura.png" alt="Soporte"></a></td>';
    echo '<td>' . htmlspecialchars($mostrarPaAdmin['estado'] ?? 'N/A') . '</td>';
    echo '<td><a href="' . htmlspecialchars($mostrarPaAdmin['soporteAdmin'] ?? '#') . '" target="_blank"><img width="50" height="50" src="/facturacion2/img/factura.png" alt="Soporte"></a></td>';
    echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10" class="text-center">No se encontraron pagos.</td></tr>';
}

$html = ob_get_clean();
mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode(['total' => (int)$total, 'html' => $html]);
?>
