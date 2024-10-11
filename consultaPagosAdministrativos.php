<?php 
include "seguridad.php"
?>
<?php 
include "sidebar.php"
 ?>
<!doctype html>


<html lang="es">


<br>
<br>


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

    <title>Facturacion Proveedores!</title>

  </head>
  <body class="bg-image" >
     

    <br>
<?php
  include 'conexion.php'; 

   $consultaSum = "SELECT estado, SUM(valor) as 'total' FROM tbl_pagos_administrativos where estado = 'Pagado' || estado = 'Soporte enviado y pagado'";
   $ejecutarSum = mysqli_query($conn,$consultaSum);
   $mostrarSum = mysqli_fetch_array($ejecutarSum);
   $total= $mostrarSum['total'];
   $numero = number_format($total, 2, ",", ".");
   ?>

<?php
   $consultaSum2 = "SELECT estado, SUM(valor) as 'total' FROM tbl_pagos_administrativos where estado = 'Pendiente'";
   $ejecutarSum2 = mysqli_query($conn,$consultaSum2);
   $mostrarSum2 = mysqli_fetch_array($ejecutarSum2);
   $total2= $mostrarSum2['total'];
   $numero2 = number_format($total2, 2, ",", ".");
   
   ?>
    <div class="container px-4">
    <div class="row gx-5 align-items-center">
        <div class="col-auto">
            <div class="p-3"><h2>Información pagos administrativos</h2></div>
        </div>
        <div class="col-6">
            <div class="container">
                <button class="btn btn-success">
                    <a class="text-light" href="ExcelPagosAdministrativos.php">Descargar información
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
   <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
     <div class="p-3 border bg-success text-white"><h2>Total Pagado: $<?php echo $numero  ?></h2></div>
    </div>
    <div class="col">
      <div class="p-3 border bg-danger text-white "><h2>Pendiente por pagar: $<?php echo $numero2  ?></h2>
      </div>
    </div>
  </div>
</div>
  <br>

  <div class="container" style="width: 100%;" >

<br>

<br>
<div class="table-responsive" style="width: 100%;" >
<div class="row g-4">

<div class="col-auto text-start">
    <label for="num_registros" class="col-form-label">Mostrar: </label>
</div>
<div class="col-auto text-start">
    <select class="form-control" name="num_registros" id="num_registros">
        <option value="100">100</option>
        <option value="300">300</option>
        <option value="500">500</option>
        <option value="1000">1000</option>
        <option value="2000">2000</option>
        <option value="3000">3000</option>
        
        <!-- Otras opciones -->
    </select>
</div>         
   <div class="col-auto text-start">
        <label for="num_registros" class="col-form-label">Filtro: </label>
  </div>
  <div class="col-auto text-start">
        <input class="form-control" id="myInput3" type="text" placeholder="Search..">
  </div>

 <br>
 <br>

<table class="table" id="myTable3" >
  <thead class="thead-light">
    <tr>
      <th scope="col">Editar</th>
      <th scope="col">Eliminar</th>
      <th scope="col">Enviar Email</th>
      <!--<th scope="col">Id Pago administrativo</th>-->
      <th scope="col">Identificación</th>
      <th scope="col">Locacion</th>
      <th scope="col">Valor</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Archivo</th>
      <th scope="col">Estado</th>
      <th scope="col">Soporte Pago Administrativo</th>
      
    </tr>
 

  </thead>
  <tbody id="tabla_resultados">

   
  </tbody>
</table>



<br>
  <br>
  <br>
  <br>
</div>
</div>
<script>
$(document).ready(function(){
  $("#myInput3").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable3 tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    var numRegistros = 50; // Establecer el número inicial de registros
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Insertar el contenido de la respuesta en el tbody de la tabla
            document.getElementById('tabla_resultados').innerHTML = this.responseText;
        }
    };
    // Envía la solicitud al archivo PHP con num_registros=50
    xhttp.open("GET", "tablaproveedoresadmin.php?num_registros=" + numRegistros, true);
    xhttp.send();
});

document.getElementById('num_registros').addEventListener('change', function() {
    var numRegistros = this.value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Insertar el contenido de la respuesta en el tbody de la tabla
            document.getElementById('tabla_resultados').innerHTML = this.responseText;
        }
    };
    // Envía la solicitud al mismo archivo PHP con el nuevo número de registros
    xhttp.open("GET", "tablaproveedoresadmin.php?num_registros=" + numRegistros, true);
    xhttp.send();
});
</script>

<br>
<br>
<br>
<br>
</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
  
  
</html>