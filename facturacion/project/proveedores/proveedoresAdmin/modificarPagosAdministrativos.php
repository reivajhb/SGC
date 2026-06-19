<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
    include "../../../config/boton_volver.php";
} else {
    include "../../../config/sidebar.php";
    include "../../../config/boton_volver.php";
}

function ConsultarPago($id_pago, $conn)
{
    $sentencia = "SELECT p.*, p.total_pagar, prov.nombre AS proveedor_nombre
                  FROM tbl_pagos p
                  JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
                  WHERE p.id_pago = ?";
    $stmt = mysqli_prepare($conn, $sentencia);
    mysqli_stmt_bind_param($stmt, "i", $id_pago);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $mostrar = mysqli_fetch_assoc($resultado);

    if (!$mostrar) {
        echo '<script>alert("No se encontró ningún pago."); window.location = "consultaProveedoresTuristicos.php";</script>';
        exit;
    }
    return $mostrar;
}

if (isset($_GET['id_pago'])) {
    $consultaPago = ConsultarPago($_GET['id_pago'], $conn);
} else {
    echo '<script>alert("Debe proporcionar un ID de pago para modificar."); window.location = "consultaProveedoresTuristicos.php";</script>';
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <title>Modificar Pago</title>
</head>

<body>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <h2 class="h5 mb-0">
                Modificar Pago: <?= htmlspecialchars($consultaPago['proveedor_nombre']); ?>
            </h2>
        </div>

        <div class="card-body">
            <div class="mx-auto" style="max-width: 800px;">
                <form action="cargaDrivePagosAdministrativos.php" method="post" enctype="multipart/form-data">
                    <input name="id_pago" type="hidden" value="<?= htmlspecialchars($consultaPago['id_pago']); ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="proveedor" class="form-label">Proveedor</label>
                            <input readonly name="proveedor" type="text" class="form-control" id="proveedor"
                                   value="<?= htmlspecialchars($consultaPago['proveedor_nombre']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="cop" class="form-label">Valor sin retenciones</label>
                            <input readonly name="cop" type="number" class="form-control" id="cop"
                                   value="<?= htmlspecialchars($consultaPago['valor_pagado']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="total_pagar" class="form-label">Valor a pagar</label>
                            <input name="total_pagar" type="number" class="form-control" id="total_pagar"
                                   value="<?= htmlspecialchars($consultaPago['total_pagar']); ?>">
                        </div>

                        <div class="col-12">
                            <label for="novedad" class="form-label">Novedad</label>
                            <input name="novedad" type="text" class="form-control" id="novedad"
                                   value="<?= htmlspecialchars($consultaPago['novedad']); ?>">
                        </div>

                        <div class="col-12">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="<?= htmlspecialchars($consultaPago['estado']); ?>">
                                    <?= htmlspecialchars($consultaPago['estado']); ?>
                                </option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Soporte enviado y pagado">Soporte enviado y pagado</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block mb-1">Archivo de Factura</label>
                            <a class="btn btn-link px-0"
                               href="<?= htmlspecialchars($consultaPago['archivo_factura']); ?>" target="_blank">
                                Ver archivo
                            </a>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block mb-1">Archivo de Soporte</label>
                            <a class="btn btn-link px-0"
                               href="<?= htmlspecialchars($consultaPago['archivo_soporte']); ?>" target="_blank">
                                Ver soporte
                            </a>
                        </div>

                        <div class="col-12">
                            <label for="soporteProveedor" class="form-label">Subir nuevo documento soporte de pago*</label>
                            <input type="file" class="form-control" name="soporteProveedor" id="soporteProveedor">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Editar Pago</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Si aún necesitas peticion.js, déjalo. Si no, puedes quitarlo -->
<!-- -->

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
