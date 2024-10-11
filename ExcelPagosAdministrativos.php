<?php 
include "seguridad.php"
?>
<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformePagosAdministrativos.xls");


 ?>



<table class="table">
  <thead>
    <tr>
      <th scope="col">Id Pago administrativo</th>
      <th scope="col">locacion</th>
      <th scope="col">valor</th>
      <th scope="col">Novedad</th>
      <th scope="col">fecha</th>
      <th scope="col">archivo</th>
      <th scope="col">estado</th>
      <th scope="col">Soporte Pago Administrativo</th>
    
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_pagos_administrativos";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

      <td><?php echo $mostrarPaAdmin ['id_pago_ad']  ?></td>
      <td><?php echo $mostrarPaAdmin ['locacion']  ?></td>
      <td><?php echo $mostrarPaAdmin ['valor']  ?></td>
      <td><?php echo $mostrarPaAdmin ['novedad'] ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha']  ?></td>
      <td><?php echo $mostrarPaAdmin ['archivo']  ?></td>
      <td><?php echo $mostrarPaAdmin ['estado']  ?></td>
      <td><?php echo $mostrarPaAdmin ['soporteAdmin']  ?></td>
     
    </tr>
<?php 

}

?>
  </tbody>
</table>