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
    $whereSearch = "WHERE (p.nit_identificacion LIKE ? OR p.nombre LIKE ? OR p.tipo_proveedor LIKE ?)";
    $like    = '%' . $search . '%';
    $params  = [$like, $like, $like];
    $types   = 'sss';
}

// Total de registros para la paginación
$countSql = "
    SELECT COUNT(*) AS total
    FROM tbl_proveedores p
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
        p.id_proveedor,
        p.nit_identificacion,
        p.nombre,
        p.tipo_proveedor,
        p.limite_credito,
        COALESCE(sub_c.saldo_facturado, 0) AS saldo_facturado,
        COALESCE(sub_pg.saldo_pagado, 0) AS saldo_pagado,
        p.limite_credito - GREATEST(0, (COALESCE(sub_c.saldo_facturado, 0) - COALESCE(sub_pg.saldo_pagado, 0))) AS saldo_disponible
    FROM tbl_proveedores p
    LEFT JOIN (
        SELECT id_proveedor, SUM(valorpagar) AS saldo_facturado
        FROM tbl_causacion
        WHERE YEAR(fecha_emision) = YEAR(CURDATE()) AND MONTH(fecha_emision) = MONTH(CURDATE())
        GROUP BY id_proveedor
    ) AS sub_c ON p.id_proveedor = sub_c.id_proveedor
    LEFT JOIN (
        SELECT id_proveedor, SUM(valor_pagado) AS saldo_pagado
        FROM tbl_pagos
        WHERE YEAR(fecha_pago) = YEAR(CURDATE()) AND MONTH(fecha_pago) = MONTH(CURDATE()) AND estado = 'Pagado'
        GROUP BY id_proveedor
    ) AS sub_pg ON p.id_proveedor = sub_pg.id_proveedor
    $whereSearch
    ORDER BY p.nombre ASC
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
    while ($row = mysqli_fetch_assoc($ejecutar)) {
        $id        = htmlspecialchars($row['id_proveedor']);
        $nit       = htmlspecialchars($row['nit_identificacion']);
        $nombre    = htmlspecialchars($row['nombre']);
        $tipo      = htmlspecialchars($row['tipo_proveedor']);
        $limite    = number_format($row['limite_credito'], 0, ',', '.');
        $facturado = number_format($row['saldo_facturado'], 0, ',', '.');
        $pagado    = number_format($row['saldo_pagado'], 0, ',', '.');
        $disponible = number_format($row['saldo_disponible'], 0, ',', '.');

        echo "<tr>
            <td><a href='modificarProveedoresTuristicosCs.php?id_proveedor={$id}'>
                <button type='button' class='btn btn-success btn-sm'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
                        <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
                    </svg>
                </button>
            </a></td>
            <td>{$nit}</td>
            <td>{$nombre}</td>
            <td>{$tipo}</td>
            <td>\$ {$limite}</td>
            <td>\$ {$facturado}</td>
            <td>\$ {$pagado}</td>
            <td>\$ {$disponible}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center text-muted'>No se encontraron registros.</td></tr>";
}
$html = ob_get_clean();

echo json_encode(['html' => $html, 'total' => (int)$total]);
