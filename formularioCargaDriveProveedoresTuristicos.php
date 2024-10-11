<?php 
include "seguridad.php"

 ?>

<!doctype html>
<html>

<header>

<?php 
include "sidebar.php"
 ?>
<?php


   $consultaProveedor = Consultarproveedor($_GET['nit']);
   function Consultarproveedor($nit)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_proveedores_pdv where nit= '".$nit."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarProveedor = $ejecutar->fetch_assoc();

       if ($mostrarProveedor  == 0)
  {
    echo '<script>
              alert("No se encontro ningun proveedor");
              window.location = "buscarProveedor.php";
              </script>';
  }
    return [


  
    $mostrarProveedor['id_proveedor_pdv'],
    $mostrarProveedor['nit'],
    $mostrarProveedor['nom_proveedor'],
    $mostrarProveedor['email_contabilidad'],
    $mostrarProveedor['email_cartera'],
  ];
  }  
   ?>
</header>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">
    <br>
    <title>Relación pagos Proveedores Turisticos!</title>

  </head>
 <body >
 <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
   <div class="p-3">
   <div class="d-flex align-items-center p-3 border bg-primary text-white">
    <h2 class="">Relación pagos Proveedores Turisticos: <?php echo $consultaProveedor[2]?>
   
</h2>
  </div>
   </div>
    </div>
  </div>
  </div>
 <div >
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div >
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">  


    <form action="cargaDriveProveedoresTuristicos.php" class="container-fluid" method="post" enctype="multipart/form-data">

      <div class="container-fluid">
   
    <input name="id_proveedor_pdv" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[0]?>">

    <input  name="id_usuario_tufo" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $_SESSION['usuario'] ?>" required>

  </div>
    
    <div class="container-fluid">
    <label for="exampleInputEmail1">Nit*</label>
    <input readonly name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[1];?>" required>
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Proveedor Turistico*</label>
    <input readonly name="proveedor" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[2];?>" required>
    
  </div>
  <div class="container-fluid">
    
    <input readonly name="email_contabilidad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[3];?>" required>
    
  </div>

  <div class="container-fluid">
    
    <input readonly name="email_cartera" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[4];?>" required>
    
  </div>
  
 <div class="container-fluid">
    <label for="exampleInputEmail1">Valor a pagar*</label>
    <input name="cop" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el valor a pagar" required>
    
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad*</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" required>
    
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de registro*</label>
    <input name="fecha" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" required>
    
  </div>
  <br>
   <div class="container-fluid">
  <label for="exampleInputEmail1">Estado*</label>
  <select name="estado"  class="form-control" aria-label="Default select example" required>
    
  <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_estado ";
   $ejecutar = mysqli_query($conn,$consulta);
   ?>
<?php foreach ($ejecutar as  $opciones): ?>
  
  <option   value="<?php echo $opciones['estado']?>"><?php echo $opciones['estado']?></option>
 
<?php endforeach ?>
</select>
</div>
  <div>
  <label class="mt-4" for="exampleInputEmail1">Subir documento PDF*</label>
  <input type="file" name="facturaProveedorTuristico" id="" required>
  <br>
  <br>
  <div class="container" >
  <button   style="width: 100%;" type="submit" class="btn btn-primary">Cargar Pago</button>
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