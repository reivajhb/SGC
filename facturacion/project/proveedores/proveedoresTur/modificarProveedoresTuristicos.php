<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
} else {
    include "../../../config/sidebar.php";
}
include "../../../config/boton_volver.php";

// === LÓGICA DE CONSULTA SEGURA ===
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
    <title>SGC | Modificar Pago Turístico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; padding-top: 20px; }
        
        .main-container { padding: 20px 0; }

        /* Cabecera Estilo ERP */
        .form-header {
            background-color: #1a3a5c;
            color: white;
            padding: 25px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 25px;
            border: none;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a3a5c;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        label { font-weight: 600; font-size: 0.85rem; color: #495057; margin-bottom: 5px; }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #dce1e5;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #1a3a5c;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }

        .form-control[readonly] { background-color: #f8f9fa; color: #6c757d; }

        /* Estilos de botones */
        .btn-submit {
            background-color: #10b981;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            color: white;
            width: 100%;
        }
        .btn-submit:hover { background-color: #059669; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .file-link:hover { background-color: #0d6efd; color: white; }
    </style>
</head>

<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-header">
                    <h2 class="mb-0 h4">MODIFICAR PAGO: <span class="fw-light"><?= htmlspecialchars($consultaPago['proveedor_nombre']); ?></span></h2>
                </div>

                <form action="cargaDriveProveedoresTuristicosSP.php" method="post" enctype="multipart/form-data">
                    <input name="id_pago" type="hidden" value="<?= htmlspecialchars($consultaPago['id_pago']); ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Detalles del Pago</div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label>Proveedor Turístico</label>
                                <input readonly type="text" class="form-control" value="<?= htmlspecialchars($consultaPago['proveedor_nombre']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Valor Sin Retenciones</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input readonly type="number" class="form-control fw-bold" value="<?= htmlspecialchars($consultaPago['valor_pagado']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Valor a Pagar (Final)</label>
                                <div class="input-group">
                                    <span class="input-group-text text-success">$</span>
                                    <input name="total_pagar" type="number" step="any" class="form-control fw-bold text-success" value="<?= htmlspecialchars($consultaPago['total_pagar']); ?>">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label>Novedad</label>
                                <input name="novedad" type="text" class="form-control" placeholder="Ej: Ajuste por retención" value="<?= htmlspecialchars($consultaPago['novedad']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Estado Actual</label>
                                <select class="form-select" name="estado" id="estado">
                                    <option value="<?= htmlspecialchars($consultaPago['estado']); ?>" selected><?= htmlspecialchars($consultaPago['estado']); ?> (Actual)</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Pagado">Pagado</option>
                                    <option value="Soporte enviado y pagado">Soporte enviado y pagado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-paperclip"></i> Gestión de Documentos</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="d-block">Factura Registrada</label>
                                <a class="file-link mt-1" href="<?= htmlspecialchars($consultaPago['archivo_factura']); ?>" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Ver Factura
                                </a>
                            </div>
                            <div class="col-md-6">
                                <label class="d-block">Soporte Existente</label>
                                <a class="file-link mt-1" href="<?= htmlspecialchars($consultaPago['archivo_soporte']); ?>" target="_blank">
                                    <i class="fas fa-image"></i> Ver Soporte Actual
                                </a>
                            </div>
                            <div class="col-12">
                                <label for="soporteProveedor">Subir Nuevo Soporte de Pago (Si aplica)</label>
                                <input type="file" class="form-control" name="soporteProveedor" id="soporteProveedor">
                                <div class="form-text">Si selecciona un archivo, reemplazará el soporte actual en el sistema.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-submit shadow-sm">
                            <i class="fas fa-save me-2"></i> Guardar Cambios en el Pago
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>