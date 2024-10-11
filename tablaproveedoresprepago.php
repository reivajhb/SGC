<?php
// Código PHP para obtener y mostrar los datos en la tabla
include 'conexion.php';

// Obtener el límite de registros seleccionado por el usuario
$numRegistros = isset($_GET['num_registros']) ? intval($_GET['num_registros']) : 20; // Valor predeterminado si no se especifica

// Consultar la base de datos con el límite de registros especificado
$consulta = "SELECT * FROM tbl_anticipos ORDER BY CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END, fecha DESC LIMIT $numRegistros";
$ejecutar = mysqli_query($conn, $consulta);

// Generar las filas de la tabla con los datos obtenidos de la consulta
while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
     $id_anticipo = $mostrarProveedor['id_anticipo'];
    echo "<td><a href='modificarProveedoresPrepagoRT.php?id_anticipo= ".$mostrarProveedor ['id_anticipo']."'>
      <button type='button' class='btn btn-success' data-toggle='modal data-target='#exampleModalCenter'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
  <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
</svg></button> </a> </td> </td>";
       echo "
       <td>

       <button type='button'class='btn btn-danger' data-toggle='modal' data-target='#exampleModalCenter<?php echo $id_anticipo; ?>'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill'viewBox='0 0 16 16'>
  <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
</svg></button>
<div class='modal fade' id='exampleModalCenter<?php echo $id_anticipo; ?>' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'>
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
      
      <th scope='col'>Id anticipo</th>
      <th scope='col'>Proveedor</th>
      <th scope='col'>Valor</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      
      <td>".$mostrarProveedor ['id_anticipo'] ."</td>
      <td>".$mostrarProveedor ['proveedor'] ."</td>
      <?php ".
      $valorApagar = $mostrarProveedor ['ValorTotalApagar'];
      $numero = number_format($valorApagar, 0, ",", ".");
      echo $numero  ."?>
      <td>$".$numero."</td>

    </tr>
  </tbody>
</table>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
        <a href='EliminarProveedoresPrepago.php?id_anticipo= ".$mostrarProveedor ['id_anticipo']."''><button type='button' class='btn btn-danger'>Eliminar</button></a>
      </div>
    </div>
  </div>
</div>



</td>  </td>";
echo "<td><a href='formularioenviocorreoproveedorPrepago.php?id_anticipo= ".$mostrarProveedor  ['id_anticipo']."''><button type='button' class='btn btn-primary'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
  <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
  <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
</svg></button></a> </td>  </td>";
    echo '<td>' . $mostrarProveedor['identificacion'] . '</td>';
    echo '<td>' . $mostrarProveedor['fecha'] . '</td>';
    echo '<td>' . $mostrarProveedor['proveedor'] . '</td>';
    echo '<td>' . $mostrarProveedor['descripcion'] . '</td>';
    echo '<td>' . $mostrarProveedor['moneda'] . '</td>';
    echo '<td>' . $mostrarProveedor['localizador'] . '</td>';
     $valorApagar1 = $mostrarProveedor ['ValorTotalApagar'];
     $numero1 = number_format($valorApagar1, 0, ",", ".");
    echo '<td>' .'$'. $numero1 . '</td>';
    echo '<td>' . $mostrarProveedor['estado'] . '</td>';
    echo '<td>' . $mostrarProveedor['fecha_lmtpago'] . '</td>';
    // Otros datos...
    echo '</tr>';
}
?>


    
      