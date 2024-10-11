 <?php 
include "seguridad.php"

 ?>

 <?php


   $consulta = ConsultarPaAdmin($_GET['id_pago_ad']);
   function ConsultarPaAdmin($id_pago_ad)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_pagos_administrativos  where id_pago_ad = '".$id_pago_ad ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_pago_ad'],
    $mostrar['locacion'],
    $mostrar['valor'],
    $mostrar['novedad'],
    $mostrar['fecha'],
    $mostrar['archivo'],
    $mostrar['estado']   
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

    <title>Editar Pagos Administrativos</title>

  </head>
  <body >
    <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
   <div class="p-3">
   <div class="d-flex align-items-center p-3 border bg-primary text-white">
    <h2 class="">Modificar Proveedor Administrativo: <?php echo $consulta[1]?>
   
</h2>
  </div>
   </div>
    </div>
  </div>
</div>

 <div >
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDrivePagosAdministrativos.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_pago_ad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Proveedor Administrativo</label>
    <input name="locacion" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el nombre del Proveedor Administrativosss</small>
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Valor a pagar</label>
    <input name="valor" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el valor a pagar</small>
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese alguna descrición</small>
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    <small id="emailHelp" class="form-text text0-muted">Ingrese la fecha del registro</small>
  </div>
  <br>
   <div class="container-fluid">
  <label for="exampleInputEmail1">Estado</label>
  <select class="form-control" name="estado"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
  <option value="<?php echo $consulta[6]?>"><?php echo $consulta[6]?></option>
  <option value="Pendiente">Pendiente</option>
  <option value="En proceso">En proceso</option>
  <option value="Pagado">Pagado</option>
  <option value="Pagado">Soporte enviado y pagado</option>
</select>
</div>
   
  <div class="container-fluid">
  <label for="exampleInputEmail1">Factura</label>

  <a href="<?php echo $consulta[5]?>">
 <label name="archivo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>"><?php echo $consulta[5]?>
  </label></a>
 
  <br>
  
  <div class="container-fluid">
     <label for="exampleInputEmail1">Subir documento soporte de pago*</label>
     <br>
  <input type="file" name="soporteAdmin" id="" required>
  </div>
  <br>
  <div class="container" >
  <button  style="width: 100%;" type="submit" class="btn btn-primary">Editar Pago</button>
  </div>
  </div>
 </div>
  </div>
  
</form>

</div>
</div>
<br>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
  

</html>