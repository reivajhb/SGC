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

// Obtener el usuario logueado
$usuario = $_SESSION['usuario'] ?? null;

// Inicializar variables
$id_usuario = '';
$nombre = '';
$correo = '';
$telefono = '';
$direccion = '';
$error_mensaje = '';

if (!$usuario) {
    $error_mensaje = "Usuario no encontrado en sesión.";
} else {
    // Consultar datos del usuario
    $consulta = "SELECT id_usuario, nombre, correo, telefono, direccion, usuario 
                 FROM tbl_usuarios 
                 WHERE usuario = ?";

    $stmt = $conn->prepare($consulta);
    
    if ($stmt) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $id_usuario = $fila['id_usuario'] ?? '';
            $nombre = $fila['nombre'] ?? $fila['usuario'] ?? '';
            $correo = $fila['correo'] ?? '';
            $telefono = $fila['telefono'] ?? '';
            $direccion = $fila['direccion'] ?? '';
        } else {
            $error_mensaje = "No se encontraron datos para el usuario.";
        }
        $stmt->close();
    } else {
        $error_mensaje = "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Perfil de Usuario</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .perfil-container {
            max-width: 600px;
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

        .form-control[readonly] {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            cursor: not-allowed;
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
        .container.perfil-container{
            margin-top: 0 !important;
        }
    </style>
</head>

<body>

    <div class="container perfil-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Perfil de Usuario</h2>
            </div>

            <div class="card-body">
                <?php if (!empty($error_mensaje)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> <?= htmlspecialchars($error_mensaje) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" value="<?= htmlspecialchars($nombre) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" value="<?= htmlspecialchars($correo) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" value="<?= htmlspecialchars($telefono) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" rows="3" readonly><?= htmlspecialchars($direccion) ?></textarea>
                </div>

                <?php if (!empty($id_usuario)): ?>
                    <div class="d-grid gap-2">
                        <a href="modificarPerfil.php?id_usuario=<?= $id_usuario ?>" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Editar Perfil
                        </a>
                        <a href="RecuperarPass.php" class="btn btn-outline-secondary">
                            <i class="bi bi-key"></i> Cambiar Contraseña
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>
