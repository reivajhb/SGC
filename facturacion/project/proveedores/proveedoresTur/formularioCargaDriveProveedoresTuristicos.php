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

function consultarProveedor($nit, $conn) {
    $sentencia = "SELECT id_proveedor, nit_identificacion, nombre, email_contabilidad, email_cartera, tipo_proveedor
                  FROM tbl_proveedores
                  WHERE nit_identificacion = ?";

    $stmt = mysqli_prepare($conn, $sentencia);
    mysqli_stmt_bind_param($stmt, "s", $nit);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $mostrarProveedor = mysqli_fetch_assoc($resultado);

    if (!$mostrarProveedor) {
        echo '<script>
                alert("No se encontró ningún proveedor con ese NIT.");
                window.location = "buscarProveedor.php";
              </script>';
        exit;
    }

    return $mostrarProveedor;
}

if (isset($_GET['nit'])) {
    $consultaProveedor = consultarProveedor($_GET['nit'], $conn);
} else {
    echo '<script>
            alert("Debe proporcionar un NIT para buscar.");
            window.location = "buscarProveedor.php";
          </script>';
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Relación Pagos Proveedores</title>
    
    <style>
        /* Encapsulamos todo en .modulo-pagos para no romper el CSS global del ERP */
        .modulo-pagos {
            margin-top: 2rem;
            margin-bottom: 3rem;
        }

        .modulo-pagos .custom-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .modulo-pagos .custom-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            padding: 2rem;
            border-bottom: none;
            color: white;
        }

        .modulo-pagos .custom-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .modulo-pagos .badge-tipo {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
            backdrop-filter: blur(5px);
        }

        .modulo-pagos .custom-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .modulo-pagos .custom-input {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            font-size: 1rem;
            transition: all 0.2s ease-in-out;
        }

        .modulo-pagos .custom-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .modulo-pagos .custom-input[readonly] {
            background-color: #f1f3f5;
            color: #6c757d;
        }

        .modulo-pagos .custom-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #adb5bd;
            margin: 2rem 0 1.5rem 0;
            display: flex;
            align-items: center;
        }

        .modulo-pagos .custom-section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
            margin-left: 15px;
        }

        .modulo-pagos .custom-btn-submit {
            padding: 1rem;
            font-weight: 700;
            border-radius: 10px;
            background: #0d6efd;
            border: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .modulo-pagos .custom-btn-submit:hover {
            background: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .modulo-pagos .input-group-text-custom {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-weight: 600;
        }

        .modulo-pagos .custom-footer-link {
            color: #adb5bd;
            transition: color 0.2s;
            font-weight: 500;
        }

        .modulo-pagos .custom-footer-link:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>
<div class="modulo-pagos container">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="custom-card card">
                <div class="custom-header card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Relación de Pagos</h2>
                            <p class="mb-0 opacity-75 fs-5">
                                <?php echo htmlspecialchars($consultaProveedor['nombre']); ?>
                            </p>
                        </div>
                        <span class="badge-tipo">
                            <?php echo htmlspecialchars($consultaProveedor['tipo_proveedor']); ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="cargaDriveProveedoresTuristicos.php" method="post" enctype="multipart/form-data">
                        <input name="id_proveedor" type="hidden" value="<?= htmlspecialchars($consultaProveedor['id_proveedor']); ?>">
                        <input name="id_usuario_tufo" type="hidden" value="<?= htmlspecialchars($_SESSION['usuario']); ?>" required>
                        <input name="email_contabilidad" type="hidden" value="<?= htmlspecialchars($consultaProveedor['email_contabilidad']); ?>" required>
                        <input name="email_cartera" type="hidden" value="<?= htmlspecialchars($consultaProveedor['email_cartera']); ?>">

                        <div class="custom-section-title">Información del Proveedor</div>
                        
                        <div class="row g-4 mb-2">
                            <div class="col-md-5">
                                <label for="nit" class="custom-label form-label">NIT / Identificación</label>
                                <input readonly name="nit" type="number" class="custom-input form-control" id="nit"
                                       value="<?= htmlspecialchars($consultaProveedor['nit_identificacion']); ?>" required>
                            </div>

                            <div class="col-md-7">
                                <label for="nombreProveedor" class="custom-label form-label">Razón Social</label>
                                <input readonly name="proveedor" type="text" class="custom-input form-control" id="nombreProveedor"
                                       value="<?= htmlspecialchars($consultaProveedor['nombre']); ?>" required>
                            </div>
                        </div>

                        <div class="custom-section-title">Detalles de la Transacción</div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="valorPagar" class="custom-label form-label">Valor a pagar (COP)*</label>
                                <div class="input-group">
                                    <span class="input-group-text-custom input-group-text" style=" font-size: 0.7rem;">$</span>
                                    <input name="cop" type="number" class="custom-input form-control" id="valorPagar"
                                           placeholder="0.00" required style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="fecha" class="custom-label form-label">Fecha de registro*</label>
                                <input name="fecha" type="date" class="custom-input form-control" id="fecha" required>
                            </div>

                            <div class="col-12">
                                <label for="novedad" class="custom-label form-label">Novedad / Descripción*</label>
                                <textarea name="novedad" class="custom-input form-control" id="novedad" rows="3"
                                          placeholder="Describa brevemente la novedad del pago" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="estado" class="custom-label form-label">Estado del Pago*</label>
                                <select name="estado" class="custom-input form-select" id="estado" required>
                                    <option value="" disabled selected>Seleccione un estado...</option>
                                    <?php
                                    $consulta = "SELECT estado FROM tbl_estado";
                                    $ejecutar = mysqli_query($conn, $consulta);
                                    while ($opciones = mysqli_fetch_assoc($ejecutar)):
                                    ?>
                                        <option value="<?= htmlspecialchars($opciones['estado']) ?>">
                                            <?= htmlspecialchars($opciones['estado']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="file" class="custom-label form-label">Soportes (PDF)*</label>
                                <input style="padding: 4px;" type="file" class="custom-input form-control" name="facturaProveedorTuristico" id="file" accept=".pdf" required>
                                <div class="form-text mt-2" style="font-size: 0.8rem;">Solo se permiten archivos en formato PDF legibles.</div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <button type="submit" class="custom-btn-submit btn btn-primary w-100 shadow">
                                    Procesar y Cargar Pago
                                </button>
                                <div class="mt-4">
                                    <a href="buscarProveedor.php" class="custom-footer-link text-decoration-none small">
                                        ← Regresar a la búsqueda de proveedores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>