 <?php 
include "seguridad.php"

 ?>


 <?php


   $consulta = ConsultarHotel($_GET['id_proveedor']);
   function ConsultarHotel($id_proveedor)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_proveedores_turtisticos where id_proveedor = '".$id_proveedor ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_proveedor'],
    $mostrar['proveedor'],
    $mostrar['cop'],
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

    <title>Modificar Proveedor Turistico</title>

  </head>
  <body>
    <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
   <div class="p-3">
   <div class="d-flex align-items-center p-3 border bg-primary text-white">
    <h2 class="">Modificar Proveedor Turistico: <?php echo $consulta[1]?>
   
</h2>
  </div>
   </div>
    </div>
  </div>
</div>

 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDriveProveedoresTuristicosSP.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_proveedor" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Proveedor Turistico</label>
    <input readonly name="proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el nombre del Proveedor turistico</small>
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Valor a pagar</label>
    <input name="cop" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese el valor a pagar</small>
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <small id="emailHelp" class="form-text text-muted">Ingrese lguna descrición</small>
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
   
  <div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Factura</label>
  <a href="<?php echo $consulta[5]?>">
 <label name="archivo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>"><?php echo $consulta[5]?>
  </label></a>
 
  
  <div class="container-fluid ">
     <label for="exampleInputEmail1">Subir documento soporte de pago*</label>
    
  <input type="file" name="soporteProveedor" id="" required>
  </div>
  <br>
  <div class="container">
  <button  style="width: 100%;"   type="submit" class="btn btn-primary">Editar Pago</button>
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