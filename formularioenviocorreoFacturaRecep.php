 <?php 
include "seguridad.php"

 ?>
 
 <?php


   $consultaFacturaRecep = consultaFacturaRecep($_GET['id_factura_recep']);
   function consultaFacturaRecep($id_factura_recep)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_facturas_recep where id_factura_recep= '".$id_factura_recep."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarFacturaRecep = $ejecutar->fetch_assoc();
    return [
    $mostrarFacturaRecep['id_factura_recep'],
    $mostrarFacturaRecep['nit'],
    $mostrarFacturaRecep['nom_clien_recep'],
    $mostrarFacturaRecep ['localizador'],
    $mostrarFacturaRecep ['novedad'],
    $mostrarFacturaRecep ['fecha'],
    $mostrarFacturaRecep ['factura'],
    $mostrarFacturaRecep['email_interesado']
    
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
    <br>
    <div class="mx-auto" style="width: 800px;" class="container">
  	<div class="container" >
<h3 class="display-4">Envio de correo a cliente: <?php echo $consultaFacturaRecep[2]?>  </h3>
</div>
 
  	
	<form action="enviarcorreoClienteRecep.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="consultaFacturaCorp" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $mostrarFacturaCorp[0]?>">

  <div class="container">
    <label for="exampleInputEmail1">Correo Electronico </label>
    <input readonly name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[7]?>">
    
  </div>
  
  <div class="container">
    <label for="exampleInputEmail1">Asunto</label>
    <input  name="asunto"type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
  </div>
    <input name="nit" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[1]?>"> 
    <input name="nom_clien_recep" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[2]?>">
    <input name="localizador" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[3]?>">
    <input name="novedad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[4]?>">
    <input name="fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[5]?>">
    <a class="collapse" type="hidden" href="<?php echo $consultaFacturaRecep[6]?>">
    <input name="factura" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaRecep[6]?>"><?php echo $consultaFacturaRecep[6]?>
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
    	<td><?php echo $consultaFacturaRecep[1]?></td>
      <td><?php echo $consultaFacturaRecep[2]?></td>
      <td><?php echo $consultaFacturaRecep[3]?></td>
      <td><?php echo $consultaFacturaRecep[4]?></td>
      <td><?php echo $consultaFacturaRecep[5]?></td>
      <td><a href="<?php echo $consultaFacturaRecep[6]?>"><?php echo $consultaFacturaRecep[6]?></a></td>
     

    
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