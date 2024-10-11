<?php
// Incluir archivos de conexión y seguridad
include "seguridad.php";
include "conexion.php";
?>
<!doctype html>
<html lang="es">
<?php 
include "sidebar.php"
 ?>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>
    <!-- Busqueda Select hoteles -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Liberia graficas-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Facturacion Proveedores!</title>

  </head>
<body>
    <div class="container px-4">
    <div class="row gx-5 align-items-center">
        <div class="col-auto">
            <div class="p-3"><h2>Información pagos causaciones</h2></div>
        </div>
        <div class="col-auto">
            <div class="container">
                <button class="btn btn-success">
                    <a class="text-light" href="ExcelPagosCausacion.php">Descargar información
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                    </a>
                </button>
            </div>
        </div>
    </div>
</div>
<br>

<?php 


error_reporting(0); // Desactivar la visualización de errores

// Tu código PHP aquí



// Consulta para obtener todos los registros de la tabla
$fechaInicio = $_POST['fecha_inicio'];
$fechaFin = $_POST['fecha_fin'];
$nit_proveedor = $_POST['nit_proveedor'];

$consulta = "SELECT * FROM tbl_causacion WHERE fecha_emision BETWEEN '$fechaInicio' AND '$fechaFin' AND nit_proveedor = '$nit_proveedor'";
$ejecutar = mysqli_query($conn, $consulta);

// Función para formatear moneda
function formatoMoneda($valor) {
    return number_format($valor, 0, ",", ".");
}
?>
<div class="mx-auto" style="width: 800px;">
    <div class="container" style="width: 100%;">
        <form method="post" class="row align-items-center">
            <div class="col-auto">
                <div class="form-group mr-2">
                    <label for="fecha_inicio" class="mr-2">Fecha de inicio:</label>
                    <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mr-2">
                    <label for="fecha_fin" class="mr-2">Fecha de fin:</label>
                    <input class="form-control" type="date" id="fecha_fin" name="fecha_fin">
                </div>
            </div>
            <div class="col-auto">
                <?php 
                // Consulta para obtener NITs distintos
                $consulta_nits = "SELECT DISTINCT nit_proveedor FROM tbl_causacion";
                $ejecutar_nits = mysqli_query($conn, $consulta_nits); 
                ?>
                <div class="form-group mr-2">
                    <label for="nirt" class="mr-2">NIT del proveedor:</label>
                    <br>
                    <select class="form-control" id="bucarh" name = "nit_proveedor" >
                        <?php while ($opcion_nit = mysqli_fetch_assoc($ejecutar_nits)): ?>
                            <option value="<?php echo $opcion_nit['nit_proveedor']; ?>">
                                <?php echo $opcion_nit['nit_proveedor']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-success" type="submit">Consultar</button>
            </div>
        </form>
    </div>
</div>

                      <script>
                       $('#bucarh').select2();
                      </script>  

<br>



<br>


    <div class="container">
        <h2>Filtro</h2>
        <p>Escriba algo en el campo de entrada para buscar en la tabla:</p>
        <input class="form-control" id="myInput2" type="text" placeholder="Search..">
        <br>
        <div class="table-responsive" style="width: 100%;">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Editar</th>
                        <th scope="col">Nit proveedor</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Número factura</th>
                        <th scope="col">Fecha emisión</th>
                        <th scope="col">Fecha vencimiento</th>
                        <th scope="col">Localizador</th>
                        <th scope="col">Tipo moneda</th>
                        <th scope="col">Iva</th>
                        <th scope="col">Valor facturado</th>
                    </tr>
                </thead>
                <tbody id="myTable2">
                    <?php while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)): ?>
                        <tr>
                            <td><a href="modificarCausacion.php?id_causacion=<?= $mostrarPaAdmin['id_causacion'] ?>"><button type="button" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen-fill" viewBox="0 0 16 16"><path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></button></a></td>
                            <td><?= $mostrarPaAdmin['nit_proveedor'] ?></td>
                            <td><?= $mostrarPaAdmin['nombre_proveedor'] ?></td>
                            <td><?= $mostrarPaAdmin['numero_factura'] ?></td>
                            <td><?= $mostrarPaAdmin['fecha_emision'] ?></td>
                            <td><?= $mostrarPaAdmin['fecha_vencimiento'] ?></td>
                            <td><?= $mostrarPaAdmin['localizador'] ?></td>
                            <td><?= $mostrarPaAdmin['tipo_moneda'] ?></td>
                            <td>$<?= formatoMoneda($mostrarPaAdmin['iva']) ?></td>
                            <td>$<?= formatoMoneda($mostrarPaAdmin['valorpagar']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#myInput2").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable2 tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

     </body>
  
  
</html>
