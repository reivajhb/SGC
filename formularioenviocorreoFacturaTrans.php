 <?php 
include "seguridad.php"

 ?>
 
  <?php

   $ConsultarSoliFacTrans = ConsultarSoliFacTranspor($_GET['id_Solicitud_trans']);
   function ConsultarSoliFacTranspor($id_Solicitud_trans)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_SolicitudFacturacionTransporte where id_Solicitud_trans = '".$id_Solicitud_trans ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarFT = $ejecutar->fetch_assoc();
    return [
    $mostrarFT['id_Solicitud_trans'],
    $mostrarFT['localizador'],
    $mostrarFT['Tipo_Servicio'],
    $mostrarFT['Valor'],
    $mostrarFT['Vendedor'],
    $mostrarFT['correo'],
    $mostrarFT['fecha'],
    $mostrarFT['estado'],
    $mostrarFT['soporte'],
    $mostrarFT['Factura']
    
  ];
  }
   ?>


   

 

<!doctype html>
<html lang="en">

<?php 
include "sidebar.php"
 ?>




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
    <div class="mx-auto" style="width: 800px;" class="container">
  	<div class="container" >
<h3 class="display-4">Envio de correo a cliente: <?php echo $ConsultarSoliFacTrans[2]?>  </h3>
</div>
 
  	
	<form action="enviarcorreoSolicitudFactrans.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="consultaFacturaCorp" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $mostrarFT[0]?>">

  <div class="container">
    <label for="exampleInputEmail1">Correo Electronico </label>
    <input  name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[5]?>">
    
  </div>
  
  <div class="container">
    <label for="exampleInputEmail1">Asunto</label>
    <input  name="asunto"type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
  </div>
    <input name="localizador" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[1]?>"> 
    <input name="Tipo_Servicio" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[2]?>">
    <input name="Valor" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[3]?>">
    <input name="Vendedor" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[4]?>">
    <input name="fecha" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[6]?>">
    <a class="collapse" type="" href="<?php echo $ConsultarSoliFacTrans[8]?>">
    <input name="factura" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[8]?>"><?php echo $ConsultarSoliFacTrans[8]?>
    </a>
   
    <br>
    <form  class="container-fluid" method="post" enctype="multipart/form-data" >
      <div  class="table-responsive">


    <table  class="table table-bordered ">
  <thead>


    <tr>

        <tr>
  
      
      <!--<th scope="col">Id proveedor</th>-->
      <th scope="col">Localizador</th>
      <th scope="col">Tipo De Servicio</th>
      <th scope="col">Valor</th>
      <th scope="col">Vendedor</th>
      <!--<th scope="col">Correo Cliente</th>-->
      <th scope="col">Fecha</th>
      <th scope="col">Factura</th>
      
      
    </tr>
     
    </tr>
    </thead>
  <tbody>

    <tr>
    	<td><?php echo $ConsultarSoliFacTrans[1]?></td>
      <td><?php echo $ConsultarSoliFacTrans[2]?></td>
      <td><?php echo $ConsultarSoliFacTrans[3]?></td>
      <td><?php echo $ConsultarSoliFacTrans[4]?></td>
      <td><?php echo $ConsultarSoliFacTrans[6]?></td>
      <td><a href="<?php echo $ConsultarSoliFacTrans[8]?>"><?php echo $ConsultarSoliFacTrans[8]?></a></td>
     

    
    </tr>

  </tbody>
</table>
</div>
<br>
<div class="container" style="width: 200px;">
  <button   type="submit" class="btn btn-primary">Enviar correo</button>
  </div>
</form>

</div>
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