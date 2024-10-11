<?php 
include "seguridad.php"
?>

<?php 
include "seguridad.php"
 ?>
<?php 

include("conexion.php");


$id_usuario  =  $_POST["id_usuario"];
$nombre =  $_POST["nombre"];
$correo  =  $_POST["correo"];
$telefono =  $_POST["telefono"];
$direccion =  $_POST["direccion"];
 



		$editarPerfil = "UPDATE tbl_usuarios SET nombre='$nombre', correo='$correo', telefono='$telefono', direccion='$direccion'  where id_usuario='$id_usuario' "; 
		$resultado = mysqli_query($conn, $editarPerfil);
		if ($resultado) {
			 echo '<script>
              alert("Perfil modificado con éxito");
              window.location = "micuenta.php";
              </script>';

		}
	




?> 




