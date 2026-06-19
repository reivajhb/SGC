<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// Verificar si el usuario es administrador o si es el usuario con ID 8
if ((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8)) {
  if ($_SESSION['id_rol'] == 1) {
    include "../../facturacion/config/sidebar3.php"; // Sidebar admin
    include "../../facturacion/config/boton_volver.php"; // Botón volver admin
  } else {
    include "../../facturacion/config/sidebar.php"; // Sidebar básico para el usuario 8
    include "../../facturacion/config/boton_volver.php"; // Botón volver usuario 8
  }
} else {
  echo "<script>alert('Acceso denegado.'); window.location.href = '../../buscarProveedor.php';</script>";
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">
  <title>Facturación Proveedores Administrativos</title>
  <style>
    body { background-color: #f7f9fc; padding-top: 10px; font-family: 'Inter', sans-serif; }
    .custom-header { background-color: #007bff !important; border-radius: 12px; padding: 20px; color: white; text-align: center; box-shadow: 0 6px 20px rgba(0,0,0,0.2); margin-bottom: 25px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
    .stat-card { border: none; border-radius: 12px; padding: 1.25rem; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .bg-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .table-responsive-scroll { max-height: 550px; overflow: auto; border-radius: 12px; border: 1px solid #dee2e6; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .table { width: 100%; margin-bottom: 0; }
    .table thead th { position: sticky; top: 0; z-index: 10; background-color: #1a3a5c !important; color: white !important; padding: 12px 8px; text-transform: uppercase; font-size: 0.7rem; border: none; }
    .table td { vertical-align: middle; font-size: 0.78rem; padding: 8px !important; }
  </style>
</head>

<body>

<?php
// ==== CÁLCULO DE TOTALES (USANDO tbl_pagos + tbl_proveedores ADMINISTRATIVOS) ====

$from_date = !empty($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : null;
$to_date   = !empty($_GET['to_date'])   ? mysqli_real_escape_string($conn, $_GET['to_date'])   : null;

// Inicializar totales
$numero  = number_format(0, 0, ",", ".");
$numero2 = number_format(0, 0, ",", ".");

if ($from_date && $to_date) {
  // Total pagado (usamos total_pagar para ser coherentes con la nueva estructura)
  $consultaSum = "
      SELECT SUM(p.total_pagar) AS total
      FROM tbl_pagos p
      JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
      WHERE prov.tipo_proveedor = 'Administrativo'
        AND p.fecha_pago BETWEEN '$from_date' AND '$to_date'
        AND p.estado = 'Pagado'
  ";
  $ejecutarSum = mysqli_query($conn, $consultaSum);
  $mostrarSum  = mysqli_fetch_assoc($ejecutarSum);
  $total  = $mostrarSum['total'] ?? 0;
  $numero = number_format($total, 0, ",", ".");

  // Total pendiente
  $consultaSum2 = "
      SELECT SUM(p.total_pagar) AS total
      FROM tbl_pagos p
      JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
      WHERE prov.tipo_proveedor = 'Administrativo'
        AND p.fecha_pago BETWEEN '$from_date' AND '$to_date'
        AND p.estado = 'Pendiente'
  ";
  $ejecutarSum2 = mysqli_query($conn, $consultaSum2);
  $mostrarSum2  = mysqli_fetch_assoc($ejecutarSum2);
  $total2  = $mostrarSum2['total'] ?? 0;
  $numero2 = number_format($total2, 0, ",", ".");
}
?>

  <div class="container-fluid px-4 mt-4" style="padding: 5% !important; padding-top: 0px !important;">
  <div class="custom-header text-uppercase">
    <h2 class="fw-bold mb-0">Consulta Pagos Proveedores Administrativos</h2>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="stat-card bg-green">
        <div class="small opacity-75 fw-medium">Total Pagado</div>
        <div class="h3 fw-bold m-0">$<?php echo $numero; ?></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="stat-card bg-red">
        <div class="small opacity-75 fw-medium">Pendiente por Pagar</div>
        <div class="h3 fw-bold m-0">$<?php echo $numero2; ?></div>
      </div>
    </div>
  </div>

  <div class="filter-card">
    <form action="" method="GET" class="row g-3 align-items-end">
      <div class="col-md-2">
        <label class="form-label fw-bold small">DEL DÍA</label>
        <input type="date" name="from_date" value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : ''; ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label fw-bold small">HASTA EL DÍA</label>
        <input type="date" name="to_date" value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : ''; ?>" class="form-control">
      </div>
      <div class="col-md-5">
        <?php
        $consulta_proveedores = "SELECT id_proveedor, nit_identificacion AS nit, nombre FROM tbl_proveedores WHERE tipo_proveedor = 'Administrativo' ORDER BY nombre ASC";
        $ejecutar_proveedores = mysqli_query($conn, $consulta_proveedores);
        ?>
        <label class="form-label fw-bold small">PROVEEDOR</label>
        <select class="form-select select2" id="buscarh" name="id_proveedoradmin_fo">
          <option value="">-- Selecciona o busca un proveedor --</option>
          <?php while ($proveedor = mysqli_fetch_assoc($ejecutar_proveedores)): ?>
            <option value="<?php echo $proveedor['id_proveedor']; ?>" <?php echo (isset($_GET['id_proveedoradmin_fo']) && $_GET['id_proveedoradmin_fo'] == $proveedor['id_proveedor']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($proveedor['nit'] . " - " . $proveedor['nombre']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100 fw-bold">BUSCAR</button>
      </div>
      <div class="col-md-1">
        <a class="btn btn-success w-100" href="../../facturacion/genexcel/ExcelPagosProveedoresAdministrativosPorFecha.php?from_date=<?php echo $_GET['from_date'] ?? ''; ?>&to_date=<?php echo $_GET['to_date'] ?? ''; ?>&id_proveedoradmin_fo=<?php echo $_GET['id_proveedoradmin_fo'] ?? ''; ?>" title="Descargar Excel">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
        </a>
      </div>
    </form>
  </div>

  <div class="table-responsive-scroll shadow-sm">
    <table class="table table-hover mb-0" id="table_id">
      <thead>
          <tr>
            <th scope="col">Acciones</th>
            <th scope="col">Nit</th>
            <th scope="col">Proveedor / Locación</th>
            <th scope="col">Valor</th>
            <th scope="col">Total a pagar</th>
            <th scope="col">Novedad</th>
            <th scope="col">Fecha</th>
            <th scope="col">Archivo</th>
            <th scope="col">Estado</th>
            <th scope="col">Soporte de Pago</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // ==== CONSULTA PRINCIPAL (IGUAL A TU TABLA, PERO FILTRADA POR FECHA / PROVEEDOR) ====

        if (isset($_GET['from_date']) && isset($_GET['to_date'])) {

          $from_date_q = mysqli_real_escape_string($conn, $_GET['from_date']);
          $to_date_q   = mysqli_real_escape_string($conn, $_GET['to_date']);
          $id_proveedoradmin = isset($_GET['id_proveedoradmin_fo'])
              ? mysqli_real_escape_string($conn, $_GET['id_proveedoradmin_fo'])
              : '';

          if (!empty($from_date_q) && !empty($to_date_q)) {

            $query = "
                SELECT 
                    p.id_pago,
                    p.valor_pagado AS valor,
                    p.novedad,
                    p.fecha_pago AS fecha,
                    p.estado,
                    p.archivo_factura AS archivo,
                    p.archivo_soporte AS soporteAdmin,
                    p.locacion,
                    p.identificacion,
                    p.total_pagar,
                    prov.email_contabilidad
                FROM 
                    tbl_pagos p
                JOIN 
                    tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
                WHERE 
                    prov.tipo_proveedor = 'Administrativo'
                  AND p.fecha_pago BETWEEN '$from_date_q' AND '$to_date_q'
            ";

            if (!empty($id_proveedoradmin)) {
              $query .= " AND p.id_proveedor = '$id_proveedoradmin'";
            }

            $query .= " ORDER BY p.fecha_pago DESC";

            $query_run = mysqli_query($conn, $query);

            if ($query_run && mysqli_num_rows($query_run) > 0) {
              while ($mostrarPaAdmin = mysqli_fetch_assoc($query_run)) {

                $id_pago_ad = $mostrarPaAdmin['id_pago'];
                $valorApagar = $mostrarPaAdmin['valor'] ?? 0;
                $numero = number_format($valorApagar, 0, ",", ".");
                $totalPagar = number_format($mostrarPaAdmin['total_pagar'] ?? 0, 0, ",", ".");
                ?>
                <tr>
                  <td>
                    <div class="d-flex flex-column align-items-center">
                      <!-- Editar -->
                      <a href="../../modificarPagosAdministrativos.php?id_pago=<?php echo $id_pago_ad; ?>">
                        <button type="button" class="btn btn-success btn-sm mb-2" title="Editar">
                          <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'
                               fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
                            <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
                          </svg>
                        </button>
                      </a>

                      <!-- Eliminar con modal -->
                      <button type="button" class="btn btn-danger btn-sm mb-2"
                              data-toggle="modal" data-target="#modalEliminar<?php echo $id_pago_ad; ?>"
                              title="Eliminar">
                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'
                             fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'>
                          <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
                        </svg>
                      </button>

                      <!-- Enviar correo -->
                      <a href="../../formularioenviopagosadministrativos.php?id_pago=<?php echo $id_pago_ad; ?>&email=<?php echo urlencode($mostrarPaAdmin['email_contabilidad']); ?>">
                        <button type="button" class="btn btn-primary btn-sm mb-2" title="Enviar correo">
                          <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'
                               fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
                            <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
                            <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
                          </svg>
                        </button>
                      </a>
                    </div>

                    <!-- MODAL ELIMINAR -->
                    <div class="modal fade" id="modalEliminar<?php echo $id_pago_ad; ?>" tabindex="-1"
                         role="dialog" aria-labelledby="modalEliminarLabel<?php echo $id_pago_ad; ?>" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">¿Estás seguro de que deseas eliminar este registro?</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <table class="table">
                              <thead>
                                <tr><th>Id Pago</th><th>Proveedor</th><th>Valor</th></tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td><?php echo htmlspecialchars($mostrarPaAdmin['id_pago']); ?></td>
                                  <td><?php echo htmlspecialchars($mostrarPaAdmin['locacion']); ?></td>
                                  <td>$<?php echo $numero; ?></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <a href="../../EliminarPagosAdministrativos.php?id_pago=<?php echo htmlspecialchars($id_pago_ad); ?>">
                              <button type="button" class="btn btn-danger">Eliminar</button>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>

                  </td>

                  <td><?php echo htmlspecialchars($mostrarPaAdmin['identificacion'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($mostrarPaAdmin['locacion'] ?? 'N/A'); ?></td>
                  <td>$<?php echo $numero; ?></td>
                  <td>$<?php echo $totalPagar; ?></td>
                  <td><?php echo htmlspecialchars($mostrarPaAdmin['novedad'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($mostrarPaAdmin['fecha'] ?? 'N/A'); ?></td>
                  <td>
                    <a href="<?php echo htmlspecialchars($mostrarPaAdmin['archivo'] ?? '#'); ?>" target="_blank">
                      <img width="50" height="50" src="/facturacion/img/factura.png" alt="Soporte">
                    </a>
                  </td>
                  <td><?php echo htmlspecialchars($mostrarPaAdmin['estado'] ?? 'N/A'); ?></td>
                  <td>
                    <a href="<?php echo htmlspecialchars($mostrarPaAdmin['soporteAdmin'] ?? '#'); ?>" target="_blank">
                      <img width="50" height="50" src="/facturacion/img/factura.png" alt="Soporte">
                    </a>
                  </td>
                </tr>
                <?php
              }
            } else {
              ?>
              <tr>
                <td colspan="10" class="text-center">No se encontraron pagos.</td>
              </tr>
              <?php
            }
          }
        }
        ?>
        </tbody>
      </table>
  </div>
  <div class="py-5"></div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: "Buscar o seleccionar proveedor",
        allowClear: true,
        width: '100%'
      });
    });
  </script>

</body>
</html>
