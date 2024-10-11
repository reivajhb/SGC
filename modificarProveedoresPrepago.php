 <?php
  include "seguridad.php"

  ?>


 <?php


  $consulta = ConsultarAnticipo($_GET['id_anticipo']);
  function ConsultarAnticipo($id_anticipo)
  {
    include 'conexion.php';
    $sentencia =   "SELECT * FROM tbl_anticipos where id_anticipo = '" . $id_anticipo . "' ";
    $ejecutar = mysqli_query($conn, $sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
      $mostrar['id_anticipo'],
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
      $mostrar['fecha_Retencion']





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
<div class="container my-4" style="max-width: 800px;">
        <form action="cargaDriveProveedoresPrepagoSP.php" method="post" enctype="multipart/form-data">
            <input name="id_anticipo" type="hidden" value="<?php echo $consulta[0] ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_registro">Fecha de registro*</label>
                    <input readonly name="fecha" type="datetime-local" class="form-control" id="fecha_registro" value="<?php echo $consulta[1] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="identificacion">Nit o Cedula*</label>
                    <input readonly name="identificacion" type="number" class="form-control" id="identificacion" value="<?php echo $consulta[3] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="proveedor">Nombre Proveedor Ocasional*</label>
                    <input readonly name="proveedor" type="text" class="form-control" id="proveedor" value="<?php echo $consulta[2] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="email_proveedor">Correo Proveedor*</label>
                    <input readonly name="email_proveedor" type="email" class="form-control" id="email_proveedor" value="<?php echo $consulta[4] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="localizador">Localizador*</label>
                    <input readonly name="localizador" type="text" class="form-control" id="localizador" value="<?php echo $consulta[5] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="num_factura">No de Factura*</label>
                    <input readonly name="num_factura" type="text" class="form-control" id="num_factura" value="<?php echo $consulta[6] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="concepto">Concepto*</label>
                    <input readonly name="concepto" type="text" class="form-control" id="concepto" value="<?php echo $consulta[7] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="descripcion">Información Adicional*</label>
                    <textarea readonly name="descripcion" class="form-control" id="descripcion"><?php echo $consulta[8] ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="moneda">Tipo de Moneda</label>
                    <input readonly name="moneda" type="text" class="form-control" id="moneda" value="<?php echo $consulta[9] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="valor">Valor del cheque o Transferencia</label>
                    <input readonly name="valor" type="number" class="form-control" id="valor" value="<?php echo $consulta[10] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="usuario">Asesor</label>
                    <input readonly name="usuario" type="text" class="form-control" id="usuario" value="<?php echo $consulta[11] ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="fecha_ingreso">Fecha de entrada de los pasajeros*</label>
                    <input readonly name="fecha_ingreso" type="date" class="form-control" id="fecha_ingreso" value="<?php echo $consulta[12] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="certificacion">Descargar certificación bancaria*</label>
                    <input readonly type="text" name="certificacion" class="form-control" id="certificacion" value="<?php echo $consulta[13] ?>">
                    <a href="<?php echo $consulta[13] ?>" class="btn btn-link">Descargar Certificación</a>
                </div>

                <div class="form-group col-md-6">
                    <label for="fecha_salida">Fecha de salida de los pasajeros*</label>
                    <input readonly name="fecha_salida" type="date" class="form-control" id="fecha_salida" value="<?php echo $consulta[14] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cuentadecobro">Descargar cuenta de cobro*</label>
                    <input readonly type="text" name="cuentadecobro" class="form-control" id="cuentadecobro" value="<?php echo $consulta[15] ?>">
                    <a href="<?php echo $consulta[15] ?>" class="btn btn-link">Descargar cuenta de cobro</a>
                </div>

   
        </form>
<button style="width: 100%;" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
    Cargar soporte de pago
</button>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Título de la Ventana Modal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
             <div class="modal-body">
           
                <!-- Contenido de la ventana modal -->
                             
                    <label for="egreso">Numero de Egreso*</label>
                    <input type="number" name="egreso" class="form-control" id="egreso" value="<?php echo $consulta[18] ?>" required>
                
          
                    <label for="estado">Estado</label>
                    <select class="form-control" name="estado" id="estado">
                        <option value="<?php echo $consulta[17] ?>"><?php echo $consulta[17] ?></option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Pagado">Pagado</option>
                        <option value="Soporte enviado y pagado">Soporte enviado y pagado</option>
                    </select>
                    <small class="form-text text-muted">Ingrese el estado del pago</small>
               

                
                    <label for="ValorTotalApagar">Total a pagar</label>
                    <input readonly type="number" name="ValorTotalApagar" id="ValorTotalApagar" class="form-control" value="<?php echo $consulta[16] ?>">
                

           
                
                    <label for="soportePrepago">Subir documento soporte de pago*</label>
                    <input type="file" name="soportePrepago" class="form-control-file" id="soportePrepago" required>
                    
               
                
                    <label for="ValorTotalApagar">Soporte</label>
                    <input readonly type="text" name="" id="" class="form-control" value="<?php echo $consulta[19] ?>">
                    <a href="<?php echo $consulta[19] ?>" class="btn btn-link" target="_blank">Descargar Soporte</a>
                    <br>
                
                
                    <label for="fecha_Retencion">Fecha de aplicación de la retención*</label>
                    <input readonly type="datetime-local" name="fecha_Retencion" class="form-control" id="fecha_Retencion" value="<?php echo $consulta[20] ?>" required>
                
                 
                    <label for="fecha_Soporte">Fecha de carga del soporte*</label>
                    <input readonly type="datetime-local" name="fecha_Soporte" class="form-control" id="fecha_hora_colombia" value="fechaHoraColombia" required>
                

           <br>

            <button type="submit" class="btn btn-primary btn-block">Actualizar Anticipo</button>
            </form>
            </div>

            <div class="modal-footer">
                <button style="width: 100%;" type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <!-- Puedes agregar más botones si lo necesitas -->
                 </div>
        </div>
    </div>
</div>
     
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