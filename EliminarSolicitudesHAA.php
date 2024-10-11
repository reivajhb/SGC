 <?php 
include "seguridad.php"
?>

 <?php


    include 'conexion.php'; 

    $id_solicitudfacturacionHAA =$_GET['id_solicitudfacturacionHAA'];
    $sentencia =   "DELETE FROM tbl_solicitudfacturacionHAA where id_solicitudfacturacionHAA= '$id_solicitudfacturacionHAA' "; 
    $resultadoEliminar = mysqli_query($conn,$sentencia);

  if ($resultadoEliminar) {
    echo '<script>
              alert("Registro eliminado con exito");
              window.location = "consultaSolicitudesHAA.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>