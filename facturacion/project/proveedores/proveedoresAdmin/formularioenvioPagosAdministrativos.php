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
    <title>Enviar Email Administrativo | SGC ERP</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (iconos) -->
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
            border-radius: 15px;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
            margin-top: 2rem;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        /* Ficha resumen del pago */
        .summary-box {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }

        .table-custom {
            font-size: 0.85rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.025em;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .doc-link img {
            transition: transform 0.2s;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .doc-link:hover img {
            transform: scale(1.1);
        }

        .btn-send {
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 12px;
            background: #0d6efd;
            border: none;
            transition: all 0.3s;
        }

        .btn-send:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .icon-header-bg {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
    </style>
</head>

<body>
<div class="container py-4">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="main-card">
            
            <!-- Cabecera Corporativa -->
            <div class="card-header-custom">
                <div class="icon-header-bg">
                    <i class="fa-solid fa-envelope-open-text fa-lg"></i>
                </div>
                <h2 class="h4 mb-1">Envío de Comprobante Administrativo</h2>
                <p class="mb-0 opacity-75">Proveedor: <?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?></p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form action="enviarcorreoPagosAdministrativos.php" method="post" enctype="multipart/form-data">
                    
                    <!-- ID del pago oculto -->
                    <input name="id_pago" type="hidden" value="<?= htmlspecialchars($consultaPago['id_pago'] ?? ''); ?>">

                    <!-- Bloque de Información del Pago -->
                    <div class="summary-box">
                        <label class="form-label mb-3 text-primary">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Resumen del Pago Administrativo
                        </label>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 table-custom">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>Proveedor</th>
                                        <th>Valor (COP)</th>
                                        <th>Novedad</th>
                                        <th>Fecha</th>
                                        <th>Archivo</th>
                                        <th>Soporte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td class="fw-bold"><?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?></td>
                                        <td class="text-success fw-bold">$<?= number_format((float)($consultaPago['valor_pagado'] ?? 0), 0, ',', '.'); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($consultaPago['novedad'] ?? 'Sin novedad'); ?></span></td>
                                        <td><?= htmlspecialchars($consultaPago['fecha_pago'] ?? ''); ?></td>
                                        <td>
                                            <a class="doc-link" href="<?= htmlspecialchars($consultaPago['archivo_factura'] ?? '#'); ?>" target="_blank">
                                                <img width="35" height="35" src="./img/factura.png" alt="Factura">
                                            </a>
                                        </td>
                                        <td>
                                            <a class="doc-link" href="<?= htmlspecialchars($consultaPago['archivo_soporte'] ?? '#'); ?>" target="_blank">
                                                <img width="35" height="35" src="./img/factura.png" alt="Soporte">
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Configuración del Envío -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Electrónico Contabilidad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-at text-muted"></i></span>
                                <input name="correo" type="email" class="form-control" id="correo"
                                       value="<?= htmlspecialchars($consultaPago['email_contabilidad_proveedor'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="correo2" class="form-label">Correo Electrónico Cartera</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-at text-muted"></i></span>
                                <input name="correo2" type="email" class="form-control" id="correo2"
                                       value="<?= htmlspecialchars($consultaPago['email_cartera_proveedor'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="asunto" class="form-label">Asunto del Correo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-pen-to-square text-muted"></i></span>
                                <input name="asunto" type="text" class="form-control" id="asunto"
                                       placeholder="Ej: Comprobante de Pago Administrativo - Panamericana Viajes" required>
                            </div>
                        </div>

                        <!-- Campos ocultos requeridos (Mantenidos) -->
                        <input name="proveedor" type="hidden" value="<?= htmlspecialchars($consultaPago['nombre_proveedor'] ?? ''); ?>">
                        <input name="cop" type="hidden" value="<?= htmlspecialchars($consultaPago['valor_pagado'] ?? ''); ?>">
                        <input name="novedad" type="hidden" value="<?= htmlspecialchars($consultaPago['novedad'] ?? ''); ?>">
                        <input name="fecha" type="hidden" value="<?= htmlspecialchars($consultaPago['fecha_pago'] ?? ''); ?>">
                        <input name="archivo" type="hidden" value="<?= htmlspecialchars($consultaPago['archivo_factura'] ?? ''); ?>">
                        <input name="soporteProveedor" type="hidden" value="<?= htmlspecialchars($consultaPago['archivo_soporte'] ?? ''); ?>">

                        <!-- Botón de Envío -->
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary btn-send w-100 shadow-sm text-white">
                                <i class="fa-solid fa-paper-plane me-2"></i> Procesar y Enviar Correo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3 border-0">
                <small class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i> Sistema de Gestión de Pagos Administrativos - Confidencial</small>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>