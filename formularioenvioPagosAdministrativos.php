 <?php 
include "seguridad.php"

 ?>

 <?php


   $consulta = ConsultarPagosAdministrativos($_GET['id_pago_ad']);
   function ConsultarPagosAdministrativos($id_pago_ad)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_pagos_administrativos where id_pago_ad= '".$id_pago_ad."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_pago_ad'],
    $mostrar['locacion'],
    $mostrar['valor'],
    $mostrar['novedad'],
    $mostrar['fecha'],
    $mostrar['archivo'],
    $mostrar['estado'], 
    $mostrar['soporteAdmin'],
    $mostrar['identificacion'],
    $mostrar['email_contabilidad']

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
  <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
      <div class="p-3">
        <div class="p-3 border bg-primary text-white ">
          <h2 class="text-center">Envio de correo a Proveedor Administrativo: <?php echo $consulta[1]?>  </h2>
        </div>
      </div>
    </div>
  </div>
</div>
  
<div class="mx-auto" style="width: 50%;" class="container">
	<form action="enviarcorreoPagosAdministrativos.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_pago_ad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

  <div class="container">
    <label for="exampleInputEmail1">Correo Electronico</label>
    <input name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[9]?>">
    
  </div>
  <div class="container">
    <label for="exampleInputEmail1">Asunto</label>
    <input  name="asunto"type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
  </div>
    <input name="locacion" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>"> 
    <input name="valor" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <input name="novedad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <input name="fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    <a class="collapse" type="hidden" href="<?php echo $consulta[5]?>">
    <input name="archivo" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php  echo $consulta[5]?>"><?php echo $consulta[5]?>
    </a>
    <a class="collapse" type="hidden" href="<?php echo $consulta[7]?>">
    <input type="hidden" name="soporteAdmin" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[7]?>"><?php echo $consulta[7]?>
    </a>
    <br>
    
</div>
<div class="mx-auto" style="width: 80%;" class="container">
    <table  class="table table-bordered ">
  <thead>


    <tr>

      <th scope="col">Proveedor Administrativo</th>
      <th scope="col">valor</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Archivo</th>
      <th scope="col">Soporte</th>
     
    </tr>
    </thead>
  <tbody>

    <tr>
    	<td><?php echo $consulta[1]?></td>
      <td>$<?php
              $valorApagar = $consulta[2];
              $numero = number_format($valorApagar, 0, ",", ".");
              echo $numero  ?></td>
      <td><?php echo $consulta[3]?></td>
      <td><?php echo $consulta[4]?></td>
      <td><a href="<?php echo $consulta[5]?>"target="_blank"><?php echo $consulta[5]?></a></td>
      <td><a href="<?php echo $consulta[7]?>"target="_blank"><?php echo $consulta[7]?></a></td>

    
    </tr>

  </tbody>
</table>
<br></div>

<div class="container" style="width: 50%;">
  <button  style="width: 100%" type="submit" class="btn btn-primary">Enviar correo</button>
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