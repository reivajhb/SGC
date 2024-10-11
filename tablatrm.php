
    
      <?php
// Código PHP para obtener y mostrar los datos en la tabla
include 'conexion.php';

// Obtener el límite de registros seleccionado por el usuario
$numRegistros = isset($_GET['num_registros']) ? intval($_GET['num_registros']) : 20; // Valor predeterminado si no se especifica

// Consultar la base de datos con el límite de registros especificado
$consulta = "SELECT * FROM tbl_informepesos ORDER BY fecha DESC LIMIT $numRegistros";
$ejecutar = mysqli_query($conn, $consulta);

// Generar las filas de la tabla con los datos obtenidos de la consulta
while ($mostrarProveedor = mysqli_fetch_array($ejecutar)) {
    
    
    $id = $mostrarProveedor['id'];
    
    echo '<td>' . $mostrarProveedor['localizador_reserva'] . '</td>';
    echo '<td>' . $mostrarProveedor['fecha'] . '</td>';
    echo '<td>' . $mostrarProveedor['agencia'] . '</td>';
    echo '<td>' . $mostrarProveedor['precio_venta_usd'] . '</td>';
    echo '<td>' . $mostrarProveedor['tipo_producto'] . '</td>';
    echo '<td>' . $mostrarProveedor['fecha_viajes'] . '</td>';
    echo '<td>' . $mostrarProveedor['trm'] . '</td>';
     $valorApagar1 = $mostrarProveedor ['totalpesos'];
     $numero1 = number_format($valorApagar1, 0, ",", ".");
    echo '<td>' .'$'. $numero1 . '</td>';
    // Otros datos...
    echo '</tr>';
}
?>


    
      