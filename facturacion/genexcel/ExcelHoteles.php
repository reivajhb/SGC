<?php 
include "seguridad.php"
?>
<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformePagosHoteles.xls");


 ?>

 <table class="table">
  <thead>
    <tr>
      <th scope="col">Id Hotel</th>
      <th scope="col">Hotel</th>
      <th scope="col">Cop</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Archivo</th>
      <th scope="col">Estado</th>
      <th scope="col">Soporte</th>
      
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_hoteles ";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrar = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

      <td><?php echo $mostrar ['id_hotel']  ?></td>
      <td><?php echo $mostrar ['Hotel']  ?></td>
      <td><?php echo $mostrar ['Cop']  ?></td>
      <td><?php echo $mostrar ['Novedad']  ?></td>
      <td><?php echo $mostrar ['Fecha'] ?></td>
      <td><?php echo $mostrar ['archivo']  ?></td>
      <td><?php echo $mostrar ['estado']  ?></td>
      <td><?php echo $mostrar ['soporte']  ?></td>
      
<?php 

}

?>
  </tbody>
</table>