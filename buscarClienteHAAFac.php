<?php 
include "seguridad.php"

 ?>

<!doctype html>
<html>

<header> 

<?php 
include "sidebar.php"
 ?>

</header>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <br>
    <title>Registro de Facturas Electronicas!</title>

  </head>
 <body >
  <div class="mx-auto" style="width: 1600px;">
     <div class="container" style="width: 1000px; ">
<h3 class="display-4">Busqueda Clientes Para Solicitud de facturación de Hoteles, Alojamientos y alimentación</h3>
</div>
 
    <div class="row d-flex justify-content-center align-items-center h-100">
      
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">  


<form action="RegistroFacturasHAA.php" class="container-fluid" method="get" enctype="multipart/form-data">
<label>Ingrese el Numero de Nit del Cliente</label>
<br>
<div class="container" style="width: 500px;" >
  <input name="nit" type="number" class="form-control" placeholder="Ingrese el Numero de Nit del Cliente Corporativo" required>
  <br>
  
    <button  class="btn btn-success " type="submit" name="buscar" value="buscar" style="width: 100%";> 
      Buscar
    </button>

</div>
    
  </div>
  </form> 
  <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


  </body>


<br>
<br>
 <div>



 </div>
</html>