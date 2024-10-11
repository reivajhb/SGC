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

  
</div>
   <div class="container px-4">
  
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
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="300">300</option>
        <option value="500">500</option>
        <option value="1000">1000</option>
        
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
  
      <!--<th scope="col">Id Anticipo</th>-->
      <th scope="col">localizador_reserva</th>
      <th scope="col">fecha</th>
      <th scope="col">agencia</th>
      <th scope="col">precio_venta_usd</th>
      <th scope="col">tipo_producto</th>
      <th scope="col">fecha_viajes</th>
      <th scope="col">trm</th>
      <th scope="col">totalpesos</th>
      
      
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
    xhttp.open("GET", "tablatrm.php?num_registros=" + numRegistros, true);
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
    xhttp.open("GET", "tablatrm.php?num_registros=" + numRegistros, true);
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