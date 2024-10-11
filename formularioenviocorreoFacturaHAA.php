 <?php 
include "seguridad.php"

 ?>
 
 <?php


   $consultaFacturaHAA = consultaFacturaHAA($_GET['id_solicitudfacturacionHAA']);
   function consultaFacturaHAA($id_solicitudfacturacionHAA)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_solicitudfacturacionHAA where id_solicitudfacturacionHAA= '".$id_solicitudfacturacionHAA."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarFacturaCorp = $ejecutar->fetch_assoc();
    return [
    $mostrarFacturaCorp['id_solicitudfacturacionHAA'],
    $mostrarFacturaCorp['nit'],
    $mostrarFacturaCorp['nom_cliente'],
    $mostrarFacturaCorp ['localizador'],
    $mostrarFacturaCorp ['novedad'],
    $mostrarFacturaCorp ['fecha'],
    $mostrarFacturaCorp ['factura'],
    $mostrarFacturaCorp['email_interesado']
    
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
<h3 class="display-4">Envio de correo a cliente: <?php echo $consultaFacturaHAA[2]?>  </h3>
</div>
 
  	
	<form action="enviarcorreoClienteHAA.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="consultaFacturaCorp" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[0]?>">

  <div class="container">
    <label for="exampleInputEmail1">Correo Electronico </label>
    <input  name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[7]?>">
    
  </div>
  
  <div class="container">
    <label for="exampleInputEmail1">Asunto</label>
    <input  name="asunto"type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
  </div>
    <input name="nit" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaCorp[1]?>"> 
    <input name="nom_cliente" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[2]?>">
    <input name="localizador" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[3]?>">
    <input name="novedad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[4]?>">
    <input name="fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[5]?>">
    <a class="collapse" type="hidden" href="<?php echo $consultaFacturaHAA[6]?>">
    <input name="factura" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaHAA[6]?>"><?php echo $consultaFacturaHAA[6]?>
    </a>
   
    <br>
    <form  class="container-fluid" method="post" enctype="multipart/form-data" >
      <div  class="table-responsive">


    <table  class="table table-bordered ">
  <thead>


    <tr>

        <tr>
  
      <th scope="col">Nit</th>
      <th scope="col">Nombre Cliente</th>
      <th scope="col">Localizador</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Factura</th>
      
      
    </tr>
     
    </tr>
    </thead>
  <tbody>

    <tr>
    	<td><?php echo $consultaFacturaHAA[1]?></td>
      <td><?php echo $consultaFacturaHAA[2]?></td>
      <td><?php echo $consultaFacturaHAA[3]?></td>
      <td><?php echo $consultaFacturaHAA[4]?></td>
      <td><?php echo $consultaFacturaHAA[5]?></td>
      <td><a href="<?php echo $consultaFacturaHAA[6]?>"><?php echo $consultaFacturaHAA[6]?></a></td>
     

    
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