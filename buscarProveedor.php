<?php 
include "seguridad.php"

 ?>
 <?php 
include "sidebar.php"
 ?>

<!doctype html>
<html>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">
    <br>
    <title>Busqueda Proveedores Turisticos</title>

  </head>
<body>
 <div  class="m-0 row justify-content-center">
  <div class="card text-center" >
  <div class=" card-header">
    Busqueda Proveedores Turisticos
  </div>
  <div class="card-body">
    <h5 class="card-title">Ingrese el Numero de Nit del Proveedor</h5>
      <form action="formularioCargaDriveProveedoresTuristicos.php" class="container-fluid" method="get" enctype="multipart/form-data">
      
           <input name="nit" type="number" class="form-control" placeholder="Ingrese el Numero de Nit del Proveedor" required>
           <br>
           <button  class="btn btn-success " type="submit" name="buscar" value="buscar" style="width: 100%";>Buscar
           </button>
      </div>
     </form>
   </div>
 </body>


<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>