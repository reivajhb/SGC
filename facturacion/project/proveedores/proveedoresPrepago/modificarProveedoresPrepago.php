<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
} else {
    include "../../../config/sidebar.php";
}
include "../../../config/boton_volver.php";

// Consulta (Se mantiene tu lógica original)
$id_anticipo = mysqli_real_escape_string($conn, $_GET['id_anticipo']);
$sentencia = "SELECT * FROM tbl_anticipos WHERE id_anticipo = '$id_anticipo'";
$ejecutar = mysqli_query($conn, $sentencia);
$c = $ejecutar->fetch_assoc();
?>

<!doctype html>
<html lang="es">
<head>
    <title>SGC | Editar Anticipo</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; padding-top: 20px; }
        
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
            padding: 25px;
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

        .form-control[readonly] { background-color: #f8f9fa; color: #6c757d; border-color: #eee; }

        /* Botones */
        .btn-action-main {
            background-color: #1a3a5c;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            color: white;
            width: 100%;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-action-main:hover { background-color: #142d4a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26, 58, 92, 0.3); }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .download-btn:hover { background-color: #0d6efd; color: white; }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-header">
                    <h2 class="mb-0 h4">GESTIÓN DE SOPORTE: <span class="fw-light"><?php echo $c['proveedor']; ?></span></h2>
                </div>

                <form action="cargaDriveProveedoresPrepagoSP.php" method="post" enctype="multipart/form-data">
                    <input name="id_anticipo" type="hidden" value="<?php echo $c['id_anticipo']; ?>">
                    <input type="hidden" name="descripcionRT" value="<?php echo $c['descripcionRT']; ?>">
                    <input type="hidden" name="fecha"          value="<?php echo $c['fecha']; ?>">
                    <input type="hidden" name="identificacion" value="<?php echo $c['identificacion']; ?>">
                    <input type="hidden" name="proveedor"      value="<?php echo $c['proveedor']; ?>">
                    <input type="hidden" name="email_proveedor" value="<?php echo $c['email_Proveedor']; ?>">
                    <input type="hidden" name="localizador"    value="<?php echo $c['localizador']; ?>">
                    <input type="hidden" name="num_factura"    value="<?php echo $c['num_factura']; ?>">
                    <input type="hidden" name="concepto"       value="<?php echo $c['concepto']; ?>">
                    <input type="hidden" name="descripcion"    value="<?php echo htmlspecialchars($c['descripcion']); ?>">
                    <input type="hidden" name="moneda"         value="<?php echo $c['moneda']; ?>">
                    <input type="hidden" name="valor"          value="<?php echo $c['valor']; ?>">
                    <input type="hidden" name="usuario"        value="<?php echo $c['usuario']; ?>">
                    <input type="hidden" name="fecha_ingreso"  value="<?php echo $c['fecha_ingreso']; ?>">
                    <input type="hidden" name="fecha_salida"   value="<?php echo $c['fecha_salida']; ?>">
                    <input type="hidden" name="certificacion"  value="<?php echo $c['certificacion']; ?>">
                    <input type="hidden" name="cuentadecobro"  value="<?php echo $c['cuentadecobro']; ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-file-invoice"></i> Datos de Referencia</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Fecha de Registro</label>
                                <input readonly class="form-control" type="datetime-local" value="<?php echo $c['fecha']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>NIT o Cédula</label>
                                <input readonly class="form-control" value="<?php echo $c['identificacion']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Correo Proveedor</label>
                                <input readonly class="form-control" type="email" value="<?php echo $c['email_Proveedor']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Localizador</label>
                                <input readonly class="form-control" value="<?php echo $c['localizador']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>No. de Factura</label>
                                <input readonly class="form-control" value="<?php echo $c['num_factura']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Asesor</label>
                                <input readonly class="form-control" value="<?php echo $c['usuario']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Concepto</label>
                                <input readonly class="form-control" value="<?php echo $c['concepto']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Valor Bruto (<?php echo $c['moneda']; ?>)</label>
                                <input readonly class="form-control fw-bold" value="<?php echo number_format($c['valor'], 2); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Fecha de Entrada de Pasajeros</label>
                                <input readonly class="form-control" type="date" value="<?php echo $c['fecha_ingreso']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Fecha de Salida de Pasajeros</label>
                                <input readonly class="form-control" type="date" value="<?php echo $c['fecha_salida']; ?>">
                            </div>
                            <div class="col-12">
                                <label>Información Adicional</label>
                                <textarea readonly class="form-control" rows="2"><?php echo $c['descripcion']; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-paperclip"></i> Soportes del Anticipo</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Certificación Bancaria</label>
                                <div class="mt-1">
                                    <a class="download-btn" href="<?php echo $c['certificacion']; ?>" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Ver Certificación
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Cuenta de Cobro / Factura</label>
                                <div class="mt-1">
                                    <a class="download-btn" href="<?php echo $c['cuentadecobro']; ?>" target="_blank">
                                        <i class="fas fa-file-invoice-dollar"></i> Ver Documento
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <button type="button" class="btn-action-main shadow" data-bs-toggle="modal" data-bs-target="#modalSoportePago">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Abrir Panel de Carga de Soporte
                        </button>
                    </div>

                    <div class="modal fade" id="modalSoportePago" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title"><i class="fas fa-file-upload me-2"></i> Cargar Soporte de Pago</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="egreso">Número de Egreso*</label>
                                            <input type="number" name="egreso" class="form-control" id="egreso" value="<?php echo $c['egreso']; ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="estado">Actualizar Estado</label>
                                            <select class="form-select" name="estado" id="estado">
                                                <option value="<?php echo $c['estado']; ?>" selected><?php echo $c['estado']; ?> (Actual)</option>
                                                <option value="Pendiente">Pendiente</option>
                                                <option value="En proceso">En proceso</option>
                                                <option value="Pagado">Pagado</option>
                                                <option value="Soporte enviado y pagado">Soporte enviado y pagado</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label>Valor Total a Pagar</label>
                                            <input readonly type="number" name="ValorTotalApagar" id="ValorTotalApagar" class="form-control fw-bold bg-light" value="<?php echo $c['ValorTotalApagar']; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="soportePrepago">Archivo Soporte de Pago*</label>
                                            <input type="file" name="soportePrepago" class="form-control" id="soportePrepago" required>
                                        </div>

                                        <div class="col-md-12">
                                            <label>Soporte Existente</label>
                                            <div class="input-group">
                                                <input readonly type="text" class="form-control form-control-sm" value="<?php echo basename($c['soportePrepago']); ?>">
                                                <a href="<?php echo $c['soportePrepago']; ?>" class="btn btn-outline-secondary btn-sm" target="_blank">Ver Actual</a>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label>Fecha Retención</label>
                                            <input readonly type="datetime-local" name="fecha_Retencion" class="form-control form-control-sm" id="fecha_Retencion" value="<?php echo $c['fecha_Retencion']; ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label>Fecha Carga Soporte</label>
                                            <input readonly type="datetime-local" name="fecha_Soporte" class="form-control form-control-sm" id="fecha_soporte_colombia" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label>Fecha Efectiva Pago*</label>
                                            <input type="datetime-local" name="fecha_pago" class="form-control form-control-sm" id="fecha_pago_colombia" required>
                                        </div>

                                        <div class="col-md-12">
                                            <label>Correo del Asesor</label>
                                            <input name="correoasesor" type="email" class="form-control" value="<?php echo $c['correoasesor']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar y Finalizar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Lógica de tiempo Colombia
        const now = new Date();
        const colombiaOffset = -5;
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const colTime = new Date(utc + (3600000 * colombiaOffset));
        const formattedDate = colTime.toISOString().slice(0, 16);

        document.getElementById("fecha_soporte_colombia").value = formattedDate;
        if (!document.getElementById("fecha_pago_colombia").value) {
            document.getElementById("fecha_pago_colombia").value = formattedDate;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>