<?php
include "../seguridad_proveedores.php";
include "../../facturacion/config/conexion.php";

// No es necesario volver a iniciar sesión, seguridad.php ya lo hace

// Sidebar según rol 
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 6 ) {
    include "headercadena.php";
}
else {
    include "header.php";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cambiar contraseña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        /* --- INTEGRACIÓN DE ESTILOS CORPORATIVOS --- */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .cambio-container {
            max-width: 600px; /* Unificado con los módulos de perfil */
            margin-top: 40px;
            margin-bottom: 40px;
        }

        /* Tarjeta principal */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Tu degradado corporativo exacto */
        .bg-perfil-header {
            --corp-azul: #2C56E6; 
            --corp-rojo: #DF3456;
            background: linear-gradient(90deg, var(--corp-azul) 30%, var(--corp-rojo) 100%) !important; 
            padding: 1.8rem;
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 2.5rem 2rem;
        }

        /* Inputs de contraseña */
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        /* Enfoque del input utilizando tu azul corporativo */
        .form-control:focus {
            border-color: #2C56E6;
            box-shadow: 0 0 0 0.25rem rgba(44, 86, 230, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.4rem;
            font-size: 0.95rem;
        }

        /* --- BOTONES DE ACCIÓN --- */

        /* Botón Guardar / Cambiar (Azul Corporativo) */
        .btn-cambiar-password {
            background-color: #2C56E6 !important;
            border-color: #2C56E6 !important;
            color: #ffffff !important;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-cambiar-password:hover {
            background-color: #1a3fb3 !important;
            border-color: #1a3fb3 !important;
        }

        /* Botón Cancelar / Regresar */
        .btn-cancelar {
            background-color: transparent !important;
            border: 1px solid #ced4da !important;
            color: #495057 !important;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancelar:hover {
            background-color: #f8f9fa !important;
            border-color: #adb5bd !important;
            color: #212529 !important;
        }

        /* Ajuste forzado de margen superior */
        .container.cambio-container {
            margin-top: 30px !important;
        }
    </style>
</head>

<body>

    <div class="container cambio-container">
        <div class="card shadow-sm">
            <div class="card-header bg-perfil-header text-white">
                <h2 class="text-center mb-0"><i class="bi bi-key-fill"></i> Cambiar Contraseña</h2>
            </div>
            
            <div class="card-body">
                <form action="../controlador/proceso_cambio_provedor.php" method="post">
                    <input type="hidden" name="usuario" value="<?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="contraseña_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="nueva_contraseña" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_contraseña" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirmar_contraseña"
                            name="confirmar_contraseña" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-cambiar-password">
                            <i class="bi bi-shield-lock"></i> Actualizar Contraseña
                        </button>
                        <a href="perfil.php" class="btn btn-cancelar text-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>