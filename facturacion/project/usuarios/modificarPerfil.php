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

$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;

if ($id_usuario <= 0) {
    echo "ID de usuario inválido.";
    exit();
}

$consulta = Consultaruser($id_usuario);

function Consultaruser($id_usuario)
{
    include "../../config/conexion.php";

    $sentencia = "SELECT id_usuario, usuario, contraseña, nombre, correo, telefono, direccion 
                  FROM tbl_usuarios 
                  WHERE id_usuario = ?";

    $stmt = $conn->prepare($sentencia);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return null;
    }

    $mostrar = $resultado->fetch_assoc();

    $stmt->close();
    $conn->close();

    return [
        $mostrar['id_usuario'],
        $mostrar['usuario'],
        $mostrar['contraseña'],
        $mostrar['nombre'],
        $mostrar['correo'],
        $mostrar['telefono'],
        $mostrar['direccion']
    ];
}

if (!$consulta) {
    echo "Usuario no encontrado.";
    exit();
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Modificar Perfil de Usuario</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .perfil-container {
            max-width: 500px;
            margin-top: 60px;
        }
    </style>
</head>

<body>

    <div class="container perfil-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Perfil de Usuario: <?= htmlspecialchars($consulta[3]) ?></h2>
            </div>
            <div class="card-body">
                <form action="editarPerfil.php" method="post">
                    <input name="id_usuario" type="hidden" value="<?= htmlspecialchars($consulta[0]) ?>">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input name="nombre" type="text" class="form-control" id="nombre"
                            value="<?= htmlspecialchars($consulta[3]) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input name="correo" type="email" class="form-control" id="correo"
                            value="<?= htmlspecialchars($consulta[4]) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input name="telefono" type="tel" class="form-control" id="telefono"
                            value="<?= htmlspecialchars($consulta[5]) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input name="direccion" type="text" class="form-control" id="direccion"
                            value="<?= htmlspecialchars($consulta[6]) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Actualizar</button>
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
