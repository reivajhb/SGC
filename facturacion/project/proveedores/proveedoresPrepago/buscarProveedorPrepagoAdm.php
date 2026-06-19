<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    // Incluir el sidebar para el administrador
    include "../../../config/sidebar3.php";
    include "../../../../facturacion/config/boton_volver.php";
} else {
    // Incluir el sidebar normal para usuarios no administradores
    include "../../../config/sidebar.php";
    include "../../../../facturacion/config/boton_volver.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Relación pagos Proveedores Prepago</title>
</head>
<body>
    <div class="container py-4 min-vh-80 d-flex align-items-center justify-content-center" style="height: 80vh;">
        <div class="card text-center" style="max-width: 520px; width: 100%;">
            <div class="card-header">
                Búsqueda Proveedor Administrativo
            </div>

            <div class="card-body">
                <h5 class="card-title mb-3">Ingrese el Número de Nit o Cédula del Proveedor</h5>

                <form action="formularioCargaDriveProveedoresPrepagoAdm.php" method="get" class="container-fluid p-0">
                    <input
                        name="identificacion"
                        type="number"
                        class="form-control"
                        placeholder="Ingrese el Número de Nit o Cédula del Proveedor"
                        required
                    >

                    <div class="mt-3">
                        <button
                            class="btn btn-success w-100"
                            type="submit"
                            name="buscar"
                            value="buscar">
                            Buscar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
