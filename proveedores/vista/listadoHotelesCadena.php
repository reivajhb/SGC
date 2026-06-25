<?php
// Seguridad y conexión
include_once '../seguridad_proveedores.php';
include_once '../../facturacion/config/conexion.php';

// ----------------------------------------------------------------------
// VERIFICACIÓN DE ACCESO
// ----------------------------------------------------------------------

$idRol = $_SESSION['id_rol'] ?? null;
$nitCadena = $_SESSION['usuario'] ?? null;

// Esta página es SOLO para cadenas hoteleras (Rol 7)
// Si el usuario no es Rol 7, denegar el acceso
if ((int) $idRol !== 7) {
  http_response_code(403);
  echo '<h1>Acceso denegado</h1>';
  echo '<p>Esta página es solo para cadenas hoteleras (Rol 7).</p>';
  echo '<a href="formularioIncripHotel.php">Volver al inicio</a>';
  exit;
}

// Parámetro de búsqueda (viene del buscador del header cuando hay múltiples resultados)
$filtroQ      = trim($_GET['q'] ?? '');
$noEncontrado = isset($_GET['notfound']) ? trim($_GET['notfound']) : null;
$bulkImportResult = $_SESSION['bulk_import_result'] ?? null;
unset($_SESSION['bulk_import_result']);

