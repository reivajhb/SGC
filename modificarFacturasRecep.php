 <?php 
include "seguridad.php"

 ?>


 <?php


   $ConsultarFacturaRecep = ConsultarFacturaRecep($_GET['id_factura_recep']);
   function ConsultarFacturaRecep($id_factura_recep)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_facturas_recep where id_factura_recep = '".$id_factura_recep ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarR = $ejecutar->fetch_assoc();
    return [
    $mostrarR['id_factura_recep'],
    $mostrarR['nom_clien_recep'],
    $mostrarR['nit'],
    $mostrarR['localizador'],
    $mostrarR['novedad'],
    $mostrarR['fecha'],
    $mostrarR['factura']
    
  ];
  }
   ?>

 

<!doctype html>
<html lang="en">

<?php 
include "sidebar.php"
 ?>
<br>
<br>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Editar factura!</title>

  </head>
  <body>
    <div  class="mx-auto" style="width: 800px;"  >
<h3 class="display-4">Modificar Factura Cliente: <?php echo $ConsultarFacturaRecep[1]?> </h3>


</div>


 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="editarFacturasRecep.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_factura_recep" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Cliente Corporativo</label>
    <input readonly name="nom_clien_recep" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[1]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el nombre del Proveedor turistico</small>
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Nit</label>
    <input name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[2]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el valor a pagar</small>
  </div>
  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[3]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese lguna descrición</small>
</div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[4]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese lguna descrición</small>
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[5]?>">
    <small id="emailHelp" class="form-text text0-muted">Ingrese la fecha del registro</small>
  </div>
   
  <div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Factura</label>
  <a href="<?php echo $ConsultarFacturaRecep[6]?>">
 <label name="archivo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaRecep[6]?>"><?php echo $ConsultarFacturaRecep[6]?>
  </label></a>
 
 </div>

  <button  style="width: 100%;"   type="submit" class="btn btn-primary">Editar Factura</button>
  </div>
  </div>
 </div>
  </div>
  
</form>

<br>
</div>
</div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
 

</html>