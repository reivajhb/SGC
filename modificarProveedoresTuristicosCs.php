 <?php 
include "seguridad.php"

 ?>


 <?php


   $consulta = ConsultarHotel($_GET['id_proveedor_pdv']);
   function ConsultarHotel($id_proveedor_pdv)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_proveedores_pdv where id_proveedor_pdv = '".$id_proveedor_pdv ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_proveedor_pdv'],
    $mostrar['nit'],
    $mostrar['nom_proveedor'],
    $mostrar['email_contabilidad'],
    $mostrar['email_cartera'],
    $mostrar['TipoRetencion'],
    $mostrar['PorcentajeRTICA'], 
    $mostrar['PorcentajeRTFUEN']
  ];
  }
   ?>

 

<!doctype html>
<html lang="es">
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
    <h2 class="">Modificar Proveedor Turistico: <?php echo $consulta[2]?>
   
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
  <form action="editarProveedorTuristicoPDV.php" class="container-fluid" method="post" enctype="multipart/form-data" >
    <div class="container-fluid">
   
    <input name="id_proveedor_pdv" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

  </div>

<div class="container-fluid">
    <label for="exampleInputEmail1">Nit</label>
    <input readonly name="nit" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Proveedor Turistico</label>
    <input readonly name="nom_proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
  </div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">Correo Contabilidad</label>
    <input name="email_contabilidad" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Correo Cartera</label>
    <input name="email_cartera" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    <small id="emailHelp" class="form-text text-muted"></small>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Tipo de retención</label>
  <select class="form-control" name="TipoRetencion"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted"></small>
  <option value="<?php echo $consulta[5]?>"><?php echo $consulta[5]?></option>
  <option value="Reteica">Reteica</option>
  <option value="Retefuente">Retefuente</option>
  <option value="Retefuente y Reteica">Retefuente y Reteica</option>
  <option value="Sin retenciones">Sin retenciones</option>
</select>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Porcentaje Reteica</label>
  <select class="form-control" name="PorcentajeRTICA"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted"></small>
  <option value="<?php echo $consulta[6]?>"><?php echo $consulta[6]?></option>
  <option value="8">8*1000</option>
         <option value="6">6*1000</option>
         <option value="13.80">13.80*1000</option>
         <option value="9.66">9.66*1000</option>
         <option value="4.14">4.14*1000</option>
         <option value="7">7*1000</option>
         <option value="0">Sin Retenciones</option>
</select>
</div>
<div class="container-fluid">
  <label for="exampleInputEmail1">Porcentaje Retefuente</label>
  <select class="form-control" name="PorcentajeRTFUEN"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted"></small>
  <option value="<?php echo $consulta[7]?>"><?php echo $consulta[7]?></option>
         <option value="0.035">3.5%</option>
         <option value="0.01">1%</option>
         <option value="0.04">4%</option>
         <option value="0.06">6%</option>
         <option value="0.025">2.5%</option>
         <option value="0">Sin Retenciones</option>
         
</select>
</div>
   <br>
   
 
  <div class="container">
  <button  style="width: 100%;"   type="submit" class="btn btn-primary">Editar</button>
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