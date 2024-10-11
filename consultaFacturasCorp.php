<?php 
include "seguridad.php"
?>
<!doctype html>
<html lang="en">

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
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>

    <title>Facturacion Clientes Corporativos!</title>

  </head>
  <body class="bg-image" >
     

    <br>

 
   <div class="mx-auto" style="width: 800px;" >
   <h1 class="display-4">Información Facturas Cliente Corporativos</h1>
   </div>
   <br>
   <div class="container">

  
  <br>
  
<br>
<div class="mx-auto" style="width: 800px;" class="container">
  <h2>Filtro</h2>
  <p>Escriba algo en el campo de entrada para buscar en la tabla :</p>  
  <input class="form-control" id="myInput3" type="text" placeholder="Search..">
</div>
<br>
<div class="mx-auto" style="width: 800px;" class="container">
<table class="table table-light">
  <thead>
    <tr>
      <th scope="col">Editar</th>
      <th scope="col">Eliminar</th>
      <th scope="col">Enviar Email</th>
      <!--<th scope="col">Id proveedor</th>-->
      <th scope="col">Nit</th>
      <th scope="col">Nombre Cliente</th>
      <th scope="col">Localizador</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Estado</th>
      <th scope="col">Factura</th>
      
      
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_facturas_corp ";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarClienteCorp = mysqli_fetch_array($ejecutar)) {
     $id_facturas_corp = $mostrarClienteCorp['id_facturas_corp'];
   ?>


  </thead>
  <tbody id="myTable3">

    <tr>
     <?php  
      echo "<td><a href='modificarFacturaCorp.php?id_facturas_corp= ".$mostrarClienteCorp ['id_facturas_corp']."'><button type='button' class='btn btn-success'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
  <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
</svg></button> </a> </td> </td>";
      echo "
       <td>

       <button type='button'class='btn btn-danger' data-toggle='modal' data-target='#exampleModalCenter<?php echo $id_facturas_corp; ?>'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill'viewBox='0 0 16 16'>
  <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
</svg></button>
<div class='modal fade' id='exampleModalCenter<?php echo $id_facturas_corp; ?>' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title' id='exampleModalLongTitle'>¿Estás seguro de que deseas eliminar este registro?</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>
      <div class='modal-body'>
       <table class='table'>
  <thead>
    <tr>
      
      <th scope='col'>Id Factura</th>
      <th scope='col'>Cliente</th>
      <th scope='col'>Localizador</th>
      
    </tr>
  </thead>
  <tbody>
    <tr>
      
      <td>".$mostrarClienteCorp ['id_facturas_corp'] ."</td>
      <td>".$mostrarClienteCorp ['nom_clien_corp'] ."</td>
      <td>".$mostrarClienteCorp ['localizador'] ."</td>
     

    </tr>
  </tbody>
</table>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
        <a href='EliminarFacturaCorp.php?id_facturas_corp= ".$mostrarClienteCorp ['id_facturas_corp']."''><button type='button' class='btn btn-danger'>Eliminar</button></a>
      </div>
    </div>
  </div>
</div>



</td>  </td>";
echo "<td><a href='formularioenviocorreoFacturaCorp.php?id_facturas_corp= ".$mostrarClienteCorp  ['id_facturas_corp']."''><button type='button' class='btn btn-primary'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
  <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
  <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
</svg></button></a> </td>  </td>";
      ?>
      <!--<td><td><?php echo $mostrarClienteCorp ['id_facturas_corp']  ?></td></td>-->
<td><?php echo $mostrarClienteCorp ['nit']  ?></td>
      <td><?php echo $mostrarClienteCorp ['nom_clien_corp']  ?></td>
      <td><?php echo $mostrarClienteCorp ['localizador']  ?></td>
      <td><?php echo $mostrarClienteCorp ['novedad']  ?></td>
      <td><?php echo $mostrarClienteCorp ['fecha'] ?></td>
      <td><?php echo $mostrarClienteCorp ['estado'] ?></td>
      <td><a href="<?php echo $mostrarClienteCorp ['factura']  ?>"><img width="50" height="50" src="./img/factura.png"  /></td>
     
      
      
    </tr>
<?php 

}

?>
  </tbody>
</table>
</div>

<script>
$(document).ready(function(){
  $("#myInput3").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable3 tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
  
  
</html>