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
$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;

if ($id_usuario <= 0) {
    echo "ID de usuario inválido.";
    exit();
}

$consulta = Consultaruser($id_usuario);

function Consultaruser($id_usuario)
{
    include "../../facturacion/config/conexion.php";

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

        .perfil-container {
            max-width: 600px; /* Unificado con la vista anterior */
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

        /* Inputs editables limpios */
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

        /* Botón Actualizar (Azul Corporativo) */
        .btn-actualizar {
            background-color: #2C56E6 !important;
            border-color: #2C56E6 !important;
            color: #ffffff !important;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-actualizar:hover {
            background-color: #1a3fb3 !important;
            border-color: #1a3fb3 !important;
        }

        /* Botón Cancelar/Regresar */
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

        /* Ajuste de margen superior */
        .container.perfil-container {
            margin-top: 30px !important;
        }
    </style>
</head>

<body>

    <div class="container perfil-container">
        <div class="card shadow-sm">
            <div class="card-header bg-perfil-header text-white">
                <h2 class="text-center mb-0"><i class="bi bi-pencil-square"></i> Modificar Perfil</h2>
            </div>
            
            <div class="card-body">  
                <form action="../controlador/editarPerfilProv.php" method="post">
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

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-actualizar">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="miCuentaProveedor.php" class="btn btn-cancelar text-center">
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