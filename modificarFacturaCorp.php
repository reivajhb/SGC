 <?php 
include "seguridad.php"

 ?>


 <?php


   $ConsultarFacturaCorp = ConsultarFacturaCorp($_GET['id_facturas_corp']);
   function ConsultarFacturaCorp($id_facturas_corp)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_facturas_corp where id_facturas_corp = '".$id_facturas_corp ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_facturas_corp'],
    $mostrar['nit'],
    $mostrar['nom_clien_corp'],
    $mostrar['fecha'],
    $mostrar['email_interesado'],
    $mostrar['localizador'],
    $mostrar['novedad'],
    $mostrar['estado'],
    $mostrar['soporte'],
    $mostrar['tiquete'],
    $mostrar['record'],
    $mostrar['tarifadmin'],
    $mostrar['formadepago'],
    $mostrar['fechafac'],
    $mostrar['ordencompra']
    
    
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
<h3 class="display-4">Agregar Facturas de tiquetes Cliente: <?php echo $ConsultarFacturaCorp[2]?> </h3>


</div>

 <div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5 ">     
  <form action="cargaDriveFacturasCorp.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_facturas_corp" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[0]?>">

  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Identificación*</label>
    <input readonly name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[1];?>" required>
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Cliente Corporativo*</label>
    <input readonly name="nom_clien_corp" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[2];?>" required>
    
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha*</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[3];?>" required >
   
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Correo*</label>
    <input readonly name="email_interesado" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[4];?>" required>
    
  </div>

  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador*</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[5];?>"  required>
    
</div>
<div class="container-fluid">
    <label  for="exampleInputEmail1">Record del tiquete*</label>
    <input name="record" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[10];?>" placeholder="Ingrese el localizador" required>
</div> 
  <div class="container-fluid">
    <label  for="exampleInputEmail1">Tarifa Administrativa(TA)*</label>
    <input name="tarifadmin" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el localizador" value="<?php echo $ConsultarFacturaCorp[11];?>"  required>
    
</div> 
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad*</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[6];?>"required>
    
</div>
  
  
<div class="container-fluid">
  <label for="exampleInputEmail1">Estado</label>
  <select class="form-control" name="estado"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" >Ingrese el estado de el pago</small>
  <option  value="<?php echo $ConsultarFacturaCorp[7];?>">Pendiente</option>
  <option value="Pendiente">Pendiente</option>
  <option value="En proceso">En proceso</option>
  <option value="Facturado">Facturado</option>
 
</select>
</select>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Forma de pago*</label>
  <select class="form-control" name="formadepago"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" >Ingrese la forma de pago</small>
  <option  value="<?php echo $ConsultarFacturaCorp[12];?>"><?php echo $ConsultarFacturaCorp[12];?></option>
  <option  value="Efectivo">Efectivo</option>
  <option value="Tarjeta">Tarjeta</option>
  <option value="Credito">Credito</option>
</select>
</select>
</div>
<div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de corte de la facturación*</label>
    <input name="fechafac" type="datetime" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[13];?>">
   
  </div>

<div class="container-fluid mt-4">
  <label for="exampleInputEmail1">Soporte de pago*</label>
  <a href="<?php echo $ConsultarFacturaCorp[8]?>" target = "_blank" >
 <label name="soporte" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[8]?>"><?php echo $ConsultarFacturaCorp[8]?>
  </label></a>
 
 </div>


   <div class="container-fluid mt-4">
     <label for="exampleInputEmail1">Tiquete*</label>
     <a href="<?php echo $ConsultarFacturaCorp[9]?>" target = "_blank">
    <label name="tiquete" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[9]?>"><?php echo $ConsultarFacturaCorp[9]?>
     </label></a>
    
    </div>

    <div class="container-fluid mt-4">
     <label for="exampleInputEmail1">Orden de compra*</label>
     <a href="<?php echo $ConsultarFacturaCorp[14]?>" target = "_blank">
    <label name="ordencompra" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $ConsultarFacturaCorp[14]?>"><?php echo $ConsultarFacturaCorp[14]?>
     </label></a>
    
    </div>
 
 
  

  <div class="container-fluid">
  <label class="mt-4" for="exampleInputEmail1">Subir Factura*</label>
  <input type="file" name="factura" id="" required>

</div>
<br>
  <div class="container-fluid" >
  <button   style="width: 100%;" type="submit" class="btn btn-primary">Registrar Factura</button>
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