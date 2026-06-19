<?php 
include "../../config/seguridad.php"?>
 <?php


    include "../../config/conexion.php"; 

    $id_Solicitud_trans =$_GET['id_Solicitud_trans'];
    $sentencia =   "DELETE FROM tbl_solicitudfacturaciontransporte where id_Solicitud_trans = '$id_Solicitud_trans' "; 
    $resultadoEliminarFac = mysqli_query($conn,$sentencia);

  if ($resultadoEliminarFac) {
    echo '<script>
              alert("Soliciotud Facturación Eliminada con Exito");
              window.location = "consultaSolicitudesFacturacionTransporte.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>
