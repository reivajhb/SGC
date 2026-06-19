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
function ConsultarProveedor($id_proveedor, $conn)
{
    $sentencia = "SELECT * FROM tbl_proveedores WHERE id_proveedor = ?";
    $stmt = mysqli_prepare($conn, $sentencia);
    mysqli_stmt_bind_param($stmt, "i", $id_proveedor);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $mostrar = mysqli_fetch_assoc($resultado);

    if (!$mostrar) {
        echo '<script>alert("No se encontró ningún proveedor."); window.location = "consultaProveedores.php";</script>';
        exit;
    }
    return $mostrar;
}

if (isset($_GET['id_proveedor'])) {
    $consulta = ConsultarProveedor($_GET['id_proveedor'], $conn);
} else {
    echo '<script>alert("Debe proporcionar un ID de proveedor para modificar."); window.location = "consultaProveedores.php";</script>';
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGC | Modificar Proveedor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; padding-top: 20px; }
        
        .main-container { padding: 20px 0; }

        /* Cabecera Estilo ERP unificada */
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

        .form-control:focus, .form-select:focus {
            border-color: #1a3a5c;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }

        .form-control[readonly] { background-color: #f8f9fa; color: #6c757d; border-color: #eee; }

        /* Botón de acción principal */
        .btn-submit {
            background-color: #10b981;
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
        .btn-submit:hover { 
            background-color: #059669; 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); 
        }

        /* Iconografía de moneda para inputs financieros */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dce1e5;
            color: #6c757d;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-header">
                    <h2 class="mb-0 h4">CONFIGURACIÓN DE PROVEEDOR: <?php echo htmlspecialchars($consulta['nombre']) ?></h2>
                    <p class="mb-0 fw-light small opacity-75"><?php echo htmlspecialchars($consulta['nombre']) ?></p>
                </div>

                <form action="update/editarProveedor.php" method="post" enctype="multipart/form-data">
                    <input name="id_proveedor" type="hidden" value="<?php echo htmlspecialchars($consulta['id_proveedor']) ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-id-card"></i> Datos de Identificación</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nit_identificacion">NIT / Identificación</label>
                                <input readonly name="nit_identificacion" type="text" class="form-control" id="nit_identificacion"
                                       value="<?php echo htmlspecialchars($consulta['nit_identificacion']) ?>">
                            </div>
                            <div class="col-md-8">
                                <label for="nombre">Razón Social / Nombre Completo</label>
                                <input name="nombre" type="text" class="form-control" id="nombre"
                                       value="<?php echo htmlspecialchars($consulta['nombre']) ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label for="tipo_proveedor">Tipo de Proveedor</label>
                                <select class="form-select" name="tipo_proveedor" id="tipo_proveedor">
                                    <option value="<?php echo htmlspecialchars($consulta['tipo_proveedor']) ?>" selected>
                                        <?php echo htmlspecialchars($consulta['tipo_proveedor']) ?> (Actual)
                                    </option>
                                    <option value="Turístico">Turístico</option>
                                    <option value="Anticipos">Anticipos</option>
                                    <option value="Administrativo">Administrativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-envelope-open-text"></i> Canales de Comunicación</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email_contabilidad">Correo Contabilidad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                    <input name="email_contabilidad" type="email" class="form-control" id="email_contabilidad"
                                           value="<?php echo htmlspecialchars($consulta['email_contabilidad']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email_cartera">Correo Cartera</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wallet"></i></span>
                                    <input name="email_cartera" type="email" class="form-control" id="email_cartera"
                                           value="<?php echo htmlspecialchars($consulta['email_cartera']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-hand-holding-usd"></i> Estado Financiero</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="limite_credito">Límite de Crédito Permitido</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input name="limite_credito" type="number" step="any" class="form-control fw-bold" id="limite_credito"
                                           value="<?php echo htmlspecialchars($consulta['limite_credito']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="saldo_deuda">Saldo de Deuda Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input name="saldo_deuda" type="number" step="any" class="form-control fw-bold text-danger" id="saldo_deuda"
                                           value="<?php echo htmlspecialchars($consulta['saldo_deuda']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <button type="submit" class="btn-submit shadow">
                            <i class="fas fa-save me-2"></i> Actualizar Ficha de Proveedor
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>