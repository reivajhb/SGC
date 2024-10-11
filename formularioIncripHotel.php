<?php 
include "seguridad.php"
?>

<?php 
include "header.html"
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- <link rel="stylesheet" type="text/css" href="estilos/estilos.css">-->
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>
	<title>Formulario Proveedor de Alojamiento</title>
    <style>
        .form-section {
            display: none; /* Oculta todas las secciones inicialmente */
        }
        .active {
            display: block; /* Muestra solo la sección activa */
        }
    </style>
</head>
<body>
<br>
<br>
<br>

<div class="container mt-4">
    <h2 class="text-center">Ficha de Inscripción como Proveedor de Alojamiento</h2>

  <form id="multiStepForm" action="/submit_form" method="post">
    <!-- Sección 1: Información General -->
    <div id="section1" class="form-section active">
        <div class="table-responsive">
            <table class="table table-bordered">
                <!-- Información General -->
                <thead class="table-dark">
                    <tr>
                        <th colspan="4" class="text-center">INFORMACIÓN GENERAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cadena/Grupo Hotelero:</td>
                        <td colspan="3"><input type="text" name="cadena_grupo" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Nombre del Hotel:</td>
                        <td colspan="2"><input type="text" name="nombre_hotel" class="form-control"></td>
                        <td>NIT: <input type="text" name="nit" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Razón Social:</td>
                        <td colspan="3"><input type="text" name="razon_social" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Teléfono:</td>
                        <td colspan="3"><input type="text" name="telefono" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Dirección del hotel:</td>
                        <td colspan="3"><input type="text" name="direccion_hotel" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Ciudad:</td>
                        <td><input type="text" name="ciudad" class="form-control"></td>
                        <td>País:</td>
                        <td><input type="text" name="pais" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Website:</td>
                        <td><input type="text" name="website" class="form-control"></td>
                        <td>Categoría:</td>
                        <td><input type="text" name="categoria" class="form-control"></td>
                    </tr>

                    <!-- Contactos -->
                    <thead class="table-dark">
                        <tr>
                            <th colspan="4" class="text-center">CONTACTOS</th>
                        </tr>
                    </thead>
                    <tr>
                        <td>Contacto Comercial</td>
                        <td><input type="text" name="contacto_comercial" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_comercial" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_comercial" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_comercial" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contacto Reservas Individuales</td>
                        <td><input type="text" name="contacto_reservas" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_reservas" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_reservas" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_reservas" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contacto Grupos</td>
                        <td><input type="text" name="contacto_grupos" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_grupos" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_grupos" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_grupos" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contacto Pagos (Control Administrativo)</td>
                        <td><input type="text" name="contacto_pagos" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_pagos" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_pagos" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_pagos" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contacto Reclamaciones (para PQR’s de pasajeros)</td>
                        <td><input type="text" name="contacto_reclamaciones" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_reclamaciones" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_reclamaciones" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_reclamaciones" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contacto Extranet (Channel Manager)</td>
                        <td><input type="text" name="contacto_extranet" class="form-control"></td>
                        <td>Móvil:</td>
                        <td><input type="text" name="movil_extranet" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>E-mail:</td>
                        <td><input type="email" name="email_extranet" class="form-control"></td>
                        <td>Teléfono:</td>
                        <td><input type="text" name="telefono_extranet" class="form-control"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<!-- Sección 2: Descripción del Producto -->
