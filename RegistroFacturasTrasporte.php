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
    <title>Relación pagos Proveedores Turisticos!</title>

  </head>
 <body >
  <div class="mx-auto" style="width: 800px;">
<h3 class="display-4">Solicitud Faturación Servicio De Transporte</h3>
</div>
 <div >
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div >
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">  


    <form action="cargaDriveSolicitudTransporte.php" class="container-fluid" method="post" enctype="multipart/form-data">

      

  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador*</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese El localizador" required>
    
</div>
 
 
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Tipo de servicio*</label>
    <input name="Tipo_Servicio" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el tipo de servicio" required>
    
</div>

<div class="container-fluid">
    <label  for="exampleInputEmail1">Valor Cobrado*</label>
    <input name="Valor" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el valor cobrado" required>
    
</div>

<div class="container-fluid">
    <label  for="exampleInputEmail1">Vendido por*</label>
    <input name="Vendedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el nombre del vendedor" required>
    
</div>

<div class="container-fluid">
    <label  for="exampleInputEmail1">Correo cliente*</label>
    <input name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el correo del cliente" required>
    
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de registro*</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" required>
    
  </div>
  
  
    
  <div class="container-fluid">
    <label for="exampleInputEmail1">Estado</label>
    <select  class="form-control" name="estado"  class="form-select" aria-label="Default select example">
    <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
    <option value="Pendiente">Pendiente</option>
  </select>
  </div>



  <div>
  <label class="mt-4" for="exampleInputEmail1">Adjuntar Soporte de pago*</label>
  <input type="file" name="soporte" id="" required>
  
  <div class="container" >
  <button   style="width: 100%;" type="submit" class="btn btn-primary">Registrar Solicitud </button>
  </div>
  </div>
 </div>
 
  
</form>

</div>
</div>
 </div>



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