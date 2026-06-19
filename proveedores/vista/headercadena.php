<?php
include_once '../../facturacion/config/conexion.php';

// --- VARIABLES BASE ---
$user_session_value = $_SESSION['usuario'] ?? null;  
$id_rol = $_SESSION['id_rol'] ?? null;               
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_hotel_logeado = null;
$consulta_enlace = '#'; 
$clase_enlace = 'disabled';

// DEBUG - Información de depuración (comentar después de resolver el problema)
$debug_info = '';
$debug_info .= "Usuario: " . ($user_session_value ?? 'NO SET') . " | ";
$debug_info .= "Rol: " . ($id_rol ?? 'NO SET') . " | ";
$debug_info .= "Conexión DB: " . (isset($conn) && $conn->ping() ? 'OK' : 'FALLO') . " | ";

// --- VERIFICAR CONEXIÓN ---
if ($user_session_value && isset($conn) && $conn->ping()) {

    // Si el usuario es CADENA HOTELERA (por ejemplo, id_rol = 7)
    if ((int)$id_rol === 7) {
        // SOLUCIÓN: Para cadenas hoteleras (ROL 7), siempre habilitar el enlace
        // El listado mostrará todos los hoteles que creó este usuario
        $consulta_enlace = 'listadoHotelesCadena.php';
        $clase_enlace = ''; // habilitado
        
        // Buscar cuántos hoteles tiene registrados (solo para info debug)
        $sql_count_hoteles = "SELECT COUNT(*) as total FROM tbl_alojamiento_general WHERE usuario_creacion = ?";
        $stmt = $conn->prepare($sql_count_hoteles);
        $stmt->bind_param("s", $user_session_value);
        $stmt->execute();
        $stmt->bind_result($total_hoteles);
        $stmt->fetch();
        $stmt->close();

        $debug_info .= "Total hoteles: " . ($total_hoteles ?? 0) . " | ";

    } else {
        // Si es un hotel individual (no cadena), solo se habilita la ficha cuando ya no esta en borrador.
        if ($id_usuario) {
            $sql_get_hotel_id = "
                SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro
                FROM tbl_alojamiento_general
                WHERE id_usuario_creacion = ? OR usuario_creacion = ? OR nit = ?
                ORDER BY id_hotel DESC
                LIMIT 1
            ";
            $stmt_get_id = $conn->prepare($sql_get_hotel_id);
            $stmt_get_id->bind_param("iss", $id_usuario, $user_session_value, $user_session_value);
        } else {
            $sql_get_hotel_id = "
                SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro
                FROM tbl_alojamiento_general
                WHERE usuario_creacion = ? OR nit = ?
                ORDER BY id_hotel DESC
                LIMIT 1
            ";
            $stmt_get_id = $conn->prepare($sql_get_hotel_id);
            $stmt_get_id->bind_param("ss", $user_session_value, $user_session_value);
        }
        $stmt_get_id->execute();
        $stmt_get_id->bind_result($id_hotel_logeado, $estado_registro_logeado);
        $stmt_get_id->fetch();
        $stmt_get_id->close();

        if ($id_hotel_logeado && strtoupper(trim((string) $estado_registro_logeado)) !== 'BORRADOR') {
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
</head>
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
               <?php echo ((int)$id_rol === 7) ? 'Mis Hoteles' : 'Mi Ficha de Hotel'; ?>
            </a>
        </li>
      
      <!-- REGISTRO NUEVO HOTEL - Solo para cadenas hoteleras (rol 7) -->
      <?php if ((int)$id_rol === 7): ?>
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
           href="miCuentaProveedor.php"
           title="Ver y editar mi perfil de usuario">
          Mi Perfil
        </a>
      </li>

    </ul>

    <!-- Buscador (solo para cadenas con múltiples hoteles) -->
    <?php if ((int)($id_rol ?? 0) === 7): ?>
    <form class="d-flex ms-auto" method="GET" action="../vista/listadoHotelesCadena.php">
      <input class="form-control me-2" type="search" name="q" placeholder="Buscar"
             value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
      <button style="margin-right: 1rem; height: 2rem !important; padding: 0 0.5rem 0 0.5rem !important;" class="btn btn-outline-light" type="submit">Buscar</button>
    </form>
    <?php endif; ?>

  </div>
</nav>
</body>
</html>