<div id="section2" class="form-section">
    <h4 class="text-center mb-4">DESCRIPCIÓN DEL PRODUCTO</h4>
        <table class="table table-bordered">
            <tbody>
                <!-- Descripción Producto -->
                <tr>
                    <th>Descripción Producto</th>
                    <td colspan="5">
                        <textarea id="descripcionProducto" name="descripcion_producto" class="form-control" rows="3" placeholder="Escriba una descripción del hotel..." required></textarea>
                    </td>
                </tr>
                
                <!-- Número de Habitaciones y Tarifas -->
                <tr>
                    <th>Número de Habitaciones total de la propiedad</th>
                    <td><input type="number" id="numeroHabitaciones" name="numero_habitaciones" class="form-control" required></td>
                    <th>¿Las tarifas incluyen desayuno?</th>
                    <td>
                        <select id="tarifasIncluyen" name="tarifas_incluyen" class="form-control" required>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </td>
                    <th>Precio del desayuno por persona (en caso de no incluirlo)</th>
                    <td><input type="number" id="precioDesayuno" name="precio_desayuno" class="form-control"></td>
                </tr>

                <!-- Tipo de Desayuno y Hora Check In/Out -->
                <tr>
                    <th>¿Qué tipo de desayuno ofrece?</th>
                    <td>
                        <select id="tipoDesayuno" name="tipo_desayuno" class="form-control" required>
                            <option value="a_la_carta">A la Carta</option>
                            <option value="americano">Americano</option>
                            <option value="buffet">Buffet</option>
                            <option value="continental">Continental</option>
                        </select>
                    </td>
                    <th>Hora Check In</th>
                    <td><input type="time" id="horaCheckIn" name="hora_check_in" class="form-control"></td>
                    <th>Hora Check Out</th>
                    <td><input type="time" id="horaCheckOut" name="hora_check_out" class="form-control"></td>
                </tr>

                <!-- Tipo de Hotel -->
                <tr>
                    <th>Tipo de Hotel</th>
                    <td colspan="5">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="familiar" name="tipo_hotel" value="familiar" class="form-check-input">
                            <label for="familiar" class="form-check-label">Familiar</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="soloAdultos" name="tipo_hotel" value="solo_adultos" class="form-check-input">
                            <label for="soloAdultos" class="form-check-label">Solo Adultos</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="corporativo" name="tipo_hotel" value="corporativo" class="form-check-input">
                            <label for="corporativo" class="form-check-label">Corporativo</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="boutique" name="tipo_hotel" value="boutique" class="form-check-input">
                            <label for="boutique" class="form-check-label">Boutique</label>
                        </div>
                    </td>
                </tr>

                <!-- Política de mascotas -->
                <tr>
                    <th>Política de mascotas</th>
                    <td colspan="5">
                        <textarea id="politicaMascotas" name="politica_mascotas" class="form-control" rows="2" placeholder="Describa la política de mascotas, si aplica"></textarea>
                    </td>
                </tr>

                <!-- Otros -->
                <tr>
                    <th>Otros</th>
                    <td colspan="5">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="gayFriendly" name="otros[]" value="gay_friendly" class="form-check-input">
                            <label for="gayFriendly" class="form-check-label">El hotel es Gay Friendly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dogFriendly" name="otros[]" value="dog_friendly" class="form-check-input">
                            <label for="dogFriendly" class="form-check-label">El hotel es Dog Friendly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="gayOnly" name="otros[]" value="gay_only" class="form-check-input">
                            <label for="gayOnly" class="form-check-label">El hotel es Gay Only</label>
                        </div>
                    </td>
                </tr>

                <!-- Accesibilidad -->
                <tr>
                    <th>Accesibilidad</th>
                    <td colspan="5">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="habitacionAccesible" name="accesibilidad[]" value="habitacion_accesible" class="form-check-input">
                            <label for="habitacionAccesible" class="form-check-label">Habitación accesible</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="altoAccesible" name="accesibilidad[]" value="alto_accesible" class="form-check-input">
                            <label for="altoAccesible" class="form-check-label">Alto accesible</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="rampa" name="accesibilidad[]" value="rampa_discapacitados" class="form-check-input">
                            <label for="rampa" class="form-check-label">Rampa para discapacitados</label>
                        </div>
                    </td>
                </tr>

                <!-- Información Adicional -->
                <tr>
                    <th>Información Adicional</th>
                    <td colspan="5">
                        <textarea id="informacionAdicional" name="informacion_adicional" class="form-control" rows="2" placeholder="Escriba cualquier información adicional"></textarea>
                    </td>
                </tr>

                <!-- Habitaciones Especiales -->
                <tr>
                    <th>Cuántas habitaciones para discapacidad</th>
                    <td><input type="number" id="habitacionesDiscapacidad" name="habitaciones_discapacidad" class="form-control"></td>
                    <th>Cuántas habitaciones Connecting</th>
                    <td><input type="number" id="habitacionesConnecting" name="habitaciones_connecting" class="form-control"></td>
                    <th colspan="2"></th>
                </tr>
                <!-- Recomendaciones para las fotos -->
            <div class="alert alert-warning">
            <strong>Recomendaciones:</strong>
            <p>No olvide proporcionar entre 15 a 25 fotos (de preferencia horizontales) en alta resolución de la propiedad, incluyendo una foto de la fachada, habitaciones, baños, lobby, restaurante, salones (si aplica) y áreas comunes.</p>
            <p>El material que nos proporcione será usado para la promoción de los servicios en todos los canales de venta de Panamericana de Viajes, medios virtuales e impresos, por tanto debe ser de libre uso. Panamericana de Viajes S.A.S no responderá por los perjuicios causados a terceros o a la propiedad con ocasión al contenido del material enviado por el proveedor y no se hará responsable de cualquier acción judicial, extra judicial o administrativa que se inicie por este motivo.</p>
            <p>(El envío de las fotos debe realizarse por WinZip o We Transfer con el nombre de la propiedad y nombre en cada foto. Ejemplo: Habitación tipo…)</p>
        </div>

            </tbody>
        </table>
