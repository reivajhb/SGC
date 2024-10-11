<?php 
include "seguridad.php"
?>
<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformeFacturas.xls");


 ?>



<table class="table">
  <thead>
    <tr>
    <th scope="col">Nit proveedor</th>
      <th scope="col">Nombre</th>
      <th scope="col">numero factura</th>
      <th scope="col">fecha emision</th>
      <th scope="col">fecha vencimiento</th>
      <th scope="col"> localizador</th>
      <th scope="col">tipo moneda</th>
      <th scope="col">Iva</th>
      <th scope="col">valor facturado</th>
      <th scope="col">valor causado</th>
    
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_causacion";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

    <td><?php echo $mostrarPaAdmin ['nit_proveedor'] ?></td>
      <td><?php echo $mostrarPaAdmin ['nombre_proveedor']  ?></td>
      <td><?php echo $mostrarPaAdmin ['numero_factura'] ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_emision']  ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_vencimiento']  ?></td>
      <td><?php echo $mostrarPaAdmin ['localizador']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_moneda']  ?></td>
      <td>
      $<?php 
      $valorApagar = $mostrarPaAdmin ['iva'];
      $numero = number_format($valorApagar, 2, ",", ".");
      echo $numero  ?>
      </td>
      <td>
      $<?php 
      $valorApagar = $mostrarPaAdmin ['valorpagar'];
      $numero = number_format($valorApagar, 2, ",", ".");
      
      echo $numero  ?>
      </td>
      <td>
      $<?php 
      $valorApagar = $mostrarPaAdmin ['valorpagar'];
      $numero = number_format($valorApagar, 2, ",", ".");
      echo $numero  ?>
      </td>
     
    </tr>
<?php 

}

?>
  </tbody>
</table>