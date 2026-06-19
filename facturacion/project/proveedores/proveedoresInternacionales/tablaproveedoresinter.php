<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);
include_once "../../../config/conexion.php";
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$limit  = max(1, intval($_GET['limit'] ?? 30));
$page   = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $limit;

$whereSearch = '';
$params      = [];
$types       = '';

if ($search !== '') {
    $whereSearch = "WHERE (identificacion LIKE ? OR proveedor LIKE ? OR descripcion LIKE ? OR estado LIKE ? OR localizador LIKE ?)";
    $like    = '%' . $search . '%';
    $params  = [$like, $like, $like, $like, $like];
    $types   = 'sssss';
}

// Total de registros
$countSql = "SELECT COUNT(*) AS total FROM tbl_pagos_inter $whereSearch";
$stmtCount = mysqli_prepare($conn, $countSql);
if ($types) mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];
mysqli_stmt_close($stmtCount);

// Consulta paginada
$consulta = "SELECT * FROM tbl_pagos_inter $whereSearch ORDER BY fecha DESC LIMIT ? OFFSET ?";
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
    while ($row = mysqli_fetch_assoc($ejecutar)) {
        $id        = (int) $row['id_pagoint'];
        $ident     = htmlspecialchars($row['identificacion'] ?? '');
        $fecha     = htmlspecialchars($row['fecha'] ?? '');
        $proveedor = htmlspecialchars($row['proveedor'] ?? '');
        $desc      = htmlspecialchars($row['descripcion'] ?? '');
        $moneda    = htmlspecialchars($row['moneda'] ?? '');
        $local     = htmlspecialchars($row['localizador'] ?? '');
        $valor     = number_format((float)($row['valor'] ?? 0), 0, ',', '.');
        $estado    = htmlspecialchars($row['estado'] ?? '');

        echo "<tr>
            <td>
                <div class='d-flex flex-column align-items-center gap-1'>
                    <a href='modificarProveedoresPrepagoInter.php?id_pagoint={$id}'>
                        <button type='button' class='btn btn-success btn-sm' title='Editar'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
                                <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
                            </svg>
                        </button>
                    </a>
                    <button type='button' class='btn btn-danger btn-sm btn-eliminar' title='Eliminar'
                        data-id='{$id}' data-proveedor='{$proveedor}' data-valor='\${$valor}'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'>
                            <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
                        </svg>
                    </button>
                    <a href='formularioenviocorreoproveedorInter.php?id_pagoint={$id}'>
                        <button type='button' class='btn btn-primary btn-sm' title='Enviar email'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
                                <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
                                <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
                            </svg>
                        </button>
                    </a>
                </div>
            </td>
            <td>{$ident}</td>
            <td>{$fecha}</td>
            <td>{$proveedor}</td>
            <td>{$desc}</td>
            <td>{$moneda}</td>
            <td>{$local}</td>
            <td>\${$valor}</td>
            <td>{$estado}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center text-muted'>No se encontraron registros.</td></tr>";
}
$html = ob_get_clean();

echo json_encode(['html' => $html, 'total' => (int)$total]);
