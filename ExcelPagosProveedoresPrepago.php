<?php 
include "seguridad.php"
?>

<?php 
header("Content-Type: application/xls");
header("Content-Disposition: attachmet; filename=InformePagosProveedoresPrepago.xls");


 ?>

<table class="table">
  <thead>
    <tr>
      <th scope="col">Id_anticipo</th>
      <th scope="col">Identificacion</th>
      <th scope="col">Fecha</th>
      <th scope="col">Proveedor</th>
      <th scope="col">Descripcion</th>
      <th scope="col">Moneda</th>
      <th scope="col">Localizador</th>
      <th scope="col">Valor del cheque o Transferencia</th>
      <th scope="col">usuario</th>
      <th scope="col">Valor Total Apagar Con retenciones en pesos</th>
      <th scope="col">Soporte de pago</th>
      <th scope="col">Estado</th>

    </tr>
 <?php
  include 'conexion.php'; 

   $consulta = "SELECT * FROM tbl_anticipos ";
   $ejecutar = mysqli_query($conn,$consulta);
   
  while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
   ?>


  </thead>
  <tbody>

    <tr>

      <td><?php echo $mostrarProveedor ['id_anticipo']  ?></td>
      <td><?php echo $mostrarProveedor ['identificacion']  ?></td>
      <td><?php echo $mostrarProveedor ['fecha']  ?></td>
      <td><?php echo $mostrarProveedor ['proveedor']  ?></td>
      <td><?php echo $mostrarProveedor ['descripcion'] ?></td>
      <td><?php echo $mostrarProveedor ['moneda']  ?></td>
      <td><?php echo $mostrarProveedor ['localizador']  ?></td>
      <td><?php echo $mostrarProveedor ['valor']  ?></td>
      <td><?php echo $mostrarProveedor ['usuario']  ?></td>
      <td><?php echo $mostrarProveedor ['ValorTotalApagar']?></td>
      <td><?php echo $mostrarProveedor ['soportePrepago']  ?></td>
      <td><?php echo $mostrarProveedor ['estado']  ?></td>
     
    </tr>
<?php 

}

?>
  </tbody>
</table>