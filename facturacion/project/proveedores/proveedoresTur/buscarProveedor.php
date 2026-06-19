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
    include "../../../config/boton_volver.php";
} else {
    // Incluir el sidebar normal para usuarios no administradores
    include "../../../config/sidebar.php";
    include "../../../config/boton_volver.php";
}
?>

<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <title>Busqueda Proveedores Turisticos</title>
</head>

<body>

    <div class="container py-4 min-vh-80 d-flex align-items-center justify-content-center" style="height: 80vh;">
        <div class="card text-center" style="max-width: 520px; width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); background-color: #ffffff;">
            <div class="card-header">
                Busqueda Proveedores Turisticos
            </div>

            <div class="card-body">
                <h5 class="card-title mb-3">Ingrese el Numero de Nit del Proveedor</h5>

                <form action="formularioCargaDriveProveedoresTuristicos.php" method="get" class="container-fluid p-0">
                    <input name="nit" type="number" class="form-control"
                        placeholder="Ingrese el Numero de Nit del Proveedor" required>

                    <div class="mt-3">
                        <button class="btn btn-success w-100" type="submit" name="buscar" value="buscar">
                            Buscar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (incluye Popper, sin jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>