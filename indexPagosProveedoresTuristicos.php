<?php 
include "seguridad.php"

 ?>

<!doctype html>
<html lang="en">





  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Facturacion Proveedores!</title>

  </head>
  <body>
    <header>
        <?php include "sidebar.php"; ?>
    </header>
     <div class="container py-5 h-100" >
<h3 class="display-6">Facturacion Proveedores Panamericana de viajes</h3>
</div>
 <section class="vh-100 gradient-custom">
 <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card bg-dark text-white" style="border-radius: 1rem;">
          <div class="card-body p-5 text-center">   
	<form action="cargarPagosProveedoresTurtisticos.php" class="container-fluid" method="post" enctype="multipart/form-data" >

  <div class="container-fluid">
    <label for="exampleInputEmail1">Proveedor</label>
    <input name="proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nombre del proveedor">
    <small id="emailHelp" class="form-text text-muted">Ingrese el nombre del Proveedor</small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Cop</label>
    <input name="cop" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el valor a pagar">
    <small id="emailHelp" class="form-text text-muted">Ingrese el valor a pagar</small>
  </div>
  <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción">
    <small id="emailHelp" class="form-text text-muted">Ingrese lguna descripción</small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha">
    <small id="emailHelp" class="form-text text0-muted">Ingrese la fecha del registro</small>
  </div>
  <br>
  <div class="container-fluid">
  <label for="exampleInputEmail1">Estado</label>
  <select name="estado"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
<?php
  include 'conexion.php'; 
   $consulta = "SELECT * FROM tbl_estado ";
   $ejecutar = mysqli_query($conn,$consulta);
   ?>
<?php foreach ($ejecutar as  $opciones): ?>
  <option   value="<?php echo $opciones['estado']?>"><?php echo $opciones['estado']?></option>
<?php endforeach ?>
</select>
</div>
  <div class="container-fluid">
   <div>
    <br>
   <label for="exampleInputEmail1">Subir documento PDF</label>
   <input type="file" name="archivo" id="">
   <div class="container" style="width: 200px;">
    <br>
   <button   type="submit" class="btn btn-primary">Cargar Pago</button>
   </div>
  </div>
 </div>
</form>

</div>
</div>
 </div>
</section>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
  <?php 
include "footer.php"
 ?>
</html>