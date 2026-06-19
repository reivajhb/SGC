<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// Verificar si el usuario es administrador o si es el usuario con ID 8
if ((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8)) {
    if ($_SESSION['id_rol'] == 1) {
        include "../../facturacion/config/sidebar3.php";
        include "../../facturacion/config/boton_volver.php"; // Sidebar admin
    } else {
        include "../../facturacion/config/sidebar.php"; // Sidebar básico para el usuario 8
        include "../../facturacion/config/boton_volver.php"; // Botón volver usuario 8
    }
} else {
    echo "<script>alert('Acceso denegado.'); window.location.href = '../../buscarProveedor.php';</script>";
    exit();
}

// ----------- LÓGICA PHP -----------

$tipoPago        = $_GET['TipoPago']      ?? '';
$usuarioFiltro   = $_GET['id_usuario_ad'] ?? ''; // aquí guardas el campo 'usuario' (texto)
$inicio          = $_GET['inicio']        ?? '';
$fin             = $_GET['fin']           ?? '';

$resultados = [];
$totalGlobal = 0;

// Función para obtener conteo de registros por usuario
function obtenerConteoPorUsuario(mysqli $conn, string $tipoPago, string $inicio, string $fin, string $usuarioFiltro = ''): array
{
    if (empty($tipoPago) || empty($inicio) || empty($fin)) {
        return [];
    }

    $inicio = mysqli_real_escape_string($conn, $inicio);
    $fin    = mysqli_real_escape_string($conn, $fin);

    switch ($tipoPago) {
        case 'PagosAdministrativos':
            // Tabla: tbl_pagos_administrativos, campo usuario: id_usuario_fo
            $sql = "
                SELECT id_usuario_fo AS usuario, COUNT(*) AS totalregistros
                FROM tbl_pagos_administrativos
                WHERE fecha BETWEEN ? AND ?
            ";
            if (!empty($usuarioFiltro)) {
                $sql .= " AND id_usuario_fo = ? ";
            }
            $sql .= " GROUP BY id_usuario_fo ORDER BY totalregistros DESC";
            break;

        case 'PagosProveedoresturisticos':
            // Tabla: tbl_proveedores_turtisticos, campo usuario: id_usuario_tufo
            $sql = "
                SELECT id_usuario_tufo AS usuario, COUNT(*) AS totalregistros
                FROM tbl_proveedores_turtisticos
                WHERE fecha BETWEEN ? AND ?
            ";
            if (!empty($usuarioFiltro)) {
                $sql .= " AND id_usuario_tufo = ? ";
            }
            $sql .= " GROUP BY id_usuario_tufo ORDER BY totalregistros DESC";
            break;

        default:
            return [];
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    if (!empty($usuarioFiltro)) {
        $stmt->bind_param("sss", $inicio, $fin, $usuarioFiltro);
    } else {
        $stmt->bind_param("ss", $inicio, $fin);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $datos = [];
    while ($fila = $res->fetch_assoc()) {
        $datos[] = $fila;
    }
    $stmt->close();

    return $datos;
}

// Si hay filtros suficientes, hacemos la consulta
if (!empty($tipoPago) && !empty($inicio) && !empty($fin)) {
    $resultados = obtenerConteoPorUsuario($conn, $tipoPago, $inicio, $fin, $usuarioFiltro);

    foreach ($resultados as $r) {
        $totalGlobal += (int)$r['totalregistros'];
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
          rel="stylesheet" />

    <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">

    <title>Registros por usuario</title>

    <style>
        th {
            font-weight: bold;
            color: white;
        }
    </style>
</head>

<body>

<br>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h2>Consulta de registros realizados por usuario</h2>
            <small class="text-muted">Selecciona el tipo de pago, rango de fechas y opcionalmente un usuario.</small>
        </div>
    </div>

    <form action="" method="GET">
        <div class="row">
            <!-- Tipo de pago y usuario -->
            <div class="col-md-5">
                <div class="form-group">
                    <label><b>Tipo de pago</b></label>
                    <select name="TipoPago" class="form-control" required>
                        <option value="">-- Seleccione --</option>
                        <option value="PagosAdministrativos" <?php echo ($tipoPago === 'PagosAdministrativos') ? 'selected' : ''; ?>>
                            Pagos Administrativos
                        </option>
                        <option value="PagosProveedoresturisticos" <?php echo ($tipoPago === 'PagosProveedoresturisticos') ? 'selected' : ''; ?>>
                            Pagos Proveedores Turísticos
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label><b>Usuario (opcional)</b></label>
                    <select id="bucarh" name="id_usuario_ad" class="form-control">
                        <option value="">-- Todos los usuarios --</option>
                        <?php
                        $consultaUsuarios = "
                            SELECT usuario
                            FROM tbl_usuarios
                            WHERE id_rol = '2'
                            ORDER BY usuario ASC
                        ";
                        $ejecutarUsuarios = mysqli_query($conn, $consultaUsuarios);

                        while ($opciones = mysqli_fetch_assoc($ejecutarUsuarios)): ?>
                            <option value="<?php echo htmlspecialchars($opciones['usuario']); ?>"
                                <?php echo ($usuarioFiltro === $opciones['usuario']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($opciones['usuario']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-text text-muted">
                        Si no seleccionas usuario, verás el conteo de todos los usuarios.
                    </small>
                </div>
            </div>

            <!-- Fechas -->
            <div class="col-md-5">
                <div class="form-group">
                    <label><b>Fecha inicial</b></label>
                    <input type="date"
                           name="inicio"
                           value="<?php echo !empty($inicio) ? htmlspecialchars($inicio) : ''; ?>"
                           class="form-control"
                           required>

                    <label class="mt-3"><b>Fecha final</b></label>
                    <input type="date"
                           name="fin"
                           value="<?php echo !empty($fin) ? htmlspecialchars($fin) : ''; ?>"
                           class="form-control"
                           required>
                </div>
            </div>

            <!-- Botón -->
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">
                    Consultar
                </button>
            </div>
        </div>
    </form>

    <?php if (!empty($tipoPago) && !empty($inicio) && !empty($fin)): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="alert alert-info">
                    <b>Resumen:</b><br>
                    Tipo de pago: <b><?php echo htmlspecialchars($tipoPago); ?></b><br>
                    Desde: <b><?php echo htmlspecialchars($inicio); ?></b> &nbsp;
                    Hasta: <b><?php echo htmlspecialchars($fin); ?></b><br>
                    <?php if (!empty($usuarioFiltro)): ?>
                        Usuario: <b><?php echo htmlspecialchars($usuarioFiltro); ?></b><br>
                    <?php else: ?>
                        Usuario: <b>Todos</b><br>
                    <?php endif; ?>
                    Registros totales en el periodo: <b><?php echo (int)$totalGlobal; ?></b>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mt-3">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Usuario</th>
                        <th scope="col">Pagos cargados</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($resultados)): ?>
                        <?php foreach ($resultados as $fila): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
                                <td><?php echo (int)$fila['totalregistros']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif (!empty($tipoPago) || !empty($inicio) || !empty($fin) || !empty($usuarioFiltro)): ?>
                        <tr>
                            <td colspan="2" class="text-center">
                                No se encontraron registros para los filtros seleccionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">
                                Completa los filtros y pulsa "Consultar".
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- JS (jQuery una sola vez + Select2) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    $(document).ready(function () {
        $('#bucarh').select2({
            theme: 'bootstrap4',
            placeholder: "Buscar o seleccionar usuario",
            allowClear: true,
            width: '100%'
        });
    });
</script>

</body>
</html>
