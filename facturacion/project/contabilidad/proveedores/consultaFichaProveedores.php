<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// ====== Ajusta estos IDs según tu tabla de roles ======
define('ROL_ADMIN', 1);   // admin
define('ROL_CADENA', 7);  // cadena hotelera
define('ROL_2', 2);       // analista contable
define('ROL_8', 8);
define('ROL_GESTORAS', 9);

$rol = (int) ($_SESSION['id_rol'] ?? 0);

// Permitir acceso a Admin, Cadena y Rol 2
$rolesPermitidos = [ROL_ADMIN, ROL_CADENA, ROL_2, ROL_8, ROL_GESTORAS];
if (!in_array($rol, $rolesPermitidos, true)) {
  http_response_code(403);
  echo "<div class='container mt-4 alert alert-danger'>Acceso denegado.</div>";
  exit();
}

// Sidebars y botón volver (admin -> sidebar3, demás -> sidebar normal)
if ($rol === ROL_ADMIN) {
  include "../../../config/sidebar3.php";
} else {
  include "../../../config/sidebar.php"; 
}
include "../../../config/boton_volver.php";


/* ==================== DATA ==================== */

$chains = [];
$sqlChains = "SELECT id_usuario, usuario AS nit_cadena, nombre AS nombre_cadena
              FROM tbl_usuarios
              WHERE id_rol = ?
              ORDER BY nombre ASC";
if ($st = $conn->prepare($sqlChains)) {
  $rid = ROL_CADENA;
  $st->bind_param("i", $rid);
  $st->execute();
  $rs = $st->get_result();
  while ($row = $rs->fetch_assoc()) {
    $chains[] = $row;
  }
  $st->close();
}

$hoteles = [];
$sqlHoteles = "
SELECT
  h.id_hotel,
  h.nombre           AS nombre_hotel,
  h.ciudad,
  h.pais,
  h.nit,
  h.razon_social,
  h.estado_aprobacion,
  h.estado_firma,
  u.id_usuario       AS id_cadena,
  u.usuario          AS nit_cadena,
  u.nombre           AS nombre_cadena
FROM tbl_alojamiento_general h
LEFT JOIN tbl_usuarios u
  ON (u.id_usuario = h.id_usuario_creacion)
  OR (u.usuario = h.usuario_creacion)
ORDER BY h.id_hotel DESC";
if ($resH = $conn->query($sqlHoteles)) {
  while ($r = $resH->fetch_assoc()) {
    $hoteles[] = $r;
  }
}

$hotelesPorCadena = [];
foreach ($hoteles as $h) {
  $key = $h['id_cadena'] ? (string) $h['id_cadena'] : 'sin';
  if (!isset($hotelesPorCadena[$key]))
    $hotelesPorCadena[$key] = [];
  $hotelesPorCadena[$key][] = $h;
}

$contadorPorCadena = [];
foreach ($chains as $c) {
  $cid = (string) $c['id_usuario'];
  $contadorPorCadena[$cid] = isset($hotelesPorCadena[$cid]) ? count($hotelesPorCadena[$cid]) : 0;
}
$contadorSinCadena = isset($hotelesPorCadena['sin']) ? count($hotelesPorCadena['sin']) : 0;

