<?php 
include "seguridad.php"

 ?>

<!doctype html>
<html>

<header> 

<?php 
include "sidebar.php"
 ?>

<?php

include('conexion.php');

// Obtener el nombre de usuario de la sesión
$usuario = $_SESSION['usuario'];

// Consultar los datos del usuario en la base de datos
$consulta = "SELECT nombre, correo, telefono, direccion, id_usuario FROM tbl_usuarios WHERE usuario = ?";
$stmt = $conn->prepare($consulta);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    // Obtener los datos del usuario
    $fila = $resultado->fetch_assoc();
    $id_usuario = $fila['id_usuario'];
    $nombre = $fila['nombre'];
    $correo = $fila['correo'];
    $telefono = $fila['telefono'];
    $direccion = $fila['direccion'];
} else {
    echo "No se encontraron datos para el usuario.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <!-- Enlace a Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Estilos CSS personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center">Perfil de Usuario</h2>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" value="<?php echo $nombre; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" value="<?php echo $correo; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" class="form-control" id="telefono" value="<?php echo $telefono; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <textarea class="form-control" id="direccion" rows="3" readonly><?php echo $direccion; ?></textarea>
                    </div>
                    <div class="text-center">
                         <a href="modificarPerfil.php?id_usuario=<?php echo $fila['id_usuario']; ?>" class="btn btn-primary">Editar Perfil</a>
                        <a href="RecuperarPass.php" class="btn btn-primary">Cambiar Contraseña</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <!-- Enlace a Bootstrap JS (opcional, solo si necesitas funcionalidad de Bootstrap) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
