 <?php
  include "seguridad.php"

  ?>

 <?php


  $consultaProveedor = Consultarproveedor($_GET['id_proveedor']);
  function Consultarproveedor($id_proveedor)
  {
    include 'conexion.php';
    $sentencia =   "SELECT * FROM tbl_proveedores_turtisticos where id_proveedor= '" . $id_proveedor . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrarProveedor = $ejecutar->fetch_assoc();
    return [
      $mostrarProveedor['id_proveedor'],
      $mostrarProveedor['proveedor'],
      $mostrarProveedor['cop'],
      $mostrarProveedor['novedad'],
      $mostrarProveedor['fecha'],
      $mostrarProveedor['archivo'],
      $mostrarProveedor['estado'],
      $mostrarProveedor['soporteProveedor'],
      $mostrarProveedor['nit'],
      $mostrarProveedor['email_contabilidad'],
      $mostrarProveedor['email_cartera']


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
             <h2 class="text-center">Envio de correo a Proveedor: <?php echo $consultaProveedor[1] ?> </h2>
           </div>
         </div>
       </div>
     </div>
   </div>

   <div class="mx-auto" style="width: 50%;" class="container">
     <form action="enviarcorreoProveedoresTuristicos.php" class="container-fluid" method="post" enctype="multipart/form-data">
       <div class="container-fluid">

         <input name="id_proveedor" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[0] ?>">




         <div class="container">
           <label for="exampleInputEmail1">Correo Electronico Contabilidad</label>
           <input name="correo" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[9] ?>">

         </div>
         <div class="container">
           <label for="exampleInputEmail1">Correo Electronico Cartera</label>
           <input name="correo2" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[10] ?>">

         </div>
         <div class="container">
           <label for="exampleInputEmail1">Asunto</label>
           <input name="asunto" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
           <small id="emailHelp" class="form-text text-muted">Ingrese el asunto</small>
         </div>
         <input name="proveedor" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[1] ?>">
         <input name="cop" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[2] ?>">
         <input name="novedad" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[3] ?>">
         <input name="fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[4] ?>">

         <a class="collapse" type="hidden" href="<?php echo $consultaProveedor[5] ?>">
           <input name="archivo" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[5] ?>"><?php echo $consultaProveedor[5] ?>
         </a>
         <a class="collapse" type="hidden" href="<?php echo $consultaProveedor[7] ?>">
           <input type="hidden" name="soporteProveedor" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[7] ?>"><?php echo $consultaProveedor[7] ?>
         </a>
       </div>
   </div>
   <br>
   <div class="mx-auto" style="width: 80%;" class="container">
     <table class="table table-bordered ">
       <thead>


         <tr>

           <th scope="col">Proveedor</th>
           <th scope="col">Cop</th>
           <th scope="col">Novedad</th>
           <th scope="col">Fecha</th>
           <th scope="col">Archivo</th>
           <th scope="col">Soporte</th>

         </tr>
       </thead>
       <tbody>

         <tr>
           <td><?php echo $consultaProveedor[1] ?></td>
           <td>$<?php
              $valorApagar = $consultaProveedor[2];
              $numero = number_format($valorApagar, 0, ",", ".");
              echo $numero  ?>
           </td>
           <td><?php echo $consultaProveedor[3] ?></td>
           <td><?php echo $consultaProveedor[4] ?></td>
           <td><a href="<?php echo $consultaProveedor[5] ?>"target="_blank"><img width="50" height="50" src="./img/factura.png"  /></a></td>
           <td><a href="<?php echo $consultaProveedor[7] ?>"target="_blank"><img width="50" height="50" src="./img/factura.png"  /></a></td>



         </tr>

       </tbody>
     </table>

   </div>
   </div>
   <br>
   <div class="container" style="width: 50%;">
     <button style="width: 100%" type="submit" class="btn btn-primary">Enviar correo</button>

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