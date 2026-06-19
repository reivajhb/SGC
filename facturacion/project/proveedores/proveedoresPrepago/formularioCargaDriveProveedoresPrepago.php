<?php
include "../../../config/seguridad.php";
include_once "../../../config/conexion.php";

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

function Consultarproveedor($identificacion, $conn)
{
    $sentencia = "SELECT id_proveedor, nit_identificacion, nombre, email_contabilidad FROM tbl_proveedores WHERE nit_identificacion = ?";

    $stmt = mysqli_prepare($conn, $sentencia);
    mysqli_stmt_bind_param($stmt, "s", $identificacion);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $mostrarProveedor = mysqli_fetch_assoc($resultado);

    if (!$mostrarProveedor) {
        echo '<script>
                alert("No se encontró ningún proveedor con esa identificación.");
                window.location = "buscarProveedorPrepago.php";
            </script>';
        exit;
    }

    return $mostrarProveedor;
}

if (isset($_GET['identificacion'])) {
    $consultaProveedor = Consultarproveedor($_GET['identificacion'], $conn);
} else {
    echo '<script>
            alert("Debe proporcionar una identificación para buscar.");
            window.location = "buscarProveedorPrepago.php";
        </script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ESTILOS ENCAPSULADOS ESTILO MINIMALISTA CORPORATIVO */
        .sgc-payment-module {
            font-family: 'Inter', sans-serif;
            padding: 20px;
            color: #374151;
        }

        .sgc-payment-module .form-card {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
        }

        .sgc-payment-module .form-header {
            text-align: center;
            margin-bottom: 35px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 20px;
        }

        .sgc-payment-module .form-header h2 {
            font-weight: 700;
            color: #111827;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .sgc-payment-module .form-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .sgc-payment-module h5 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #374151; 
            margin-top: 25px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sgc-payment-module h5::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #f3f4f6;
            margin-left: 15px;
        }

        .sgc-payment-module label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .sgc-payment-module .form-control, 
        .sgc-payment-module .form-select {
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .sgc-payment-module .form-control:focus {
            background-color: #fff;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            outline: none;
        }

        /* Campos de solo lectura */
        .sgc-payment-module .form-control[readonly] {
            background-color: #f3f4f6;
            border-color: #e5e7eb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .sgc-payment-module .form-control[type="file"] {
            padding: 7px 12px; /* Reducimos el padding superior para centrar el botón interno */
            line-height: 1.5;
            display: flex;
            align-items: center;
        }
        .sgc-payment-module .btn-submit {
            background-color: #13aa6e; /* Verde bosque profesional */
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s ease;
        }

        ..sgc-payment-module .btn-submit:hover {
            background-color: #145d40; /* Sombra del verde mate */
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(26, 122, 84, 0.2);
        }
        /* Estilo para los inputs de archivo */
        .sgc-payment-module .form-control-file {
            background: #fff;
            border: 1px dashed #d1d5db;
            padding: 10px;
            width: 100%;
            border-radius: 10px;
        }
    </style>

    <title>Relación pagos Proveedores Prepago</title>
</head>
<body class="bg-light">
<div class="sgc-payment-module">
    <div class="form-card">
        <div class="form-header">
            <h2>Anticipo Proveedor: <?= htmlspecialchars($consultaProveedor['nombre']); ?></h2>
            <p>Por favor diligencie todos los campos para procesar el anticipo.</p>
        </div>

        <form action="cargaDriveProveedoresPrepago.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars($consultaProveedor['id_proveedor']); ?>">
            <input type="hidden" name="email_proveedor" value="<?= htmlspecialchars($consultaProveedor['email_contabilidad']); ?>">
            <input type="hidden" name="correoasesor" value="<?= htmlspecialchars(isset($_SESSION['correo']) ? $_SESSION['correo'] : ''); ?>">
            <input type="hidden" name="usuario" value="<?= htmlspecialchars(isset($_SESSION['usuario']) ? $_SESSION['usuario'] : ''); ?>">
            <input type="hidden" name="estado" value="Pendiente">

            <h5>1. Información del Proveedor</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Fecha de registro</label>
                    <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Nit o Cédula</label>
                    <input readonly name="identificacion" type="number" class="form-control" value="<?= htmlspecialchars($consultaProveedor['nit_identificacion']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Nombre Proveedor</label>
                    <input readonly name="proveedor" type="text" class="form-control" value="<?= htmlspecialchars($consultaProveedor['nombre']); ?>" required>
                </div>
            </div>

            <h5>2. Detalles del Pago</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Localizador*</label>
                    <input name="localizador" type="text" class="form-control" placeholder="Ej: ABC123" required>
                </div>
                <div class="col-md-4">
                    <label>No. de Factura*</label>
                    <input name="num_factura" type="text" class="form-control" placeholder="00000" required>
                </div>
                <div class="col-md-4">
                    <label>Moneda*</label>
                    <select class="form-select" name="moneda" required>
                        <option value="COP">COP - Pesos Colombianos</option>
                        <option value="USD">USD - Dólares</option>
                        <option value="EUR">EUR - Euros</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Valor a transferir*</label>
                    <input name="valor" type="number" class="form-control" placeholder="0.00" required step="any" min="0">
                </div>
                <div class="col-md-8">
                    <label>Concepto*</label>
                    <input name="concepto" type="text" class="form-control" placeholder="Ej: Pago anticipado reserva hotel" required>
                </div>
                <div class="col-12">
                    <label>Información Adicional / Descripción*</label>
                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalles importantes del pago..." required></textarea>
                </div>
            </div>

            <h5>3. Fechas del Servicio</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Fecha entrada pasajeros*</label>
                    <input name="fecha_ingreso" type="date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Fecha salida pasajeros*</label>
                    <input name="fecha_salida" type="date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Fecha límite de pago*</label>
                    <input name="fecha_lmtpago" type="date" class="form-control" required>
                </div>
            </div>

            <h5>4. Documentos Anexos</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Certificación Bancaria*</label>
                    <input type="file" name="certificacion" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Cuenta de Cobro*</label>
                    <input type="file" name="cuentadecobro" class="form-control" required>
                </div>
            </div>

            <button id="guardarBtn" type="submit" class="btn-submit">Enviar Solicitud de Anticipo</button>
        </form>
    </div>
</div>

<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-success mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mb-0 fw-bold">Procesando y guardando información...</p>
                <small class="text-muted">Por favor no cierre la ventana.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $("#guardarBtn").click(function (event) {
            event.preventDefault();
            let formularioValido = true;
            
            // Validación visual estilo minimalista
            $("form [required]").each(function () {
                if ($(this).val().trim() === "") {
                    $(this).addClass("is-invalid");
                    formularioValido = false;
                } else {
                    $(this).removeClass("is-invalid");
                }
            });

            if (!formularioValido) {
                alert("Por favor, completa todos los campos obligatorios resaltados.");
                return;
            }

            // Mostrar modal de carga
            var myModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            myModal.show();
            
            $("form").submit();
        });

        // Configuración de fecha Colombia
        var fechaHoraActualDispositivo = new Date();
        var diferenciaHorariaColombia = -5;
        // Ajuste manual de horas para el input datetime-local
        fechaHoraActualDispositivo.setHours(fechaHoraActualDispositivo.getHours() + diferenciaHorariaColombia);
        var fechaHoraColombia = new Date(new Date().getTime() - (5 * 60 * 60 * 1000)).toISOString().slice(0, 16);
        $("#fecha_hora_colombia").val(fechaHoraColombia);
    });
</script>
</body>
</html>