<?php 
include "seguridad.php"

 ?>
<?php 
include "sidebar.php"
 ?>
<!doctype html>
<html>

<header> 



</header>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Registro de nuevos clientes tiristicos!</title>

  </head>
 <body >
  <br>
  <div class="mx-auto" style="width: 800px;" >
<h3 class="display-4">Registro de nuevos clientes Receptivo</h3>
</div>
  
 <div >
    <div class="container" style="width: 50%">
      <div >
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">   

    
    <form action="cargaClienteRecep.php" class="container-fluid" method="post" enctype="multipart/form-data">
<div class="container-fluid">
    <label for="exampleInputEmail1">Nit*</label>
    <input name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el Nit" required>
    
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Cliente Receptivo*</label>
    <input name="nom_clien_recep" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el nombre del Proveedor" required>
    
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Correo Interesado*</label>
    <input name="email_interesado" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Correo Contablidad*" required>
    
  </div>
 
  <br>
  <div class="container" >
  <button   style="width: 100%;"  type="submit" class="btn btn-primary">Registrar</button>
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
<br>






</html>