function estadoFirmaFicha($estado): string
{
  $estadoFirma = strtoupper(trim((string) ($estado ?? '')));
  return $estadoFirma === 'FIRMADO' ? 'FIRMADO' : 'SIN FIRMA';
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Consulta Fichas Proveedores</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

  <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9;
        color: #1e293b;
    }

    .header-panel {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .card-glass {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }

    /* Tabs Estilizados */
    .nav-pills-custom .nav-link {
        color: #64748b;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 20px;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    .nav-pills-custom .nav-link.active {
        background-color: #3b82f6 !important;
        color: white !important;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }

    .badge-soft {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-weight: 500;
        padding: 0.4em 0.8em;
    }

    /* Accordion Corporativo */
    .accordion-cadena .card {
        border: 1px solid #e2e8f0;
        border-radius: 10px !important;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .accordion-cadena .card-header {
        background-color: white;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        padding: 1rem 1.25rem;
    }

    .accordion-cadena .card-header:hover {
        background-color: #f8fafc;
    }

    .accordion-cadena a {
        text-decoration: none;
        color: #1e293b;
        font-size: 1.05rem;
    }

    /* Tablas corporativas */
    .table-custom {
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .table-custom thead th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        padding: 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-custom tbody td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .btn-action {
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .dropdown-item {
        font-size: 0.85rem;
        padding: 8px 16px;
    }

    .text-cadena {
        color: #3b82f6;
        font-weight: 600;
    }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

</head>

<body class="bg-image">

  <div class="container mt-5 mb-5">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
      <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> Hotel eliminado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-12">

        <div class="header-panel text-center">
          <h1 class="text-white fw-bold mb-0">Consulta General de Proveedores</h1>
          <p class="text-white-50 mt-2 mb-0">Administre las fichas técnicas de cadenas y hoteles independientes</p>
        </div>

        <div class="card-glass shadow-lg">

          <ul class="nav nav-pills nav-pills-custom mb-4" role="tablist">
            <li class="nav-item">
              <button class="nav-link active me-2" data-bs-toggle="pill" data-bs-target="#pane-cadenas" type="button">
                <i class="fa-solid fa-network-wired me-2"></i>Cadenas 
                <span class="badge rounded-pill bg-white text-dark ms-1"><?php echo count($chains); ?></span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-hoteles" type="button">
                <i class="fa-solid fa-hotel me-2"></i>Listado Global de Hoteles 
                <span class="badge rounded-pill bg-white text-dark ms-1"><?php echo count($hoteles); ?></span>
              </button>
            </li>
          </ul>

          <div class="tab-content">

            <!-- PANE CADENAS -->
            <div class="tab-pane fade show active" id="pane-cadenas">
              <div class="accordion accordion-cadena" id="accordionCadenas">

                <?php foreach ($chains as $c):
                  $cid = (string) $c['id_usuario'];
                  $count = $contadorPorCadena[$cid] ?? 0;
                  ?>
                  <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between" 
                         data-bs-toggle="collapse" 
                         data-bs-target="#c<?php echo $cid; ?>" 
                         aria-expanded="false">
                      <h6 class="mb-0 fw-bold">
                        <i class="fa-solid fa-building-circle-check text-primary me-2"></i>
                        <?php echo htmlspecialchars($c['nombre_cadena'] ?: $c['nit_cadena']); ?>
                      </h6>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        <?php echo $count; ?> propiedades
                      </span>
                    </div>

                    <div id="c<?php echo $cid; ?>" class="collapse" data-bs-parent="#accordionCadenas">
                      <div class="card-body bg-light-subtle">

                        <?php if ($count > 0): ?>
                          <div class="table-responsive">
                            <table class="table table-hover table-custom bg-white shadow-sm rounded">
                              <thead>
                                <tr>
                                  <th>ID</th>
                                  <th>Hotel</th>
                                  <th>Razón Social</th>
                                  <th>NIT</th>
                                  <th>Ubicación</th>
                                  <th>Aprobación</th>
                                  <th>Firma</th>
                                  <th class="text-center">Acciones</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php foreach ($hotelesPorCadena[$cid] as $h): ?>
                                  <tr>
                                    <td><span class="text-muted">#<?php echo $h['id_hotel']; ?></span></td>
                                    <td class="fw-bold"><?php echo $h['nombre_hotel']; ?></td>
                                    <td class="small"><?php echo $h['razon_social']; ?></td>
                                    <td><?php echo $h['nit']; ?></td>
                                    <td><?php echo $h['ciudad']; ?>, <?php echo $h['pais']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $h['estado_aprobacion'] === 'APROBADO' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill">
                                            <?php echo $h['estado_aprobacion']; ?>
                                        </span>
                                    </td>
                                    <td>
                                      <span class="badge <?php echo estadoFirmaFicha($h['estado_firma']) === 'FIRMADO' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill">
                                        <?php echo estadoFirmaFicha($h['estado_firma']); ?>
                                      </span>
                                    </td>
                                    <td>
                                      <div class="dropdown d-flex justify-content-center">
                                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle btn-action" type="button" data-bs-toggle="dropdown">
                                            Gestionar
                                          </button>
                                          <ul class="dropdown-menu shadow border-0">
                                            <li><a class="dropdown-item" href="/facturacion/proveedores/vista/consultaHotel.php?id=<?php echo (int) $h['id_hotel']; ?>"><i class="fa fa-search me-2 text-primary"></i> Ver ficha</a></li>
                                            <li><a class="dropdown-item" href="/facturacion/proveedores/vista/editarHotel.php?id=<?php echo (int) $h['id_hotel']; ?>"><i class="fa fa-pencil me-2 text-warning"></i> Editar</a></li>
                                            <li><a class="dropdown-item" href="/facturacion/proveedores/vista/ficha_pdf_hotel.php?id=<?php echo (int) $h['id_hotel']; ?>" target="_blank"><i class="fa-regular fa-file-pdf me-2 text-danger"></i> Exportar PDF</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger" onclick="eliminarHotel(<?php echo (int) $h['id_hotel']; ?>)"><i class="fa fa-trash me-2"></i> Eliminar</button></li>
                                          </ul>
                                      </div>
                                    </td>
                                  </tr>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>

                        <?php else: ?>
                          <div class="alert alert-light border text-center py-4">
                            <i class="fa-solid fa-folder-open fa-2x opacity-25 mb-3"></i>
                            <p class="mb-0 text-muted">Esta cadena no tiene hoteles registrados actualmente.</p>
                          </div>
                        <?php endif; ?>

                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>

              </div>
            </div>

            <!-- PANE GLOBAL HOTELES -->
            <div class="tab-pane fade" id="pane-hoteles">
              <div class="row mb-3">
                <div class="col-12 col-md-6 col-lg-5">
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="search" id="filtroHotelesGlobal" class="form-control"
                      placeholder="Buscar por nombre del hotel o NIT">
                  </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover table-custom" id="tablaHotelesGlobal">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Hotel</th>
                      <th>NIT</th>
                      <th>Ubicación</th>
                      <th>Cadena</th>
                      <th>Aprobación</th>
                      <th>Firma</th>
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($hoteles as $h): ?>
                      <tr>
                        <td><span class="text-muted">#<?php echo $h['id_hotel']; ?></span></td>
                        <td class="fw-bold hotel-nombre"><?php echo htmlspecialchars($h['nombre_hotel'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="hotel-nit"><?php echo htmlspecialchars($h['nit'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $h['ciudad']; ?></td>
                        <td class="text-cadena"><?php echo $h['nombre_cadena'] ?: 'Independiente'; ?></td>
                        <td>
                            <span class="badge <?php echo $h['estado_aprobacion'] === 'APROBADO' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill">
                                <?php echo $h['estado_aprobacion']; ?>
                            </span>
                        </td>
                        <td class="small">
                          <span class="badge <?php echo estadoFirmaFicha($h['estado_firma']) === 'FIRMADO' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill">
                            <?php echo estadoFirmaFicha($h['estado_firma']); ?>
                          </span>
                        </td>
                        <td>
                          <div class="dropdown d-flex justify-content-center">
                              <button class="btn btn-sm btn-outline-secondary dropdown-toggle btn-action" type="button" data-bs-toggle="dropdown">
                                Gestionar
                              </button>
                            <ul class="dropdown-menu shadow border-0">
                              <li><a class="dropdown-item" href="/facturacion/proveedores/vista/consultaHotel.php?id=<?php echo (int) $h['id_hotel']; ?>"><i class="fa fa-search me-2 text-primary"></i> Ver ficha</a></li>
                              <li><a class="dropdown-item" href="/facturacion/proveedores/vista/editarHotel.php?id=<?php echo (int) $h['id_hotel']; ?>"><i class="fa fa-pencil me-2 text-warning"></i> Editar</a></li>
                              <li><a class="dropdown-item" href="/facturacion/proveedores/vista/ficha_pdf_hotel.php?id=<?php echo (int) $h['id_hotel']; ?>" target="_blank"><i class="fa-regular fa-file-pdf me-2 text-danger"></i> Exportar PDF</a></li>
                              <li><hr class="dropdown-divider"></li>
                              <li><button class="dropdown-item text-danger" onclick="eliminarHotel(<?php echo (int) $h['id_hotel']; ?>)"><i class="fa fa-trash me-2"></i> Eliminar</button></li>
                            </ul>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <div id="sinResultadosHotelesGlobal" class="alert alert-light border text-center py-4 d-none">
                  <i class="fa-solid fa-magnifying-glass fa-2x opacity-25 mb-3"></i>
                  <p class="mb-0 text-muted">No se encontraron hoteles con ese nombre o NIT.</p>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

  </div>

  <script>
    function eliminarHotel(idHotel) {
      if (confirm("¿Seguro deseas eliminar este hotel? Esta acción no se puede deshacer.")) {
        window.location.href = "/facturacion/proveedores/controlador/eliminarHotel.php?id=" + idHotel;
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const filtro = document.getElementById('filtroHotelesGlobal');
      const tabla = document.getElementById('tablaHotelesGlobal');
      const sinResultados = document.getElementById('sinResultadosHotelesGlobal');

      if (!filtro || !tabla) return;

      filtro.addEventListener('input', function () {
        const busqueda = filtro.value.trim().toLowerCase();
        let visibles = 0;

        tabla.querySelectorAll('tbody tr').forEach(function (fila) {
          const nombre = (fila.querySelector('.hotel-nombre')?.textContent || '').toLowerCase();
          const nit = (fila.querySelector('.hotel-nit')?.textContent || '').toLowerCase();
          const coincide = nombre.includes(busqueda) || nit.includes(busqueda);

          fila.style.display = coincide ? '' : 'none';
          if (coincide) visibles++;
        });

        if (sinResultados) {
          sinResultados.classList.toggle('d-none', visibles > 0);
        }
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Script para manejar el overflow en dropdowns dentro de contenedores responsivos
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        var savedAncestors = [];

        el.addEventListener('show.bs.dropdown', function () {
          savedAncestors = [];
          var node = el.parentElement;
          while (node && node !== document.body) {
            var computed = window.getComputedStyle(node);
            if (computed.overflow !== 'visible' || computed.overflowX !== 'visible' || computed.overflowY !== 'visible') {
              savedAncestors.push({ el: node, overflow: node.style.overflow });
              node.style.overflow = 'visible';
            }
            node = node.parentElement;
          }
        });

        el.addEventListener('hidden.bs.dropdown', function () {
          savedAncestors.forEach(function (item) {
            item.el.style.overflow = item.overflow;
          });
          savedAncestors = [];
        });
      });
    });
  </script>
</body>

</html>
