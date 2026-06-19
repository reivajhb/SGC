<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Los includes del sidebar suelen contener el <nav>. 
// Al estar fuera del div "container-formulario", ocuparán el 100% del ancho.
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
    include "../../../config/boton_volver.php";
} else {
    include "../../../config/sidebar.php";
    include "../../../config/boton_volver.php";
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
    <title>Registro de nuevos proveedores</title>
    <style>
        /* Estilos encapsulados para esta página mediante el ID #registro-proveedores */
        #cuerpo-registro {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        #container-formulario {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 15px;
        }

        /* Encabezado Corporativo */
        .header-proveedor {
            background: linear-gradient(135deg, #1a2a6c, #2a4858);
            color: white;
            padding: 2.5rem 1rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: -50px; /* Efecto de solapamiento */
            position: relative;
            z-index: 2;
        }

        /* Tarjeta del Formulario */
        .card-proveedor {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            padding-top: 60px; /* Espacio para el solapamiento del header */
            z-index: 1;
        }

        .label-custom {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .input-custom {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .input-custom:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.15);
        }

        /* Botón Verde Vivo */
        .btn-registrar-prov {
            background-color: #27ae60; /* Verde más vivo */
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-registrar-prov:hover {
            background-color: #2ecc71;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
            color: white;
        }
    </style>
</head>

<body id="cuerpo-registro">

    <div style="margin-top: 0;" id="container-formulario">
        
        <div class="header-proveedor text-center">
            <h2 class="fw-bold mb-1">Registro de Proveedores</h2>
            <p class="mb-0 text-white-50">Gestión Administrativa de Terceros</p>
        </div>

        <div style="margin-top: 100px; padding-top: 0;" class="card card-proveedor">
            <div class="card-body p-4 p-md-5">
                <form action="cargaProveedopdvAdmin.php" method="post" enctype="multipart/form-data">

                    <input name="tipo_proveedor" type="hidden" value="Administrativo">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">NIT / Identificación*</label>
                            <input name="nit_identificacion" type="number" class="form-control input-custom"
                                placeholder="Ej: 900123456" required>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Nombre o Razón Social*</label>
                            <input name="nombre" type="text" class="form-control input-custom"
                                placeholder="Nombre comercial" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="label-custom">Correo Electrónico Contabilidad*</label>
                        <input name="email_contabilidad" type="email" class="form-control input-custom"
                            placeholder="contabilidad@empresa.com" required>
                    </div>

                    <div class="mb-5">
                        <label class="label-custom">Correo Electrónico Cartera*</label>
                        <input name="email_cartera" type="email" class="form-control input-custom"
                            placeholder="cartera@empresa.com" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn-registrar-prov">
                            Registrar Proveedor
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <p class="text-center mt-4 text-muted small">
            SGC ERP - Sistema de Gestión de Proveedores Administrativos
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>