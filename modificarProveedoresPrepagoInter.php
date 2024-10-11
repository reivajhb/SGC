 <?php
  include "seguridad.php"

  ?>


 <?php


  $consulta = ConsultarAnticipo($_GET['id_pagoint']);
  function ConsultarAnticipo($id_pagoint)
  {
    include 'conexion.php';
    $sentencia =   "SELECT * FROM tbl_pagos_inter where id_pagoint = '" . $id_pagoint . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
      $mostrar['id_pagoint'],
      $mostrar['fecha'],
      $mostrar['proveedor'],
      $mostrar['identificacion'],
      $mostrar['email_Proveedor'],
      $mostrar['localizador'],
      $mostrar['num_factura'],
      $mostrar['concepto'],
      $mostrar['descripcion'],
      $mostrar['moneda'],
      $mostrar['valor'],
      $mostrar['usuario'],
      $mostrar['fecha_ingreso'],
      $mostrar['certificacion'],
      $mostrar['fecha_salida'],
      $mostrar['cuentadecobro'],
      $mostrar['ValorTotalApagar'],
      $mostrar['estado'],
      $mostrar['egreso'],
      $mostrar['soportePrepago'],
      $mostrar['fecha_Retencion'],
      $mostrar['relacionpago'],

    ];
  }
  ?>



 <!doctype html>
 <html lang="en">

 <?php
  include "sidebar.php"
  ?>
 <br>
 <br>


 <head>
   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

   <title>Editar Anticipo Proveedor!</title>

 </head>

 <body>
   <div class="container px-4">
  <div class="row gx-5">
    <div class="col">
   <div class="p-3">
   <div class="d-flex align-items-center p-3 border bg-primary text-white">
    <h2 class="">Modificar Proveedor Turistico: <?php echo $consulta[2]?>
   
</h2>
  </div>
   </div>
    </div>
  </div>
</div>


   <div class="container" style="width: 600px; ">


     <form action="cargaDriveProveedoresInterSP.php" class="container-fluid" method="post" enctype="multipart/form-data">
       <div class="container-fluid">

         <input name="id_pagoint" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0] ?>">

       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Fecha de registro*</label>
         <input readonly name="fecha" type="datatime-location" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" value="<?php echo $consulta[1] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Nit o Cedula*</label>
         <input readonly name="identificacion" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Nombre Proveedor Ocasional*</label>
         <input readonly name="proveedor" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Correo Proveedor*</label>
         <input readonly name="email_proveedor" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Localizador*</label>
         <input readonly name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el localizador" value="<?php echo $consulta[5] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">No de Factura*</label>
         <input readonly name="num_factura" type="TEXT" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el Numero de factura" value="<?php echo $consulta[6] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Concepto*</label>
         <input readonly name="concepto" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el concepto" value="<?php echo $consulta[7] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Información Adicional*</label>
         <input readonly name="descripcion" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" value="<?php echo $consulta[8] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Tipo de Moneda</label>
         <input readonly name="moneda" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" value="<?php echo $consulta[9] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Valor del cheque o Transferencia</label>
         <input readonly name="valor" type="number" class="form-control" id="valor" aria-describedby="emailHelp" placeholder="Valor del cheque o Transferencia" value="<?php echo $consulta[10] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Asesor</label>
         <input readonly name="usuario" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[11] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Fecha de entrada de los pasajeros*</label>
         <input readonly name="fecha_ingreso" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[12] ?>">
       </div>
       <div class="container-fluid">
         <label for="exampleInputEmail1">Descargar certificación bancaria*</label>
         <input readonly type="text" name="certificacion" class="form-control" value="<?php echo $consulta[13] ?>"> <a href="<?php echo $consulta[13] ?>">Descargar Certificación</a>
         <div>

           <label for="exampleInputEmail1">Fecha de salida de los pasajeros*</label>
           <input readonly name="fecha_salida" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[14] ?>">
         </div>
         <div>
           <label for="exampleInputEmail1">Descargar cuenta de cobro*</label>
           <input readonly type="text" name="cuentadecobro" class="form-control" value="<?php echo $consulta[15] ?>"> <a href="<?php echo $consulta[15] ?>">Descargar cuenta de cobro</a>
         </div>

         <div>
           <label for="exampleInputEmail1">Numero de Egreso*</label>
           <input type="number" name="egreso" class="form-control" value="<?php echo $consulta[18] ?>" require>
         </div>

         <div>
           <label for="exampleInputEmail1">Estado</label>
           <select class="form-control" name="estado" class="form-select" aria-label="Default select example">
             <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
             <option value="<?php echo $consulta[17] ?>"><?php echo $consulta[17] ?></option>
             <option value="Pendiente">Pendiente</option>
             <option value="En proceso">En proceso</option>
             <option value="Pagado">Pagado</option>
             <option value="Pagado">Soporte enviado y pagado</option>
           </select>
         </div>
         <div>

           <label for="ValorTotalApagar"> Total a pagar</label>
           <input readonly type="number" name="ValorTotalApagar" id="ValorTotalApagar" class="form-control" value="<?php echo $consulta[10] ?>">
           <div>

            <div>
              
              <label for="exampleInputEmail1">Subir Relacion de pagos*</label>
             <br>

             <input type="file" name="relacionpago" id="" value="<?php echo $consulta[21] ?>" required>
             <a href="<?php echo $consulta[21] ?>">
               <label name="archivo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[21] ?>"><?php echo $consulta[21] ?>
               </label></a>
            </div>

             <label for="exampleInputEmail1">Subir documento soporte de pago*</label>
             <br>

             <input type="file" name="soportePrepago" id="" value="<?php echo $consulta[19] ?>" required>
             <a href="<?php echo $consulta[19] ?>">
               <label name="archivo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[19] ?>"><?php echo $consulta[19] ?>
               </label></a>
             <div class="container-fluid">
               <!--<label for="exampleInputEmail1">Fecha de aplicación de la retencion*</label>
               <input readonly type="datetime-local"  name="fecha_Retencion" class="form-control" value="<?php echo $consulta[20] ?>" >-->

             </div>

             <div class="container-fluid">
               <label for="exampleInputEmail1">Fecha de carga del soporte*</label>
               <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha_Soporte" class="form-control" value="fechaHoraColombia" required>

             </div>

             <br>
             <br>
             <button style="width: 100%;" type="submit" class="btn btn-primary">Actualizar Pago</button>



     </form>
   </div>
   <br>
   <br>
   <br>
   <br>
   <br>

 </body>
 <script>
   // Crear un objeto Date para obtener la hora actual del dispositivo
   var fechaHoraActualDispositivo = new Date();

   // Calcular la diferencia de tiempo entre la zona horaria del dispositivo y la de Colombia
   var diferenciaHorariaColombia = -5; // Cambiar esto según las circunstancias

   // Ajustar la hora actual del dispositivo a la hora de Colombia
   fechaHoraActualDispositivo.setHours(fechaHoraActualDispositivo.getHours() + diferenciaHorariaColombia);

   // Formatear la fecha y hora actual en formato "YYYY-MM-DDTHH:mm"
   var fechaHoraColombia = fechaHoraActualDispositivo.toISOString().slice(0, 16);

   // Obtener el elemento del campo de entrada
   var inputFechaHoraColombia = document.getElementById("fecha_hora_colombia");

   // Establecer el valor en el campo de entrada
   inputFechaHoraColombia.value = fechaHoraColombia;
 </script>

 <!-- Optional JavaScript -->
 <!-- jQuery first, then Popper.js, then Bootstrap JS -->
 <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
 <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

 </html>