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
    $mostrarR = $ejecutar->fetch_assoc();
    return [
    $mostrarR['id_Solicitud_trans'],
    $mostrarR['localizador'],
    $mostrarR['Tipo_Servicio'],
    $mostrarR['Valor'],
    $mostrarR['Vendedor'],
    $mostrarR['correo'],
    $mostrarR['fecha'],
    $mostrarR['estado'],
    $mostrarR['soporte']
    
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

    <title>Editar factura!</title>

  </head>
  <body>
    <div  class="mx-auto" style="width: 800px;"  >
<h3 class="display-4">Modificar Solicitud Facturación Transporte: <?php echo $ConsultarSoliFacTrans[1]?> </h3>


</div>


 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDriveFacturaTransporte.php" class="" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_Solicitud_trans" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Localizador</label>
    <input readonly name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[1]?>">
   
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Tipo de Servicio</label>
    <input name="Tipo_Servicio" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[2]?>">
    
  </div>
  <div class="container-fluid">
    <label  for="exampleInputEmail1">Valor</label>
    <input name="Valor" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[3]?>">
    </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Vendedor</label>
    <input name="Vendedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[4]?>">
   
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Correo Cliente</label>
    <input name="correo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[5]?>">
    
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha del registro</label>
    <input name="fecha" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[6]?>">
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Estado</label>
    <select class="form-control" name="estado"  class="form-select" aria-label="Default select example">
    <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
    <option value="<?php echo $ConsultarSoliFacTrans[7]?>">Pendiente</option>
    <option value="En proceso">En proceso</option>
    <option value="Pagado">Pagado</option>
    <option value="Factura Generada y enviada">Factura Generada y enviada</option>
  </select>
  </div>
 

  <div class="container-fluid">
  <label for="exampleInputEmail1">Soporte</label>
  <a href="<?php echo $ConsultarSoliFacTrans[8]?>">
 <label name="soporte" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarSoliFacTrans[8]?>"><?php echo $ConsultarSoliFacTrans[8]?>
  </label></a>
 
 </div>

 <div class="container-fluid">
     <label for="exampleInputEmail1">Subir Factura Electronica*</label>
    
  <input type="file" name="factura" id="" required>
  </div>

  <button  style="width: 100%;"   type="submit" class="btn btn-primary">Editar Factura</button>
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