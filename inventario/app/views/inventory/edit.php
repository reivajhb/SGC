<?php 
// Validar sesión primero
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /facturacion/index.php');
    exit;
}

// Verificar que sea administrador
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 1) {
    echo "
    <script>
        alert('No tienes permisos para acceder a este módulo.');
        window.location.href = '/facturacion/paneladmin.php';
    </script>
    ";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; 
include "../../facturacion/config/boton_volver.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Editar Inventario</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #334155;
        }

        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            border-left: 4px solid #3b82f6;
            padding-left: 10px;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #3b82f6;
            border: none;
        }

        .btn-danger {
            background-color: #ef4444;
            border: none;
        }

        .container-form {
            max-width: 1000px;
        }

        /* Estilo para las filas internas */
        .form-group-custom {
            margin-bottom: 1.25rem;
        }
    </style>
</head>

<body>
    <div class="container container-form mt-5 mb-5">
        <div class="main-card p-4 p-md-5">
            <div class="d-flex align-items-center mb-4">
                <i class="fa-solid fa-boxes-stacked fa-2x text-primary me-3"></i>
                <h1 class="h3 mb-0 fw-bold">Editar Registro de Inventario</h1>
            </div>

            <hr class="text-muted mb-4">

            <form action="/facturacion/inventario/public/index.php?action=update" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                
                <div class="row">
                    <!-- Columna Izquierda: Información Técnica -->
                    <div class="col-md-6 border-end pe-md-4">
                        <h2 class="section-title">Información del Equipo</h2>
                        
                        <div class="form-group-custom">
                            <label for="nombre_equipo" class="form-label">Nombre del Equipo</label>
                            <input type="text" id="nombre_equipo" name="nombre_equipo" class="form-control"
                                value="<?php echo htmlspecialchars($item['nombre_equipo']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="numero_serie" class="form-label">Número de Serie</label>
                            <input type="text" id="numero_serie" name="numero_serie" class="form-control"
                                value="<?php echo htmlspecialchars($item['numero_serie']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="sistema_operativo_nombre" class="form-label">Sistema Operativo</label>
                            <select id="sistema_operativo_nombre" name="sistema_operativo_nombre" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Microsoft Windows 10 Pro" <?php echo ($item['sistema_operativo_nombre'] == 'Microsoft Windows 10 Pro') ? 'selected' : ''; ?>>
                                    Microsoft Windows 10 Pro
                                </option>
                                <option value="Microsoft Windows 11 Pro" <?php echo ($item['sistema_operativo_nombre'] == 'Microsoft Windows 11 Pro') ? 'selected' : ''; ?>>
                                    Microsoft Windows 11 Pro
                                </option>
                                <option value="macOS" <?php echo ($item['sistema_operativo_nombre'] == 'macOS') ? 'selected' : ''; ?>>
                                    macOS
                                </option>
                            </select>
                        </div>

                        <div class="form-group-custom">
                            <label for="serial_windows" class="form-label">Serial Windows</label>
                            <input type="text" id="serial_windows" name="serial_windows" class="form-control"
                                value="<?php echo htmlspecialchars($item['serial_windows']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="version_office" class="form-label">Versión de Office</label>
                            <input type="text" id="version_office" name="version_office" class="form-control"
                                value="<?php echo htmlspecialchars($item['version_office']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="serial_office" class="form-label">Serial Office</label>
                            <input type="text" id="serial_office" name="serial_office" class="form-control"
                                value="<?php echo htmlspecialchars($item['serial_office']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="localizaciones" class="form-label">Localización</label>
                            <select id="localizaciones" name="localizaciones" class="form-select">
                                <option value="SAN ANDRES" <?php echo $item['localizaciones'] === 'SAN ANDRES' ? 'selected' : ''; ?>>SAN ANDRES</option>
                                <option value="RINCON DEL PARQUE" <?php echo $item['localizaciones'] === 'RINCON DEL PARQUE' ? 'selected' : ''; ?>>RINCON DEL PARQUE</option>
                                <option value="ASTAF" <?php echo $item['localizaciones'] === 'ASTAF' ? 'selected' : ''; ?>>ASTAF</option>
                                <option value="CARTAGENA" <?php echo $item['localizaciones'] === 'CARTAGENA' ? 'selected' : ''; ?>>CARTAGENA</option>
                                <option value="CASA" <?php echo $item['localizaciones'] === 'CASA' ? 'selected' : ''; ?>>CASA</option>
                                <option value="MEDELLIN" <?php echo $item['localizaciones'] === 'MEDELLIN' ? 'selected' : ''; ?>>MEDELLIN</option>
                            </select>
                        </div>
                    </div>

                    <!-- Columna Derecha: Asignación y Verificación -->
                    <div class="col-md-6 ps-md-4">
                        <h2 class="section-title">Asignación y Verificación</h2>
                        
                        <div class="form-group-custom">
                            <label for="usuario" class="form-label">Usuario Responsable</label>
                            <input type="text" id="usuario" name="usuario" class="form-control"
                                value="<?php echo htmlspecialchars($item['usuario']); ?>" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="area" class="form-label">Área</label>
                            <select id="area" name="area" class="form-select">
                                <option value="ADMINISTRATIVO" <?php echo $item['area'] === 'ADMINISTRATIVO' ? 'selected' : ''; ?>>ADMINISTRATIVO</option>
                                <option value="CONTABILIDAD" <?php echo $item['area'] === 'CONTABILIDAD' ? 'selected' : ''; ?>>CONTABILIDAD</option>
                                <option value="RECEPTIVO" <?php echo $item['area'] === 'RECEPTIVO' ? 'selected' : ''; ?>>RECEPTIVO</option>
                                <option value="TURIVEL" <?php echo $item['area'] === 'TURIVEL' ? 'selected' : ''; ?>>TURIVEL</option>
                                <option value="AGENCIAS" <?php echo $item['area'] === 'AGENCIAS' ? 'selected' : ''; ?>>AGENCIAS</option>
                                <option value="SISTEMAS" <?php echo $item['area'] === 'SISTEMAS' ? 'selected' : ''; ?>>SISTEMAS</option>
                                <option value="MERCADEO Y DISEÑO" <?php echo $item['area'] === 'MERCADEO Y DISEÑO' ? 'selected' : ''; ?>>MERCADEO Y DISEÑO</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 form-group-custom">
                                <label for="licencia_win10_verificada" class="form-label">Licencia WIN Verificada</label>
                                <select id="licencia_win10_verificada" name="licencia_win10_verificada" class="form-select">
                                    <option value="1" <?php echo $item['licencia_win10_verificada'] ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo !$item['licencia_win10_verificada'] ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div class="col-6 form-group-custom">
                                <label for="licencia_office_verificada" class="form-label">Licencia Office Verificada</label>
                                <select id="licencia_office_verificada" name="licencia_office_verificada" class="form-select">
                                    <option value="1" <?php echo $item['licencia_office_verificada'] ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo !$item['licencia_office_verificada'] ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label for="softwarenolicenciado" class="form-label">¿Desinstalo software no licenciado?</label>
                            <select id="softwarenolicenciado" name="softwarenolicenciado" class="form-select">
                                <option value="1" <?php echo $item['softwarenolicenciado'] ? 'selected' : ''; ?>>Sí</option>
                                <option value="0" <?php echo !$item['softwarenolicenciado'] ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 form-group-custom">
                                <label for="marquilla_licencia" class="form-label">Marquilla Licencia</label>
                                <select id="marquilla_licencia" name="marquilla_licencia" class="form-select">
                                    <option value="1" <?php echo $item['marquilla_licencia'] ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo !$item['marquilla_licencia'] ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div class="col-6 form-group-custom">
                                <label for="marquilla_pc" class="form-label">Marquilla PC</label>
                                <select id="marquilla_pc" name="marquilla_pc" class="form-select">
                                    <option value="1" <?php echo $item['marquilla_pc'] ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo !$item['marquilla_pc'] ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <h2 class="section-title">Activos Fijos e Inventario</h2>
                    <div class="col-md-6 form-group-custom">
                        <label for="inventario_cpu" class="form-label"># Inventario CPU</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-microchip"></i></span>
                            <input type="text" id="inventario_cpu" name="inventario_cpu" class="form-control"
                                value="<?php echo htmlspecialchars($item['inventario_cpu']); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6 form-group-custom">
                        <label for="inventario_monitor" class="form-label"># Inventario Monitor</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-desktop"></i></span>
                            <input type="text" id="inventario_monitor" name="inventario_monitor" class="form-control"
                                value="<?php echo htmlspecialchars($item['inventario_monitor']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group-custom">
                    <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                    <textarea id="observaciones" name="observaciones" class="form-control"
                        rows="4" placeholder="Escriba aquí notas importantes sobre el equipo..."><?php echo htmlspecialchars($item['observaciones']); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" formaction="/facturacion/inventario/public/index.php?action=delete" class="btn btn-danger" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                        <i class="fa-solid fa-trash-can me-2"></i>Eliminar Registro
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar Inventario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>