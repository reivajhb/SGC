<?php 
// Headers para exportar a Excel
header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=InformeFacturas.xls");

include '../config/seguridad.php';
include '../config/conexion.php'; 

// Función para formatear moneda
function formatoMoneda($valor) {
    return number_format($valor, 2, ",", ".");
}

// === CONSTRUCCIÓN DE LA CONSULTA DINÁMICA ===
// Recibir los filtros de la URL
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$nit_proveedor_busqueda = $_GET['nit_proveedor'] ?? '';

$consulta_base = "SELECT 
    c.*,
    p.nit_identificacion,
    p.nombre AS nombre_proveedor,
    p.tipo_proveedor
FROM 
    tbl_causacion c
JOIN 
    tbl_proveedores p ON c.id_proveedor = p.id_proveedor";

$parametros = [];
$tipos_datos = "";
$clausula_where = [];

// Añadir filtros si existen
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $clausula_where[] = "c.fecha_emision BETWEEN ? AND ?";
    $parametros[] = $fechaInicio;
    $parametros[] = $fechaFin;
    $tipos_datos .= "ss";
}

if (!empty($nit_proveedor_busqueda)) {
    $clausula_where[] = "p.nit_identificacion = ?";
    $parametros[] = $nit_proveedor_busqueda;
    $tipos_datos .= "s";
}

// Si hay filtros, agregar la cláusula WHERE
if (!empty($clausula_where)) {
    $consulta_final = $consulta_base . " WHERE " . implode(' AND ', $clausula_where);
} else {
    $consulta_final = $consulta_base;
}

$stmt = mysqli_prepare($conn, $consulta_final);

// Vincular parámetros si la consulta los tiene
if (!empty($parametros)) {
    mysqli_stmt_bind_param($stmt, $tipos_datos, ...$parametros);
}

mysqli_stmt_execute($stmt);
$ejecutar = mysqli_stmt_get_result($stmt);
?>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Nit proveedor</th>
            <th scope="col">Nombre</th>
            <th scope="col">Tipo Proveedor</th>
            <th scope="col">Numero factura</th>
            <th scope="col">Prefijo</th>
            <th scope="col">Fecha emision</th>
            <th scope="col">Fecha vencimiento</th>
            <th scope="col">Localizador</th>
            <th scope="col">Tipo moneda</th>
            <th scope="col">Iva</th>
            <th scope="col">Valor facturado</th>
            <th scope="col">Valor causado</th>
            <th scope="col">Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {
            ?>
            <tr>
                <td><?php echo $mostrarPaAdmin['nit_identificacion']; ?></td>
                <td><?php echo $mostrarPaAdmin['nombre_proveedor']; ?></td>
                <td><?php echo $mostrarPaAdmin['tipo_proveedor']; ?></td>
                <td><?php echo $mostrarPaAdmin['numero_factura']; ?></td>
                <td><?php echo $mostrarPaAdmin['prefijo']; ?></td>
                <td><?php echo $mostrarPaAdmin['fecha_emision']; ?></td>
                <td><?php echo $mostrarPaAdmin['fecha_vencimiento']; ?></td>
                <td><?php echo $mostrarPaAdmin['localizador']; ?></td>
                <td><?php echo $mostrarPaAdmin['tipo_moneda']; ?></td>
                <td>$<?php echo formatoMoneda($mostrarPaAdmin['iva']); ?></td>
                <td>$<?php echo formatoMoneda($mostrarPaAdmin['valorpagar']); ?></td>
                <td>$<?php echo formatoMoneda($mostrarPaAdmin['valorpagar']); ?></td>
                <td><?php echo $mostrarPaAdmin['estado']; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>