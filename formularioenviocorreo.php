 <?php 
include "seguridad.php"

 ?>

 <?php


   $consulta = ConsultarHotel($_GET['id_hotel']);
   function ConsultarHotel($id_hotel)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_hoteles where id_hotel= '".$id_hotel."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_hotel'],
    $mostrar['Hotel'],
    $mostrar['Cop'],
    $mostrar['Novedad'],
    $mostrar['Fecha'],
    $mostrar['archivo'],
    $mostrar['estado'], 
    $mostrar['soporte']  
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
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">

    <title>Enviar Email</title>

  </head>
  <body>
  	<div class="container py-5 h-100" >
<h3 class="display-4">Envio de correo a Hoteles Panamericana de viajes</h3>
</div>
 
  	
	<form action="enviarcorreo.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_hotel" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container">
    <label for="exampleInputEmail1">Correo Electronico</label>
    <input name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el correo electronico</small>
  </div>
  <div class="container">
    <label for="exampleInputEmail1">Asunto</label>
    <input  name="asunto"type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
  </div>
    <input name="Hotel" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>"> 
    <input name="Cop" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <input name="Novedad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <input name="Fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
 
    <a class="collapse" type="hidden" href="<?php echo $consulta[5]?>">
    <input name="archivo" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>"><?php echo $consulta[5]?>
    </a>
    <a class="collapse" type="hidden" href="<?php echo $consulta[7]?>">
    <input type="hidden" name="soporte" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[7]?>"><?php echo $consulta[7]?>
    </a>
    <br>
    <form  class="container-fluid" method="post" enctype="multipart/form-data" >
    <table  class="table table-bordered ">
  <thead>


    <tr>

      <th scope="col">Hotel</th>
      <th scope="col">Cop</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Archivo</th>
      <th scope="col">Soporte</th>
     
    </tr>
    </thead>
  <tbody>

    <tr>
    	<td><?php echo $consulta[1]?></td>
      <td>$<?php echo $consulta[2]?></td>
      <td><?php echo $consulta[3]?></td>
      <td><?php echo $consulta[4]?></td>
      <td><a href="<?php echo $consulta[5]?>"><?php echo $consulta[5]?></a></td>
      <td><a href="<?php echo $consulta[7]?>"><?php echo $consulta[7]?></a></td>

    
    </tr>

  </tbody>
</table>
<div class="container" style="width: 200px;">
  <button   type="submit" class="btn btn-primary">Enviar correo</button>
  </div>
</form>


  <br>
  <br>
  <br>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>

</html>