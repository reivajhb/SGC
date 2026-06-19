<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$consultaProveedor = Consultarproveedor($_GET['identificacion']);

function Consultarproveedor($identificacion)
{
    include "../../../config/conexion.php";
    $sentencia = "SELECT * FROM tbl_proveedores_inter WHERE identificacion = '" . $identificacion . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrarProveedor = $ejecutar->fetch_assoc();

    if (!$mostrarProveedor) {
        echo '<script>
              alert("No se encontró ningún proveedor");
              window.location = "buscarProveedorinter.php";
              </script>';
        exit;
    }

    return [
        $mostrarProveedor['id_proveedor_int'],
        $mostrarProveedor['identificacion'],
        $mostrarProveedor['nombre'],
        $mostrarProveedor['email_proveedor'],
    ];
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

    <title>Pago a proveedor internacional</title>
</head>

<body>
<header>
    <?php include "../../../config/sidebar.php"; ?>
</header>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0 text-center">
                Pago a proveedor internacional: <?php echo htmlspecialchars($consultaProveedor[2]); ?>
            </h2>
        </div>

        <div class="card-body">
            <div class="mx-auto" style="max-width: 900px;">
                <form id="formPagoInter" action="cargaDriveProveedoresInter.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fecha_hora_colombia" class="form-label">Fecha de registro*</label>
                            <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="identificacion" class="form-label">Nit o Cédula*</label>
                            <input readonly name="identificacion" type="number" class="form-control" id="identificacion"
                                   value="<?php echo htmlspecialchars($consultaProveedor[1]); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="proveedor" class="form-label">Nombre Proveedor Ocasional*</label>
                            <input readonly name="proveedor" type="text" class="form-control" id="proveedor"
                                   value="<?php echo htmlspecialchars($consultaProveedor[2]); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email_proveedor" class="form-label">Correo Proveedor*</label>
                            <input readonly name="email_proveedor" type="email" class="form-control" id="email_proveedor"
                                   value="<?php echo htmlspecialchars($consultaProveedor[3]); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="localizador" class="form-label">Localizador*</label>
                            <input name="localizador" type="text" class="form-control" id="localizador"
                                   placeholder="Ingrese el localizador" required>
                        </div>

                        <div class="col-md-6">
                            <label for="num_factura" class="form-label">No. de Factura</label>
                            <input name="num_factura" type="text" class="form-control" id="num_factura"
                                   placeholder="Ingrese el número de factura">
                        </div>

                        <div class="col-md-6">
                            <label for="concepto" class="form-label">Concepto*</label>
                            <input name="concepto" type="text" class="form-control" id="concepto"
                                   placeholder="Ingrese el concepto" required>
                        </div>

                        <div class="col-md-6">
                            <label for="descripcion" class="form-label">Información Adicional*</label>
                            <input name="descripcion" type="text" class="form-control" id="descripcion"
                                   placeholder="Ingrese alguna descripción" required>
                        </div>

                        <div class="col-md-6">
                            <label for="moneda" class="form-label">Tipo de Moneda*</label>
                            <select class="form-select" name="moneda" id="moneda" required>
                                <option value="COP">COP</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="valor" class="form-label">Valor del cheque o Transferencia*</label>
                            <input name="valor" type="number" class="form-control" id="valor"
                                   placeholder="Valor del cheque o transferencia" required>
                        </div>

                        <div class="col-md-6">
                            <label for="usuario" class="form-label">Asesor</label>
                            <input readonly name="usuario" type="text" class="form-control" id="usuario"
                                   value="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_ingreso" class="form-label">Fecha de entrada de los pasajeros*</label>
                            <input name="fecha_ingreso" type="date" class="form-control" id="fecha_ingreso" required>
                        </div>

                        <div class="col-md-6">
                            <label for="certificacion" class="form-label">Anexar certificación bancaria*</label>
                            <input type="file" class="form-control" name="certificacion" id="certificacion" required>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_salida" class="form-label">Fecha de salida de los pasajeros*</label>
                            <input name="fecha_salida" type="date" class="form-control" id="fecha_salida" required>
                        </div>

                        <input name="estado" type="hidden" value="Pendiente">

                        <div class="col-12">
                            <label for="cuentadecobro" class="form-label">Anexar cuenta de cobro*</label>
                            <input type="file" class="form-control" name="cuentadecobro" id="cuentadecobro" required>
                        </div>

                        <div class="col-12">
                            <button id="guardarBtn" type="submit" class="btn btn-primary w-100">
                                Enviar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Modal de carga (Bootstrap 5) -->
                <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel"
                     aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-4">
                                <div class="spinner-border" role="status" aria-hidden="true"></div>
                                <p class="mt-3 mb-0">Guardando...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal -->

            </div>
        </div>
    </div>
</div>

<script>
    // Hora Colombia (UTC-5) robusta
    const now = new Date();
    const colombiaOffsetHours = -5;
    const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
    const colombiaDate = new Date(utc + (colombiaOffsetHours * 3600000));
    const fechaHoraColombia = colombiaDate.toISOString().slice(0, 16);

    const inputFecha = document.getElementById("fecha_hora_colombia");
    if (inputFecha) inputFecha.value = fechaHoraColombia;

    // Mostrar modal al enviar el formulario
    const form = document.getElementById('formPagoInter');
    const loadingModalEl = document.getElementById('loadingModal');

    if (form && loadingModalEl) {
        const loadingModal = new bootstrap.Modal(loadingModalEl);

        form.addEventListener('submit', function () {
            loadingModal.show();
        });
    }
</script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
