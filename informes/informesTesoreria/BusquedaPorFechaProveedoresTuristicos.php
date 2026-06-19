<?php
include "../../facturacion/config/seguridad.php";
include('../../facturacion/config/conexion.php');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Verificar si el usuario es administrador o si es el usuario con ID 8
if ((isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 8)) {
  // Si cumple la condición, incluir el sidebar correspondiente
  if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include('../../facturacion/config/sidebar3.php');
    include "../../facturacion/config/boton_volver.php"; // Sidebar admin
  } else {
    include('../../facturacion/config/sidebar.php');
    include "../../facturacion/config/boton_volver.php"; // Sidebar básico para el usuario 8
  }
} else {
  // Si no cumple, redirigir a buscarProveedor
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
  <title>Facturación Proveedores Turísticos</title>
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
  <div class="container-fluid px-4 mt-4" style="padding: 5% !important; padding-top: 0px !important;">
  <div class="custom-header text-uppercase">
    <h2 class="fw-bold mb-0">Consulta Pagos Proveedores Turísticos</h2>
  </div>
  <?php
  // ==== RESÚMENES SUPERIORES (TOTALES) ====

  // Sanitizar fechas desde GET
  $from_date = !empty($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : null;
  $to_date   = !empty($_GET['to_date'])   ? mysqli_real_escape_string($conn, $_GET['to_date'])   : null;

  // Si no hay fechas aún, evita consultas con BETWEEN NULL
  if ($from_date && $to_date) {

    // Total pagado
    $consultaSum = "
      SELECT SUM(p.total_pagar) AS total
      FROM tbl_pagos p
      JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
      WHERE prov.tipo_proveedor = 'Turístico'
        AND p.fecha_pago BETWEEN '$from_date' AND '$to_date'
        AND p.estado = 'Pagado'
    ";
    $ejecutarSum = mysqli_query($conn, $consultaSum);
    $mostrarSum  = mysqli_fetch_array($ejecutarSum);
    $total  = $mostrarSum['total'] ?? 0;
    $numero = number_format($total, 0, ",", ".");

    // Total pendiente
    $consultaSum2 = "
      SELECT SUM(p.total_pagar) AS total
      FROM tbl_pagos p
      JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
      WHERE prov.tipo_proveedor = 'Turístico'
        AND p.fecha_pago BETWEEN '$from_date' AND '$to_date'
        AND p.estado = 'Pendiente'
    ";
    $ejecutarSum2 = mysqli_query($conn, $consultaSum2);
    $mostrarSum2  = mysqli_fetch_array($ejecutarSum2);
    $total2  = $mostrarSum2['total'] ?? 0;
    $numero2 = number_format($total2, 0, ",", ".");
  } else {
    // Si no se han enviado fechas, totales en 0
    $numero  = number_format(0, 0, ",", ".");
    $numero2 = number_format(0, 0, ",", ".");
  }
  ?>

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
        $consulta_proveedores = "SELECT id_proveedor, nit_identificacion AS nit, nombre AS nom_proveedor FROM tbl_proveedores WHERE tipo_proveedor = 'Turístico' ORDER BY nombre ASC";
        $ejecutar_proveedores = mysqli_query($conn, $consulta_proveedores);
        ?>
        <label class="form-label fw-bold small">PROVEEDOR</label>
        <select class="form-select select2" id="buscarh" name="id_proveedor_fo">
          <option value="">-- Selecciona o busca un proveedor --</option>
          <?php while ($proveedor = mysqli_fetch_assoc($ejecutar_proveedores)): ?>
            <option value="<?php echo $proveedor['id_proveedor']; ?>" <?php echo (isset($_GET['id_proveedor_fo']) && $_GET['id_proveedor_fo'] == $proveedor['id_proveedor']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($proveedor['nit'] . " - " . $proveedor['nom_proveedor']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100 fw-bold">BUSCAR</button>
      </div>
      <div class="col-md-1">
        <a class="btn btn-success w-100" href="../../facturacion/genexcel/ExcelPagosProveedoresPorFecha.php?from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>&id_proveedor_fo=<?php echo urlencode($_GET['id_proveedor_fo'] ?? ''); ?>" title="Descargar Excel">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
        </a>
      </div>
    </form>
  </div>

  <div class="table-responsive-scroll shadow-sm">
    <table class="table table-hover mb-0">
      <thead>
            <tr>
              <th scope="col">Editar</th>
              <th scope="col">Eliminar</th>
              <th scope="col">Enviar Email</th>
              <th scope="col">Nit</th>
              <th scope="col">Proveedor</th>
              <th scope="col">Valor a pagar</th>
              <th scope="col">Novedad</th>
              <th scope="col">Fecha</th>
              <th scope="col">Archivo</th>
              <th scope="col">Estado</th>
              <th scope="col">Soporte</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // ==== CONSULTA PRINCIPAL DEL INFORME ====
            if (isset($_GET['from_date']) && isset($_GET['to_date'])) {

              $from_date_q = mysqli_real_escape_string($conn, $_GET['from_date']);
              $to_date_q   = mysqli_real_escape_string($conn, $_GET['to_date']);
              $id_proveedor_fo = isset($_GET['id_proveedor_fo'])
                ? mysqli_real_escape_string($conn, $_GET['id_proveedor_fo'])
                : '';

              if (!empty($from_date_q) && !empty($to_date_q)) {

                // ✅ Prepared Statement (evita inyección, mantiene lógica)
                $query = "
                  SELECT 
                    p.id_pago,
                    p.valor_pagado,
                    p.novedad,
                    p.fecha_pago,
                    p.estado,
                    p.archivo_factura,
                    p.archivo_soporte,
                    p.total_pagar,
                    prov.id_proveedor,
                    prov.nit_identificacion AS nit,
                    prov.nombre AS proveedor,
                    prov.email_contabilidad,
                    prov.email_cartera
                  FROM tbl_pagos p
                  JOIN tbl_proveedores prov ON p.id_proveedor = prov.id_proveedor
                  WHERE prov.tipo_proveedor = 'Turístico'
                    AND p.fecha_pago BETWEEN ? AND ?
                ";

                if ($id_proveedor_fo !== '') {
                  $query .= " AND p.id_proveedor = ?";
                }

                $query .= " ORDER BY p.fecha_pago DESC";

                $stmt = mysqli_prepare($conn, $query);

                if ($id_proveedor_fo !== '') {
                  mysqli_stmt_bind_param($stmt, "sss", $from_date_q, $to_date_q, $id_proveedor_fo);
                } else {
                  mysqli_stmt_bind_param($stmt, "ss", $from_date_q, $to_date_q);
                }

                mysqli_stmt_execute($stmt);
                $query_run = mysqli_stmt_get_result($stmt);

                if ($query_run && mysqli_num_rows($query_run) > 0) {
                  while ($mostrarProveedor = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                      <?php
                      // Botón editar
                      echo "<td>
                              <a href='../../modificarProveedoresTuristicos.php?id_pago=" . htmlspecialchars($mostrarProveedor['id_pago']) . "'>
                                <button type='button' class='btn btn-success'>
                                  <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
                                    <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
                                  </svg>
                                </button>
                              </a>
                            </td>";

                      // Botón eliminar
                      echo "<td>
                              <a href='../../EliminarProveedoresTuristicos.php?id_pago=" . htmlspecialchars($mostrarProveedor['id_pago']) . "'>
                                <button type='button' class='btn btn-danger'>
                                  <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'>
                                    <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
                                  </svg>
                                </button>
                              </a>
                            </td>";

                      // Botón enviar email
                      echo "<td>
                              <a href='../../formularioenviocorreoproveedor.php?id_pago=" . htmlspecialchars($mostrarProveedor['id_pago']) . "&email=" . urlencode($mostrarProveedor['email_contabilidad']) . "'>
                                <button type='button' class='btn btn-primary'>
                                  <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
                                    <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
                                    <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
                                  </svg>
                                </button>
                              </a>
                            </td>";
                      ?>

                      <td><?php echo htmlspecialchars($mostrarProveedor['nit']); ?></td>
                      <td><?php echo htmlspecialchars($mostrarProveedor['proveedor']); ?></td>

                      <td>
                        $<?php
                        $valorApagar = $mostrarProveedor['total_pagar'] ?? $mostrarProveedor['valor_pagado'];
                        $numeroDetalle = number_format($valorApagar, 0, ",", ".");
                        echo $numeroDetalle;
                        ?>
                      </td>

                      <td><?php echo htmlspecialchars($mostrarProveedor['novedad']); ?></td>
                      <td><?php echo htmlspecialchars($mostrarProveedor['fecha_pago']); ?></td>
                      <td>
                        <a href="<?php echo htmlspecialchars($mostrarProveedor['archivo_factura']); ?>" target="_blank">
                          <img width="50" height="50" src="/facturacion/img/factura.png" alt="Factura" />
                        </a>
                      </td>
                      <td><?php echo htmlspecialchars($mostrarProveedor['estado']); ?></td>
                      <td>
                        <a href="<?php echo htmlspecialchars($mostrarProveedor['archivo_soporte']); ?>" target="_blank">
                          <img width="50" height="50" src="/facturacion/img/factura.png" alt="Soporte" />
                        </a>
                      </td>
                    </tr>
                    <?php
                  }
                } else {
                  ?>
                  <tr>
                    <td colspan="11" class="text-center">No se encontraron resultados</td>
                  </tr>
                  <?php
                }

                mysqli_stmt_close($stmt);
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
