<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; 
include "../../facturacion/config/boton_volver.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Inventario | SGC ERP</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .main-header {
            background: white;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Tarjeta contenedora de la tabla */
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Estilos de la Tabla */
        .table {
            font-size: 0.8rem;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.025em;
            padding: 12px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }

        /* Inputs de filtro dentro de la cabecera */
        .form-control-sm {
            font-size: 0.75rem;
            border-radius: 4px;
            margin-top: 5px;
            border: 1px solid #e2e8f0;
        }

        /* Badges de Verificación */
        .badge-verify {
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.65rem;
            display: inline-block;
            min-width: 35px;
            text-align: center;
        }
        .badge-si { background: #dcfce7; color: #15803d; }
        .badge-no { background: #fee2e2; color: #b91c1c; }

        .btn-action {
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            padding: 5px 10px;
        }

        /* Scroll horizontal personalizado */
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive-custom::-webkit-scrollbar {
            height: 8px;
        }
        .table-responsive-custom::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .table-responsive-custom::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .text-nowrap {
            white-space: nowrap !important;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <h1 class="h4 m-0 fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Consultar Inventario de Equipos</h1>
            <div class="d-flex gap-2">
                <button type="submit" form="inventoryForm" class="btn btn-primary btn-action shadow-sm">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar Resultados
                </button>
                <a href="/facturacion/inventario/public/index.php?action=downloadExcel" class="btn btn-success btn-action shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Descargar Excel
                </a>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4">
        <div class="content-card shadow-sm">
            <form method="GET" action="/facturacion/inventario/public/index.php" id="inventoryForm">
                <input type="hidden" name="action" value="consultar">
                
                <div class="table-responsive-custom">
                    <table class="table table-hover table-bordered w-100">
                        <thead>
                            <tr>
                                <th class="text-center">Acciones</th>
                                <th>Nombre del equipo 
                                    <input type="text" name="filter_nombre_equipo" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Número de serie 
                                    <input type="text" name="filter_numero_serie" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>S.O. - Nombre 
                                    <input type="text" name="filter_sistema_operativo_nombre" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Serial Windows 
                                    <input type="text" name="filter_serial_windows" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Versión Office 
                                    <input type="text" name="filter_version_office" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Serial Office 
                                    <input type="text" name="filter_serial_office" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Localizaciones 
                                    <input type="text" name="filter_localizaciones" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Usuario 
                                    <input type="text" name="filter_usuario" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Área 
                                    <input type="text" name="filter_area" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Licencia WIN 
                                    <select name="filter_licencia_win10_verificada" class="form-select form-control-sm">
                                        <option value="">Todos</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </th>
                                <th>Licencia Office 
                                    <select name="filter_licencia_office_verificada" class="form-select form-control-sm">
                                        <option value="">Todos</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </th>
                                <th>SW No Licenciado 
                                    <input type="text" name="filter_softwarenolicenciado" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Marquilla Licencia 
                                    <input type="text" name="filter_marquilla_licencia" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Marquilla PC 
                                    <input type="text" name="filter_marquilla_pc" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Inv. CPU 
                                    <input type="text" name="filter_inventario_cpu" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Inv. Monitor 
                                    <input type="text" name="filter_inventario_monitor" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                                <th>Observaciones 
                                    <input type="text" name="filter_observaciones" class="form-control form-control-sm" placeholder="Buscar...">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryItems as $item): ?>
                                <tr>
                                    <td class="text-center">
                                        <a href="/facturacion/inventario/public/index.php?action=edit&id=<?php echo htmlspecialchars($item['id']); ?>"
                                            class="btn btn-warning btn-sm btn-action text-white">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </td>
                                    <td class="text-nowrap fw-semibold"><?php echo htmlspecialchars($item['nombre_equipo']); ?></td>
                                    <td class="text-nowrap"><?php echo htmlspecialchars($item['numero_serie']); ?></td>
                                    <td><?php echo htmlspecialchars($item['sistema_operativo_nombre']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($item['serial_windows']); ?></td>
                                    <td><?php echo htmlspecialchars($item['version_office']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($item['serial_office']); ?></td>
                                    <td><?php echo htmlspecialchars($item['localizaciones']); ?></td>
                                    <td class="text-nowrap"><i class="fa-solid fa-user-tag me-1 text-muted"></i><?php echo htmlspecialchars($item['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($item['area']); ?></td>
                                    <td class="text-center">
                                        <span class="badge-verify <?php echo $item['licencia_win10_verificada'] ? 'badge-si' : 'badge-no'; ?>">
                                            <?php echo $item['licencia_win10_verificada'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-verify <?php echo $item['licencia_office_verificada'] ? 'badge-si' : 'badge-no'; ?>">
                                            <?php echo $item['licencia_office_verificada'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-verify <?php echo $item['softwarenolicenciado'] ? 'badge-si' : 'badge-no'; ?>">
                                            <?php echo $item['softwarenolicenciado'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-verify <?php echo $item['marquilla_licencia'] ? 'badge-si' : 'badge-no'; ?>">
                                            <?php echo $item['marquilla_licencia'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-verify <?php echo $item['marquilla_pc'] ? 'badge-si' : 'badge-no'; ?>">
                                            <?php echo $item['marquilla_pc'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['inventario_cpu']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['inventario_monitor']); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($item['observaciones']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>

</html>