</div>
  <div id="section3" class="form-section">
       <h2 class="text-center mb-4">SERVICIOS DE LA PROPIEDAD</h2>
      <table class="table table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Servicio</th>
            <th>Disponibilidad</th>
            <th>Servicio</th>
            <th>Disponibilidad</th>
             <th>Servicio</th>
            <th>Disponibilidad</th>
             <th>Servicio</th>
            <th>Disponibilidad</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Alquiler de bicicletas</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Adaptado para personas de movilidad reducida</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Aire Acondicionado en el hotel</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Ascensor</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>

              </select>
            </td>
          </tr>
          <tr>
            <td>Atracciones Acuáticas</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Alquiler de móviles y portátiles</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Agencia de Viajes   </td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Bar</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>

              </select>
            </td>
          </tr>
          <tr>
            <td>Basquetbol en la piscina</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Asoleadoras</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Café, Agua Saborizada y Aromática en la recepción</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Bar en la piscina</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Cajero Automático</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Cancha de Baloncesto    </td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Café-Bar</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Cajilla de seguridad</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Cambio de moneda</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Cancha de Futbol</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Cancha de Squash</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Cancha de Tenis</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <!-- Más servicios replicados de la imagen -->
          <tr>
            <td>Campo  de Golf</td>
            <td>
              <select class="form-control">
                <option>Sin campo de golf</option>
                <option>Cerca de la propiedad</option>
                <option>En la propiedad</option>
              </select>
            </td>
            <td>Consola de Videojuegos</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Canchas de Paddle</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Club de Niños</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Casino  Casino</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Lobby con sala de espera</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Capilla</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Concierge</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Centro Comercial</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Mesa de Ping Pong  Mesa de Ping Pong</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Discoteca</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Futbolín</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Deportes Acuáticos No Motorizados</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Muelle Privado</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Guardería Infantil</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Piscina</td>
            <td>
              <select class="form-control">
                <option>Interior</option>
                <option>Exterior</option>
                <option>Sin Piscina</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Enfermería y/o Servicio Médico</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Pista de Carts</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Juegos de Mesa  Juegos de Mesa</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Piscina infantil</td>
            <td>
              <select class="form-control">
                <option>Interior</option>
                <option>Exterior</option>
                <option>Sin Piscina</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Guarda equipaje</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Restaurante</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Lobby Lounge</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Jacuzzi</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Mesa de Billar  Mesa de Billar</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Salón de Reuniones</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Mini Golf</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Mini Golf</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Personal Bilingüe</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Sendero Ecológico</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Miniclub (4/12 años)</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Parque Infantil</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Recepción 24 hrs</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Servicio de planchado</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Pesca</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Parqueadero</td>
            <td>
              <select class="form-control">
                <option>Si-Gratis</option>
                <option>Si-Con costo</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Salón de juegos</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Servicio de Tintorería</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Sala de Masajes</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Gimnasio</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Servicio a la Habitación</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Snack-bar</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Salón de belleza</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Gimnasio</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Servicio a la habitación 24 Horas</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Solárium</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Salón de Fitness</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Playa</td>
            <td>
              <select class="form-control">
                <option>Sin Playa</option>
                <option>Sobre la playa</option>
                <option>Cerca de la playa</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Servicio de Lavandería</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Submarinismo</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Spa</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Sauna</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Servicios de Niñera (Cargo Adicional)</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Tfr Aero-Htl-Aero</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Teenieclub (13/17 años)</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Snorkel</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Super/Minimercado/ Tienda de regalos</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Tfr Htl - Playa - Htl</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Terraza</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Turco</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
          </tr>
           <tr>
            <td>Toallas para la playa y piscina</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>Zona de Juegos Infantiles</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Ventanearía Anti-Ruido</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
                <option>Si, Con Costo</option>
              </select>
            </td>
            <td>Voleibol</td>
            <td>
              <select class="form-control">
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
          </tr>


          <!-- Continuar replicando los servicios de la imagen -->
            <!-- Sección ALGO OTRO SERVICIO -->
          <tr>
            <td colspan="1">ALGO OTRO SERVICIO?</td>
            <td colspan="2">
               <textarea id="otroservicio" name="otroservicio" class="form-control" rows="2" placeholder="Describa el servicio"></textarea>
            </td>
          </tr>
          
          <!-- Sección INTERNET -->
          <tr>
            <td rowspan="2" class="align-middle" style="background-color: #DCE6F1;">INTERNET</td>
            <td style="background-color: #E6EAF1;">Wifi:</td>
            <td style="background-color: #E6EAF1;">Canal Dedicado</td>
            <td rowspan="2" class="checkbox-group">
              <div>Área de cobertura del internet:</div>
              <label><input type="checkbox"> Habitaciones</label>
              <label><input type="checkbox"> Áreas específicas</label>
            </td>
          </tr>
          <tr>
            <td style="background-color: #E6EAF1;">Cable:</td>
            <td style="background-color: #E6EAF1;">Wi-Fi Zonas Comunes</td>
          </tr>
          
          <!-- Sección agua caliente -->
          <tr>
            <td colspan="2" rowspan="2" class="align-middle" style="background-color: #DCE6F1;">Cuenta con agua caliente en habitaciones</td>
            <td colspan="2" class="checkbox-group">
              <label><input type="checkbox"> Sí</label>
              <label><input type="checkbox"> No</label>
              <label><input type="checkbox"> En algunos tipos</label>
              <label><input type="checkbox"> Áreas públicas</label>
            </td>
          </tr>
        </tbody>
      </table>

      

  </div>
       <!-- Botones de navegación -->
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary" id="prevBtn" style="display:none;">Anterior</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Siguiente</button>
        </div>

        <!-- Botón para enviar en la última sección -->
        <div class="text-center" id="submitSection" style="display:none;">
            <button type="submit" class="btn btn-primary mt-3">Enviar</button>
        </div>
    </form>
</div>

<script>
    // JavaScript para navegación entre formularios
    let currentSection = 0; // Índice de la sección actual
    const sections = document.querySelectorAll('.form-section'); // Obtiene todas las secciones

    function showSection(index) {
        sections.forEach((section, i) => {
            section.classList.toggle('active', i === index);
        });
        document.getElementById('prevBtn').style.display = index === 0 ? 'none' : 'inline'; // Oculta el botón "Anterior" en la primera sección
        document.getElementById('nextBtn').style.display = index === sections.length - 1 ? 'none' : 'inline'; // Oculta el botón "Siguiente" en la última sección
        document.getElementById('submitSection').style.display = index === sections.length - 1 ? 'inline' : 'none'; // Muestra el botón "Enviar" en la última sección
    }

    document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentSection < sections.length - 1) {
            currentSection++;
            showSection(currentSection);
        }
    });

    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentSection > 0) {
            currentSection--;
            showSection(currentSection);
        }
    });

    // Muestra la primera sección
    showSection(currentSection);
</script>
 <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>