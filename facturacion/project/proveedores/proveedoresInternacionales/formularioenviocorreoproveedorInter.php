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

$consulta = ConsultarAnticipo($_GET['id_pagoint']);

function ConsultarAnticipo($id_pagoint)
{
    include "../../../config/conexion.php";
    $sentencia = "SELECT * FROM tbl_pagos_inter WHERE id_pagoint = '" . $id_pagoint . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrar = $ejecutar->fetch_assoc();

    return [
        $mostrar['id_pagoint'],
        $mostrar['fecha'],
        $mostrar['proveedor'],
        $mostrar['identificacion'],
        $mostrar['email_Proveedor'],
        $mostrar['localizador'],
        $mostrar['num_factura'],
        $mostrar['concepto'],
        $mostrar['descripcion'],
        $mostrar['moneda'],
        $mostrar['valor'],
        $mostrar['usuario'],
        $mostrar['fecha_ingreso'],
        $mostrar['certificacion'],
        $mostrar['fecha_salida'],
        $mostrar['cuentadecobro'],
        $mostrar['egreso'],
        $mostrar['ValorTotalApagar'],
        $mostrar['soportePrepago'],
        $mostrar['relacionpago'],
    ];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enviar Email | Pagos Internacionales</title>

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
            border-radius: 16px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
            margin-top: 2rem;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 2.5rem 1.5rem;
            color: white;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
        }

        /* Bloque informativo de resumen */
        .info-summary {
            background-color: #f1f5f9;
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            border: 1px solid #e2e8f0;
        }

        .table-custom {
            font-size: 0.85rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .btn-send-email {
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-radius: 12px;
            background: #0d6efd;
            border: none;
            transition: all 0.4s ease;
        }

        .btn-send-email:hover {
            background: #0b5ed7;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .doc-thumbnail {
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 3px rgba(0,0,0,0.07));
        }

        .doc-thumbnail:hover {
            transform: scale(1.2) rotate(5deg);
        }

        .header-icon-container {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            backdrop-filter: blur(4px);
        }
    </style>
</head>

<body>
<div class="container py-4">
    <div class="mx-auto" style="max-width: 1100px;">
        <div class="main-card">
            
            <!-- Header Corporativo -->
            <div class="card-header-custom">
                <div class="header-icon-container">
                    <i class="fa-solid fa-globe fa-2x"></i>
                </div>
                <h2 class="h4 mb-1 fw-bold">Notificación de Pago Internacional</h2>
                <p class="mb-0 opacity-75">Proveedor: <?php echo htmlspecialchars($consulta[2]); ?></p>
            </div>

            <div class="card-body p-4 p-lg-5">
                <form action="enviarcorreoProveedoresInter.php" method="post" enctype="multipart/form-data">

                    <!-- ID Oculto -->
                    <input name="id_anticipo" type="hidden" value="<?php echo htmlspecialchars($consulta[0]); ?>">

                    <!-- Resumen del Pago Internacional -->
                    <div class="info-summary">
                        <label class="form-label mb-3 text-primary">
                            <i class="fa-solid fa-clipboard-list me-2"></i>Verificación de Datos de Pago
                        </label>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 table-custom">
                                <thead>
                                    <tr class="text-center">
                                        <th>Proveedor</th>
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                        <th>Total Pagado</th>
                                        <th>Relación</th>
                                        <th>Soporte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td class="fw-bold"><?php echo htmlspecialchars($consulta[2]); ?></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($consulta[8]); ?></td>
                                        <td><span class="badge bg-white text-dark border"><?php echo htmlspecialchars($consulta[1]); ?></span></td>
                                        <td class="text-success fw-bold">
                                            $<?php
                                                $valorApagar = (float)($consulta[17] ?? 0);
                                                echo number_format($valorApagar, 0, ",", ".");
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($consulta[19]); ?>" target="_blank">
                                                <img class="doc-thumbnail" width="35" height="35" src="./img/factura.png" alt="Relación" title="Ver Relación de Pagos">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($consulta[18]); ?>" target="_blank">
                                                <img class="doc-thumbnail" width="35" height="35" src="./img/factura.png" alt="Soporte" title="Ver Soporte de Pago">
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Configuración del Mensaje -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Destinatario (Contabilidad)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-at text-muted"></i></span>
                                <input name="correo" type="email" class="form-control border-start-0" id="correo"
                                       value="<?php echo htmlspecialchars($consulta[4]); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="asunto" class="form-label">Asunto de Notificación</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-file-signature text-muted"></i></span>
                                <input name="asunto" type="text" class="form-control border-start-0" id="asunto" 
                                       placeholder="Ej: Confirmación de Pago Internacional" required>
                            </div>
                        </div>

                        <!-- Campos ocultos técnicos -->
                        <input name="proveedor" type="hidden" value="<?php echo htmlspecialchars($consulta[2]); ?>">
                        <input name="descripcion" type="hidden" value="<?php echo htmlspecialchars($consulta[8]); ?>">
                        <input name="fecha" type="hidden" value="<?php echo htmlspecialchars($consulta[1]); ?>">
                        <input name="ValorTotalApagar" type="hidden" value="<?php echo htmlspecialchars($consulta[17]); ?>">
                        <input name="relacionpago" type="hidden" value="<?php echo htmlspecialchars($consulta[19]); ?>">
                        <input name="soportePrepago" type="hidden" value="<?php echo htmlspecialchars($consulta[18]); ?>">

                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary btn-send-email w-100 text-white shadow">
                                <i class="fa-solid fa-paper-plane me-2"></i> Procesar Envío Internacional
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3 border-0">
                <p class="text-muted small mb-0">
                    <i class="fa-solid fa-lock me-1"></i> Sistema de Notificaciones SGC ERP - Asegure que los archivos adjuntos sean legibles.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>