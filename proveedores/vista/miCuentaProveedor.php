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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Perfil de Usuario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        /* --- AJUSTES DE ESTILO INTEGRADOS --- */
        body {
            background-color: #f4f6f9; /* Un gris sutil para que resalte la tarjeta */
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .perfil-container {
            max-width: 650px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        /* Tarjeta principal con bordes más limpios */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Color azul exacto del banner del perfil */
        .bg-perfil-header {
            --corp-rojo: #2C56E6; 
            --corp-azul: #DF3456;
            background: linear-gradient(90deg, var(--corp-azul), var(--corp-rojo)) !important; 
            padding: 1.8rem;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 2.5rem 2rem;
        }

        /* Estilos para los campos de texto en solo lectura (Readonly) */
        .form-control[readonly] {
            background-color: #ffffff; /* Fondo blanco igual que tu imagen */
            border: 1px solid #ced4da;
            color: #333333;
            cursor: not-allowed;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,.05);
            transition: none;
        }

        /* Quitar el redimensionamiento molesto del textarea */
        textarea.form-control[readonly] {
            resize: none;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.4rem;
            font-size: 0.95rem;
        }

        /* --- BOTONES PERSONALIZADOS (Match con tu captura) --- */
        
        /* Botón Editar Perfil (Rosa/Rojo vivo) */
        .btn-editar-perfil {
            background-color: #e23e57 !important;
            border-color: #e23e57 !important;
            color: #ffffff !important;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-editar-perfil:hover {
            background-color: #c22b42 !important;
            border-color: #c22b42 !important;
        }

        /* Botón Cambiar Contraseña */
        .btn-cambiar-pass {
            background-color: transparent !important;
            border: 1px solid #ced4da !important;
            color: #495057 !important;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-cambiar-pass:hover {
            background-color: #f8f9fa !important;
            border-color: #adb5bd !important;
            color: #212529 !important;
        }

        /* Forzar margen cero si el contenedor lo requiere */
        .container.perfil-container {
            margin-top: 30px !important;
        }
    </style>
</head>

<body>

    <div class="container perfil-container">
        <div class="card shadow-sm">
            <div class="card-header bg-perfil-header text-white">
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
                    <textarea class="form-control" id="direccion" rows="2" readonly><?= htmlspecialchars($direccion) ?></textarea>
                </div>

                <?php if (!empty($id_usuario)): ?>
                    <div class="d-grid gap-2 mt-4">
                        <a href="modificarPerfilProveedor.php?id_usuario=<?= $id_usuario ?>" class="btn btn-editar-perfil">
                            <i class="bi bi-pencil-square"></i> Editar Perfil
                        </a>
                        <a href="cambiarPassProveedor.php" class="btn btn-cambiar-pass">
                            <i class="bi bi-key"></i> Cambiar Contraseña
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>