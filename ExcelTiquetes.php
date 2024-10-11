<?php 
include "seguridad.php"
?>
<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformeTiquetes.xls");


 ?>



<table class="table">
  <thead>
    <tr>
     
      <th scope="col">tipo_trato</th>
      <th scope="col">vendedor</th>
      <th scope="col">importe</th>
      <th scope="col">fecha_cierre</th>
      <th scope="col">nombre_trato</th>
      <th scope="col">nom_agen_cli</th>
      <th scope="col">tipo_moneda</th>
      <th scope="col">tipo_soli_serv</th>
      <th scope="col">canal_venta</th>
      <th scope="col">fecha_salida</th>
      <th scope="col">num_pasajeros</th>
      <th scope="col">destinos</th>
      <th scope="col">Centro_costo</th>
      <th scope="col">localizador</th>
      <th scope="col">fecha_emision</th>
      <th scope="col">fecha_servicio</th>
      <th scope="col">num_tiquete</th>
      <th scope="col">tipo_pago</th>
      <th scope="col">proveedor</th>
      <th scope="col">ruta</th>
      <th scope="col">emd</th>
    
    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_resum_tiquetes";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarPaAdmin = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

      <td><?php echo $mostrarPaAdmin ['tipo_trato']  ?></td>
      <td><?php echo $mostrarPaAdmin ['vendedor']  ?></td>
      <td><?php echo $mostrarPaAdmin ['importe']  ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_cierre'] ?></td>
      <td><?php echo $mostrarPaAdmin ['nombre_trato']  ?></td>
      <td><?php echo $mostrarPaAdmin ['nom_agen_cli']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_moneda']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_soli_serv']  ?></td>
      <td><?php echo $mostrarPaAdmin ['canal_venta']  ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_salida']  ?></td>
      <td><?php echo $mostrarPaAdmin ['num_pasajeros']  ?></td>
      <td><?php echo $mostrarPaAdmin ['destinos']  ?></td>
      <td><?php echo $mostrarPaAdmin ['Centro_costo']  ?></td>
      <td><?php echo $mostrarPaAdmin ['localizador'] ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_emision']  ?></td>
      <td><?php echo $mostrarPaAdmin ['fecha_servicio']  ?></td>
      <td><?php echo $mostrarPaAdmin ['num_tiquete']  ?></td>
      <td><?php echo $mostrarPaAdmin ['tipo_pago']  ?></td>
      <td><?php echo $mostrarPaAdmin ['proveedor']  ?></td>
      <td><?php echo $mostrarPaAdmin ['ruta']  ?></td>
      <td><?php echo $mostrarPaAdmin ['emd']  ?></td>
     
    </tr>
<?php 

}

?>
  </tbody>
</table>