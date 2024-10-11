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
    <h2 class="">Aplicar retenciones: <?php echo $consulta[2]?>
   
</h2>
  </div>
   </div>
    </div>
  </div>
   <br>



   
  <form action="editarProveedoresPrepagoRT.php" class="container-fluid" method="post" enctype="multipart/form-data">

       <div class="row">
    <div class="col-md-6">
        <input name="id_anticipo" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0] ?>">
    </div>
    <div class="col-md-6">
        
        <input readonly name="fecha" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" value="<?php echo $consulta[1] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Nit o Cedula*</label>
        <input readonly name="identificacion" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Nombre Proveedor Opcional*</label>
        <input readonly name="proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Correo Proveedor*</label>
        <input readonly name="email_proveedor" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4] ?>">
    </div>
    <div class="col-md-6">
        <label for="localizador">Localizador*</label>
        <input readonly name="localizador" type="text" class="form-control" id="localizador" aria-describedby="emailHelp" placeholder="Ingrese el localizador" value="<?php echo $consulta[5] ?>" oninput="updateDescription()">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">No de Factura*</label>
        <input readonly name="num_factura" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el Numero de factura" value="<?php echo $consulta[6] ?>">
    </div>
    <div class="col-md-6">
        <label for="concepto">Concepto*</label>
        <input readonly name="concepto" type="text" class="form-control" id="concepto" aria-describedby="emailHelp" placeholder="Ingrese el concepto" value="<?php echo $consulta[7] ?>" oninput="updateDescription()">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Información Adicional*</label>
        <textarea readonly name="descripcion" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción"><?php echo $consulta[8] ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Tipo de Moneda</label>
        <input readonly name="moneda" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" value="<?php echo $consulta[9] ?>">
    </div>
    <div class="col-md-6">
        <label for="valor">Valor del cheque o Transferencia</label>
        <input readonly name="valor" type="number" class="form-control" id="valor" aria-describedby="emailHelp" placeholder="Valor del cheque o Transferencia" value="<?php echo $consulta[10] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Asesor</label>
        <input readonly name="usuario" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[11] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Fecha de entrada de los pasajeros*</label>
        <input readonly name="fecha_ingreso" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[12] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Descargar certificación bancaria*</label>
        <input readonly name="certificacion" type="text" class="form-control" value="<?php echo $consulta[13] ?>"> <a href="<?php echo $consulta[13] ?>">Descargar Certificación</a>
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Fecha de salida de los pasajeros*</label>
        <input readonly name="fecha_salida" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[14] ?>">
    </div>
    <div class="col-md-6">
        <label for="exampleInputEmail1">Descargar cuenta de cobro*</label>
        <input readonly name="cuentadecobro" type="text" class="form-control" value="<?php echo $consulta[15] ?>"> <a href="<?php echo $consulta[15] ?>">Descargar cuenta de cobro</a>
    </div>
</div>
<br>

<button style="width: 100%;" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
    Aplicar Reteciones
