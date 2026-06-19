<?php
include "../../config/seguridad.php";
include "../../config/conexion.php";

// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    // Incluir el sidebar para el administrador
    include "../../config/sidebar3.php";
} else {
    // Incluir el sidebar normal para usuarios no administradores
    include "../../config/sidebar.php";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cambiar contraseña</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .cambio-container {
            max-width: 500px;
            margin-top: 60px;
        }
    </style>
</head>

<body>

    <div class="container cambio-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Cambiar contraseña</h2>
            </div>
            <div class="card-body">
                <form action="procesar_cambio_contraseña.php" method="post">
                    <!-- Campo oculto para el nombre de usuario -->
                    <input type="hidden" name="usuario" value="<?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="contraseña_actual" class="form-label">Contraseña Actual:</label>
                        <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="nueva_contraseña" class="form-label">Nueva Contraseña:</label>
                        <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmar_contraseña" class="form-label">Confirmar Nueva Contraseña:</label>
                        <input type="password" class="form-control" id="confirmar_contraseña"
                            name="confirmar_contraseña" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>
