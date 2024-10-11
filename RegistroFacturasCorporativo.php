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


   $consultaFacturaCorp = consultaFacturaCorp($_GET['nit']);
   function consultaFacturaCorp($nit)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_clientes_corp where nit= '".$nit."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarFacturaCorp = $ejecutar->fetch_assoc();

       if ($mostrarFacturaCorp  == 0)
  {
    echo '<script>
              alert("No se encontro ningun Cliente");
              window.location = "buscarClienteCorp.php";
              </script>';
  }
    return [


  
    $mostrarFacturaCorp['id_factura_corp'],
    $mostrarFacturaCorp['nit'],
    $mostrarFacturaCorp['nom_clien_corp'],
    $mostrarFacturaCorp['email_interesado']
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
    <br>
    <title>Relación pagos Proveedores Turisticos!</title>

  </head>
 <body >
  <div class="mx-auto" style="width: 800px;">
<h3 class="display-4">Registro de facturas por Cliente Corporativo</h3>
</div>
 <div >
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div >
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">  


    <form action="cargaDriveFacturasCorp.php" class="container-fluid" method="post" enctype="multipart/form-data">

      <div class="container-fluid">
    <label for="exampleInputEmail1">Nit*</label>
    <input readonly name="nit" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaCorp[1];?>" required>
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Cliente Corporativo*</label>
    <input readonly name="nom_clien_corp" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaCorp[2];?>" required>
    
  </div>
  <div class="container-fluid">
    
    <input readonly name="email_interesado" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaFacturaCorp[3];?>" required>
    
  </div>

  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador*</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" required>
    
</div>
 
 
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Novedad*</label>
    <input name="novedad" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" required>
    
</div>
  
  


  <div>
  <label class="mt-4" for="exampleInputEmail1">Subir documento PDF*</label>
  <input type="file" name="facturacorp" id="" required>
  
  <div class="container" >
  <button   style="width: 100%;" type="submit" class="btn btn-primary">Registrar Factura </button>
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