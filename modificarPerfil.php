 <?php 
include "seguridad.php"

 ?>


 <?php


   $consulta = Consultaruser($_GET['id_usuario']);
   function Consultaruser($id_usuario)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_usuarios where id_usuario = '".$id_usuario ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_usuario'],
    $mostrar['usuario'],
    $mostrar['contraseña'],
    $mostrar['nombre'],
    $mostrar['correo'],
    $mostrar['telefono'],
    $mostrar['direccion']   
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
    <h2 class="">Modificar Perfil: <?php echo $consulta[1]?>
   
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
  <form action="editarPerfil.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_usuario" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre</label>
    <input  name="nombre" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Correo </label>
    <input name="correo" type="mail" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Teléfono</label>
    <input name="telefono" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>">
   
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Dirección</label>
    <input name="direccion" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[6]?>">
    
  </div>
  
   <br>
   
  
  <div class="container">
  <button  style="width: 100%;"   type="submit" class="btn btn-primary">Actualizar</button>
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