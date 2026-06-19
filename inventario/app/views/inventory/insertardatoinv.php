<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Datos en tbl_inventarioeqc</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Insertar Datos</h1>
        <form action="/facturacion/inventario/public/index.php?action=insertar" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre_equipo" class="form-label">Nombre del equipo:</label>
                        <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
                    </div>

                    <div class="mb-3">
                        <label for="numero_serie" class="form-label">Número de serie:</label>
                        <input type="text" class="form-control" id="numero_serie" name="numero_serie" required>
                    </div>

                    <div class="mb-3">
                        <label for="sistema_operativo_nombre" class="form-label">Sistema operativo - Nombre:</label>
                        <input type="text" class="form-control" id="sistema_operativo_nombre" name="sistema_operativo_nombre" required>
                    </div>

                    <div class="mb-3">
                        <label for="serial_windows" class="form-label">Serial Windows:</label>
                        <input type="text" class="form-control" id="serial_windows" name="serial_windows" required>
                    </div>

                    <div class="mb-3">
                        <label for="version_office" class="form-label">Versión de Office:</label>
                        <input type="text" class="form-control" id="version_office" name="version_office" required>
                    </div>

                    <div class="mb-3">
                        <label for="serial_office" class="form-label">Serial Office:</label>
                        <input type="text" class="form-control" id="serial_office" name="serial_office" required>
                    </div>

                    <div class="mb-3">
                        <label for="localizaciones" class="form-label">Localizaciones:</label>
                        <input type="text" class="form-control" id="localizaciones" name="localizaciones" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inventario_cpu" class="form-label"># INVENTARIO CPU:</label>
                        <input type="text" class="form-control" id="inventario_cpu" name="inventario_cpu">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario:</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="area" class="form-label">Área:</label>
                        <input type="text" class="form-control" id="area" name="area" required>
                    </div>

                    <div class="mb-3">
                        <label for="licencia_win10_verificada" class="form-label">Licencia WIN Verificada:</label>
                        <select id="licencia_win10_verificada" name="licencia_win10_verificada" class="form-control" required>
                            <option value="" disabled selected>Seleccione...</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="licencia_office_verificada" class="form-label">Licencia Office Verificada:</label>
                        <select id="licencia_office_verificada" name="licencia_office_verificada" class="form-control" required>
                            <option value="" disabled selected>Seleccione...</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="softwarenolicenciado" class="form-label">¿Desinstalo software no licenciado?:</label>
                        <select id="softwarenolicenciado" name="softwarenolicenciado" class="form-control" required>
                            <option value="" disabled selected>Seleccione...</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="marquilla_licencia" class="form-label">Marquilla Licencia:</label>
                        <input type="text" class="form-control" id="marquilla_licencia" name="marquilla_licencia">
                    </div>

                    <div class="mb-3">
                        <label for="marquilla_pc" class="form-label">Marquilla PC:</label>
                        <input type="text" class="form-control" id="marquilla_pc" name="marquilla_pc">
                    </div>

                    

                    <div class="mb-3">
                        <label for="inventario_monitor" class="form-label"># INVENTARIO MONITOR:</label>
                        <input type="text" class="form-control" id="inventario_monitor" name="inventario_monitor">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones:</label>
                <textarea class="form-control" id="observaciones" name="observaciones"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Insertar Datos</button>
        </form>

        <br>
        <br>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>
</html>