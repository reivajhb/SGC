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
        $mostrar['ValorTotalApagar'],
        $mostrar['estado'],
        $mostrar['egreso'],
        $mostrar['soportePrepago'],
        $mostrar['fecha_Retencion'],
        $mostrar['relacionpago'],
    ];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGC | Editar Pago Internacional</title>

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
        }

        .form-control[readonly] { background-color: #f8f9fa; color: #6c757d; }

        /* Botón Principal */
        .btn-submit {
            background-color: #1a3a5c;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-submit:hover { background-color: #142d4a; transform: translateY(-2px); }

        /* Botones de Descarga */
        .download-btn {
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
        .download-btn:hover { background-color: #0d6efd; color: white; }
    </style>
</head>

<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-header">
                    <h2 class="mb-0 h4">PAGO INTERNACIONAL: <span class="fw-light"><?php echo $consulta[2] ?></span></h2>
                </div>

                <form action="cargaDriveProveedoresInterSP.php" method="post" enctype="multipart/form-data">
                    <input name="id_pagoint" type="hidden" value="<?php echo $consulta[0] ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-id-card"></i> Información General</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Fecha de Registro</label>
                                <input readonly class="form-control" value="<?php echo $consulta[1] ?>">
                            </div>
                            <div class="col-md-6">
                                <label>NIT o Cédula</label>
                                <input readonly class="form-control" value="<?php echo $consulta[3] ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Proveedor</label>
                                <input readonly class="form-control" value="<?php echo $consulta[2] ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Correo Electrónico</label>
                                <input readonly class="form-control" value="<?php echo $consulta[4] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-plane-departure"></i> Detalles de Reserva</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Localizador</label>
                                <input readonly class="form-control" value="<?php echo $consulta[5] ?>">
                            </div>
                            <div class="col-md-4">
                                <label>No. de Factura</label>
                                <input readonly class="form-control" value="<?php echo $consulta[6] ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Moneda</label>
                                <input readonly class="form-control fw-bold text-primary" value="<?php echo $consulta[9] ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Valor Bruto</label>
                                <input readonly class="form-control" value="<?php echo number_format($consulta[10], 2) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Total a Pagar</label>
                                <input readonly class="form-control fw-bold text-success" value="<?php echo number_format($consulta[16], 2) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Soportes Existentes</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="d-block">Certificación Bancaria</label>
                                <a class="download-btn w-100" href="<?php echo $consulta[13] ?>" target="_blank">
                                    <i class="fas fa-university"></i> Descargar Certificación
                                </a>
                            </div>
                            <div class="col-md-6">
                                <label class="d-block">Cuenta de Cobro</label>
                                <a class="download-btn w-100" href="<?php echo $consulta[15] ?>" target="_blank">
                                    <i class="fas fa-file-contract"></i> Descargar Cuenta
                                </a>
                            </div>
                            <?php if (!empty($consulta[21])): ?>
                            <div class="col-md-6">
                                <label class="d-block">Relación de Pago Actual</label>
                                <a class="download-btn w-100" href="<?php echo $consulta[21] ?>" target="_blank">
                                    <i class="fas fa-list-ol"></i> Ver Relación Actual
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($consulta[19])): ?>
                            <div class="col-md-6">
                                <label class="d-block">Soporte de Pago Actual</label>
                                <a class="download-btn w-100" href="<?php echo $consulta[19] ?>" target="_blank">
                                    <i class="fas fa-receipt"></i> Ver Soporte Actual
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-edit"></i> Gestión de Pago</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="egreso">Número de Egreso*</label>
                                <input type="number" name="egreso" class="form-control" id="egreso" value="<?php echo $consulta[18] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="estado">Estado</label>
                                <select class="form-select" name="estado" id="estado">
                                    <option value="<?php echo $consulta[17] ?>" selected><?php echo $consulta[17] ?> (Actual)</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Pagado">Pagado</option>
                                    <option value="Soporte enviado y pagado">Soporte enviado y pagado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="relacionpago">Subir Nueva Relación de Pagos*</label>
                                <input type="file" class="form-control" name="relacionpago" id="relacionpago" required>
                            </div>
                            <div class="col-md-6">
                                <label for="soportePrepago">Subir Nuevo Soporte de Pago*</label>
                                <input type="file" class="form-control" name="soportePrepago" id="soportePrepago" required>
                            </div>
                            <div class="col-md-6">
                                <label>Fecha de Carga (Soporte)*</label>
                                <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha_Soporte" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <button type="submit" class="btn-submit shadow">
                            <i class="fas fa-save me-2"></i> Actualizar Pago Internacional
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Obtener Hora Colombia UTC-5
        const now = new Date();
        const colombiaOffset = -5;
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const colTime = new Date(utc + (3600000 * colombiaOffset));
        const formattedDate = colTime.toISOString().slice(0, 16);
        document.getElementById("fecha_hora_colombia").value = formattedDate;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>