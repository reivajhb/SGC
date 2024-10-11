 <?php 
include "seguridad.php"
?>

 <?php


    include 'conexion.php'; 

    $id_facturas_corp =$_GET['id_facturas_corp'];
    $sentencia =   "DELETE FROM tbl_facturas_corp where id_facturas_corp= '$id_facturas_corp' "; 
    $resultadoEliminar = mysqli_query($conn,$sentencia);

  if ($resultadoEliminar) {
    echo '<script>
              alert("Registro eliminado con exito");
              window.location = "consultaFacturasCorp.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>