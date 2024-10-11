
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
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>
   


    <title>Facturacion Tiquetes!</title>

  </head>
  <body >

   
<div class="container px-4">
  <div class="row gx-5">
  <div class="col">
  <div class="p-3"><h1>Información Tiquetes</h1>
</div>
</div>
</div>
</div>

 <br>
   <div  class="mx-auto" style="width: 800px;" class="container" >
     <div class="container" style="width: 100%;">
      <button class="btn-success" >  
    <a  class="text-light" href="ExcelTiquetes.php">Descargar Informe

    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
    </svg>
    </a>
    </button>
    </div>
  </div>
<br>
  <div class="container">
     <h2>Filtro</h2>
      <p>Escriba algo en el campo de entrada para buscar en la tabla :</p>  
    <input class="form-control" id="myInput2" type="text" placeholder="Search..">
   <br>
  <div class="table-responsive" style="width: 100%;" >
 <table class="table">
   <thead class="thead-light">
    <tr>
      <th scope="col">Editar</th>
      <th scope="col">Eliminar</th>
      <th scope="col">Enviar Email</th>
      <!--<th scope="col">Id Pago administrativo</th>-->
      <th scope="col">tipo_trato</th>
      <th scope="col">vendedor</th>
      <th scope="col">importe</th>
      <th scope="col">fecha_cierre</th>
      <th scope="col">nombre_trato</th>
      <th scope="col">nom_agen_cli</th>
      <th scope="col">tipo_moneda</th>
      <th scope="col">tipo_soli_serv</th>
      <th scope="col">canal_venta</th>
      <th scope="col">localizador</th>


      
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_resum_tiquetes";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {

    $id_tiquete_resum = $mostrarPaAdmin['id_tiquete_resum'];
   ?>


  </thead>
  <tbody id="myTable2">

    <tr>
      <?php  

      echo "<td><a href='modificartiquete.php?id_tiquete_resum= ".$mostrarPaAdmin ['id_tiquete_resum']."'><button type='button' class='btn btn-success'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
  <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
</svg></button> </a> </td> </td>";
      

      echo "
       <td>

       <button type='button'class='btn btn-danger' data-toggle='modal' data-target='#exampleModalCenter<?php echo $id_tiquete_resum; ?>'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill'viewBox='0 0 16 16'>
  <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
</svg></button>
<div class='modal fade' id='exampleModalCenter<?php echo $id_tiquete_resum; ?>' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'>
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
      
      <th scope='col'>Id Tiquete</th>
      <th scope='col'>Localizador</th>
      <th scope='col'>Valor</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>".$mostrarPaAdmin ['id_tiquete_resum'] ."</td>
      <td>".$mostrarPaAdmin ['localizador'] ."</td>
      <?php ".
      $valorApagar = $mostrarPaAdmin ['importe'];
      $numero = number_format($valorApagar, 0, ",", ".");
      echo $numero  ."?>
      <td>$".$numero."</td>

    </tr>
  </tbody>
</table>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
        <a href='Eliminartiquete.php?id_tiquete_resum= ".$mostrarPaAdmin ['id_tiquete_resum']."''><button type='button' class='btn btn-danger'>Eliminar</button></a>
      </div>
    </div>
  </div>
</div>



</td>  </td>";
echo "<td><a href='formularioenvioPagosAdministrativos.php?id_tiquete_resum= ".$mostrarPaAdmin  ['id_tiquete_resum']."''><button type='button' class='btn btn-primary'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
  <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
  <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
</svg></button></a> </td>  </td>";
      ?>

      <!--<td><?php echo $mostrarPaAdmin ['id_tiquete_resum'] ?></td>-->
      <td><?php echo $mostrarPaAdmin ['tipo_trato'] ?></td>
      
      <td><?php echo $mostrarPaAdmin ['vendedor']  ?></td>
      <td>
      $<?php 
      $valorApagar = $mostrarPaAdmin ['importe'];
      $numero = number_format($valorApagar, 0, ",", ".");
      echo $numero  ?>
      </td>
      <td><?php echo $mostrarPaAdmin ['fecha_cierre']  ?></td>
      <td><?php echo $mostrarPaAdmin ['nombre_trato']  ?></td>
      <td><?php echo $mostrarPaAdmin ['nom_agen_cli']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_moneda']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_soli_serv']  ?></td>
      <td><?php echo $mostrarPaAdmin ['canal_venta'] ?></td>
      <td><?php echo $mostrarPaAdmin ['localizador']  ?></td>
      
      
    </tr>
<?php 

}

?>


</table>
<br>
  <br>
  <br>
  <br>
</div>

</div>
<script>
$(document).ready(function(){
  $("#myInput2").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable2 tr").filter(function() {
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