// Consultar hoteles de la cadena (compatibilidad: usuario_creacion o FK id_usuario_creacion)
$hoteles = [];
if ($filtroQ !== '') {
    $like = '%' . $filtroQ . '%';
    $stmt = $conn->prepare("
        SELECT 
            h.id_hotel,
            h.nombre,
            h.ciudad,
            h.pais,
            h.nit,
            h.razon_social,
            h.estado_aprobacion,
            h.estado_firma,
            u.nombre AS nombre_cadena,
            u.usuario AS nit_cadena
        FROM tbl_alojamiento_general h
        LEFT JOIN tbl_usuarios u 
            ON u.id_usuario = h.id_usuario_creacion
        WHERE (h.usuario_creacion = ? OR u.usuario = ?)
          AND COALESCE(h.estado_registro, 'FINALIZADO') = 'FINALIZADO'
          AND (h.nombre LIKE ? OR h.nit LIKE ?)
        ORDER BY h.id_hotel DESC
    ");
    $stmt->bind_param("ssss", $nitCadena, $nitCadena, $like, $like);
} else {
    $stmt = $conn->prepare("
        SELECT 
            h.id_hotel,
            h.nombre,
            h.ciudad,
            h.pais,
            h.nit,
            h.razon_social,
            h.estado_aprobacion,
            h.estado_firma,
            u.nombre AS nombre_cadena,
            u.usuario AS nit_cadena
        FROM tbl_alojamiento_general h
        LEFT JOIN tbl_usuarios u 
            ON u.id_usuario = h.id_usuario_creacion
        WHERE (h.usuario_creacion = ? OR u.usuario = ?)
          AND COALESCE(h.estado_registro, 'FINALIZADO') = 'FINALIZADO'
        ORDER BY h.id_hotel DESC
    ");
    $stmt->bind_param("ss", $nitCadena, $nitCadena);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $hoteles[] = $row;
}
$stmt->close();

// Anti-cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<BR></BR>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Mis Hoteles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="estilos_hotel_moderno.css?v=habitaciones-20260612">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
  <link rel="icon" type="image/x-icon" href="/facturacion/img/favicon.jpg">
  
  <style>
    body {
      background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
      min-height: 100vh;
      padding-bottom: 3rem;
    }

    .container {
      margin-top: 2rem;
    }

    /* Header Section */
    .page-header {
      background: linear-gradient(135deg, #0077ff, #0066ff);
      color: white !important;
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      margin-bottom: 2rem;
    }

    .page-header h3 {
      margin: 0;
      font-weight: 700;
      font-size: 1.8rem;
      color: white !important;
    }

    .page-header p {
      color: white !important;
      opacity: 0.9;
    }

    .btn-register {
      background: linear-gradient(135deg, #2ecc71, #27ae60);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }

    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
      background: linear-gradient(135deg, #27ae60, #229954);
      color: white;
    }

    .btn-register i {
      margin-right: 0.5rem;
    }
    .btn-import {
      background: linear-gradient(135deg, #00a3a3, #007b83);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 123, 131, 0.3);
    }

    .btn-import:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 123, 131, 0.4);
      background: linear-gradient(135deg, #008b8b, #006a70);
      color: white;
    }

    .btn-import i {
      margin-right: 0.5rem;
    }

    /* Card Styles */
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      overflow: visible;
    }

    .card-body {
      padding: 2rem;
      overflow: visible !important;
    }

    /* Alert Styles */
    .alert {
      border-radius: 10px;
      border: none;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
      background: linear-gradient(135deg, #d4edda, #c3e6cb);
      color: #155724;
    }

    .alert-warning {
      background: linear-gradient(135deg, #fff3cd, #ffeeba);
      color: #856404;
    }

    .alert-info {
      background: linear-gradient(135deg, #d1ecf1, #bee5eb);
      color: #0c5460;
    }

    /* Table Styles */
    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background: linear-gradient(135deg, #0077ff, #0066ff) !important;
      color: white !important;
      font-weight: 600;
      border: none;
      padding: 1rem;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }

    .table tbody tr {
      transition: all 0.3s ease;
    }

    .table tbody tr:hover {
      background-color: #f8f9fa;
    }

    .table tbody tr:has(.dropdown.show) {
      z-index: 10000 !important;
      position: relative;
    }

    .table tbody td {
      vertical-align: middle;
      padding: 1rem;
    }

    .table tbody td.table-actions {
      overflow: visible;
      z-index: 1;
      position: static;
    }

    /* Badge States */
    .badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.8rem;
    }

    /* Estado Aprobación */
    td:nth-child(8) {
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
    }

    /* Estado Firma */
    td:nth-child(9) {
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
    }

    /* Dropdown Actions */
    .table-actions {
      position: static;
    }

    .table-actions .dropdown {
      position: static;
    }

    .table-actions .dropdown-toggle {
      background: linear-gradient(135deg, #0077ff, #0066ff) !important;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .table-actions .dropdown-toggle:hover {
      box-shadow: 0 4px 15px rgba(0, 119, 255, 0.4);
      background: linear-gradient(135deg, #1976D2, #1565C0) !important;
    }

    .table-actions .dropdown.show .dropdown-toggle {
      z-index: 10001 !important;
    }

    .table-actions .dropdown-menu {
      border: none;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
      z-index: 10000 !important;
      padding: 0.5rem;
      will-change: transform;
    }

    .table-actions .dropdown-item {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 0.25rem;
      transition: all 0.3s ease;
    }

    .table-actions .dropdown-item:hover {
      background-color: #f8f9fa;
      color: #1565C0 !important;
      transform: translateX(5px);
    }

    .table-actions .dropdown-item i {
      width: 20px;
      text-align: center;
    }

    /* DataTables Custom Styles */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      border-radius: 8px;
      border: 1px solid #ced4da;
      padding: 0.5rem;
      transition: all 0.3s ease;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: #1565C0;
      box-shadow: 0 0 0 0.2rem rgba(21, 101, 192, 0.25);
      outline: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: linear-gradient(135deg, #1565C0, #0D47A1) !important;
      color: white !important;
      border: none !important;
      border-radius: 8px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: linear-gradient(135deg, #1565C0, #0D47A1) !important;
      color: white !important;
      border: none !important;
      border-radius: 8px !important;
    }

    /* Fix para overflow de la tabla */
    .table-responsive {
      overflow-x: auto;
      overflow-y: visible;
    }

    .card {
      overflow: visible !important;
    }

    /* Empty State */
    .alert-warning.mb-0 {
      text-align: center;
      font-size: 1.1rem;
      padding: 2rem;
    }
  </style>

</head>

<body>

  <?php
  // Header para cadenas
  include_once '../vista/headercadena.php';
  ?>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h3><i class="fas fa-hotel me-2"></i>Mis Hoteles</h3>
          <p class="mb-0 mt-2" style="opacity: 0.9;">Gestiona todos los hoteles de tu cadena hotelera</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
          <a href="../vista/formularioIncripHotel.php?nuevo=1" class="btn btn-register">
            <i class="fas fa-plus-circle"></i> Registrar nuevo hotel
          </a>
        </div>
      </div>
    </div>
    <?php if (is_array($bulkImportResult)): ?>
      <div class="alert <?php echo !empty($bulkImportResult['ok']) ? 'alert-success' : 'alert-warning'; ?>">
        <i class="fas <?php echo !empty($bulkImportResult['ok']) ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($bulkImportResult['mensaje'] ?? 'Proceso finalizado.'); ?>

        <?php if (!empty($bulkImportResult['items']) && is_array($bulkImportResult['items'])): ?>
          <ul class="mb-0 mt-2">
            <?php foreach ($bulkImportResult['items'] as $item): ?>
              <li>
                <?php echo htmlspecialchars(($item['accion'] ?? '') === 'actualizado' ? 'Actualizado' : 'Creado'); ?>:
                <strong><?php echo htmlspecialchars($item['hotel'] ?? ''); ?></strong>
                (ID <?php echo (int) ($item['id_hotel'] ?? 0); ?>)
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($bulkImportResult['errores']) && is_array($bulkImportResult['errores'])): ?>
          <ul class="mb-0 mt-2">
            <?php foreach ($bulkImportResult['errores'] as $errorImport): ?>
              <li><?php echo htmlspecialchars($errorImport); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><strong>¡Éxito!</strong> Hotel eliminado correctamente.
      </div>
    <?php endif; ?>

    <?php if ($noEncontrado !== null): ?>
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        No se encontró ningún hotel con el nombre "<strong><?php echo htmlspecialchars($noEncontrado); ?></strong>".
        <a href="listadoHotelesCadena.php" class="alert-link ms-2">
          <i class="fas fa-arrow-left me-1"></i>Ver todos
        </a>
      </div>
    <?php elseif ($filtroQ !== ''): ?>
      <div class="alert alert-info">
        <i class="fas fa-search me-2"></i>
        Mostrando resultados para: "<strong><?php echo htmlspecialchars($filtroQ); ?></strong>"
        <a href="listadoHotelesCadena.php" class="alert-link ms-2">
          <i class="fas fa-arrow-left me-1"></i>Ver todos
        </a>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body" style="padding: 0 !important;">
        <div class="table-responsive">
          <table id="tablaHoteles" class="display nowrap table table-striped table-bordered" style="width:100%; ">
            <thead>
              <tr>
                <th>ID</th>
                <th>Hotel</th>
                <th>Razón Social</th>
                <th>NIT</th>
                <th>Ciudad</th>
                <th>País</th>
                <th>Cadena</th>
                <th>Estado de Aprobación</th>
                <th>Estado de Firma</th>
                <th class="dt-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($hoteles)): ?>
                <?php foreach ($hoteles as $h): ?>
                  <tr>
                    <td><?php echo (int) $h['id_hotel']; ?></td>
                    <td><?php echo htmlspecialchars($h['nombre'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['razon_social'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['nit'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['ciudad'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['pais'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['nombre_cadena'] ?: $h['nit_cadena'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($h['estado_aprobacion'] ?? ''); ?></td>
                    <td>
                      <?php
                      $estadoFirma = strtoupper(trim((string) ($h['estado_firma'] ?? '')));
                      echo htmlspecialchars($estadoFirma === 'FIRMADO' ? 'FIRMADO' : 'SIN FIRMA');
                      ?>
                    </td>
                    <td class="table-actions dt-center">
                      <div class="dropdown d-flex justify-content-center">
                        <button style="padding: 0.5rem 1rem !important; background-color: #0077ff !important;" class="btn btn-sm dropdown-toggle" type="button"
                          data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-bars me-1"></i> 
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="consultaHotel.php?id=<?php echo (int) $h['id_hotel']; ?>">
                              <i class="fas fa-eye text-primary"></i> Ver ficha
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="editarHotel.php?id=<?php echo (int) $h['id_hotel']; ?>">
                              <i class="fas fa-edit text-warning"></i> Editar
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="ficha_pdf_hotel.php?id=<?php echo (int) $h['id_hotel']; ?>" target="_blank">
                              <i class="fas fa-file-pdf" style="color:#dc3545;"></i> Ver PDF
                            </a>
                          </li>
                          <?php if (isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [1,8])):?>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <button type="button" class="dropdown-item text-danger"
                              onclick="eliminarHotel(<?php echo (int) $h['id_hotel']; ?>)">
                              <i class="fas fa-trash"></i> Eliminar
                            </button>
                          </li>
                          <?php endif; ?>
                        </ul>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <?php if (empty($hoteles)): ?>
            <div class="alert alert-warning mb-0">
              <i class="fas fa-info-circle me-2"></i>
              <strong>No hay hoteles registrados.</strong><br>
              Aún no tienes hoteles registrados. Usa el botón <strong>"+ Registrar nuevo hotel"</strong> para comenzar.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(function () {
      var searchQuery = <?php echo json_encode($_GET['q'] ?? ''); ?>;
      var table = $('#tablaHoteles').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          zeroRecords: 'Hotel no encontrado'
        },
        columnDefs: [
          { targets: [0], width: 60 },
          { targets: -1, orderable: false, searchable: false, width: 180 }
        ]
      });
      window.hotelTable = table;
      if (searchQuery) {
        table.search(searchQuery).draw();
      }

      // Manejar z-index cuando se abre/cierra el dropdown
      $(document).on('show.bs.dropdown', '.table-actions .dropdown', function() {
        // Resetear z-index de todas las filas primero
        $('.table tbody tr').css({'z-index': '', 'position': ''});
        // Aplicar z-index alto a la fila actual
        $(this).closest('tr').css({'z-index': '10000', 'position': 'relative'});
      });

      $(document).on('hide.bs.dropdown', '.table-actions .dropdown', function() {
        $(this).closest('tr').css({'z-index': '', 'position': ''});
      });
    });

    function eliminarHotel(idHotel) {
      if (confirm("⚠️ ¿Estás seguro de eliminar este hotel?\n\nEsta acción no se puede deshacer y eliminará toda la información asociada.")) {
        // Mostrar indicador de carga
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;';
        overlay.innerHTML = '<div style="background:white;padding:2rem;border-radius:15px;text-align:center;"><i class="fas fa-spinner fa-spin fa-3x" style="color:#1565C0;"></i><p style="margin-top:1rem;font-weight:600;">Eliminando hotel...</p></div>';
        document.body.appendChild(overlay);
        
        // Redirigir después de un breve delay para mostrar el loader
        setTimeout(() => {
          window.location.href = "../controlador/eliminarHotel.php?id=" + idHotel;
        }, 500);
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el, {
          popperConfig: {
            strategy: 'fixed',
            modifiers: [
              {
                name: 'preventOverflow',
                options: {
                  boundary: 'viewport',
                  padding: 8,
                },
              },
              {
                name: 'flip',
                options: {
                  fallbackPlacements: ['bottom', 'top', 'left'],
                },
              },
              {
                name: 'offset',
                options: {
                  offset: [0, 8],
                },
              },
            ],
          }
        });
      });
    });
  </script>
</body>

</html>
