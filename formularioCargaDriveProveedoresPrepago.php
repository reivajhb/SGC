<?php 
include "seguridad.php";
include "sidebar.php";

$consultaProveedor = Consultarproveedor($_GET['identificacion']);

function Consultarproveedor($identificacion) {
    include 'conexion.php'; 
    $sentencia = "SELECT * FROM tbl_proveedores_ocasionales WHERE identificacion = '".$identificacion."'"; 
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrarProveedor = $ejecutar->fetch_assoc();

    if (!$mostrarProveedor) {
        echo '<script>
                  alert("No se encontró ningún proveedor");
                  window.location = "buscarProveedorPrepago.php";
              </script>';
    }

    return [
        $mostrarProveedor['id_proveedor_ocs'],
        $mostrarProveedor['identificacion'],
        $mostrarProveedor['nombre'],
        $mostrarProveedor['email_proveedor'],
    ];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relación Anticipos Proveedores</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 30px;
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
        }
        .form-control, .form-control:focus {
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 0.25rem;
            padding: 10px 20px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 0.25rem;
            padding: 10px 20px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .card-body {
            background-color: #fff;
            border-radius: 0.25rem;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
        }
        .modal-content {
            border-radius: 0.25rem;
        }
        .spinner-border {
            margin-right: 10px;
        }
        /* Ajustar el ancho del contenedor del formulario */
        .form-container {
            max-width: 800px; /* Ajusta el ancho máximo según tus necesidades */
            margin: 0 auto; /* Centra el contenedor horizontalmente */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header text-center">
                <h2>Anticipo Proveedor: <?php echo $consultaProveedor[2]; ?></h2>
            </div>
            <div class="card-body form-container">
                <h4 class="text-center">Todos los campos deben estar diligenciados</h4>
                <form action="cargaDriveProveedoresPrepago.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fecha_hora_colombia">Fecha de registro*</label>
                        <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="identificacion">Nit o Cédula*</label>
                        <input readonly name="identificacion" type="number" class="form-control" id="identificacion" value="<?php echo $consultaProveedor[1]; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="proveedor">Nombre Proveedor Ocasional*</label>
                        <input readonly name="proveedor" type="text" class="form-control" id="proveedor" value="<?php echo $consultaProveedor[2]; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email_proveedor">Correo Proveedor*</label>
                        <input readonly name="email_proveedor" type="email" class="form-control" id="email_proveedor" value="<?php echo $consultaProveedor[3]; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="localizador">Localizador*</label>
                        <input name="localizador" type="text" class="form-control" id="localizador" placeholder="Ingrese el localizador" required>
                    </div>
                    <div class="form-group">
                        <label for="num_factura">No de Factura</label>
                        <input name="num_factura" type="text" class="form-control" id="num_factura" placeholder="Ingrese el Número de factura">
                    </div>
                    <div class="form-group">
                        <label for="concepto">Concepto*</label>
                        <input name="concepto" type="text" class="form-control" id="concepto" placeholder="Ingrese el concepto" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Información Adicional*</label>
                        <textarea name="descripcion" class="form-control" id="descripcion" placeholder="Ingrese alguna descripción" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="moneda">Tipo de Moneda</label>
                        <select class="form-control" name="moneda" id="moneda" aria-label="Tipo de Moneda">
                            <option value="COP">COP</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor del cheque o Transferencia*</label>
                        <input name="valor" type="number" class="form-control" id="valor" placeholder="Valor del cheque o Transferencia" required step="any">
                    </div>
                    <div class="form-group">
                        <label for="usuario">Asesor</label>
                        <input readonly name="usuario" type="text" class="form-control" id="usuario" value="<?php echo $_SESSION['usuario']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_ingreso">Fecha de entrada de los pasajeros*</label>
                        <input name="fecha_ingreso" type="date" class="form-control" id="fecha_ingreso" placeholder="Ingrese la fecha del registro" required>
                    </div>
                    <div class="form-group">
                        <label for="certificacion">Anexar certificación bancaria*</label>
                        <input type="file" name="certificacion" id="certificacion" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_salida">Fecha de salida de los pasajeros*</label>
                        <input name="fecha_salida" type="date" class="form-control" id="fecha_salida" placeholder="Ingrese la fecha del registro" required>
                    </div>
                    <div class="form-group">
                        <input name="estado" type="hidden" class="form-control" value="Pendiente" required>
                    </div>
                    <div class="form-group">
                        <label for="cuentadecobro">Anexar cuenta de cobro*</label>
                        <input type="file" name="cuentadecobro" id="cuentadecobro" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_lmtpago">Fecha límite de pago*</label>
                        <input name="fecha_lmtpago" type="date" class="form-control" id="fecha_lmtpago" placeholder="Fecha límite de pago" required>
                    </div>
                    <button id="guardarBtn" type="submit" class="btn btn-primary btn-block">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pantalla de espera modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Guardando...</p>
                </div>
            </div>
        </div>
    </div>    

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $("#guardarBtn").click(function() {
                $("#loadingModal").modal("show");
                $.ajax({
                    type: "POST",
                    url: "guardar_registro.php",
                    success: function(response) {
                        $("#loadingModal").modal("hide");
                    }
                });
            });
        });

        var fechaHoraActualDispositivo = new Date();
        var diferenciaHorariaColombia = -5;
        fechaHoraActualDispositivo.setHours(fechaHoraActualDispositivo.getHours() + diferenciaHorariaColombia);
        var fechaHoraColombia = fechaHoraActualDispositivo.toISOString().slice(0, 16);
        var inputFechaHoraColombia = document.getElementById("fecha_hora_colombia");
        inputFechaHoraColombia.value = fechaHoraColombia;
    </script>
</body>
</html>
