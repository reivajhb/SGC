<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");

$Hotel =  $_POST["Hotel"];
$Cop  =  $_POST["Cop"];
$Novedad =  $_POST["Novedad"];
$Fecha =  $_POST["Fecha"];
$estado = $_POST["estado"];

if ($_FILES["archivo"]) {

	$nombre_base = basename($_FILES ['archivo']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "FacturasHoteles/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$insertar = "INSERT INTO tbl_hoteles (Hotel, Cop, Novedad, Fecha, archivo, estado) VALUES ('$Hotel','$Cop','$Novedad','$Fecha', '$ruta', '$estado')";
		$resultado = mysqli_query($conn, $insertar);
		if ($resultado) {
              echo '<script>
              alert("Pago cargado con exito");
              window.location = "principal.php";
              </script>';

		}elseif ($resultado =  0) {

			echo '<script>alert("Error en la carga")</script>';
		}


	}
}
 
?> 
	   
    