<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");


$id_hotel  =  $_POST["id_hotel"];
$Hotel =  $_POST["Hotel"];
$Cop  =  $_POST["Cop"];
$Novedad =  $_POST["Novedad"];
$Fecha =  $_POST["Fecha"];
$estado = $_POST["estado"];


if ($_FILES["soporte"]) {

	$nombre_base = basename($_FILES ['soporte']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "SoportesHoteles/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['soporte']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$editarHotel = "UPDATE tbl_hoteles SET Hotel='$Hotel', Cop='$Cop', Novedad='$Novedad', Fecha='$Fecha' , estado='$estado', soporte='$ruta' where id_hotel='$id_hotel' "; 
		$resultado = mysqli_query($conn, $editarHotel);
		if ($resultado) {
			 echo '<script>
              alert("Pago editado con exito");
              window.location = "consulta.php";
              </script>';

		}
	}
}



?> 




