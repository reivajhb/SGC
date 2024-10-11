<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");

$nit =  $_POST["nit"];
$nom_clien_recep  =  $_POST["nom_clien_recep"];
$email_interesado =  $_POST["email_interesado"];



		$insertar = "INSERT INTO tbl_clientes_recep (nit, nom_clien_recep, email_interesado) VALUES ('$nit ','$nom_clien_recep','$email_interesado')";
		$resultado = mysqli_query($conn, $insertar);
		if ($resultado) {
              echo '<script>
              alert("Cliente receptivo cargado con exito");
              window.location = "buscarClienteRecep.php";
              </script>';

		}if ($resultado =  0) {

			echo '<script>alert("Error en la carga")</script>';
		}


	
 
?> 
	 