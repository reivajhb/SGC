 <?php 
include "seguridad.php"

 ?>


 <?php


   $ConsultarFacturaHAA = ConsultarFacturaHAA($_GET['id_solicitudfacturacionHAA']);
   function ConsultarFacturaHAA($id_solicitudfacturacionHAA)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_solicitudfacturacionHAA where id_solicitudfacturacionHAA = '".$id_solicitudfacturacionHAA ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_solicitudfacturacionHAA'],
    $mostrar['nit'],
    $mostrar['nom_cliente'],
    $mostrar['fecha'],
    $mostrar['email_interesado'],
    $mostrar['localizador'],
    $mostrar['novedad'],
    $mostrar['estado'],
    $mostrar['soporte'],
    $mostrar['valor'],
    $mostrar['fee'],
    $mostrar['formadepago'],
    $mostrar['tipomoneda'],
    $mostrar['fechafac'],
    $mostrar['valoralim'],
    $mostrar['valoraloj'],
    $mostrar['soporteproveedor'],
    $mostrar['OrdenCompra']
    
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
<h3 class="display-4">Agregar Facturas Hoteles, Alojamientos y alimentación: <?php echo $ConsultarFacturaHAA[2]?> </h3>


</div>

 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDriveFacHAACorp.php" class="container-fluid" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
   
    <input name="id_solicitudfacturacionHAA" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Identificación*</label>
    <input readonly name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[1];?>" required>
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Cliente*</label>
    <input readonly name="nom_cliente" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[2];?>" required>
    
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha*</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[3];?>" required >
   
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Correo*</label>
    <input readonly name="email_interesado" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[4];?>" required>
    
  </div>

  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador*</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[5];?>"  required>
    
</div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad*</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[6];?>"required>
    
</div>
 
 
 

  
  
<div class="container-fluid">
  <label for="exampleInputEmail1">Estado</label>
  <select class="form-control" name="estado"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" >Ingrese el estado de el pago</small>
  <option  value="<?php echo $ConsultarFacturaHAA[7];?>">Pendiente</option>
  <option value="Pendiente">Pendiente</option>
  <option value="En proceso">En proceso</option>
  <option value="Facturado">Facturado</option>
 
</select>
</select>
</div>

<div class="container-fluid">
    <label  for="exampleInputEmail1">Valor Total de la reserva*</label>
    <input name="valor" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el valor total de la reserva" value="<?php echo $ConsultarFacturaHAA[9];?>"required>
    
</div>
<div class="container-fluid">
    <label  for="exampleInputEmail1">Fee (para reservas con moneda usd)</label>
    <input name="fee" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Fee"  value="<?php echo $ConsultarFacturaHAA[10];?>"required>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Tipo de moneda*</label>
  <select class="form-control" name="formadepago"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" >Ingrese la forma de pago</small>
  <option  value="<?php echo $ConsultarFacturaHAA[11];?>"><?php echo $ConsultarFacturaHAA[11];?></option>
  
</select>
</select>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Forma de pago*</label>
  <select class="form-control" name="formadepago"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" >Ingrese la forma de pago</small>
  <option  value="<?php echo $ConsultarFacturaHAA[12];?>"><?php echo $ConsultarFacturaHAA[12];?></option>
  
</select>
</select>
</div>

<div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de corte de la facturación*</label>
    <input name="fechafac" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"  value="<?php echo $ConsultarFacturaHAA[13];?>"required>
   
  </div>

<div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Orden de compra*</label>
  <a href="<?php echo $ConsultarFacturaHAA[17]?> "target = "_blank">
 <label name="soporte" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[17]?>"><?php echo $ConsultarFacturaHAA[17]?>
  </label></a>

</div>
<div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Soporte de pago*</label>
  <a href="<?php echo $ConsultarFacturaHAA[8]?>" target = "_blank">
 <label name="soporte" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[8]?>"><?php echo $ConsultarFacturaHAA[8]?>
  </label></a>
</div>
<div>
  <label class="mt-4" for="exampleInputEmail1">Subir factura*</label>
  <input type="file" name="factura" id="" required> 
  <div>
    <script type="text/javascript">
   
   $('#myModal').on('shown.bs.modal', function () {
  $('#myInput').trigger('focus')
})
 </script>
 <div class="container-fluid mt-4">
  <!-- Button trigger modal -->
<button style="width: 100%;" type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
  Verificar Proveedor sin desglose del IVA
</button>
<div class="container-fluid mt-4">
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Proveedor sin desglose del IVA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
   <div class="form-group">
    <label for="">Valor del alojamiento </label>
    <input name="valoraloj" type="number" class="form-control" id="valoraloj" aria-describedby="emailHelp"  value="<?php echo $ConsultarFacturaHAA[14];?>">   
</div>
<div class="form-group">
    <label for="">Valor de la alimentación </label>
    <input name="valoralim" type="number" class="form-control" id="valoralim"  value="<?php echo $ConsultarFacturaHAA[15];?>">
</div>
<div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Soporte del Proveedor*</label>
  <a href="<?php echo $ConsultarFacturaHAA[16]?>" target = "_blank">
 <label name="soporte" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaHAA[16]?>"><?php echo $ConsultarFacturaHAA[16]?>
  </label></a>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        
      </div>
    </div>
  </div>
</div>
</div>
</div>
</form>
<br>
  <div class="container-fluid" >
  <button   style="width: 100%;" type="submit" class="btn btn-primary">Registrar Factura</button>
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