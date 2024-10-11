<?php 
include "seguridad.php"
?>
 <?php


    include("conexion.php");

    $id_hotel =$_GET['id_hotel'];
    $sentencia =   "DELETE FROM tbl_hoteles where id_hotel = '$id_hotel'"; 
    $resultadoEliminar = mysqli_query($conn,$sentencia);

  if ($resultadoEliminar) {
    echo '<script>
              alert("Pago Hotel eliminado con exito");
              window.location = "consulta.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>

 