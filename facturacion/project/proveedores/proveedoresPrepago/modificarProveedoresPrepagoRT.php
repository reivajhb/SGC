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

// Consulta (Limpieza de entrada)
$id_anticipo = mysqli_real_escape_string($conn, $_GET['id_anticipo']);
$sentencia = "SELECT * FROM tbl_anticipos WHERE id_anticipo = '$id_anticipo'";
$ejecutar = mysqli_query($conn, $sentencia);
$c = $ejecutar->fetch_assoc();
?>

<!doctype html>
<html lang="es">
<head>
    <title>Aplicar Retenciones | SGC</title>

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

        /* Botones */
        .btn-apply {
            background-color: #0d6efd;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-apply:hover { background-color: #0b5ed7; transform: translateY(-2px); }

        .download-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .alert-trm {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-header">
                    <h2 class="mb-0 h4">APLICAR RETENCIONES: <span class="fw-light"><?php echo $c['proveedor']; ?></span></h2>
                </div>

                <form action="editarProveedoresPrepagoRT.php" method="post" enctype="multipart/form-data">
                    <input name="id_anticipo"      type="hidden" value="<?php echo $c['id_anticipo']; ?>">
                    <input name="fecha"             type="hidden" value="<?php echo $c['fecha']; ?>">
                    <input name="proveedor"         type="hidden" value="<?php echo htmlspecialchars($c['proveedor']); ?>">
                    <input name="email_proveedor"   type="hidden" value="<?php echo htmlspecialchars($c['email_Proveedor']); ?>">
                    <input name="identificacion"    type="hidden" value="<?php echo $c['identificacion']; ?>">
                    <input name="localizador"       id="localizador" type="hidden" value="<?php echo htmlspecialchars($c['localizador']); ?>">
                    <input name="num_factura"       type="hidden" value="<?php echo htmlspecialchars($c['num_factura']); ?>">
                    <input name="concepto"          id="concepto" type="hidden" value="<?php echo htmlspecialchars($c['concepto']); ?>">
                    <input name="descripcion"       type="hidden" value="<?php echo htmlspecialchars($c['descripcion']); ?>">
                    <input name="moneda"            type="hidden" value="<?php echo htmlspecialchars($c['moneda']); ?>">
                    <input name="valor"             type="hidden" value="<?php echo $c['valor']; ?>">
                    <input name="usuario"           type="hidden" value="<?php echo htmlspecialchars($c['usuario']); ?>">
                    <input name="fecha_ingreso"     type="hidden" value="<?php echo $c['fecha_ingreso']; ?>">
                    <input name="fecha_salida"      type="hidden" value="<?php echo $c['fecha_salida']; ?>">
                    <input name="certificacion"     type="hidden" value="<?php echo htmlspecialchars($c['certificacion']); ?>">
                    <input name="cuentadecobro"     type="hidden" value="<?php echo htmlspecialchars($c['cuentadecobro']); ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-user-tie"></i> Información del Proveedor</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>NIT o Cédula</label>
                                <input readonly class="form-control" value="<?php echo $c['identificacion']; ?>">
                            </div>
                            <div class="col-md-8">
                                <label>Correo Electrónico</label>
                                <input readonly class="form-control" value="<?php echo $c['email_Proveedor']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Soportes Adjuntos</label>
                                <div class="d-flex flex-column">
                                    <a class="download-link" href="<?php echo $c['certificacion']; ?>"><i class="fas fa-file-download"></i> Certificación Bancaria</a>
                                    <a class="download-link" href="<?php echo $c['cuentadecobro']; ?>"><i class="fas fa-file-invoice-dollar"></i> Cuenta de Cobro</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-money-check-alt"></i> Detalles del Anticipo</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Localizador</label>
                                <input readonly class="form-control" value="<?php echo $c['localizador']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>No. de Factura</label>
                                <input readonly class="form-control" value="<?php echo $c['num_factura']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Moneda</label>
                                <input readonly class="form-control" value="<?php echo $c['moneda']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Concepto</label>
                                <input readonly class="form-control" value="<?php echo $c['concepto']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Valor Bruto</label>
                                <input readonly id="valor_base" class="form-control fw-bold text-dark" value="<?php echo $c['valor']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Asesor</label>
                                <input readonly class="form-control" value="<?php echo $c['usuario']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Fecha Entrada Pasajeros</label>
                                <input readonly type="date" class="form-control" value="<?php echo $c['fecha_ingreso']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Fecha Salida Pasajeros</label>
                                <input readonly type="date" class="form-control" value="<?php echo $c['fecha_salida']; ?>">
                            </div>
                            <div class="col-12">
                                <label>Información Adicional</label>
                                <textarea readonly class="form-control" rows="2"><?php echo htmlspecialchars($c['descripcion']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="button" class="btn-apply shadow" data-bs-toggle="modal" data-bs-target="#modalRetenciones">
                            <i class="fas fa-percentage me-2"></i> Configurar Cálculo de Retenciones
                        </button>
                    </div>

                    <div class="modal fade" id="modalRetenciones" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg shadow-lg">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title">Configuración de Retenciones</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    
                                    <div class="alert-trm">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Si la moneda es <strong>USD o EUR</strong>, ingrese la TRM para convertir a pesos antes de aplicar retenciones.
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label>TRM / Cambio del Día</label>
                                            <input type="number" id="vld" class="form-control" placeholder="0.00">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Valor a Convertir (Base)</label>
                                            <input readonly id="dolares" class="form-control" value="<?php echo $c['valor']; ?>">
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="cambio()">
                                                <i class="fas fa-sync"></i> Calcular Equivalente en Pesos
                                            </button>
                                            <p class="mt-2 mb-0 fw-bold">Resultado: <span id="resul" class="text-primary">$0</span></p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label>Tipo de Retención</label>
                                            <select name="tipo" id="tipo" class="form-select" required onchange="updateDescription()"></select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Seleccione Tarifa*</label>
                                            <select name="retencion" id="retencion" class="form-select" required onchange="updateDescription()"></select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="text-danger">¿Aplica Doble Retención? (Reteica adicional)</label>
                                            <select name="dobleretencion" id="dobleretencion" class="form-select">
                                                <option value="Seleccione">-- No aplica --</option>
                                                <option value="8">8*1000 Reteica Cartagena</option>
                                                <option value="6">6*1000 Reteica Cartagena</option>
                                                <option value="13.80">13.80*1000 Bogotá</option>
                                                <option value="9.66">9.66*1000 Bogotá</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Estado del Pago</label>
                                            <select class="form-select" name="estado">
                                                <option value="<?php echo $c['estado']; ?>"><?php echo $c['estado']; ?></option>
                                                <option value="Pendiente">Pendiente</option>
                                                <option value="En proceso">En proceso</option>
                                                <option value="Pagado">Pagado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Total Final a Pagar</label>
                                            <input type="number" step="any" name="ValorTotalApagar" id="ValorTotalApagar" class="form-control fw-bold bg-light" value="<?php echo $c['ValorTotalApagar']; ?>">
                                        </div>
                                        <div class="col-12">
                                            <label>Notas Internas / Descripción de Retenciones</label>
                                            <textarea name="descripcionRT" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <input type="hidden" name="resultRetefuente" id="resultRetefuente">
                                    <input type="hidden" name="valorestaretefuente" id="valorestaretefuente" oninput="updateDescription()">
                                    <input type="hidden" name="resultReteica" id="resultReteica">
                                    <input type="hidden" name="valorestareteica" id="valorestareteica" oninput="updateDescription()">
                                    <input type="hidden" name="valorPagaretefuente" id="valorPagaretefuente">
                                    <input type="hidden" name="valorPagareteica" id="valorPagareteica">
                                    <input type="hidden" name="sumretenciones" id="sumretenciones">
                                    <input type="hidden" name="fecha_Retencion" id="fecha_hora_colombia">

                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary px-5 fw-bold">Actualizar y Aplicar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // TRM Conversion
        function cambio() {
            var pe = parseFloat(document.getElementById('vld').value) || 0;
            var dol = parseFloat(document.getElementById('dolares').value) || 0;
            var re = pe * dol;
            document.getElementById('resul').innerHTML = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(re);
        }

        // Auto-fecha Colombia
        document.addEventListener('DOMContentLoaded', function() {
            var ahora = new Date();
            ahora.setHours(ahora.getHours() - 5);
            document.getElementById("fecha_hora_colombia").value = ahora.toISOString().slice(0, 16);
        });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // main.js espera un input con id="valor" para calcular retenciones,
        // pero este formulario usa id="valor_base". Creamos el alias antes de cargar main.js.
        document.addEventListener('DOMContentLoaded', function () {
            var alias = document.createElement('input');
            alias.type = 'hidden';
            alias.id = 'valor';
            alias.value = document.getElementById('valor_base').value;
            document.body.appendChild(alias);
        });
    </script>
    <script src="/facturacion/js/main.js"></script>
</body>
</html>