<?php
// /proveedores/vista/header.php

// La seguridad ya se verifica en seguridad_proveedores.php (incluido en la página principal)
// Solo incluir la conexión a la base de datos
include_once '../../facturacion/config/conexion.php';

// --- VARIABLES BASE ---
$user_session_value = $_SESSION['usuario'] ?? null;  // normalmente es el NIT del usuario
$id_rol = $_SESSION['id_rol'] ?? null;               // rol del usuario logeado
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_hotel_logeado = null;
$consulta_enlace = '#'; 
$clase_enlace = 'disabled';
$es_cadena_hotelera = ((int) $id_rol === 7);

// --- VERIFICAR CONEXIÓN ---
if ($user_session_value && isset($conn) && $conn->ping()) {
    // Para hotel individual: buscar su hotel por usuario y NIT.
    if ($id_usuario) {
        $sql_get_hotel_id = "SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro FROM tbl_alojamiento_general WHERE id_usuario_creacion = ? OR usuario_creacion = ? OR nit = ? ORDER BY id_hotel DESC LIMIT 1";
        $stmt_get_id = $conn->prepare($sql_get_hotel_id);
        $stmt_get_id->bind_param("iss", $id_usuario, $user_session_value, $user_session_value);
    } else {
        $sql_get_hotel_id = "SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro FROM tbl_alojamiento_general WHERE usuario_creacion = ? OR nit = ? ORDER BY id_hotel DESC LIMIT 1";
        $stmt_get_id = $conn->prepare($sql_get_hotel_id);
        $stmt_get_id->bind_param("ss", $user_session_value, $user_session_value);
    }
    $stmt_get_id->execute();
    $stmt_get_id->bind_result($id_hotel_logeado, $estado_registro_logeado);
    $stmt_get_id->fetch();
    $stmt_get_id->close();

    if ($id_hotel_logeado) {
        // Verificar si este usuario es creador de múltiples hoteles (cadena)
        $sql_count = "SELECT COUNT(*) FROM tbl_alojamiento_general WHERE usuario_creacion = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("s", $user_session_value);
        $stmt_count->execute();
        $stmt_count->bind_result($count_hoteles);
        $stmt_count->fetch();
        $stmt_count->close();
        
        if ($es_cadena_hotelera) {
            // Es una cadena hotelera
            $es_cadena_hotelera = true;
            $consulta_enlace = '../vista/listadoHotelesCadena.php';
        } elseif (strtoupper(trim((string) $estado_registro_logeado)) !== 'BORRADOR') {
            // Rol 6 solo puede consultar la ficha cuando ya termino de crear el hotel.
            $consulta_enlace = '../vista/consultaHotel.php?id=' . $id_hotel_logeado;
            $clase_enlace = '';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <link rel="icon" type="image/x-icon" href="../../img/pnv.png">
<style>
        /* Fuente corporativa */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
          --corp-rojo: #2C56E6; 
          --corp-azul: #DF3456;
          --fondo-oscuro: #ffffffff;
          --texto-claro: #000000ff;
        }

        /* ---------------------------
           BASE GENERAL
           --------------------------- */
        body {
          padding-top: 70px;
          font-family: "Poppins", sans-serif !important;
          background: var(--fondo-oscuro);
          color: var(--texto-claro);
        }

        .navbar {
          top: 0;
          width: 100%;
          background: linear-gradient(90deg, var(--corp-rojo), var(--corp-azul));
          box-shadow: 0 10px 25px rgba(0,0,0,0.45);
          border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        /* ---------------------------
           LOGO
           --------------------------- */
        .navbar-brand img {
          object-fit: contain;
          border-radius: 10px;
          box-shadow: 0 0 10px rgba(0,0,0,.4);
          background: rgba(255,255,255,0.15);
          padding: 4px;
        }

        /* ---------------------------
           LINKS DEL MENÚ
           --------------------------- */
        .navbar-nav .nav-link {
          font-family: "Poppins", sans-serif !important;
          font-weight: 500;
          letter-spacing: 0.4px;
          transition: all .25s ease;
          position: relative;
        }
        .navbar-nav .nav-link::after {
          bottom: 0.2rem;
        }
        .form-inline {
          margin-top: .5rem;
        }
    </style>
</head>

<link rel="stylesheet" href="/facturacion/estilos/navbar-corporativo.css">
<style>
  /* Sobreescribir variables oscuras de navbar-corporativo.css para el área de proveedores */
  body {
    background: #ffffff !important;
    color: #000000 !important;
  }
  .navbar {
    background: linear-gradient(90deg, #2C56E6, #DF3456) !important;
  }
</style>
<body>
<nav class="navbar fixed-top navbar-expand-lg navbar-dark">

  <a class="navbar-brand" href="">
      <img src="../../img/pnv.png" width="50" height="50" alt="Logo" style="margin-left: 1rem;">
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto">

        <!-- ENLACE DE CONSULTA -->
        <li class="nav-item">
            <a style="margin-left: 0.8rem;" class="nav-link <?php echo $clase_enlace; ?>" 
               href="<?php echo $consulta_enlace; ?>"
               <?php echo $clase_enlace === 'disabled' ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : ''; ?>
               title="Ver la ficha o los hoteles asociados.">
               <?php echo $es_cadena_hotelera ? 'Mis Hoteles' : 'Mi Ficha de Hotel'; ?>
            </a>
        </li>
      
      <!-- REGISTRO NUEVO HOTEL - Solo para cadenas hoteleras -->
      <?php if ($es_cadena_hotelera): ?>
      <li style="margin-left: 0.8rem;" class="nav-item">
        <a class="nav-link" href="../vista/formularioIncripHotel.php">Registro nuevo hotel</a>
      </li>
      <?php endif; ?>

      <!-- CERRAR SESIÓN -->
      <li style="margin-left: 0.8rem;" class="nav-item">
        <a class="nav-link text-danger"
           href="/facturacion/proveedores/controlador/CerrarSesionProveedor.php"
           onclick="return confirm('¿Estás seguro que deseas cerrar sesión?');">
          Cerrar Sesión
        </a>
      </li>
      <li style="margin-left: 0.8rem;" class="nav-item">
        <a class="nav-link text-danger"
           href="/facturacion/proveedores/vista/miCuentaProveedor.php">
          Mi perfil
        </a>
      </li>

    </ul>

    <!-- Buscador (solo para cadenas con múltiples hoteles) -->
    <?php if ($es_cadena_hotelera): ?>
    <form class="d-flex ms-auto" method="GET" action="../vista/listadoHotelesCadena.php">
      <input class="form-control me-2" type="search" name="q" placeholder="Buscar"
             value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
      <button style="margin-right: 1rem;" class="btn btn-outline-light" type="submit">Buscar</button>
    </form>
    <?php endif; ?>

  </div>
</nav>
</body>
</html>
