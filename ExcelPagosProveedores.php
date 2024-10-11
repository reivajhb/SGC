
<?php 
include "seguridad.php"
?>
<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformePagosProveedores.xls");


 ?>

<table class="table">
  <thead>
    <tr>
      <th scope="col">Id proveedor</th>
      <th scope="col">Proveedor</th>
      <th scope="col">Cop</th>
      <th scope="col">Novedad</th>
      <th scope="col">Fecha</th>
      <th scope="col">Archivo</th>
      <th scope="col">Estado</th>
      <th scope="col">Soporte</th>

    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_proveedores_turtisticos ";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

      <td><?php echo $mostrarProveedor ['id_proveedor']  ?></td>
      <td><?php echo $mostrarProveedor ['proveedor']  ?></td>
      <td><?php echo $mostrarProveedor ['cop']  ?></td>
      <td><?php echo $mostrarProveedor ['novedad']  ?></td>
      <td><?php echo $mostrarProveedor ['fecha'] ?></td>
      <td><?php echo $mostrarProveedor ['archivo']  ?></td>
      <td><?php echo $mostrarProveedor ['estado']  ?></td>
      <td><?php echo $mostrarProveedor ['soporteProveedor']  ?></td>
     
    </tr>
<?php 

}

?>
  </tbody>
</table>