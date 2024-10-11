<?php 
include "seguridad.php"
?>
<?php 
include "sidebar.php"
 ?>
<!doctype html>
<html lang="es">
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

   
   <br>

  <?php
error_reporting(0); // Desactivar la visualización de errores

// Tu código PHP aquí

?>
   
 <?php
    

  include 'conexion.php'; 

   $inicio =!empty($_GET['inicio']) ? $_GET['inicio'] : null;
   $fin=!empty($_GET['fin']) ? $_GET['fin'] : null;

   $id_proveedor_adm = ConsultarProveedorAdmin(!empty($_GET['id_proveedor_adm']) ? $_GET['id_proveedor_adm'] : null);


   function ConsultarProveedorAdmin($id_proveedor_adm)
   {
    include 'conexion.php'; 

   $inicio =!empty($_GET['inicio']) ? $_GET['inicio'] : null;
   $fin=!empty($_GET['fin']) ? $_GET['fin'] : null;

    $sentencia =   "
    SELECT  id_proveedoradmin_fo, estado, locacion, SUM(valor) as 'total' , fecha FROM tbl_pagos_administrativos where fecha BETWEEN '$inicio' AND '$fin' and     id_proveedoradmin_fo = $id_proveedor_adm and estado = 'Pagado' GROUP BY id_proveedoradmin_fo";
    $ejecutarPa = mysqli_query($conn,$sentencia);

    $mostrarProveedoradmin = $ejecutarPa->fetch_assoc();
    return [
    $mostrarProveedoradmin['id_proveedoradmin_fo'],
    $mostrarProveedoradmin['locacion'],
    $mostrarProveedoradmin['total'],
    $mostrarProveedoradmin['estado'],
    $mostrarProveedoradmin['fecha'],
  ];   
  }

unset ($id_proveedor_admPe);



 $id_proveedor_admPe = ConsultarProveedorAdminPe(!empty($_GET['id_proveedor_adm']) ? $_GET['id_proveedor_adm'] : null);
   

   function ConsultarProveedorAdminPe($id_proveedor_adm)
   {
    include 'conexion.php'; 
   $inicio =!empty($_GET['inicio']) ? $_GET['inicio'] : null;
   $fin=!empty($_GET['fin']) ? $_GET['fin'] : null;

 $sentenciaPen =   "
    SELECT  id_proveedoradmin_fo, estado , locacion, SUM(valor) as 'total', fecha FROM tbl_pagos_administrativos where  fecha BETWEEN '$inicio' AND '$fin' and     id_proveedoradmin_fo = $id_proveedor_adm and estado = 'Pendiente'  GROUP BY     id_proveedoradmin_fo "; 

    $ejecutarPe = mysqli_query($conn,$sentenciaPen);

    $mostrarProveedoradminPe = $ejecutarPe->fetch_assoc();
    return [
    $mostrarProveedoradminPe['id_proveedoradmin_fo'],
    $mostrarProveedoradminPe['locacion'],
    $mostrarProveedoradminPe['total'],
    $mostrarProveedoradminPe['estado'],
    $mostrarProveedoradminPe['fecha'],
  ];   
  }

   
   ?>
   <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
   <div class="p-3">
   <div class="p-3 border bg-primary text-white"><h2 class="display-0">Proveedor Administrativo:
                 <?php  
                    if (empty($id_proveedor_adm[1])) { 
                        echo $id_proveedor_admPe[1]; 
                    } else {
                        echo $id_proveedor_adm[1];
                    }
                  ?> 
</h2>
  </div>
   </div>
    </div>
  </div>
</div>
   
    <div class="mx-auto" style="width: 800px;" >
  <tr>
  <!--<th scope="col"><h1 class="display-0">Total Pagado: $<?php echo $numero  ?></h1></th>
  <th scope="col"><h1 class="display-0">Pendiente por pagar: $<?php echo $numero2  ?></h1></th>-->
  </tr>
  </div>
  <br>
  <div class="container" style="width: 100%;">
<br>
<div class="container is-fluid">
<div class="col-xs-12">
    <h2></h2>
<br>

    <div>
<style> th {
        font-weight: bold;
        color: white;
    }</style>

<form action="" method="GET">
                     <div class="row">
                            <div class="col-md-4">
                                <label><b>Seleccione el Proveedor</b></label>
                                <select id="bucarh" name = "id_proveedor_adm" class="form-control" required>
                                    
                                        <?php
                                         include 'conexion.php'; 

                                         $consulta = "SELECT * FROM tbl_proveedores_administrativos";
                                         $ejecutar = mysqli_query($conn,$consulta); 
                                        ?>
                                        <?php foreach ($ejecutar as $opciones): ?>
                                            <option value="<?php echo $opciones['id_proveedor_administrativo'] ?>"><?php echo $opciones['nombre'] ?></option>
                                           <?php endforeach ?> 
                                        </select>
                            </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Fecha inicial:</b></label>
                                        <input type="date" name="inicio"  value="<?php if(isset($_GET['inicio'])){ echo $_GET['inicio']; } ?>"class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Fecha final:</b></label>
                                        <input type="date" name="fin" value="<?php if(isset($_GET['fin'])){ echo $_GET['fin']; } ?>" class="form-control"required>
                                    </div>
                                </div>
                                <div class="center-div">
                                 <button  type="submit" class="btn btn-primary"  > 
                               Consultar
                           </button>
                           </div>


                            </div>

                     </div>
                      <script>
                       $('#bucarh').select2();
                      </script>                     
