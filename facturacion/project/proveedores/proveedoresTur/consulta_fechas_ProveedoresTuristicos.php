<?php 
include "../../../config/seguridad.php"?>
<?php 
include "../../../config/sidebar.php"?>
<!doctype html>
<html>


  
 <body>
  <div class="mx-auto" style="width: 800px;" class="container">
  <h1 class="display-8">Consultar pagos por fecha</h1>
</div>
<br>
<br>
<br>
<div class="container is-fluid">
  <?php
  include "../../../config/conexion.php"; 

  ?>
<?php
$f1 =$_POST['f1']."00:00:00";
$f1 =$_POST['f1']."23:59:59";
 ?>
   </body>


</html>

  