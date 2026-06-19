<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
  // Incluir el sidebar para el administrador
  include "../../../config/sidebar3.php";
  include "../../../config/boton_volver.php"; 
} else {
  // Incluir el sidebar normal para usuarios no administradores
  include "../../../config/sidebar.php";
  include "../../../config/boton_volver.php";
}
?>

<?php


$consulta = ConsultarHotel($_GET['id_causacion']);
function ConsultarHotel($id_causacion)
{
  include "../../../config/conexion.php";
  $sentencia = "SELECT c.*, p.nombre AS nombre_proveedor 
                FROM tbl_causacion c 
                JOIN tbl_proveedores p ON c.id_proveedor = p.id_proveedor 
                WHERE c.id_causacion = '" . $id_causacion . "'";
  $ejecutar = mysqli_query($conn, $sentencia);
  $mostrar = $ejecutar->fetch_assoc();
  return [
    $mostrar['id_causacion'],
    $mostrar['nombre_proveedor'],
    $mostrar['numero_factura'],
    $mostrar['fecha_emision'],
    $mostrar['fecha_vencimiento'],
    $mostrar['localizador'],
    $mostrar['tipo_moneda'],
    $mostrar['iva'],
    $mostrar['valorpagar']
  ];
}
?>



<!doctype html>
<html lang="es">





<head>
 <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Tu CSS -->
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <!-- jQuery (solo si peticion.js lo usa) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <!-- Script propio -->
  <title>Modificar Causación</title>

  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    .form-card { background: white; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); padding: 2rem; }
    .form-label { font-weight: 600; font-size: 0.78rem; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
    .form-control, .form-select { border-radius: 8px; font-size: 0.88rem; border-color: #e2e8f0; }
    .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .form-control[readonly] { background-color: #f8fafc; color: #94a3b8; }
    .page-header { background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%); border-radius: 12px; padding: 1.5rem 2rem; color: white; margin-bottom: 2rem; }
    .file-link { display: inline-flex; align-items: center; gap: 6px; color: #2563eb; font-size: 0.85rem; text-decoration: none; }
    .file-link:hover { text-decoration: underline; }
    .section-divider { border: none; border-top: 1px solid #e2e8f0; margin: 1.25rem 0; }
  </style>

</head>

<body>
  <div class="container-fluid px-4 mt-4" style="padding: 5% !important; padding-top: 0px !important;">

    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h4 class="fw-bold mb-0">Modificar Causación</h4>
        <div class="opacity-75 mt-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($consulta[1]) ?></div>
      </div>
      <i class="fas fa-file-invoice fa-2x opacity-50"></i>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-lg-8">
        <div class="form-card">
          <form action="cargaDriveProveedoresTuristicosSP.php" method="post" enctype="multipart/form-data">

            <input name="id_causacion" type="hidden" value="<?php echo $consulta[0] ?>">

            <div class="row g-3">

              <div class="col-12">
                <label class="form-label">Proveedor</label>
                <input readonly name="proveedor" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[1]) ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Número de Factura</label>
                <input name="cop" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[2]) ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Localizador</label>
                <input name="localizador" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[5]) ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha Emisión</label>
                <input name="novedad" type="date" class="form-control" value="<?php echo $consulta[3] ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha Vencimiento</label>
                <input name="fecha" type="date" class="form-control" value="<?php echo $consulta[4] ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">Tipo Moneda</label>
                <input name="tipo_moneda" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[6]) ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">IVA</label>
                <input name="iva" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[7]) ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">Valor Facturado</label>
                <input name="valorpagar" type="text" class="form-control" value="<?php echo htmlspecialchars($consulta[8]) ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                  <option value="Pendiente" <?= $consulta[6] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                  <option value="Causado" <?= $consulta[6] === 'Causado' ? 'selected' : '' ?>>Causado</option>
                  <option value="Pagado" <?= $consulta[6] === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                  <option value="Soporte enviado y pagado" <?= $consulta[6] === 'Soporte enviado y pagado' ? 'selected' : '' ?>>Soporte enviado y pagado</option>
                </select>
              </div>

            </div>

            <hr class="section-divider">

            <div class="mb-4">
              <label class="form-label">Subir documento soporte de pago <span class="text-danger">*</span></label>
              <input type="file" name="soporteProveedor" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
              <i class="fas fa-save me-2"></i>Guardar Cambios
            </button>

          </form>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>