</button>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Aplicar retención: <?php echo $consulta[2]?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
<div class="modal-body">
                <!-- Contenido de la ventana modal -->
      
       
       <h5> <label style="color:rgb(255,0,0);" for="dobleretencion">Ingresar TRM del dia en caso de que la moneda sea USD o EUR*</label></h5>
       
       <label for="vld">Valor cambio del dia</label>
       <input name="vld" type="number" class="form-control" id="vld" aria-describedby="emailHelp" placeholder="Valor cambio del dia" value="">
       
       
       <label for="tasacambio">Valor del cheque o Transferencia a convertir</label>
       <input readonly name="dolares" type="number" class="form-control" id="dolares" aria-describedby="emailHelp" placeholder="Valor del cheque o Transferencia" value="<?php echo $consulta[10] ?>">
       
       <br>
       
       <button style="width: 100%;" type="button" class="btn btn-primary" type="button" onClick="cambio()">
         Convertir a Pesos
       </button>
       
       <br>
       
       <label>Valor en pesos</label>

       <p type="text" id="resul">
       </p>
       <script type="text/javascript">
         function cambio() {
           var pe = parseInt(document.getElementById('vld').value, 10);
           var re;
           var dol = parseInt(document.getElementById('dolares').value, 10);

           re = pe * dol;
           document.getElementById('resul').innerHTML = re;

         }
       </script>
      
       <label for="tipo"> Tipo de Retención</label>
       <select name="tipo" id="tipo" class="form-control" required onchange="updateDescription()">
         <!-- cargaremos las etiquetas de option con javascript -->
       </select>
      
       <label for="retencion"> Selecciona la retención*</label>
       <select name="retencion" id="retencion" class="form-control" required onchange="updateDescription()">
         <!-- cargaremos las etiquetas de option con javascript -->
       </select>
       
       <h5> <label style="color:rgb(255,0,0);" for="dobleretencion">Seleccionar solo cuando se vaya a aplicar doble retención*</label></h5>
       <select name="dobleretencion" id="dobleretencion" class="form-control">
         <option value="Seleccione" selected>--Seleccione--</option>
         <option value="8">8*1000 Reteica Cartagena</option>
         <option value="6">6*1000 Reteica Cartagena</option>
         <option value="13.80">13.80*1000 Bogotá</option>
         <option value="9.66">9.66*1000 Bogotá</option>
         <option value="4.14">4.14*1000 Bogotá</option>
         <option value="7">7*1000 Bogotá</option>
       </select>
       
       

       <!--<label for="resultRetefuente">Porcentaje Decimal Retefuente</label>-->
       <input type="hidden" name="resultRetefuente" id="resultRetefuente" class="form-control" value="<?php echo $consulta[18] ?>">
       <!--<label  for="valorestaretefuente">Valor a restar Retefuente</label>-->
       <input hidden readonly type="number" name="valorestaretefuente" id="valorestaretefuente" class="form-control" oninput="updateDescription()">
       <!--<label for="valorPagaretefuente">Valor a pagar Retefuente</label>-->
       <input type="hidden" name="valorPagaretefuente" id="valorPagaretefuente" class="form-control" value="<?php echo $consulta[22] ?>">




       <!--<label for="resultReteica">Porcentaje Decimal</label>-->
       <input type="hidden" name="resultReteica" id="resultReteica" class="form-control" value="<?php echo $consulta[18] ?>">
      <!-- <label for="valorestareteica">Valor a restar Reteica</label>-->
       <input hidden readonly type="" name="valorestareteica" id="valorestareteica" class="form-control"  oninput="updateDescription()">
       <!--<label for="valorPagareteica">Valor a pagar Reteica</label>-->
       <input type="hidden" name="valorPagareteica" id="valorPagareteica" class="form-control" value="<?php echo $consulta[22] ?>">
       <!--<label for="sumretenciones" > Suma Doble Retención</label>-->
       <input type="hidden" name="sumretenciones" id="sumretenciones" class="form-control" value="<?php echo $consulta[20] ?>">

      

       <label for="exampleInputEmail1">Estado</label>
       <select class="form-control" name="estado" class="form-select" aria-label="Default select example">
         <small id="emailHelp" class="form-text text0-muted">Ingrese el estado de el pago</small>
         <option value="<?php echo $consulta[17] ?>"><?php echo $consulta[17] ?></option>
         <option value="Pendiente">Pendiente</option>
         <option value="En proceso">En proceso</option>
         <option value="Pagado">Pagado</option>
         <option value="Pagado">Soporte enviado y pagado</option>
       </select>
       

       <label for="ValorTotalApagar">Total a pagar</label>
       <input type="number" name="ValorTotalApagar" id="ValorTotalApagar" class="form-control" value="<?php echo $consulta[20] ?>">
     

         <!--<label for="exampleInputEmail1">Fecha de aplicación de la retencion*</label>-->
         <input hidden type="datetime-local" id="fecha_hora_colombia" name="fecha_Retencion" class="form-control" value="fechaHoraColombia" required>
       

        <label for="exampleFormControlTextarea1">Descripción Retenciones</label>
        <textarea  name="descripcionRT" class="form-control" id="exampleFormControlTextarea1" rows="3">
        </textarea>
        <br>
      
       <button style="width: 100%;" type="submit" class="btn btn-primary">Actualizar Anticipo</button>
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
 <!-- Calcular Retefuente y reteica-->

 <script src="js/main.js"> </script>


 <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
 <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

 </html>