</form>
<style>
        .center-div {
            width: 100px; /* Ancho deseado del div */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
<br>
 <div class="table-responsive" style="width: 100%;" >
<table class="table">
  <thead class="thead-light">
    <tr>
      <!--<th scope="col">ID Proveedor</th>-->
      <th scope="col">Proveedor</th>
      <th scope="col">Valor</th>
      <th scope="col">Estado</th>

  </thead>
  <tbody id="myTable3">

    <tr>
     
      <!--<td><?php echo $id_proveedor_adm[0]  ?></td>-->
      <td><?php echo $id_proveedor_adm[1]  ?></td>
      <td >$<?php 
      $valorApagar = $id_proveedor_adm[2];
      $numero = number_format($valorApagar, 0, ",", ".");

      
      echo $numero  ?></td>
      <td><?php echo $id_proveedor_adm[3]  ?></td>
        
      
      
    </tr>


  </tbody>
</table>


</div>
</div>
<br>
 <div class="table-responsive" style="width: 100%;" >
<table class="table">
  <thead class="thead-light">
    <tr>
      <!--<th scope="col">ID Proveedor</th>-->
      <th scope="col">Proveedor</th>
      <th scope="col">Valor</th>
      <th scope="col">Estado</th>



  </thead>
  <tbody id="myTable3">

    <tr>
     
     <!-- <td><?php echo $id_proveedor_admPe[0]  ?></td>-->
      <td><?php echo $id_proveedor_admPe[1]  ?></td>
      <td>$<?php 
      $valorApagar = $id_proveedor_admPe[2];
      $numero = number_format($valorApagar, 0, ",", ".");

      
      echo $numero  ?></td>
       
      </td>

      <td><?php echo $id_proveedor_admPe[3]  ?></td>
      
      
    </tr>


  </tbody>
</table>
<br>  


<button  type="submit" class="btn btn-success" data-toggle="modal" data-target="#myModal" style="width: 100%";> 
      Generar Grafica
    </button>

    <br>
    <br>
    <br>
    <br>
    <br>

    <!-- Ventana modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grafica 
                    <?php 
                    if (empty($id_proveedor_adm[1])) { 
                        echo $id_proveedor_admPe[1]; 
                    } else {
                        echo $id_proveedor_adm[1];
                    }
                     
                     ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                <canvas id="columnChart"></canvas>
  <script>
    // Obtener los datos de MySQL utilizando PHP para cada estado y valor
    <?php
    // Conexión a la base de datos MySQL
    include 'conexion.php';
    // Consulta SQL para obtener los datos de los estados y valores
    $sql = "SELECT locacion, SUM(valor) as 'total', estado, fecha FROM tbl_pagos_administrativos where id_proveedoradmin_fo = $id_proveedor_admPe[0]  GROUP BY estado";
    $result = $conn->query($sql);

    // Arreglos para almacenar los datos de cada estado y valor
    $estados = array();
    $valores = array();
    $colores = array();

    // Colores de las columnas
    $coloresDisponibles = array('rgba(75, 192, 192, 0.8)', 'rgba(192, 75, 75, 0.8)', 'rgba(75, 192, 75, 0.8)');

    if ($result->num_rows > 0) {
        // Almacenar los datos en los arreglos correspondientes
        while($row = $result->fetch_assoc()) {
            $estados[] = $row["estado"];
            $valores[] = $row["total"];
            $colores[] = $coloresDisponibles[array_rand($coloresDisponibles)]; // Asignar un color aleatorio a cada columna
        }
    }

    $conn->close();
    ?>
    // Obtener los datos desde PHP
    var estados = <?php echo json_encode($estados); ?>;
    var valores = <?php echo json_encode($valores); ?>;
    var colores = <?php echo json_encode($colores); ?>;
    
    // Crear la estructura de datos para la gráfica
        var dataset = [];
        for (var i = 0; i < estados.length; i++) {
            dataset.push({
                label: estados[i],
                data: [valores[i]],
                backgroundColor: colores[i]
            });
        }

        var data = {
            labels: [''],
            datasets: dataset
        };

        // Crear la gráfica de columnas
        var ctx = document.getElementById('columnChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir los archivos JavaScript de Bootstrap -->
    

</div>
</div>
</div>



</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  </body>
  
  
</html>