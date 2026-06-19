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
                window.location = "buscarProveedorAdministrativo.php";
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
            window.location = "buscarProveedorAdministrativo.php";
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
        /* Encapsulamiento para proteger el diseño global del ERP */
        .modulo-pagos {
            margin-top: 2rem;
            margin-bottom: 3rem;
        }

        .modulo-pagos .custom-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }

        .modulo-pagos .custom-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            padding: 2rem;
            border-bottom: none;
            color: white;
        }

        .modulo-pagos .custom-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modulo-pagos .badge-tipo {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modulo-pagos .custom-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .modulo-pagos .custom-input {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
        }

        .modulo-pagos .custom-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .modulo-pagos .custom-input[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .modulo-pagos .custom-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #adb5bd;
            margin: 2rem 0 1rem 0;
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
            padding: 0.9rem;
            font-weight: 700;
            border-radius: 10px;
            background: #0d6efd;
            border: none;
            transition: all 0.3s ease;
        }

        .modulo-pagos .custom-btn-submit:hover {
            background: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2);
        }

        .modulo-pagos .form-text {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
<div class="modulo-pagos container">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-11">
            <div class="custom-card card shadow-sm">
                
                <div class="custom-header card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Relación Pagos Administrativos</h2>
                        <p class="mb-0 opacity-75">Carga de soportes para gestión administrativa</p>
                    </div>
                    <span class="badge-tipo">
                        <?= htmlspecialchars($consultaProveedor['tipo_proveedor']); ?>
                    </span>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="cargaDriveAdministrativos.php" method="post" enctype="multipart/form-data">
                        
                        <input name="id_proveedor" type="hidden" value="<?= htmlspecialchars($consultaProveedor['id_proveedor']); ?>">
                        <input name="id_usuario_tufo" type="hidden" value="<?= htmlspecialchars($_SESSION['usuario']); ?>" required>
                        <input name="email_cartera" type="hidden" value="<?= htmlspecialchars($consultaProveedor['email_cartera']); ?>">

                        <div class="custom-section-title">Datos Maestros</div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="nit" class="custom-label form-label">NIT / Identificación*</label>
                                <input readonly name="nit" type="number" class="custom-input form-control" id="nit"
                                       value="<?= htmlspecialchars($consultaProveedor['nit_identificacion']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email_contabilidad" class="custom-label form-label">Correo Contabilidad*</label>
                                <input readonly name="email_contabilidad" type="email" class="custom-input form-control" id="email_contabilidad"
                                       value="<?= htmlspecialchars($consultaProveedor['email_contabilidad']); ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="locacion" class="custom-label form-label">Razón Social del Proveedor*</label>
                                <input readonly name="locacion" type="text" class="custom-input form-control" id="locacion"
                                       value="<?= htmlspecialchars($consultaProveedor['nombre']); ?>" required>
                            </div>
                        </div>

                        <div class="custom-section-title">Información del Pago</div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="valor" class="custom-label form-label">Valor a Pagar (COP)*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="border-radius: 10px 0 0 10px; padding:5px; font-size: 13px;">$</span>
                                    <input name="valor" type="number" class="custom-input form-control" id="valor"
                                           placeholder="Ej: 500000" required style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="fecha" class="custom-label form-label">Fecha de Registro*</label>
                                <input name="fecha" type="date" class="custom-input form-control" id="fecha" required>
                            </div>

                            <div class="col-12">
                                <label for="novedad" class="custom-label form-label">Novedad / Observaciones*</label>
                                <input name="novedad" type="text" class="custom-input form-control" id="novedad"
                                       placeholder="Indique brevemente el concepto del pago" required>
                            </div>

                            <div class="col-md-6">
                                <label for="estado" class="custom-label form-label">Estado de la Solicitud*</label>
                                <select name="estado" class="custom-input form-select" id="estado" required>
                                    <option value="" disabled selected>Seleccione estado...</option>
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
                                <label for="file" class="custom-label form-label">Soporte PDF*</label>
                                <input style="padding: 4px;" type="file" class="custom-input form-control" name="facturaPagosAdministrativos" id="file" accept=".pdf" required>
                                <div class="form-text">Cargue el documento equivalente o factura en PDF.</div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12">
                                <button type="submit" class="custom-btn-submit btn btn-primary w-100">
                                    Finalizar y Cargar Pago
                                </button>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="buscarProveedorAdministrativo.php" class="text-decoration-none text-muted small">
                                ← Regresar a búsqueda administrativa
                            </a>
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