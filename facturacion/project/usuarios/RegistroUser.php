<?php
include "../../config/seguridad.php";
include "../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sidebar según rol
if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 8)) {
    include "../../config/sidebar3.php";
} else {
    include "../../config/sidebar.php";
}
include "../../config/boton_volver.php";

// Validar acceso para registro de usuarios
$isAdmin = isset($_SESSION['id_rol']) && ((int)$_SESSION['id_rol'] === 1 || (int)$_SESSION['id_rol'] === 8);
if (!$isAdmin) {
    header("Location: ../index.php");
    exit();
}

// Obtener roles
$roles = [];
$error_mensaje = '';

$consulta = "SELECT id, descripcion FROM tbl_roles ORDER BY descripcion ASC";
$ejecutar = mysqli_query($conn, $consulta);

if ($ejecutar) {
    while ($fila = mysqli_fetch_assoc($ejecutar)) {
        $roles[] = $fila;
    }
} else {
    $error_mensaje = "Error al consultar los roles: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Registro de Usuario</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .registro-container {
            max-width: 850px;
            margin-top: 60px;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-control,
        .form-select {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 0.65rem 0.75rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            min-width: 45px;
            justify-content: center;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 5px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .container.registro-container {
            margin-top: 0 !important;
        }

        .text-required {
            color: #dc3545;
        }
    </style>
</head>

<body>

    <div class="container registro-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Registro de Usuario</h2>
            </div>

            <div class="card-body">
                <?php if (!empty($error_mensaje)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> <?= htmlspecialchars($error_mensaje) ?>
                    </div>
                <?php endif; ?>

                <form action="RegistroNew.php" method="post" autocomplete="off">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario <span class="text-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input name="usuario" type="text" id="usuario" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre completo <span class="text-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input name="nombre" type="text" id="nombre" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña <span class="text-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input name="contraseña" type="password" id="password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_rol" class="form-label">Rol <span class="text-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php foreach ($roles as $opciones): ?>
                                            <option value="<?= (int)$opciones['id'] ?>">
                                                <?= htmlspecialchars($opciones['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo electrónico <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input name="correo" type="email" id="correo" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input name="telefono" type="text" id="telefono" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-4">
                                <label for="direccion" class="form-label">Dirección <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input name="direccion" type="text" id="direccion" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-user-plus"></i> Registrar Usuario
                        </button>
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
