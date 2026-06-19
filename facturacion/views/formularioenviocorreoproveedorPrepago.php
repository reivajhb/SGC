<?php
include "../config/seguridad.php";
include "../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../config/sidebar3.php";
    include "../config/boton_volver.php";
} else {
    include "../config/sidebar.php";
    include "../config/boton_volver.php";
}

$consulta = ConsultarAnticipo($_GET['id_anticipo']);

function ConsultarAnticipo($id_anticipo)
{
    include '../config/conexion.php';
    $sentencia = "SELECT * FROM tbl_anticipos WHERE id_anticipo = '" . $id_anticipo . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrar = $ejecutar->fetch_assoc();

    return [
        $mostrar['id_anticipo'],
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
        $mostrar['descripcionRT'],
    ];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enviar Email | Gestión de Anticipos</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (iconos) -->
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
            border-radius: 15px;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
            margin-top: 2rem;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        /* Ficha resumen del anticipo */
        .summary-box {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }

        .table-custom {
            font-size: 0.9rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 1rem;
        }

        .doc-link img {
            transition: transform 0.2s;
        }

        .doc-link:hover img {
            transform: scale(1.15);
        }

        .btn-send {
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 12px;
            background: #0d6efd;
            border: none;
            transition: all 0.3s;
        }

        .btn-send:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .icon-bg {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
    </style>
</head>

<body>
<div class="container py-4">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="main-card">
            <!-- Cabecera Estilizada -->
            <div class="card-header-custom">
                <div class="icon-bg">
                    <i class="fa-solid fa-paper-plane fa-lg"></i>
                </div>
                <h2 class="h4 mb-1">Notificación de Pago de Anticipo</h2>
                <p class="mb-0 opacity-75">Proveedor: <?php echo htmlspecialchars($consulta[2]); ?></p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form action="../project/proveedores/proveedoresPrepago/enviarcorreoProveedoresPrepago.php" method="post" enctype="multipart/form-data">

                    <!-- ID Oculto -->
                    <input name="id_anticipo" type="hidden" value="<?php echo htmlspecialchars($consulta[0]); ?>">

                    <!-- Resumen del Anticipo -->
                    <div class="summary-box">
                        <label class="form-label mb-3 text-primary">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Detalle del Anticipo a Notificar
                        </label>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 table-custom">
                                <thead>
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                        <th>Valor Pagado</th>
                                        <th class="text-center">Soporte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($consulta[2]); ?></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($consulta[19]); ?></td>
                                        <td><?php echo htmlspecialchars($consulta[1]); ?></td>
                                        <td class="text-success fw-bold">
                                            $<?php
                                                $valorApagar = (float)($consulta[17] ?? 0);
                                                echo number_format($valorApagar, 0, ",", ".");
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="doc-link" href="<?php echo htmlspecialchars($consulta[18]); ?>" target="_blank">
                                                <img width="35" height="35" src="../../img/factura.png" alt="Soporte" title="Ver Soporte">
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Configuración del Correo -->
                    <div class="row g-4">
                        <?php 
                        $emailProveedor = trim($consulta[4] ?? '');
                        $emailVacio = empty($emailProveedor);
                        ?>
                        
                        <?php if ($emailVacio): ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <strong>Atención:</strong> Este proveedor no tiene correo electrónico registrado. Por favor, ingrese un correo válido antes de enviar.
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Electrónico Contabilidad <?php if($emailVacio) echo '<span class="text-danger">*</span>'; ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input name="correo" type="email" class="form-control <?php if($emailVacio) echo 'border-danger'; ?>" id="correo"
                                       value="<?php echo htmlspecialchars($emailProveedor); ?>" 
                                       placeholder="ejemplo@correo.com" required>
                            </div>
                            <small class="text-muted">Ingrese el correo donde se enviará la notificación</small>
                        </div>

                        <div class="col-md-6">
                            <label for="asunto" class="form-label">Asunto del Mensaje</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-pen-nib text-muted"></i></span>
                                <input name="asunto" type="text" class="form-control" id="asunto" 
                                       placeholder="Ej: Comprobante de Anticipo - Panamericana Viajes" required>
                            </div>
                        </div>

                        <!-- Campos ocultos requeridos por el script -->
                        <input name="proveedor" type="hidden" value="<?php echo htmlspecialchars($consulta[2]); ?>">
                        <input name="descripcionRT" type="hidden" value="<?php echo htmlspecialchars($consulta[19]); ?>">
                        <input name="fecha" type="hidden" value="<?php echo htmlspecialchars($consulta[1]); ?>">
                        <input name="ValorTotalApagar" type="hidden" value="<?php echo htmlspecialchars($consulta[17]); ?>">
                        <input name="soportePrepago" type="hidden" value="<?php echo htmlspecialchars($consulta[18]); ?>">

                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary btn-send w-100 shadow-sm text-white">
                                <i class="fa-solid fa-paper-plane me-2"></i> Enviar Notificación por Correo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3 border-0">
                <small class="text-muted"><i class="fa-solid fa-circle-exclamation me-1"></i> Por favor verifique los datos antes de proceder con el envío.</small>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validación adicional del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const correo = document.getElementById('correo').value.trim();
    const asunto = document.getElementById('asunto').value.trim();
    
    if (!correo) {
        e.preventDefault();
        alert('Por favor, ingrese un correo electrónico válido.');
        document.getElementById('correo').focus();
        return false;
    }
    
    // Validación de formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) {
        e.preventDefault();
        alert('Por favor, ingrese un correo electrónico con formato válido.');
        document.getElementById('correo').focus();
        return false;
    }
    
    if (!asunto) {
        e.preventDefault();
        alert('Por favor, ingrese el asunto del correo.');
        document.getElementById('asunto').focus();
        return false;
    }
    
    return true;
});
</script>
</body>
</html>