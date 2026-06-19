<?php
include "../config/seguridad.php";
include "../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../config/sidebar3.php";
    include "../config/boton_volver.php";
} else {
    include "../config/sidebar.php";
    include "../config/boton_volver.php";
}

function ConsultarPago($id_pago, $conn) {
    $sentencia = "
        SELECT
            p.*,
            prov.nombre AS nombre_proveedor,
            prov.email_contabilidad AS email_contabilidad_proveedor,
            prov.email_cartera AS email_cartera_proveedor
        FROM tbl_pagos p
        JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
        WHERE p.id_pago = ?
    ";

    $stmt = mysqli_prepare($conn, $sentencia);
    mysqli_stmt_bind_param($stmt, "i", $id_pago);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $datos = mysqli_fetch_assoc($resultado);

    if (!$datos) {
        echo '<script>alert("Error: No se encontró ningún pago."); window.location = "consultaProveedoresTuristicos.php";</script>';
        exit;
    }
    return $datos;
}

if (isset($_GET['id_pago'])) {
    $consultaPago = ConsultarPago($_GET['id_pago'], $conn);
} else {
    echo '<script>alert("Error: ID de pago no proporcionado."); window.location = "consultaProveedoresTuristicos.php";</script>';
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enviar Email | SGC ERP</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 1.5rem;
            color: white;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.6rem 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }

        /* Estilo para la tabla informativa */
        .info-table {
            background-color: #f1f5f9;
            border-radius: 8px;
            overflow: hidden;
        }

        .info-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-table td {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .doc-icon {
            transition: transform 0.2s;
        }

        .doc-icon:hover {
            transform: scale(1.1);
        }

        .btn-send {
            padding: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>

<body>
<div class="container py-5">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="main-card">
            <!-- Encabezado con Icono -->
            <div class="card-header-custom text-center">
                <i class="fa-solid fa-paper-plane fa-2x mb-3"></i>
                <h2 class="h4 mb-0">Gestión de Envío de Notificación</h2>
                <p class="mb-0 opacity-75">Proveedor: <?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?></p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form action="../project/proveedores/proveedoresTur/enviarcorreoProveedoresTuristicos.php" method="post" enctype="multipart/form-data">
                    <!-- ID del pago oculto -->
                    <input name="id_pago" type="hidden" value="<?= htmlspecialchars($consultaPago['id_pago'] ?? ''); ?>">

                    <!-- Resumen del Pago (Visualización) -->
                    <div class="mb-4">
                        <label class="form-label mb-2 text-primary"><i class="fa-solid fa-circle-info me-2"></i>Información del Pago a Notificar</label>
                        <div class="table-responsive info-table border">
                            <table class="table table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-center">
                                        <th>Proveedor</th>
                                        <th>Valor Pagado</th>
                                        <th>Novedad</th>
                                        <th>Fecha Pago</th>
                                        <th>Factura</th>
                                        <th>Soporte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center border-top">
                                        <td class="fw-bold"><?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?></td>
                                        <td class="text-success fw-bold">$<?= number_format((float)($consultaPago['valor_pagado'] ?? 0), 0, ',', '.'); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($consultaPago['novedad'] ?? 'N/A'); ?></span></td>
                                        <td><?= htmlspecialchars($consultaPago['fecha_pago'] ?? ''); ?></td>
                                        <td>
                                            <a href="<?= htmlspecialchars($consultaPago['archivo_factura'] ?? '#'); ?>" target="_blank" class="doc-icon">
                                                <img width="32" height="32" src="../../img/factura.png" alt="Ver Factura" title="Ver Factura">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= htmlspecialchars($consultaPago['archivo_soporte'] ?? '#'); ?>" target="_blank" class="doc-icon">
                                                <img width="32" height="32" src="../../img/factura.png" alt="Ver Soporte" title="Ver Soporte">
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Campos de Correo -->
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Contabilidad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input name="correo" type="email" class="form-control" id="correo"
                                       value="<?= htmlspecialchars($consultaPago['email_contabilidad_proveedor'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="correo2" class="form-label">Correo Cartera </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input name="correo2" type="email" class="form-control" id="correo2"
                                       value="<?= htmlspecialchars($consultaPago['email_cartera_proveedor'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="asunto" class="form-label">Asunto del Correo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-pen-to-square text-muted"></i></span>
                                <input name="asunto" type="text" class="form-control" id="asunto"
                                       placeholder="Ej: Comprobante de Pago - Panamericana Viajes" required>
                            </div>
                        </div>

                        <!-- Campos ocultos requeridos por el script de envío (Mantenidos todos) -->
                        <input name="proveedor" type="hidden" value="<?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?>">
                        <input name="cop" type="hidden" value="<?= htmlspecialchars($consultaPago['valor_pagado'] ?? ''); ?>">
                        <input name="novedad" type="hidden" value="<?= htmlspecialchars($consultaPago['novedad'] ?? ''); ?>">
                        <input name="fecha" type="hidden" value="<?= htmlspecialchars($consultaPago['fecha_pago'] ?? ''); ?>">
                        <input name="archivo" type="hidden" value="<?= htmlspecialchars($consultaPago['archivo_factura'] ?? ''); ?>">
                        <input name="soporteProveedor" type="hidden" value="<?= htmlspecialchars($consultaPago['archivo_soporte'] ?? ''); ?>">

                        <!-- Botón de Acción -->
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary btn-send w-100 shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i> Enviar Notificación al Proveedor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted italic">Asegúrese de verificar los correos antes de realizar el envío.</small>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>