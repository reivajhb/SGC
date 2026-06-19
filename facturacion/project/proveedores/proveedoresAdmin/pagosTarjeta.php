<?php
include "../../../config/seguridad.php";
include_once "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
    include "../../../../facturacion/config/boton_volver.php";
} else {
    include "../../../config/sidebar.php";
    include "../../../../facturacion/config/boton_volver.php";
}



$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = '';

if ($id_usuario) {
    $sql = "SELECT nombre FROM tbl_usuarios WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($fila = mysqli_fetch_assoc($result)) {
        $nombre_usuario = $fila['nombre'];
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagos Tarjetas Crédito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        body { background: #f8f9fa; }
        .form-container { max-width: 700px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);}
        .btn-block { width: 100%; }
    </style>
</head>
<body>
<div class="sgc-payment-module">
    
    <style>
        
        .sgc-payment-module {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
            color: #374151;
        }

        .sgc-payment-module .form-card {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f3f4f6;
        }

        .sgc-payment-module .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .sgc-payment-module .form-header h3 {
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            font-size: 1.75rem;
        }

        .sgc-payment-module .form-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .sgc-payment-module label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 8px;
            text-transform: none; 
        }

        .sgc-payment-module .form-control, 
        .sgc-payment-module .form-select {
            height: auto;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            box-shadow: none;
        }

        .sgc-payment-module .form-control:focus, 
        .sgc-payment-module .form-select:focus {
            background-color: #fff;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            outline: none;
        }

        .sgc-payment-module #camposTuristica {
            background-color: #f0fdf4;
            border: 1px solid #b9fbd0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            margin-top: 10px;
        }

        .sgc-payment-module .btn-submit {
            background-color: #10b981;
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 20px;
        }

        .sgc-payment-module .btn-submit:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .sgc-payment-module .section-divider {
            height: 1px;
            background: #f3f4f6;
            margin: 30px 0;
        }


        .sgc-payment-module .form-control[readonly] {
            background-color: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }
    </style>

    <div class="form-card">
        <div class="form-header">
            <h3>Pagos Tarjetas Crédito</h3>
            <p>Complete la información para el registro de la transacción</p>
        </div>

        <form action="cargaFormularioTarjetas.php" method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="tipo_identificacion">Tipo de Identificación</label>
                    <select name="tipo_identificacion" id="tipo_identificacion" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="CC">Cédula</option>
                        <option value="NIT">NIT</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="identificacion_proveedor">Identificación Proveedor</label>
                    <input type="text" name="identificacion_proveedor" id="identificacion_proveedor" class="form-control" placeholder="Número de ID">
                </div>

                <div class="col-md-6">
                    <label for="nombre_proveedor">Nombre Proveedor</label>
                    <input type="text" name="nombre_proveedor" id="nombre_proveedor" class="form-control" placeholder="Razón social">
                </div>
                <div class="col-md-6">
                    <label for="solicitante">Solicitante</label>
                    <input type="text" name="solicitante" id="solicitante" class="form-control" value="Jeison Castro" readonly>
                </div>

                <div class="col-md-6">
                    <label for="tipo_servicio">Tipo de Servicio</label>
                    <select name="tipo_servicio" id="tipo_servicio" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="Tiquete">Tiquete</option>
                        <option value="Alojamiento">Alojamiento</option>
                        <option value="Software">Software</option>
                        <option value="Compra equipos">Compra equipos</option>
                        <option value="Publicidad">Publicidad</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="area">Área</label>
                    <select name="area" id="area" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Turistica">Turística</option>
                    </select>
                </div>
            </div>

            <div id="camposTuristica" style="display: none;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="localizador">Localizador</label>
                        <textarea name="localizador" id="localizador" class="form-control" rows="1" placeholder="Ingrese localizador..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="factura_panamericana">Factura Panamericana</label>
                        <textarea name="factura_panamericana" id="factura_panamericana" class="form-control" rows="1" placeholder="Número de factura..."></textarea>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <div class="row g-4">
                <div class="col-md-4">
                    <label for="tipo_moneda">Tipo de Moneda</label>
                    <select name="tipo_moneda" id="tipo_moneda" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="COP">COP</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="valor">Valor</label>
                    <input type="number" name="valor" id="valor" class="form-control" step="any" min="0" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label for="fee_bancario">Fee Bancario</label>
                    <input type="number" name="fee_bancario" id="fee_bancario" class="form-control" step="any" min="0" placeholder="0.00">
                </div>

                <div class="col-md-12">
                    <label for="link_pagos">Link de pagos</label>
                    <input type="text" name="link_pagos" id="link_pagos" class="form-control" placeholder="https://dominio.com/pago...">
                </div>

                <div class="col-md-6">
                    <label for="factura_proveedor">Factura Proveedor (Adjunto)</label>
                    <input type="file" name="factura_proveedor" id="factura_proveedor" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="1" placeholder="Notas adicionales..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit">Enviar Registro de Pago</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const areaSelect = document.getElementById("area");
            const camposTuristica = document.getElementById("camposTuristica");
            
            function toggleCampos() {
                if (areaSelect.value === "Turistica") {
                    camposTuristica.style.display = "block";
                } else {
                    camposTuristica.style.display = "none";
                }
            }
            
            toggleCampos();
            areaSelect.addEventListener("change", toggleCampos);
        });
    </script>
</div>
</body>
</html>