 <?php 
include "seguridad.php"

 ?>


 <?php


   $consulta = ConsultarHotel($_GET['id_causacion']);
   function ConsultarHotel($id_causacion)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_causacion where id_causacion = '".$id_causacion ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_causacion'],
    $mostrar['nombre_proveedor'],
    $mostrar['numero_factura'],
    $mostrar['fecha_emision'],
    $mostrar['fecha_vencimiento'],
    $mostrar['localizador'],
    $mostrar['tipo_moneda'], 
    $mostrar['iva'],
    $mostrar['valorpagar']
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

    <title>Aplicar Retenciones</title>

  </head>
  <body>
    <div  class="mx-auto" style="width: 800px;"  >
<h3 class="display-4">Aplicar Retenciones</h3>
<h3 class="display-4"> <?php echo $consulta[1]?> </h3>
  


</div>

 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDriveProveedoresTuristicosSP.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_causacion" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre</label>
    <input readonly name="proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Número factura</label>
    <input name="cop" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
 </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Fecha emisión</label>
    <input name="novedad" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha vencimiento</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    <small id="emailHelp" class="form-text text0-muted"></small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Localizador</label>
    <input name="fecha" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>">
    <small id="emailHelp" class="form-text text0-muted"></small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Tipo moneda</label>
    <input name="fecha" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[6]?>">
    <small id="emailHelp" class="form-text text0-muted"></small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Iva</label>
    <input name="fecha" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[7]?>">
    <small id="emailHelp" class="form-text text0-muted"></small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Valor facturado</label>
    <input name="fecha" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[8]?>">
    <small id="emailHelp" class="form-text text0-muted"></small>
  </div>
  <br>
   <div class="container-fluid">
  <label for="exampleInputEmail1">Estado</label>
  <select class="form-control" name="estado"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
  <option value="<?php echo $consulta[6]?>"><?php echo $consulta[6]?></option>
  <option value="Pendiente">Pendiente</option>
  <option value="Causado">Causado</option>
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