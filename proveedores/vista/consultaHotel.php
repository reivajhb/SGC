<?php
// editarHotel.php  (ubicado en /proveedores/vista/)

// Seguridad y conexión
include_once "../seguridad_proveedores.php";
include_once "../../facturacion/config/conexion.php";


// Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes de roles
define('ROL_ADMIN', 1);
define('ROL_CADENA', 7);
define('ROL_2', 2); // ANALISTA CONTABLE
define('ROL_8', 8);
define('ROL_PROVEEDOR', 6);
define('ROL_GESTORAS', 9);

// Resolver rutas absolutas
$ROOT = realpath(dirname(__DIR__, 2));

// Variables de sesión
$idRol = (int) ($_SESSION['id_rol'] ?? 0);
// ================== PERMISOS ==================
// En vista de consulta se permite: Admin, Cadena, Analistas, Gestoras y Proveedor
$rolesPermitidos = [ROL_ADMIN, ROL_CADENA, ROL_2, ROL_8, ROL_PROVEEDOR, ROL_GESTORAS];

if (!in_array($idRol, $rolesPermitidos, true)) {
    http_response_code(403);
    die("<div class='container mt-4 alert alert-danger'>Acceso denegado.</div>");
}


// ================== SIDEBAR ==================
if ($idRol === ROL_ADMIN) {
    include_once $ROOT . "/facturacion/config/sidebar3.php";
} elseif (!in_array($idRol, [ROL_CADENA, ROL_PROVEEDOR], true)) {
    include_once $ROOT . "/facturacion/config/sidebar.php";
    include_once $ROOT . "/facturacion/config/boton_volver.php";
}

// ================== HEADER ==================
// ADMIN (1) y GESTORAS (9) -> sin header
// CADENA (7) -> headercadena.php
// PROVEEDOR (6) -> header.php
$headerFile = null;
if ($idRol === ROL_CADENA) {
    $headerFile = 'headercadena.php';
} elseif ($idRol === ROL_PROVEEDOR) {
    $headerFile = 'header.php';
}

// 3) ID del hotel
$id_hotel = $_GET['id'] ?? null;
if (!$id_hotel || !ctype_digit((string) $id_hotel)) {
    die("❌ Error: ID de hotel no válido.");
}
$id_hotel = (int) $id_hotel;

// ---------- Funciones de utilidad ----------
function formatTinyint($value)
{
    if ($value === null || $value === '')
        return '<span class="badge bg-secondary">N/D</span>';
    if ($value == 1)
        return '<span class="badge bg-success">Sí</span>';
    if ($value == 2)
        return '<span class="badge bg-danger">No</span>';
    return '<span class="badge bg-warning text-dark">N/A</span>';
}


/**
 * Extrae ID de Google Drive desde:
 * - https://drive.google.com/file/d/<ID>/view
 * - https://drive.google.com/open?id=<ID>
 * - https://drive.google.com/uc?id=<ID>...
 */
function driveIdFromUrl($url)
{
    if (!$url)
        return null;

    // 1) Formato: /file/d/<ID>/view
    if (preg_match('~/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
        return $m[1];
    }

    // 2) Formato: open?id=<ID>
    $parts = parse_url($url);
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $q);
        if (!empty($q['id']))
            return $q['id'];
    }

    // 3) Formato: uc?id=<ID>...
    if (preg_match('~[?&]id=([a-zA-Z0-9_-]+)~', $url, $m)) {
        return $m[1];
    }

    return null;
}

function fileLooksImage($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

/**
 * Construye URL directa para <img> (requiere que el archivo sea público en Drive)
 */
function driveViewUrl($driveUrl)
{
    $id = driveIdFromUrl($driveUrl);
    if (!$id)
        return null;

    // Preview confiable para <img> (no es HTML)
    return "https://drive.google.com/thumbnail?id=" . $id . "&sz=w1000";
}


// Mapeo etiquetas de servicios
$map_servicios = [
    'parqueadero' => 'Parqueadero',
    'minibar' => 'Minibar',
    'con_cocina' => 'Con cocina',
    'cafetera_cortesia' => 'Cafetera de cortesia',
    'ventilador_techo' => 'Ventilador de techo',
    'servicio_habitacion' => 'Servicio a la habitacion',
    'servicio_habitacion_24_hrs' => 'Servicio a la habitacion 24 hrs',
    'recepcion_24_hrs' => 'Recepción 24 hrs',
    'transfer_aero_htl' => 'Transfer Aero-Htl-Aero',
    'aire_acondicionado' => 'A/C (General)',
    'turco' => 'Turco',
    'servicio_lavanderia' => 'Lavandería',
    'transfer_htl_playa' => 'Transfer Htl - Playa',
    'lobby_lounge' => 'Lobby Lounge',
    'bar' => 'Bar',
    'guarda_equipaje' => 'Guarda Equipaje',
    'asoleadoras' => 'Asoleadoras',
    'terraza' => 'Terraza',
    'bar_piscina' => 'Bar en Piscina',
    'servicios_ninera' => 'Niñera',
    'muelle_privado' => 'Muelle Privado',
    'cafe_bar' => 'Café-Bar',
    'concierge' => 'Concierge',
    'super_minimercado' => 'Tienda/MiniMercado',
    'sendero_ecologico' => 'Sendero Ecológico',
    'discoteca' => 'Discoteca',
    'playa' => 'Playa',
    'alquiler_bicicletas' => 'Alquiler Bicis',
    'mini_golf' => 'Mini Golf',
    'cafe_recepcion' => 'Bebidas Recepción',
    'piscina' => 'Piscina',
    'cajero_automatico' => 'Cajero Automático',
    'snack_bar' => 'Snack-bar',
    'capilla' => 'Capilla',
    'piscina_infantil' => 'Piscina Infantil',
    'cambio_moneda' => 'Cambio de Moneda',
    'salon_fitness' => 'Salón Fitness',
    'club_ninos' => 'Club de Niños',
    'pesca' => 'Pesca',
    'enfermeria_medico' => 'Enfermería/Médico',
    'zona_juegos_infantiles' => 'Juegos Infantiles',
    'sala_masajes' => 'Sala de Masajes',
    'salon_juegos' => 'Salón de Juegos',
    'personal_bilingue' => 'Personal Bilingüe',
    'sauna' => 'Sauna',
    'salon_belleza' => 'Salón de Belleza',
    'gimnasio' => 'Gimnasio',
    'juegos_mesa' => 'Juegos de Mesa',
    'ascensor' => 'Ascensor',
    'toallas_playa_piscina' => 'Toallas Playa/Piscina',
    'jacuzzi' => 'Jacuzzi',
    'agua_caliente_hab' => 'Agua Caliente Hab',
    'internet_wifi' => 'Wi-Fi Hotel',
    'internet_cable' => 'Internet por Cable (Hab)',
    'wifi_zonas_comunes' => 'Wi-Fi Zonas Comunes',
    'casino' => 'Casino',
    'lobby_sala_espera' => 'Lobby con sala de espera',
    'spa' => 'Spa',
    'cobertura_internet' => 'Área de cobertura del internet',
    'canal_dedicado' => 'Canal dedicado',
    'otro_servicio' => 'Otros Servicios'
];

// --------- Contenedores ----------
$datos_hotel = null;
$contactos = [];
$servicios = [];
$habitaciones = [];
$salones = [];
$documentos = [];
$docs_por_tipo = [];
$docs_count = 0;

// ---------- GENERAL ----------
if ($idRol === ROL_CADENA) {
    $nitCadena = $_SESSION['usuario'] ?? null;
    if (!$nitCadena) {
        die("<div class='container mt-4 alert alert-danger'>No se pudo identificar la cadena del usuario.</div>");
    }
    $sql_general = "SELECT *,
        salones_eventos_count, centro_negocios_count, espacios_externos_count, forma_conexion,
        channel_manager_nombre, descuento_dinamico, rep_legal_nombre, rep_legal_cargo, acepta_declaracion,
        acepta_terminos, acepta_politicas, acepta_compromiso, tiene_certificado_sostenibilidad
        FROM tbl_alojamiento_general
        WHERE id_hotel = ? AND usuario_creacion = ?";
    $stmt_general = $conn->prepare($sql_general);
    $stmt_general->bind_param("is", $id_hotel, $nitCadena);
} else {
    $sql_general = "SELECT *,
        salones_eventos_count, centro_negocios_count, espacios_externos_count, forma_conexion,
        channel_manager_nombre, descuento_dinamico, rep_legal_nombre, rep_legal_cargo, acepta_declaracion,
        acepta_terminos, acepta_politicas, acepta_compromiso, tiene_certificado_sostenibilidad
        FROM tbl_alojamiento_general WHERE id_hotel = ?";
    $stmt_general = $conn->prepare($sql_general);
    $stmt_general->bind_param("i", $id_hotel);
}
$stmt_general->execute();
$result_general = $stmt_general->get_result();
$datos_hotel = $result_general->fetch_assoc();
$stmt_general->close();


// ================== CAMPOS NUEVOS (JSON / POLÍTICAS) ==================
$tarifa_tipo_data = json_decode($datos_hotel['tarifa_tipo_json'] ?? '[]', true);
if (!is_array($tarifa_tipo_data))
    $tarifa_tipo_data = [];

$planes_tarifarios_data = json_decode($datos_hotel['planes_tarifarios_json'] ?? '[]', true);
if (!is_array($planes_tarifarios_data))
    $planes_tarifarios_data = [];

$allotment_data = json_decode($datos_hotel['allotment_json'] ?? '[]', true);
if (!is_array($allotment_data))
    $allotment_data = [];



if (!$datos_hotel) {
    die("❌ Error: Hotel con ID " . htmlspecialchars($id_hotel) . " no encontrado o sin permisos.");
}

$estado_aprob = strtoupper(trim((string) ($datos_hotel['estado_aprobacion'] ?? 'PENDIENTE')));
$estado_firma = strtoupper(trim((string) ($datos_hotel['estado_firma'] ?? '')));
if ($estado_firma !== 'FIRMADO') {
    $estado_firma = 'SIN FIRMA';
}
$aprobacionFirmada = ($estado_firma === 'FIRMADO');
$aprobacionYaAprobada = ($estado_aprob === 'APROBADO');
$puedeAprobar = ($aprobacionFirmada && !$aprobacionYaAprobada);
$motivoBloqueoAprobacion = '';

if ($aprobacionYaAprobada) {
    $motivoBloqueoAprobacion = '';
} elseif (!$aprobacionFirmada) {
    $motivoBloqueoAprobacion = 'No se puede aprobar hasta que la ficha este firmada.';
}

// ---------- CONTACTOS ----------
$sql_contactos = "SELECT id_contacto, tipo_contacto, nombre, movil, email, telefono
                  FROM tbl_alojamiento_contactos WHERE id_hotel = ?";
$stmt_contactos = $conn->prepare($sql_contactos);
$stmt_contactos->bind_param("i", $id_hotel);
$stmt_contactos->execute();
$result_contactos = $stmt_contactos->get_result();
while ($row = $result_contactos->fetch_assoc()) {
    $contactos[] = $row;
}
$stmt_contactos->close();

// ---------- SERVICIOS ----------
$sql_servicios = "SELECT * FROM tbl_alojamiento_servicios WHERE id_hotel = ?";
$stmt_servicios = $conn->prepare($sql_servicios);
$stmt_servicios->bind_param("i", $id_hotel);
$stmt_servicios->execute();
$result_servicios = $stmt_servicios->get_result();
$servicios = $result_servicios->fetch_assoc() ?? [];
$stmt_servicios->close();

// ---------- HABITACIONES ----------
$sql_habitaciones = "SELECT * FROM tbl_alojamiento_habitaciones WHERE id_hotel = ?";
$stmt_habitaciones = $conn->prepare($sql_habitaciones);
$stmt_habitaciones->bind_param("i", $id_hotel);
$stmt_habitaciones->execute();
$result_habitaciones = $stmt_habitaciones->get_result();
while ($row = $result_habitaciones->fetch_assoc()) {
    $habitaciones[] = $row;
}
$stmt_habitaciones->close();

// ---------- SALONES ----------
$sql_salones = "SELECT * FROM tbl_alojamiento_salones WHERE id_hotel = ?";
$stmt_salones = $conn->prepare($sql_salones);
$stmt_salones->bind_param("i", $id_hotel);
$stmt_salones->execute();
$result_salones = $stmt_salones->get_result();
while ($row = $result_salones->fetch_assoc()) {
    $salones[] = $row;
}
$stmt_salones->close();

// ---------- DOCUMENTOS ----------
$sql_docs = "SELECT id_doc, tipo_documento, nombre_archivo, ruta_almacenamiento, fecha_vigencia, fuente_vigencia, estado_vigencia, dias_vencimiento
             FROM tbl_alojamiento_documentos
             WHERE id_hotel = ? 
             ORDER BY tipo_documento, id_doc";
$stmt_docs = $conn->prepare($sql_docs);
$stmt_docs->bind_param("i", $id_hotel);
$stmt_docs->execute();
$res_docs = $stmt_docs->get_result();
while ($row = $res_docs->fetch_assoc()) {
    $row['fecha'] = date('Y-m-d H:i');
    $documentos[] = $row;
    $docs_por_tipo[$row['tipo_documento']][] = $row;
}
$stmt_docs->close();
$docs_count = count($documentos);

// Decodificar JSON
$tipo_hotel_data = [];
if (!empty($datos_hotel['tipo_hotel_json'])) {
    $tipo_hotel_data = json_decode($datos_hotel['tipo_hotel_json'], true);
    if (!is_array($tipo_hotel_data))
        $tipo_hotel_data = [];
}

$mercados_distribucion_data = json_decode($datos_hotel['mercados_distribucion_json'] ?? '[]', true);
if (!is_array($mercados_distribucion_data))
    $mercados_distribucion_data = [];



// Otros campos tipo "check" (guardados como JSON o texto)
$regimen_alimenticio_data = [];
if (!empty($datos_hotel['regimen_alimenticio_json'])) {
    $regimen_alimenticio_data = json_decode($datos_hotel['regimen_alimenticio_json'], true);
    if (!is_array($regimen_alimenticio_data))
        $regimen_alimenticio_data = [];
} elseif (!empty($datos_hotel['regimen_alimenticio'])) {
    $regimen_alimenticio_data = array_filter(array_map('trim', explode(',', (string) $datos_hotel['regimen_alimenticio'])));
}

$cobertura_internet_data = [];
if (!empty($datos_hotel['cobertura_internet_json'])) {
    $cobertura_internet_data = json_decode($datos_hotel['cobertura_internet_json'], true);
    if (!is_array($cobertura_internet_data))
        $cobertura_internet_data = [];
} elseif (!empty($datos_hotel['cobertura_internet'])) {
    // Si viene como texto
    $cobertura_internet_data = array_filter(array_map('trim', explode(',', (string) $datos_hotel['cobertura_internet'])));
}

$modal_service_data = [];
if (!empty($datos_hotel['modal_service_json'])) {
    $modal_service_data = json_decode($datos_hotel['modal_service_json'], true);
    if (!is_array($modal_service_data))
        $modal_service_data = [];
} elseif (!empty($datos_hotel['modal_service'])) {
    $modal_service_data = array_filter(array_map('trim', explode(',', (string) $datos_hotel['modal_service'])));
}

// ================== FALLBACK: leer checks desde informacion_adicional si no existen columnas ==================
/**
 * En algunos entornos, estos campos NO existen como columnas en tbl_alojamiento_general.
 * En ese caso, actualizarHotel guarda los valores como líneas con marcador dentro de informacion_adicional, por ejemplo:
 *   [[REGIMEN_ALIMENTICIO]]: PC,MP
 *   [[COBERTURA_INTERNET]]: habitaciones,areas_publicas
 *   [[MODAL_SERVICE]]: Habitación,Baño
 */
function markerToArray($text, $marker)
{
    $text = (string) ($text ?? '');
    if ($text === '')
        return [];
    $pattern = '/^' . preg_quote($marker, '/') . ':\s*(.*)$/m';
    if (preg_match($pattern, $text, $m)) {
        $raw = trim($m[1] ?? '');
        if ($raw === '')
            return [];
        // separador CSV
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
    return [];
}

$info_adicional_text = (string) ($datos_hotel['informacion_adicional'] ?? '');
if (empty($regimen_alimenticio_data)) {
    $regimen_alimenticio_data = markerToArray($info_adicional_text, '[[REGIMEN_ALIMENTICIO]]');
}
if (empty($cobertura_internet_data)) {
    $cobertura_internet_data = markerToArray($info_adicional_text, '[[COBERTURA_INTERNET]]');
}
if (empty($modal_service_data)) {
    $modal_service_data = markerToArray($info_adicional_text, '[[MODAL_SERVICE]]');
}

$distribucion_data = [];
if (!empty($datos_hotel['mercados_distribucion_json'])) {
    $distribucion_data = json_decode($datos_hotel['mercados_distribucion_json'], true);
    if (!is_array($distribucion_data))
        $distribucion_data = [];
}

// ================== FIRMA ACTUAL (última) ==================
$firma_doc_id = 0;
$firma_url = null;
$firma_img = null;
$firma_zoom = null;

// Buscar última firma guardada
$stmtSig = $conn->prepare("
    SELECT id_doc, ruta_almacenamiento, nombre_archivo
    FROM tbl_alojamiento_documentos
    WHERE id_hotel = ?
      AND tipo_documento = 'Firma Digital'
    ORDER BY id_doc DESC
    LIMIT 1
");
$stmtSig->bind_param("i", $id_hotel);
$stmtSig->execute();
$resSig = $stmtSig->get_result();
$rowSig = $resSig->fetch_assoc();
$stmtSig->close();

if ($rowSig && !empty($rowSig['ruta_almacenamiento'])) {
    $firma_doc_id = (int) $rowSig['id_doc'];
    $firma_url = $rowSig['ruta_almacenamiento'];

    // URL para mostrar como imagen (reutiliza tus helpers)
    $firma_img = driveViewUrl($firma_url);
    $firma_zoom = driveViewUrl($firma_url);

    // Cache-bust para que NO muestre la vieja
    $cb = '?v=' . $firma_doc_id;
    if ($firma_img)
        $firma_img .= $cb;
    if ($firma_zoom)
        $firma_zoom .= $cb;
}



?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Hotel (Consulta): <?php echo htmlspecialchars($datos_hotel['nombre']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="estilos_hotel_moderno.css?v=habitaciones-20260612">
    <link rel="icon" type="image/x-icon" href="../../img/pnv.png">
    <style>
        /* Estilo alineado con formularioIncripHotel.php */
        body {
            background-color: #f8f9fa;
            background-image: url('ruta/a/tu/logo.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        /* Contenedor principal tipo “tarjeta” */
        .container-fluid.page-card {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            max-width: 1280px;
        }

        h1,
        h3 {
            color: #0d6efd;
        }

        /* Tabs */
        .nav-link.active {
            background-color: #0d6efd !important;
            color: white !important;
        }

        /* Tamaño compacto coherente */
        .form-control-sm,
        .form-select-sm {
            font-size: 0.85rem;
        }

        /* Indicador requerido */
        .form-label.required::after {
            content: '*';
            color: #dc3545;
            margin-left: 2px;
        }

        .btn-xs {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }

        .tabla-documentos-legales {
            min-width: 980px;
        }

        .tabla-documentos-legales .col-tipo-documento {
            width: 220px;
            min-width: 220px;
        }

        .tabla-documentos-legales .badge-tipo-documento {
            display: inline-block;
            max-width: 100%;
            white-space: normal;
            line-height: 1.25;
            text-align: left;
        }

        @media print {

            .nav-tabs,
            .btn,
            form button[type=submit] {
                display: none;
            }

            .tab-pane {
                display: block !important;
                opacity: 1 !important;
                margin-bottom: 30px;
                page-break-after: always;
            }
        }

        /* ---- Layout Servicios y Amenidades (Editar) ---- */
        .servicios-grid .box {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
            padding: 12px 14px;
            background: #fff;
            height: 100%;
        }

        .servicios-grid .box-title {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .servicios-grid .form-check {
            margin-bottom: 6px;
        }

        /* Dos columnas SOLO para amenidades (evita que se pegue todo) */
        .servicios-grid .amenidades-two-col {
            column-count: 2;
            column-gap: 18px;
        }

        .servicios-grid .amenidades-two-col .form-check {
            break-inside: avoid;
        }

        @media (max-width: 992px) {
            .servicios-grid .amenidades-two-col {
                column-count: 1;
            }
        }
    </style>
</head>

<body>
<?php if ($headerFile) include_once $headerFile; ?>
    <div class="container-fluid page-card mt-5 mb-5">
        <h1 class="mb-2">Ficha de Proveedor: <?php echo htmlspecialchars($datos_hotel['nombre']); ?></h1>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php unset($_SESSION['flash_success']); ?>

        <div class="acciones-ficha d-flex flex-column flex-sm-row flex-wrap gap-2 mb-3">

            <?php
            // 1. BLOQUE PARA EDITAR: Visible para Admin (1), Cadena (7) y Proveedor (6)
            if (isset($_SESSION['id_rol']) && in_array((int) $_SESSION['id_rol'], [ROL_ADMIN, ROL_CADENA, ROL_PROVEEDOR], true)):
                ?>
                <a href="editarHotel.php?id=<?php echo (int) $datos_hotel['id_hotel']; ?>"
                    class="btn btn-primary d-inline-flex align-items-center justify-content-center">
                    <i class="fas fa-edit me-2"></i> Editar Ficha
                </a>
            <?php endif; ?>

            <?php
            // 2. BLOQUE DE GESTIÓN: Visible solo para Admin (1), Analistas (2, 8) y Gestoras (9)
            if (isset($_SESSION['id_rol']) && in_array((int) $_SESSION['id_rol'], [ROL_ADMIN, ROL_2, ROL_8, ROL_GESTORAS], true)):
                ?>
                <?php if ($puedeAprobar): ?>
                    <a href="../controlador/enviar_aprobacion.php?id=<?php echo (int) $datos_hotel['id_hotel']; ?>"
                        class="btn btn-success d-inline-flex align-items-center justify-content-center"
                        id="btnEnviarAprobacion">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerAprobacion" role="status"
                            aria-hidden="true"></span>
                        <span id="textEnviarAprobacion">Aprobar</span>
                    </a>
                <?php else: ?>
                    <button type="button"
                        class="btn btn-success d-inline-flex align-items-center justify-content-center"
                        id="btnEnviarAprobacion" aria-disabled="true"
                        data-aprobacion-bloqueada="1"
                        data-aprobacion-mensaje="<?php echo htmlspecialchars($motivoBloqueoAprobacion, ENT_QUOTES, 'UTF-8'); ?>"
                        title="<?php echo htmlspecialchars($motivoBloqueoAprobacion, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerAprobacion" role="status"
                            aria-hidden="true"></span>
                        <span id="textEnviarAprobacion"><?php echo $aprobacionYaAprobada ? 'Aprobado' : 'Aprobar'; ?></span>
                    </button>
                <?php endif; ?>

                <button type="button" class="btn btn-danger-corp d-inline-flex align-items-center justify-content-center"
                    id="btnRechazar" data-bs-toggle="modal" data-bs-target="#modalRechazo"
                    data-id="<?php echo (int) $datos_hotel['id_hotel']; ?>"
                    data-nombre="<?php echo htmlspecialchars($datos_hotel['nombre'], ENT_QUOTES); ?>">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerRechazo" role="status"
                        aria-hidden="true"></span>
                    <span id="textRechazar">Rechazar</span>
                </button>

                <?php if ($idRol === ROL_ADMIN): ?>
                    <a href="../controlador/enviar_zoho.php?id=<?php echo $datos_hotel['id_hotel']; ?>"
                        class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center" id="btnZoho">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerZoho" role="status"
                            aria-hidden="true"></span>
                        <span id="textZoho">Enviar a Zoho</span>
                    </a>

                    <a href="../controlador/enviar_zeus.php?id=<?php echo $datos_hotel['id_hotel']; ?>&debug=1"
                        class="btn btn-secondary d-inline-flex align-items-center justify-content-center" id="btnZeus">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerZeus" role="status"
                            aria-hidden="true"></span>
                        <span id="textZeus">Zeus (Debug)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

        </div>

        <?php if (isset($_SESSION['id_rol']) && in_array((int) $_SESSION['id_rol'], [ROL_ADMIN, ROL_2, ROL_8, ROL_GESTORAS], true) && !$puedeAprobar): ?>
            <div id="avisoAprobacionBloqueada"
                class="alert <?php echo $aprobacionYaAprobada ? 'alert-info' : 'alert-warning'; ?> d-none mb-3"
                role="alert"></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['id_rol']) && in_array((int) $_SESSION['id_rol'], [ROL_ADMIN, ROL_2, ROL_8, ROL_GESTORAS], true)): ?>
            <div class="modal fade" id="modalRechazo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST" action="../controlador/enviar_rechazo.php" class="modal-content">
                        <div class="modal-header">
                            <h5 style="color: white !important;" class="modal-title">Rechazar ficha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_hotel" id="rechazo_id_hotel" value="">
                            <p class="mb-2">Estás rechazando la ficha de: <strong id="rechazo_nombre_hotel">---</strong></p>
                            <label for="motivo_rechazo" class="form-label mb-1"><strong>Motivo del rechazo</strong></label>
                            <textarea class="form-control" name="motivo_rechazo" id="motivo_rechazo" rows="5" required
                                placeholder="Describe qué debe corregir el hotel/cadena..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger-corp" id="btnEnviarRechazoModal">Enviar
                                rechazo</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <p class="lead text-muted">
            ID de Registro: <?php echo htmlspecialchars($datos_hotel['id_hotel']); ?>
            | NIT: <?php echo htmlspecialchars($datos_hotel['nit']); ?>
        </p>
        <div class="d-flex flex-wrap gap-3 mb-4 mt-2">
            <div class="d-flex align-items-center border rounded px-3 py-2 bg-white shadow-sm">
                <span class="text-muted small me-2 text-uppercase fw-bold">Aprobación:</span>
                <?php
                $estado_aprob = strtoupper($datos_hotel['estado_aprobacion'] ?? 'PENDIENTE');
                $clase_aprob = 'bg-secondary'; // Default
                
                if ($estado_aprob == 'APROBADO')
                    $clase_aprob = 'bg-success';
                elseif ($estado_aprob == 'RECHAZADO')
                    $clase_aprob = 'bg-danger';
                elseif ($estado_aprob == 'PENDIENTE')
                    $clase_aprob = 'bg-warning text-dark';
                ?>
                <span style="color: black !important;" class="badge <?php echo $clase_aprob; ?> fs-6">
                    <?php echo $estado_aprob; ?>
                </span>
            </div>

            <div class="d-flex align-items-center border rounded px-3 py-2 bg-white shadow-sm">
                <span class="text-muted small me-2 text-uppercase fw-bold">Firma Digital:</span>
                <?php
                $estado_firma = strtoupper(trim((string) ($datos_hotel['estado_firma'] ?? '')));
                if ($estado_firma !== 'FIRMADO') {
                    $estado_firma = 'SIN FIRMA';
                }
                $clase_firma = ($estado_firma == 'FIRMADO') ? 'bg-success' : 'bg-warning text-dark';
                ?>
                <span style="color: black !important;" class="badge <?php echo $clase_firma; ?> fs-6">
                    <?php echo $estado_firma; ?>
                </span>
            </div>
        </div>
        <form id="formHotel" action="../controlador/actualizarHotel.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_hotel" value="<?php echo (int) $id_hotel; ?>">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="general-tab"
                        data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">1. General</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="contactos-tab"
                        data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab">2. Contactos</button>
                </li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="servicios-tab"
                        data-bs-toggle="tab" data-bs-target="#servicios" type="button" role="tab">3. Servicios</button>
                </li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="habitaciones-tab"
                        data-bs-toggle="tab" data-bs-target="#habitaciones" type="button" role="tab">4.
                        Habitaciones</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="salones-tab" data-bs-toggle="tab"
                        data-bs-target="#salones" type="button" role="tab">5. Salones</button></li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos"
                        type="button" role="tab">
                        6. Documentos
                        <?php if ($docs_count): ?>
                            <span class="badge bg-secondary ms-1"><?php echo (int) $docs_count; ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-legal-firma-tab" data-bs-toggle="tab"
                        data-bs-target="#tab-legal-firma" type="button" role="tab" aria-controls="tab-legal-firma"
                        aria-selected="false">
                        7. Legal y Firma
                    </button>
                </li>

            </ul>

            <div class="tab-content border border-top-0 p-4" id="myTabContent">

                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <h3 class="mb-3">Información General del Hotel y Producto</h3>


                    <div class="row g-4">

                        <div class="card mb-4 black-theme-card">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="4">INFORMACIÓN GENERAL</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td class="required-label">Cadena/Grupo Hotelero:</td>
                                        <td colspan="3">
                                            <input type="text" name="general[cadena_hotelera]" class="form-control"
                                                required aria-required="true"
                                                value="<?php echo htmlspecialchars($datos_hotel['cadena_hotelera'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <?php
                                    // 1. ¿Es usuario CADENA?
                                    $esCadena = isset($_SESSION['id_rol']) && (int) $_SESSION['id_rol'] === 7;

                                    // 2. NIT base sugerido (normalmente el usuario = NIT de la cadena)
                                    $nit_base_sugerido = $esCadena ? ($_SESSION['usuario'] ?? '') : '';

                                    // 3. Sufijo por defecto = A
                                    $nit_sufijo_sugerido = 'A';

                                    // 4. Si es cadena y tenemos NIT base, intentamos consultar la BD
                                    if ($esCadena && $nit_base_sugerido !== '') {

                                        // Conexión local SOLO para esta consulta
                                        $mysqli = new mysqli("localhost", "root", "", "facturacion"); // ajusta si tus credenciales son otras
                                    
                                        if (!$mysqli->connect_errno) {

                                            $sql = "SELECT nit_consecutivo
                        FROM tbl_alojamiento_general
                        WHERE nit = ?
                          AND nit_consecutivo IS NOT NULL
                          AND nit_consecutivo <> ''";

                                            if ($stmt = $mysqli->prepare($sql)) {
                                                $stmt->bind_param("s", $nit_base_sugerido);
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                $usados = [];

                                                while ($row = $res->fetch_assoc()) {
                                                    $nc = $row['nit_consecutivo'];

                                                    if (strpos($nc, $nit_base_sugerido) === 0) {
                                                        // Lo que viene después del NIT base
                                                        $suffix = substr($nc, strlen($nit_base_sugerido));
                                                        $suffix = strtoupper(preg_replace('/[^A-Z]/', '', $suffix));
                                                        if ($suffix !== '') {
                                                            $usados[$suffix] = true;
                                                        }
                                                    }
                                                }
                                                $stmt->close();

                                                // Buscar primera letra libre de A..Z
                                                for ($i = 0; $i < 26; $i++) {
                                                    $letra = chr(ord('A') + $i);
                                                    if (!isset($usados[$letra])) {
                                                        $nit_sufijo_sugerido = $letra;
                                                        break;
                                                    }
                                                }
                                            }

                                            $mysqli->close();
                                        }
                                    }
                                   
                                    $tiene_nit_consecutivo = !empty($datos_hotel['nit_consecutivo']);
                                    ?>

                                    <tr>
                                        <td class="required-label">Nombre del Hotel:</td>
                                        <td>
                                            <input type="text" name="general[nombre]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>

                                        <td class="required-label">
                                            <?php if ($tiene_nit_consecutivo): ?>
                                                NIT Consecutivo:
                                            <?php else: ?>
                                                NIT:
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tiene_nit_consecutivo): ?>
                                                <!-- Mostrar NIT Consecutivo (ej: 860402288A) -->
                                                <input type="text" class="form-control" 
                                                    value="<?php echo htmlspecialchars($datos_hotel['nit_consecutivo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                    readonly>
                                                <input type="hidden" name="general[nit]" value="<?php echo htmlspecialchars($datos_hotel['nit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="general[nit_consecutivo]" value="<?php echo htmlspecialchars($datos_hotel['nit_consecutivo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <small class="text-muted d-block mt-1">
                                                    NIT base: <?php echo htmlspecialchars($datos_hotel['nit'] ?? ''); ?>
                                                </small>
                                            <?php elseif ($esCadena): ?>
                                                <!-- Vista especial para CADENA creando nuevo hotel -->
                                                <div class="input-group">
                                                    <span class="input-group-text">NIT</span>
                                                    <input type="text" class="form-control" name="general[nit]" id="nitBase"
                                                        value="<?php echo htmlspecialchars($datos_hotel['nit'] ?? $nit_base_sugerido, ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="860402288" required>
                                                    <span class="input-group-text">Sufijo</span>
                                                    <input type="text" class="form-control text-center fw-bold sufijo-input"
                                                        id="nitSufijo" maxlength="2"
                                                        value="<?php echo htmlspecialchars($nit_sufijo_sugerido); ?>"
                                                        required readonly>
                                                </div>
                                                <input type="hidden" name="general[nit_consecutivo]" id="nitConsecutivo"
                                                    value="<?php echo htmlspecialchars($datos_hotel['nit_consecutivo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <small class="form-text text-muted">
                                                    El sistema asigna automáticamente un consecutivo interno para este NIT de cadena.
                                                </small>
                                            <?php else: ?>
                                                <!-- Vista normal para NO cadenas -->
                                                <input type="text" name="general[nit]" class="form-control" required
                                                    value="<?php echo htmlspecialchars($datos_hotel['nit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="required-label">Razón Social:</td>
                                        <td colspan="3">
                                            <input type="text" name="general[razon_social]" id="razon_social"
                                                class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['razon_social'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="required-label">Teléfono:</td>
                                        <td colspan="3">
                                            <input type="number" name="general[telefono]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="required-label">Dirección del hotel:</td>
                                        <td colspan="3">
                                            <input type="text" name="general[direccion]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="required-label">Ciudad:</td>
                                        <td>
                                            <input type="text" name="general[ciudad]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['ciudad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                        <td class="required-label">País:</td>
                                        <td>
                                            <input type="text" name="general[pais]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['pais'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Website:</td>
                                        <td>
                                            <input type="text" name="general[website]" class="form-control"
                                                value="<?php echo htmlspecialchars($datos_hotel['website'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                        <td class="required-label">Categoría:</td>
                                        <td>
                                            <select id="categoria" name="general[categoria]" class="form-control"
                                                required>
                                                <option value="" selected>-----</option>
                                                <option value="3-star">3 Estrellas</option>
                                                <option value="4-star">4 Estrellas</option>
                                                <option value="5-star">5 Estrellas</option>
                                                <option value="boutique">Boutique</option>
                                                <option value="glamping">Glamping</option>
                                                <option value="luxury">Luxury</option>
                                            </select>
                                        </td>
                                        <script>
                                            (function () {
                                                const select = document.getElementById('categoria');
                                                const current = <?php echo json_encode($datos_hotel['categoria'] ?? ''); ?>;
                                                if (current) {
                                                    for (const opt of select.options) {
                                                        if (opt.value === current) { opt.selected = true; break; }
                                                    }
                                                } else {
                                                    // deja el placeholder seleccionado
                                                    select.selectedIndex = 0;
                                                }
                                            })();
                                        </script>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <!-- ===================== DESCRIPCION PRODUCTO ===================== -->
                        <h4 class="text-center mt-2 mb-4">DESCRIPCION PRODUCTO</h4>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Descripción del Hotel</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="descripcionProducto"
                                            class="form-label required-label">Descripción</label>
                                        <textarea id="descripcionProducto" name="general[descripcion_producto]"
                                            class="form-control" rows="4"
                                            placeholder="Favor escribir una breve descripción del hotel..."
                                            required><?php echo htmlspecialchars($datos_hotel['descripcion_producto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Habitaciones y Tarifas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="tarifasIncluyen" class="form-label required-label">¿Las tarifas
                                            incluyen desayuno?</label>
                                        <select id="tarifasIncluyen" name="general[incluye_desayuno]"
                                            class="form-select" required>
                                            <option value="0" <?php echo (empty($datos_hotel['incluye_desayuno']) && $datos_hotel['incluye_desayuno'] !== '0') ? 'selected' : ''; ?>>-------
                                            </option>
                                            <option value="1" <?php echo ((string) ($datos_hotel['incluye_desayuno'] ?? '') === '1') ? 'selected' : ''; ?>>Sí</option>
                                            <option value="2" <?php echo ((string) ($datos_hotel['incluye_desayuno'] ?? '') === '0') ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="numeroHabitaciones" class="form-label required-label">Número total
                                            de habitaciones</label>
                                        <input type="number" id="numeroHabitaciones" name="general[numero_habitaciones]"
                                            class="form-control" required min="0"
                                            value="<?php echo htmlspecialchars($datos_hotel['numero_habitaciones'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="precioDesayuno" class="form-label">Precio del desayuno por persona
                                            (si no está incluido)</label>
                                        <input type="text" id="precioDesayuno" name="general[precio_desayuno]"
                                            class="form-control"
                                            value="<?php echo htmlspecialchars($datos_hotel['precio_desayuno'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="tipoDesayuno" class="form-label required-label">Tipo de
                                            desayuno</label>
                                        <select id="tipoDesayuno" name="general[tipo_desayuno]" class="form-select"
                                            required>
                                            <option value="0" <?php echo ((string) ($datos_hotel['tipo_desayuno'] ?? '') === "0") ? 'selected' : ''; ?>>-------</option>
                                            <option value="a_la_carta" <?php echo ((string) ($datos_hotel['tipo_desayuno'] ?? '') === "a_la_carta") ? 'selected' : ''; ?>>A la Carta</option>
                                            <option value="americano" <?php echo ((string) ($datos_hotel['tipo_desayuno'] ?? '') === "americano") ? 'selected' : ''; ?>>Americano</option>
                                            <option value="buffet" <?php echo ((string) ($datos_hotel['tipo_desayuno'] ?? '') === "buffet") ? 'selected' : ''; ?>>Buffet</option>
                                            <option value="continental" <?php echo ((string) ($datos_hotel['tipo_desayuno'] ?? '') === "continental") ? 'selected' : ''; ?>>Continental</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="horaCheckIn" class="form-label required-label">Hora Check In</label>
                                        <input type="time" id="horaCheckIn" name="general[hora_check_in]"
                                            class="form-control" required
                                            value="<?php echo htmlspecialchars($datos_hotel['hora_check_in'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="horaCheckOut" class="form-label required-label">Hora Check
                                            Out</label>
                                        <input type="time" id="horaCheckOut" name="general[hora_check_out]"
                                            class="form-control" required
                                            value="<?php echo htmlspecialchars($datos_hotel['hora_check_out'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Servicios y Amenidades</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="restauranteEspecializado"
                                                name="general[amenidad_restaurante]" value="1" class="form-check-input"
                                                <?php echo ((string) ($datos_hotel['amenidad_restaurante'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="restauranteEspecializado" class="form-check-label">Restaurante
                                                Especializado</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="barLounge" name="general[amenidad_bar_lounge]"
                                                value="1" class="form-check-input" <?php echo ((string) ($datos_hotel['amenidad_bar_lounge'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="barLounge" class="form-check-label">Bar/Lounge</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="habitacionesEspeciales"
                                                name="general[amenidad_hab_especiales]" value="1"
                                                class="form-check-input" <?php echo ((string) ($datos_hotel['amenidad_hab_especiales'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="habitacionesEspeciales" class="form-check-label">Habitaciones o
                                                pisos especiales</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="gayFriendly"
                                                name="general[amenidad_gay_friendly]" value="1" class="form-check-input"
                                                <?php echo ((string) ($datos_hotel['amenidad_gay_friendly'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="gayFriendly" class="form-check-label">Gay Friendly</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="planesBoda" name="general[amenidad_planes_boda]"
                                                value="1" class="form-check-input" <?php echo ((string) ($datos_hotel['amenidad_planes_boda'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="planesBoda" class="form-check-label">Planes de Boda y Luna de
                                                Miel</label>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="mb-2 required-label" style="font-weight: bold;">
                                            Régimen Alimenticio (Seleccione uno o más)
                                        </h6>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="pensioncompleta" name="regimen_alimenticio[]"
                                                value="PC" class="form-check-input" <?php if (in_array('PC', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="pensioncompleta" class="form-check-label">
                                                PC: Pensión Completa
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="mediapension" name="regimen_alimenticio[]"
                                                value="MP" class="form-check-input" <?php if (in_array('MP', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="mediapension" class="form-check-label">
                                                MP: Media Pensión
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="desayunobuffet" name="regimen_alimenticio[]"
                                                value="BB" class="form-check-input" <?php if (in_array('BB', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="desayunobuffet" class="form-check-label">
                                                BB: Desayuno Buffet
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="desayunoamericano" name="regimen_alimenticio[]"
                                                value="AB" class="form-check-input" <?php if (in_array('AB', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="desayunoamericano" class="form-check-label">
                                                AB: Desayuno Americano
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="desayunoalacarta" name="regimen_alimenticio[]"
                                                value="CB" class="form-check-input" <?php if (in_array('CB', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="desayunoalacarta" class="form-check-label">
                                                CB: Desayuno a la Carta
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="todoIncluido" name="regimen_alimenticio[]"
                                                value="FULL" class="form-check-input" <?php if (in_array('FULL', $regimen_alimenticio_data ?? []))
                                                    echo 'checked'; ?>>
                                            <label for="todoIncluido" class="form-check-label">
                                                FULL: Todo Incluido
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Política de Mascotas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="PetFriendly" class="form-label required-label">Pet Friendly</label>
                                        <select id="PetFriendly" name="general[es_pet_friendly]" class="form-select"
                                            required onchange="togglePolicyDetails()">
                                            <option value="0" <?php echo ($datos_hotel['es_pet_friendly'] === null || $datos_hotel['es_pet_friendly'] === '') ? 'selected' : ''; ?>>-------
                                            </option>
                                            <option value="1" <?php echo ((string) ($datos_hotel['es_pet_friendly'] ?? '') === '1') ? 'selected' : ''; ?>>Sí</option>
                                            <option value="2" <?php echo ((string) ($datos_hotel['es_pet_friendly'] ?? '') === '0') ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label for="politicaMascotas" class="form-label" id="policyDetailsLabel">
                                            Detalles de la política <span id="requiredIndicator"
                                                style="color: red; display: none;">*</span>
                                        </label>
                                        <textarea id="politicaMascotas" name="general[politica_mascotas]"
                                            class="form-control" rows="3"
                                            placeholder="Describa la política de mascotas, si aplica"><?php echo htmlspecialchars($datos_hotel['politica_mascotas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function togglePolicyDetails() {
                                const petFriendly = document.getElementById('PetFriendly');
                                const policyDetails = document.getElementById('politicaMascotas');
                                const requiredIndicator = document.getElementById('requiredIndicator');

                                if (petFriendly.value === '1') {
                                    policyDetails.required = true;
                                    requiredIndicator.style.display = 'inline';
                                } else {
                                    policyDetails.required = false;
                                    requiredIndicator.style.display = 'none';
                                }
                            }

                            document.addEventListener('DOMContentLoaded', togglePolicyDetails);
                        </script>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Accesibilidad</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="hidden" name="general[accesibilidad_banos]" value="0">
                                            <input type="checkbox" id="banosAccesibles"
                                                name="general[accesibilidad_banos]" value="1" class="form-check-input"
                                                <?php echo ((string) ($datos_hotel['accesibilidad_banos'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="banosAccesibles" class="form-check-label">Baños
                                                accesibles</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="hidden" name="general[accesibilidad_habitaciones]" value="0">
                                            <input type="checkbox" id="habitacionesAccesibles"
                                                name="general[accesibilidad_habitaciones]" value="1"
                                                class="form-check-input" <?php echo ((string) ($datos_hotel['accesibilidad_habitaciones'] ?? '0') === '1') ? 'checked' : ''; ?>>
                                            <label for="habitacionesAccesibles" class="form-check-label">Habitaciones
                                                accesibles</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="hidden" name="general[accesibilidad_espacios_comunes]"
                                                value="0">
                                            <input type="checkbox" id="espaciosAccesibles"
                                                name="general[accesibilidad_espacios_comunes]" value="1"
                                                class="form-check-input">
                                            <label for="espaciosAccesibles" class="form-check-label">Espacios comunes
                                                accesibles</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="habitacionesDiscapacidad" class="form-label">Habitaciones para
                                            discapacidad</label>
                                        <input type="number" id="habitacionesDiscapacidad"
                                            name="general[habitaciones_discapacidad]" class="form-control" min="0"
                                            value="<?php echo htmlspecialchars($datos_hotel['habitaciones_discapacidad'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="habitacionesConnecting" class="form-label">Habitaciones
                                            Connecting</label>
                                        <input type="number" id="habitacionesConnecting"
                                            name="general[habitaciones_connecting]" class="form-control" min="0"
                                            value="<?php echo htmlspecialchars($datos_hotel['habitaciones_connecting'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0 required-label">Tipo de Hotel (Seleccione uno o más)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="familiar" name="tipo_hotel[]" value="familiar"
                                                class="form-check-input" <?php echo (in_array("familiar", $tipo_hotel_data ?? [], true)) ? "checked" : ""; ?>>
                                            <label for="familiar" class="form-check-label">Familiar</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="gayOnly" name="tipo_hotel[]" value="gay_only"
                                                class="form-check-input" <?php echo (in_array("gay_only", $tipo_hotel_data ?? [], true)) ? "checked" : ""; ?>>
                                            <label for="gayOnly" class="form-check-label">Gay Only</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="corporativo" name="tipo_hotel[]"
                                                value="corporativo" class="form-check-input" <?php echo (in_array("corporativo", $tipo_hotel_data ?? [], true)) ? "checked" : ""; ?>>
                                            <label for="corporativo" class="form-check-label">Corporativo</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="boutique" name="tipo_hotel[]" value="boutique"
                                                class="form-check-input" <?php echo (in_array("boutique", $tipo_hotel_data ?? [], true)) ? "checked" : ""; ?>>
                                            <label for="boutique" class="form-check-label">Boutique</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" id="soloAdultos" name="tipo_hotel[]"
                                                value="solo_adultos" class="form-check-input" <?php echo (in_array("solo_adultos", $tipo_hotel_data ?? [], true)) ? "checked" : ""; ?>>
                                            <label for="soloAdultos" class="form-check-label">Solo Adultos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 black-theme-card">
                            <div class="card-header">
                                <h5 class="mb-0">Información Adicional</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="informacionAdicional" class="form-label">Información
                                            adicional</label>
                                        <textarea id="informacionAdicional" name="general[informacion_adicional]"
                                            class="form-control" rows="4"
                                            placeholder="Escriba cualquier información adicional"><?php echo htmlspecialchars($datos_hotel['informacion_adicional'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- =============================================================== -->


                    </div>

                    <style>
                        .separador {
                            border-top: 4px solid #333;
                        }
                    </style>




                    <div class="card mb-4 black-theme-card">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">CONDICIONES DE NEGOCIACIÓN</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Mercados de distribución:</strong></td>
                                        <td>
                                            <?php
                                            // Mercado puede venir como array JSON o como string simple
                                            $mercado_actual = '';
                                            if (!empty($mercados_distribucion_data)) {
                                                // si guardaste ["Nacional"] o ["Ambos"], tomamos el primero
                                                $mercado_actual = is_array($mercados_distribucion_data) ? (string) ($mercados_distribucion_data[0] ?? '') : (string) $mercados_distribucion_data;
                                            } else {
                                                $mercado_actual = (string) ($datos_hotel['mercados_distribucion_json'] ?? '');
                                            }
                                            ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="mercado_distribucion[]" id="mercado-nac" value="Nacional"
                                                    required <?php if ($mercado_actual === 'Nacional')
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label" for="mercado-nac">Nacional</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="mercado_distribucion[]" id="mercado-int" value="Internacional"
                                                    <?php if ($mercado_actual === 'Internacional')
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label" for="mercado-int">Internacional</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="mercado_distribucion[]" id="mercado-ambos" value="Ambos" <?php if ($mercado_actual === 'Ambos')
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label" for="mercado-ambos">Ambos</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Forma de pago:</strong></td>
                                        <td>Transferencia bancaria.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Monto de crédito aprobado:</strong></td>
                                        <td>
                                            <input type="text" class="form-control" id="monto_visual" placeholder="$ 0"
                                                value="<?php echo htmlspecialchars($datos_hotel['monto_credito'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="general[monto_credito]" id="monto_real"
                                                value="<?php echo htmlspecialchars($datos_hotel['monto_credito'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Tiempo de crédito aprobado:</strong></td>
                                        <td>
                                            <input type="text" class="form-control" name="general[tiempo_credito]"
                                                placeholder="Ejemplo: 5 Días"
                                                value="<?php echo htmlspecialchars($datos_hotel['tiempo_credito'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Indicar %RETEICA:</strong></td>
                                        <td>
                                            <input type="text" class="form-control" name="general[reteica]"
                                                placeholder="Ejemplo: 5%"
                                                value="<?php echo htmlspecialchars($datos_hotel['reteica'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Indicar % RETEFUENTE:</strong></td>
                                        <td>
                                            <input type="text" class="form-control" name="general[retefuente]"
                                                placeholder="Ejemplo: 5%"
                                                value="<?php echo htmlspecialchars($datos_hotel['retefuente'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Código CIIU:</strong></td>
                                        <td>
                                            <input type="text" name="general[ciiu]" class="form-control"
                                                placeholder="Ej: 5511" maxlength="4"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');" required
                                                value="<?php echo htmlspecialchars($datos_hotel['ciiu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="form-text">Código de 4 dígitos de su actividad económica.</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipo de contribuyente:</strong></td>
                                        <td>
                                            <select id="tipoContribuyente" name="general[tipo_contribuyente]"
                                                class="form-select" required>
                                                <option value="" disabled>-- Selecciona un tipo de contribuyente --
                                                </option>

                                                <optgroup label="Personas Naturales">
                                                    <option value="PN - No responsable de IVA">Persona Natural - No
                                                        responsable de IVA</option>
                                                    <option value="PN - Responsable de IVA">Persona Natural -
                                                        Responsable de
                                                        IVA</option>
                                                    <option value="PN - Régimen Simple (RST)">Persona Natural - Régimen
                                                        Simple (RST)</option>
                                                    <option value="PN - Profesional Independiente">Persona Natural -
                                                        Profesional Independiente</option>
                                                </optgroup>

                                                <optgroup label="Personas Jurídicas">
                                                    <option value="PJ - Responsable de IVA">Persona Jurídica -
                                                        Responsable
                                                        de IVA</option>
                                                    <option value="PJ - No responsable de IVA">Persona Jurídica - No
                                                        responsable de IVA</option>
                                                    <option value="PJ - Gran Contribuyente">Gran Contribuyente</option>
                                                    <option value="PJ - Autorretenedor">Autorretenedor</option>
                                                    <option value="PJ - Entidad sin Ánimo de Lucro (ESAL)">Entidad sin
                                                        Ánimo
                                                        de Lucro (ESAL)</option>
                                                    <option value="PJ - Sociedad de Comercialización Internacional">
                                                        Sociedad
                                                        de Comercialización Internacional (C.I.)</option>
                                                </optgroup>

                                                <optgroup label="Casos Especiales / Otros">
                                                    <option value="Consorcio / Unión Temporal">Consorcio / Unión
                                                        Temporal
                                                    </option>
                                                    <option value="Sucesión Ilíquida">Sucesión Ilíquida (Herencias no
                                                        repartidas)</option>
                                                    <option value="Entidad de Derecho Público">Entidad de Derecho
                                                        Público
                                                        (Gobierno)</option>
                                                    <option value="No Residente">Persona Natural Extranjera / No
                                                        Residente
                                                    </option>
                                                    <option value="Otro">Otro (No especificado)</option>
                                                </optgroup>
                                            </select>

                                            <script>
                                                (function () {
                                                    const select = document.getElementById('tipoContribuyente');
                                                    const current = <?php echo json_encode($datos_hotel['tipo_contribuyente'] ?? ''); ?>;
                                                    if (current) {
                                                        for (const opt of select.options) {
                                                            if (opt.value === current) { opt.selected = true; break; }
                                                        }
                                                    } else {
                                                        // deja el placeholder seleccionado
                                                        select.selectedIndex = 0;
                                                    }
                                                })();
                                            </script>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Número de cuenta bancaria:</strong></td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-bank"></i> #</span>
                                                <input type="text" class="form-control" id="numero_cuenta"
                                                    name="general[numero_cuenta]" placeholder="Ej: 0123456789"
                                                    inputmode="numeric"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" required
                                                    value="<?php echo htmlspecialchars($datos_hotel['numero_cuenta'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-text">Solo se permiten números, sin espacios ni guiones.
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Forma de conexión (una opción):</strong></td>
                                        <td>
                                            <?php
                                            $forma_conexion_actual = (string) ($datos_hotel['forma_conexion'] ?? '');
                                            $channel_manager_actual = (string) ($datos_hotel['channel_manager_nombre'] ?? '');
                                            ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="general[forma_conexion]" id="extranetRadio" value="extranet"
                                                    <?php if ($forma_conexion_actual === 'extranet')
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label" for="extranetRadio">Extranet</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="general[forma_conexion]" id="channelManagerRadio"
                                                    value="channelManager" <?php if ($forma_conexion_actual === 'channelManager')
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label" for="channelManagerRadio">Channel
                                                    Manager</label>
                                            </div>

                                            <select id="channelManagerName" name="general[channel_manager_nombre]"
                                                class="form-select" style="display: none; margin-top:10px;">
                                                <option value="" disabled selected>-- Selecciona un Channel Manager --
                                                </option>
                                                <option value="Cloudbeds">Cloudbeds</option>
                                                <option value="Siteminder">Siteminder</option>
                                                <option value="TravelClick">TravelClick (Amadeus)</option>
                                                <option value="FNSRooms">FNSRooms</option>
                                                <option value="Omnibees">Omnibees</option>
                                                <option value="RateGain">RateGain (Sabre y SynXis)</option>
                                                <option value="Roibos">Roibos</option>
                                                <option value="Roomcloud">Roomcloud</option>
                                                <option value="Myallocator">Myallocator</option>
                                                <option value="Dingus">Dingus</option>
                                                <option value="Pxol">Pxol</option>
                                                <option value="Otro">Otro (no listado)</option>
                                            </select>

                                            <input type="text" id="otroChannelInput"
                                                name="general[channel_manager_nombre]" class="form-control"
                                                placeholder="Escribe el nombre del Channel Manager"
                                                style="display: none; margin-top: 10px;"
                                                value="<?php echo htmlspecialchars($channel_manager_actual, ENT_QUOTES, 'UTF-8'); ?>" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Tipo de inventario (solo aplica para extranet):</strong></td>
                                        <td>
                                            <?php $allotment_selected = (int) ($datos_hotel['allotment_selected'] ?? 0); ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allotmentCheckbox"
                                                    name="general[allotment_selected]" value="1" <?php if ($allotment_selected === 1)
                                                        echo 'checked'; ?>>
                                                <label class="form-check-label"
                                                    for="allotmentCheckbox">Allotment</label>
                                            </div>

                                            <div id="allotmentFields" class="mt-2" style="display: none;">
                                                <div id="allotmentRooms" class="mt-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">Allotment por tipo de habitación</h6>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="agregarFilaAllotment()">Agregar fila</button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered align-middle"
                                                            id="tablaAllotment">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th>Tipo de habitación</th>
                                                                    <th>Número de habitaciones</th>
                                                                    <th style="width: 110px;">Acción</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($allotment_data) && is_array($allotment_data)): ?>
                                                                    <?php foreach ($allotment_data as $row): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="text"
                                                                                    class="form-control form-control-sm"
                                                                                    name="allotment_tipo_habitacion[]"
                                                                                    value="<?php echo htmlspecialchars($row['tipo_habitacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                                    placeholder="Ej: Estándar">
                                                                            </td>
                                                                            <td style="max-width: 160px;">
                                                                                <input type="number"
                                                                                    class="form-control form-control-sm"
                                                                                    name="allotment_num_habitaciones[]" min="0"
                                                                                    value="<?php echo htmlspecialchars($row['num_habitaciones'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                                    placeholder="0">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button"
                                                                                    class="btn btn-danger btn-sm"
                                                                                    onclick="eliminarFilaAllotment(this)">Eliminar</button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text"
                                                                                class="form-control form-control-sm"
                                                                                name="allotment_tipo_habitacion[]"
                                                                                placeholder="Ej: Estándar">
                                                                        </td>
                                                                        <td style="max-width: 160px;">
                                                                            <input type="number"
                                                                                class="form-control form-control-sm"
                                                                                name="allotment_num_habitaciones[]" min="0"
                                                                                placeholder="0">
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-sm"
                                                                                onclick="eliminarFilaAllotment(this)">Eliminar</button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="general[allotment_json]"
                                                id="allotment_json_hidden" value="">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Tipos de tarifas a conectar:</strong></td>
                                        <td>
                                            <label class="me-3">
                                                <input type="checkbox" id="tarifaFIT" name="tarifa_tipo[]" value="FIT"
                                                    <?php if (in_array('FIT', $tarifa_tipo_data ?? []))
                                                        echo 'checked'; ?>> FIT
                                            </label>
                                            <label class="me-3">
                                                <input type="checkbox" id="tarifaDinamica" name="tarifa_tipo[]"
                                                    value="Dinamicas" <?php if (in_array('Dinamicas', $tarifa_tipo_data ?? []))
                                                        echo 'checked'; ?>> Dinámicas
                                            </label>
                                            <label class="me-3">
                                                <input type="checkbox" id="tarifaAmbas" name="tarifa_tipo[]"
                                                    value="Ambos" <?php if (in_array('Ambos', $tarifa_tipo_data ?? []))
                                                        echo 'checked'; ?>> Ambos
                                            </label>

                                            <input type="hidden" name="general[tarifa_tipo_json]"
                                                id="tarifa_tipo_json_hidden" value="">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Descuento fijo (opaco) sobre tarifa Dinámica:</strong></td>
                                        <td>
                                            <input type="text" class="form-control" name="general[descuento_dinamico]"
                                                placeholder="Escribir texto..."
                                                value="<?php echo htmlspecialchars($datos_hotel['descuento_dinamico'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2">
                                            <strong>Vigencia del contrato:</strong><br>
                                            Será de un (1) año contado a partir de la fecha de su firma, renovándose
                                            automáticamente a su
                                            vencimiento en forma sucesiva y por períodos iguales, salvo que alguna de
                                            LAS
                                            PARTES notifique
                                            por escrito a la otra con no menos de 30 días calendario de anticipación a
                                            la
                                            fecha de vencimiento
                                            del Contrato, su voluntad de no renovar el mismo.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tablaDinamica" class="card mb-4 black-theme-card" style="display: none;">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">PLANES TARIFARIOS Y POLÍTICAS PARA CONTRATOS DINÁMICOS</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered" id="tablaTarifas">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>NOMBRE PLAN TARIFARIO</th>
                                        <th>POLÍTICA DE CANCELACIÓN (Días)</th>
                                        <th>PENALIDAD</th>
                                        <th>PENALIDAD POR NO SHOW</th>
                                        <th>PENALIDAD SALIDA ANTICIPADA</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($planes_tarifarios_data) && is_array($planes_tarifarios_data)): ?>
                                        <?php foreach ($planes_tarifarios_data as $p): ?>
                                            <tr>
                                                <td><input type="text" class="form-control" name="plan_tarifario_nombre[]"
                                                        value="<?php echo htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td><input type="text" class="form-control" name="plan_tarifario_cancelacion[]"
                                                        value="<?php echo htmlspecialchars($p['cancelacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td><input type="text" class="form-control" name="plan_tarifario_penalidad[]"
                                                        value="<?php echo htmlspecialchars($p['penalidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td><input type="text" class="form-control" name="plan_tarifario_no_show[]"
                                                        value="<?php echo htmlspecialchars($p['no_show'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td><input type="text" class="form-control"
                                                        name="plan_tarifario_salida_anticipada[]"
                                                        value="<?php echo htmlspecialchars($p['salida'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                </td>
                                                <td><button class="btn btn-danger btn-sm" type="button"
                                                        onclick="eliminarFilaTablaTarifas(this)">Eliminar</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td><input type="text" class="form-control" name="plan_tarifario_nombre[]"
                                                    value="Ejemplo: Standard Rate Refundable - Breakfast included."></td>
                                            <td><input type="text" class="form-control" name="plan_tarifario_cancelacion[]"
                                                    value="2 días"></td>
                                            <td><input type="text" class="form-control" name="plan_tarifario_penalidad[]"
                                                    value="1 noche"></td>
                                            <td><input type="text" class="form-control" name="plan_tarifario_no_show[]"
                                                    value="100% de la reserva"></td>
                                            <td><input type="text" class="form-control"
                                                    name="plan_tarifario_salida_anticipada[]" value="50% de la reserva">
                                            </td>
                                            <td><button class="btn btn-danger btn-sm" type="button"
                                                    onclick="eliminarFilaTablaTarifas(this)">Eliminar</button></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-primary mb-3"
                                onclick="agregarFilaTablaTarifas()">Agregar
                                fila</button>

                            <table class="table table-bordered mt-4">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>POLÍTICAS</th>
                                        <th>DESCRIPCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Niños</td>
                                        <td>
                                            <input type="text" class="form-control" name="general[politica_ninos]"
                                                placeholder="(Especificar si incluye o no el desayuno)"
                                                value="<?php echo htmlspecialchars($datos_hotel['politica_ninos'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Grupos</td>
                                        <td>
                                            <input type="text" class="form-control" name="general[politica_grupos]"
                                                placeholder="(Desde cuántas habitaciones se manejan grupos)"
                                                value="<?php echo htmlspecialchars($datos_hotel['politica_grupos'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p class="text-muted mt-2">* El contrato dinámico se habilita mínimo desde 1 año</p>

                            <input type="hidden" name="general[planes_tarifarios_json]"
                                id="planes_tarifarios_json_hidden" value="">
                        </div>
                    </div>


                </div>

                <div class="tab-pane fade" id="contactos" role="tabpanel">
                    <h3 class="mb-3">Contactos por Área</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Tipo de Contacto</th>
                                    <th>Nombre</th>
                                    <th>Móvil</th>
                                    <th>Email</th>
                                    <th>Teléfono Fijo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactos as $i => $c): ?>
                                    <tr>
                                        <input type="hidden" name="contactos[<?php echo $i; ?>][id_contacto]"
                                            value="<?php echo (int) $c['id_contacto']; ?>">
                                        <td><input type="text" name="contactos[<?php echo $i; ?>][tipo_contacto]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['tipo_contacto']); ?>"></td>
                                        <td><input type="text" name="contactos[<?php echo $i; ?>][nombre]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['nombre']); ?>"></td>
                                        <td><input type="text" name="contactos[<?php echo $i; ?>][movil]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['movil']); ?>"></td>
                                        <td><input type="email" name="contactos[<?php echo $i; ?>][email]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['email']); ?>"></td>
                                        <td><input type="text" name="contactos[<?php echo $i; ?>][telefono]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['telefono']); ?>"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="servicios" role="tabpanel">
                    <h3 class="mb-3 text-center">SERVICIOS DE LA PROPIEDAD</h3>

                    <?php if (!empty($servicios)): ?>

                        <?php
                        // ---------------- HELPERS ----------------
                        $getv = function ($k) use ($servicios) {
                            return isset($servicios[$k]) ? (string) $servicios[$k] : '';
                        };

                        $sel = function ($k, $opt) use ($getv) {
                            return ($getv($k) === (string) $opt) ? 'selected' : '';
                        };

                        $chkArr = function ($val, $arr) {
                            return in_array($val, $arr ?? [], true) ? 'checked' : '';
                        };

                        $radio = function ($k, $val) use ($getv) {
                            return ($getv($k) === (string) $val) ? 'checked' : '';
                        };

                        // Select estándar 0/1/2
                        $opts_012 = function ($k) use ($sel) {
                            return '
              <option value="0" ' . $sel($k, 0) . '>--</option>
              <option value="1" ' . $sel($k, 1) . '>Sí</option>
              <option value="2" ' . $sel($k, 2) . '>No</option>
            ';
                        };

                        // Select 0/1/2/3 (Sí con costo)
                        $opts_0123 = function ($k) use ($sel) {
                            return '
              <option value="0" ' . $sel($k, 0) . '>--</option>
              <option value="1" ' . $sel($k, 1) . '>Sí</option>
              <option value="2" ' . $sel($k, 2) . '>No</option>
              <option value="3" ' . $sel($k, 3) . '>Si, Con Costo</option>
            ';
                        };

                        // Ascensor
                        $opts_ascensor = function ($k) use ($sel) {
                            return '
              <option value="0" ' . $sel($k, 0) . '>--</option>
              <option value="1" ' . $sel($k, 1) . '>Directo Al Piso</option>
              <option value="2" ' . $sel($k, 2) . '>Interpiso</option>
            ';
                        };

                        // Gimnasio (incluye 24h)
                        $opts_gimnasio = function ($k) use ($sel) {
                            return '
              <option value="0" ' . $sel($k, 0) . '>--</option>
              <option value="1" ' . $sel($k, 1) . '>Sí</option>
              <option value="2" ' . $sel($k, 2) . '>No</option>
              <option value="3" ' . $sel($k, 3) . '>Sí, Abierto las 24 horas</option>
            ';
                        };

                        // Internet texto
                        $opts_internet = function ($k) use ($getv) {
                            $v = $getv($k);
                            $mk = fn($x) => ($v === $x) ? 'selected' : '';
                            return '
              <option value="------" ' . $mk('------') . '>------</option>
              <option value="Gratis" ' . $mk('Gratis') . '>Gratis</option>
              <option value="Con costo" ' . $mk('Con costo') . '>Con costo</option>
              <option value="No hay internet" ' . $mk('No hay internet') . '>No hay internet</option>
            ';
                        };

                        // ---------------- MAPEO (según tu $map_servicios) ----------------
                        // Nota: aquí alineamos nombres del FORMULARIO (section3) con tus keys del map_servicios.
                        // Si en tu BD/array $servicios ya usan exactamente estas keys, perfecto.
                    
                        // Campos con opción 0/1/2/3
                        $campos_0123 = [
                            'recepcion_24_hrs',
                            'servicio_lavanderia',
                            'transfer_htl_playa',
                            'guarda_equipaje',
                            'asoleadoras',
                            'zona_juegos_infantiles',
                            'toallas_playa_piscina',
                            'alquiler_bicicletas',
                        ];

                        // Campos con opción 0/1/2
                        $campos_012 = [
                            'transfer_aero_htl',
                            'aire_acondicionado',
                            'turco',
                            'lobby_lounge',
                            'bar',
                            'terraza',
                            'bar_piscina',
                            'servicios_ninera',
                            'muelle_privado',
                            'cafe_bar',
                            'concierge',
                            'super_minimercado',
                            'sendero_ecologico',
                            'discoteca',
                            'playa',
                            'mini_golf',
                            'cafe_recepcion',
                            'piscina',
                            'cajero_automatico',
                            'snack_bar',
                            'capilla',
                            'piscina_infantil',
                            'cambio_moneda',
                            'salon_fitness',
                            'club_ninos',
                            'pesca',
                            'enfermeria_medico',
                            'sala_masajes',
                            'salon_juegos',
                            'personal_bilingue',
                            'sauna',
                            'salon_belleza',
                            'casino',
                            'lobby_sala_espera',
                            'spa',
                            'juegos_mesa',
                            'jacuzzi',
                        ];
                        ?>

                        <table class="table table-bordered mb-4 black-theme-card">
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
                                    <td class="required-label">Parqueadero</td>
                                    <td>
                                        <select name="servicios[parqueadero]" class="form-select" required>
                                            <?php echo $opts_0123('parqueadero'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Minibar</td>
                                    <td>
                                        <select name="servicios[minibar]" class="form-select" required>
                                            <?php echo $opts_0123('minibar'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Con cocina</td>
                                    <td>
                                        <select name="servicios[con_cocina]" class="form-select" required>
                                            <?php echo $opts_012('con_cocina'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Cafetera de cortesia</td>
                                    <td>
                                        <select name="servicios[cafetera_cortesia]" class="form-select" required>
                                            <?php echo $opts_012('cafetera_cortesia'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="required-label">Servicio a la habitacion</td>
                                    <td>
                                        <select name="servicios[servicio_habitacion]" class="form-select" required>
                                            <?php echo $opts_0123('servicio_habitacion'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Servicio a la habitacion 24 hrs</td>
                                    <td>
                                        <select name="servicios[servicio_habitacion_24_hrs]" class="form-select" required>
                                            <?php echo $opts_0123('servicio_habitacion_24_hrs'); ?>
                                        </select>
                                    </td>

                                    <td colspan="4"></td>
                                </tr>

                                <!-- FILA 1 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['recepcion_24_hrs'] ?? 'Recepción 24 hrs'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[recepcion_24_hrs]" class="form-select" required>
                                            <?php echo $opts_0123('recepcion_24_hrs'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Transfer Aero-Htl-Aero</td>
                                    <td>
                                        <select name="servicios[transfer_aero_htl]" class="form-select" required>
                                            <?php echo $opts_012('transfer_aero_htl'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Aire Acondicionado en el hotel</td>
                                    <td>
                                        <select name="servicios[aire_acondicionado]" class="form-select" required>
                                            <?php echo $opts_012('aire_acondicionado'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['turco'] ?? 'Turco'; ?></td>
                                    <td>
                                        <select name="servicios[turco]" class="form-select" required>
                                            <?php echo $opts_012('turco'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 2 -->
                                <tr>
                                    <td class="required-label">Servicio de Lavandería</td>
                                    <td>
                                        <select name="servicios[servicio_lavanderia]" class="form-select" required>
                                            <?php echo $opts_0123('servicio_lavanderia'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Transfer Htl - Playa - Htl</td>
                                    <td>
                                        <select name="servicios[transfer_htl_playa]" class="form-select" required>
                                            <?php echo $opts_0123('transfer_htl_playa'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['lobby_lounge'] ?? 'Lobby Lounge'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[lobby_lounge]" class="form-select" required>
                                            <?php echo $opts_012('lobby_lounge'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['bar'] ?? 'Bar'; ?></td>
                                    <td>
                                        <select name="servicios[bar]" class="form-select" required>
                                            <?php echo $opts_012('bar'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 3 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['guarda_equipaje'] ?? 'Guarda equipaje'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[guarda_equipaje]" class="form-select" required>
                                            <?php echo $opts_0123('guarda_equipaje'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['asoleadoras'] ?? 'Asoleadoras'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[asoleadoras]" class="form-select" required>
                                            <?php echo $opts_0123('asoleadoras'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['terraza'] ?? 'Terraza'; ?></td>
                                    <td>
                                        <select name="servicios[terraza]" class="form-select" required>
                                            <?php echo $opts_012('terraza'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Bar en la piscina</td>
                                    <td>
                                        <select name="servicios[bar_piscina]" class="form-select" required>
                                            <?php echo $opts_012('bar_piscina'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 4 -->
                                <tr>
                                    <td class="required-label">Servicios de Niñera (Cargo Adicional)</td>
                                    <td>
                                        <select name="servicios[servicios_ninera]" class="form-select" required>
                                            <?php echo $opts_012('servicios_ninera'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['muelle_privado'] ?? 'Muelle Privado'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[muelle_privado]" class="form-select" required>
                                            <?php echo $opts_012('muelle_privado'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['cafe_bar'] ?? 'Café-Bar'; ?></td>
                                    <td>
                                        <select name="servicios[cafe_bar]" class="form-select" required>
                                            <?php echo $opts_012('cafe_bar'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['concierge'] ?? 'Concierge'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[concierge]" class="form-select" required>
                                            <?php echo $opts_012('concierge'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 5 -->
                                <tr>
                                    <td class="required-label">Super/Minimercado/ Tienda de regalos</td>
                                    <td>
                                        <select name="servicios[super_minimercado]" class="form-select" required>
                                            <?php echo $opts_012('super_minimercado'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['sendero_ecologico'] ?? 'Sendero Ecológico'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[sendero_ecologico]" class="form-select" required>
                                            <?php echo $opts_012('sendero_ecologico'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['discoteca'] ?? 'Discoteca'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[discoteca]" class="form-select" required>
                                            <?php echo $opts_012('discoteca'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['playa'] ?? 'Playa'; ?></td>
                                    <td>
                                        <select name="servicios[playa]" class="form-select" required>
                                            <?php echo $opts_012('playa'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 6 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['alquiler_bicicletas'] ?? 'Alquiler de bicicletas'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[alquiler_bicicletas]" class="form-select" required>
                                            <?php echo $opts_0123('alquiler_bicicletas'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['mini_golf'] ?? 'Mini Golf'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[mini_golf]" class="form-select" required>
                                            <?php echo $opts_012('mini_golf'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Café, Agua Saborizada y Aromática en la recepción</td>
                                    <td>
                                        <select name="servicios[cafe_recepcion]" class="form-select" required>
                                            <?php echo $opts_012('cafe_recepcion'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['piscina'] ?? 'Piscina'; ?></td>
                                    <td>
                                        <select name="servicios[piscina]" class="form-select" required>
                                            <?php echo $opts_012('piscina'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 7 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['cajero_automatico'] ?? 'Cajero Automático'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[cajero_automatico]" class="form-select" required>
                                            <?php echo $opts_012('cajero_automatico'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['snack_bar'] ?? 'Snack-bar'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[snack_bar]" class="form-select" required>
                                            <?php echo $opts_012('snack_bar'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['capilla'] ?? 'Capilla'; ?></td>
                                    <td>
                                        <select name="servicios[capilla]" class="form-select" required>
                                            <?php echo $opts_012('capilla'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['piscina_infantil'] ?? 'Piscina infantil'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[piscina_infantil]" class="form-select" required>
                                            <?php echo $opts_012('piscina_infantil'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 8 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['cambio_moneda'] ?? 'Cambio de moneda'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[cambio_moneda]" class="form-select" required>
                                            <?php echo $opts_012('cambio_moneda'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['salon_fitness'] ?? 'Salón de Fitness'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[salon_fitness]" class="form-select" required>
                                            <?php echo $opts_012('salon_fitness'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['club_ninos'] ?? 'Club de Niños'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[club_ninos]" class="form-select" required>
                                            <?php echo $opts_012('club_ninos'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['pesca'] ?? 'Pesca'; ?></td>
                                    <td>
                                        <select name="servicios[pesca]" class="form-select" required>
                                            <?php echo $opts_012('pesca'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 9 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['enfermeria_medico'] ?? 'Enfermería y/o Servicio Médico'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[enfermeria_medico]" class="form-select" required>
                                            <?php echo $opts_012('enfermeria_medico'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['zona_juegos_infantiles'] ?? 'Zona de Juegos Infantiles'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[zona_juegos_infantiles]" class="form-select" required>
                                            <?php echo $opts_0123('zona_juegos_infantiles'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['sala_masajes'] ?? 'Sala de Masajes'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[sala_masajes]" class="form-select" required>
                                            <?php echo $opts_012('sala_masajes'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['salon_juegos'] ?? 'Salón de juegos'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[salon_juegos]" class="form-select" required>
                                            <?php echo $opts_012('salon_juegos'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 10 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['personal_bilingue'] ?? 'Personal Bilingüe'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[personal_bilingue]" class="form-select" required>
                                            <?php echo $opts_012('personal_bilingue'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['sauna'] ?? 'Sauna'; ?></td>
                                    <td>
                                        <select name="servicios[sauna]" class="form-select" required>
                                            <?php echo $opts_012('sauna'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['salon_belleza'] ?? 'Salón de belleza'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[salon_belleza]" class="form-select" required>
                                            <?php echo $opts_012('salon_belleza'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['casino'] ?? 'Casino'; ?></td>
                                    <td>
                                        <select name="servicios[casino]" class="form-select" required>
                                            <?php echo $opts_012('casino'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 11 -->
                                <tr>
                                    <td class="required-label">
                                        <?php echo $map_servicios['lobby_sala_espera'] ?? 'Lobby con sala de espera'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[lobby_sala_espera]" class="form-select" required>
                                            <?php echo $opts_012('lobby_sala_espera'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['spa'] ?? 'Spa'; ?></td>
                                    <td>
                                        <select name="servicios[spa]" class="form-select" required>
                                            <?php echo $opts_012('spa'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['gimnasio'] ?? 'Gimnasio'; ?></td>
                                    <td>
                                        <select name="servicios[gimnasio]" class="form-select" required>
                                            <?php echo $opts_gimnasio('gimnasio'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['juegos_mesa'] ?? 'Juegos de Mesa'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[juegos_mesa]" class="form-select" required>
                                            <?php echo $opts_012('juegos_mesa'); ?>
                                        </select>
                                    </td>
                                </tr>

                                <!-- FILA 12 -->
                                <tr>
                                    <td class="required-label"><?php echo $map_servicios['ascensor'] ?? 'Ascensor'; ?></td>
                                    <td>
                                        <select name="servicios[ascensor]" class="form-select" required>
                                            <?php echo $opts_ascensor('ascensor'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">
                                        <?php echo $map_servicios['toallas_playa_piscina'] ?? 'Toallas para la playa y piscina'; ?>
                                    </td>
                                    <td>
                                        <select name="servicios[toallas_playa_piscina]" class="form-select" required>
                                            <?php echo $opts_0123('toallas_playa_piscina'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label"><?php echo $map_servicios['jacuzzi'] ?? 'Jacuzzi'; ?></td>
                                    <td>
                                        <select name="servicios[jacuzzi]" class="form-select" required>
                                            <?php echo $opts_012('jacuzzi'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Ventilador de techo</td>
                                    <td>
                                        <select name="servicios[ventilador_techo]" class="form-select" required>
                                            <?php echo $opts_012('ventilador_techo'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label">Cuenta con agua caliente en habitaciones</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="servicios[agua_caliente_hab]"
                                                id="agua_caliente_si" value="1" required <?php echo $radio('agua_caliente_hab', '1'); ?>>
                                            <label class="form-check-label" for="agua_caliente_si">SI</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="servicios[agua_caliente_hab]"
                                                id="agua_caliente_no" value="0" <?php echo $radio('agua_caliente_hab', '0'); ?>>
                                            <label class="form-check-label" for="agua_caliente_no">No</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1">ALGUN OTRO SERVICIO?</td>
                                    <td colspan="7">
                                        <textarea id="otroservicio" name="servicios[otro_servicio]" class="form-control"
                                            rows="2"
                                            placeholder="Describa el servicio."><?php echo htmlspecialchars($servicios['otro_servicio'] ?? ''); ?></textarea>
                                    </td>
                                </tr>
                                <?php
                                // Cobertura internet: preferimos $cobertura_internet_data (si existe)
                                // Si no existe, intentamos derivarlo de $servicios['cobertura_internet'] (CSV o JSON)
                                $cobertura = $cobertura_internet_data ?? [];
                                if (empty($cobertura) && !empty($servicios['cobertura_internet'])) {
                                    $raw = $servicios['cobertura_internet'];
                                    if (is_string($raw)) {
                                        $tmp = json_decode($raw, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                                            $cobertura = $tmp;
                                        } else {
                                            $cobertura = array_filter(array_map('trim', explode(',', $raw)));
                                        }
                                    } elseif (is_array($raw)) {
                                        $cobertura = $raw;
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="text-center align-middle section-title required-label" rowspan="2">INTERNET
                                    </td>
                                    <td>Wifi:</td>
                                    <td>
                                        <select name="servicios[internet_wifi]" class="form-select" required>
                                            <?php echo $opts_internet('internet_wifi'); ?>
                                        </select>
                                    </td>
                                    <td class="required-label">Cable:</td>
                                    <td>
                                        <select name="servicios[internet_cable]" class="form-select" required>
                                            <?php echo $opts_internet('internet_cable'); ?>
                                        </select>
                                    </td>
                                    <td class="required-label">Área de cobertura del internet:</td>
                                    <td colspan="2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                                id="internet_habitaciones" value="habitaciones" <?php echo $chkArr('habitaciones', $cobertura); ?>>
                                            <label class="form-check-label" for="internet_habitaciones">Habitaciones</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                                id="internet_areas" value="areas_especificas" <?php echo $chkArr('areas_especificas', $cobertura); ?>>
                                            <label class="form-check-label" for="internet_areas">Áreas específicas</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                                id="internet_publicas" value="areas_publicas" <?php echo $chkArr('areas_publicas', $cobertura); ?>>
                                            <label class="form-check-label" for="internet_publicas">Áreas Públicas</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                                id="no_hay_internet" value="no_hay_internet" <?php echo $chkArr('no_hay_internet', $cobertura); ?>>
                                            <label class="form-check-label" for="no_hay_internet">No hay internet</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="required-label" colspan="1">Canal dedicado:</td>
                                    <td colspan="2">
                                        <select name="servicios[canal_dedicado]" class="form-select" required>
                                            <?php echo $opts_internet('canal_dedicado'); ?>
                                        </select>
                                    </td>

                                    <td class="required-label" colspan="2" style="text-align:center;">Wi-Fi Zonas Comunes
                                    </td>
                                    <td colspan="2">
                                        <select name="servicios[wifi_zonas_comunes]" class="form-select" required>
                                            <?php echo $opts_internet('wifi_zonas_comunes'); ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No se encontraron servicios registrados.</div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="habitaciones" role="tabpanel">
                    <h3 class="mb-3">Tipos de Habitación</h3>
                    <button type="button" class="btn btn-sm btn-success mb-2" disabled style="opacity: 0.5;">+ Agregar
                        habitación</button>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm align-middle small habitaciones-table">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>Tipo Habitación</th>
                                    <th>Total</th>
                                    <th>Max Adultos</th>
                                    <th>Max Niños</th>
                                    <th>Max Total</th>
                                    <th>Mts²</th>
                                    <th>C. Sencilla</th>
                                    <th>C. Doble</th>
                                    <th>C. Queen</th>
                                    <th>C. King</th>
                                    <th>Camas Adic.</th>
                                    <th>Servicios</th>
                                    <th>Observaciones</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="habitacionesBody">
                                <?php foreach ($habitaciones as $i => $h):
                                    $raw_servicios = $h['servicios_gen_json'] ?? '{"servicios":[],"obs":""}';
                                    $data_decoded = json_decode($raw_servicios, true) ?: ['servicios' => [], 'obs' => ''];
                                    $cant_servicios = count($data_decoded['servicios']);
                                    ?>
                                    <tr class="hab-row">
                                        <td><input type="text" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['tipo_habitacion']); ?>" readonly
                                                disabled></td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['total_habitaciones']); ?>" readonly
                                                disabled></td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_adultos']); ?>" readonly
                                                disabled></td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_ninos']); ?>" readonly disabled>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_total']); ?>" readonly disabled>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['mts2']); ?>" readonly disabled></td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_sencilla']); ?>" readonly
                                                disabled></td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_doble']); ?>" readonly disabled>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_queen']); ?>" readonly disabled>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_king']); ?>" readonly disabled>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['camas_adicionales']); ?>" readonly
                                                disabled></td>
                                        <td class="services-cell text-center"
                                            data-services="<?php echo htmlspecialchars(implode(';', $data_decoded['servicios'])); ?>"
                                            data-description="<?php echo htmlspecialchars($data_decoded['obs']); ?>">

                                            <span
                                                class="badge <?php echo $cant_servicios > 0 ? 'bg-success' : 'bg-secondary'; ?> mb-1 d-block">
                                                <?php echo $cant_servicios; ?> seleccionados
                                            </span>

                                            <button type="button" class="btn btn-info btn-xs ver-servicios-btn text-white">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>

                                        <td><textarea class="form-control form-control-sm" rows="1" readonly
                                                disabled><?php echo htmlspecialchars($h['observaciones']); ?></textarea>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-xs btn-danger"
                                                disabled>X</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="salones" role="tabpanel">
                    <h3 class="mb-3">Salones y Espacios para Eventos</h3>
                    <button type="button" class="btn btn-sm btn-success mb-2" id="addSalonBtn" disabled>+ Agregar
                        salón</button>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm align-middle small text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>Nombre</th>
                                    <th>M²</th>
                                    <th>Largo</th>
                                    <th>Ancho</th>
                                    <th>Alto</th>
                                    <th>Herradura</th>
                                    <th>Aula</th>
                                    <th>Auditorio</th>
                                    <th>Banquete</th>
                                    <th>Cóctel</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="salonesBody">
                                <?php foreach ($salones as $i => $s): ?>
                                    <tr class="salon-row">
                                        <input type="hidden" name="salones[<?php echo $i; ?>][id_salon]"
                                            value="<?php echo (int) $s['id_salon']; ?>">
                                        <input type="hidden" name="salones[<?php echo $i; ?>][accion]" value="keep">
                                        <td><input type="text" name="salones[<?php echo $i; ?>][nombre_salon]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['nombre_salon']); ?>"></td>
                                        <td><input type="number" step="0.01" name="salones[<?php echo $i; ?>][m2]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['m2']); ?>"></td>
                                        <td><input type="number" step="0.01" name="salones[<?php echo $i; ?>][largo]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['largo']); ?>"></td>
                                        <td><input type="number" step="0.01" name="salones[<?php echo $i; ?>][ancho]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['ancho']); ?>"></td>
                                        <td><input type="number" step="0.01" name="salones[<?php echo $i; ?>][alto]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['alto']); ?>"></td>
                                        <td><input type="number" name="salones[<?php echo $i; ?>][cap_u_herradura]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['cap_u_herradura']); ?>" min="0"></td>
                                        <td><input type="number" name="salones[<?php echo $i; ?>][cap_aula]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['cap_aula']); ?>" min="0"></td>
                                        <td><input type="number" name="salones[<?php echo $i; ?>][cap_auditorio]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['cap_auditorio']); ?>" min="0"></td>
                                        <td><input type="number" name="salones[<?php echo $i; ?>][cap_banquete]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['cap_banquete']); ?>" min="0"></td>
                                        <td><input type="number" name="salones[<?php echo $i; ?>][cap_coctel]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($s['cap_coctel']); ?>" min="0"></td>
                                        <td><button type="button" class="btn btn-xs btn-danger btn-del-salon"
                                                disabled>X</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="modalVerServicios" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title" style="color: white !important;">DETALLES DE SERVICIOS - CONSULTA</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                <div class="row">
                                    <?php
                                    $categorias = [
                                        "Equipamiento" => ["Closet", "Baño Privado", "Cocineta", "Escritorio de Trabajo", "Balcón o Terraza", "Comedor", "Minibar", "Amenidades", "Sofá Cama"],
                                        "Tecnología" => ["Teléfono", "Cajilla de Seguridad", "Wifi Habitaci6n", "Nevera", "iPad", "Smart TV", "Base para conectar IPOD/MP3"],
                                        "Comodidades" => ["Batas de Baño y Pantuflas", "Cuna para bebé disponible", "Secador para pelo", "Máquina de Café", "Plancha y tabla de planchar"],
                                        "Extras" => ["Servicio a la habitación", "Servicio Despertador", "Llamadas Locales", "Llamadas Internacionales", "Tina de Hidromasajes", "Aire Acondicionado Habitación"]
                                    ];
                                    foreach ($categorias as $cat => $items): ?>
                                        <div class="col-md-3">
                                            <fieldset class="border p-2 mb-3">
                                                <legend class="h6 fw-bold"><?php echo $cat; ?></legend>
                                                <?php foreach ($items as $item): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input view-cb" type="checkbox"
                                                            value="<?php echo $item; ?>" disabled>
                                                        <label class="form-check-label small"
                                                            style="opacity: 1;"><?php echo $item; ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </fieldset>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-2">
                                    <label class="fw-bold small">Observaciones adicionales:</label>
                                    <textarea id="view_obs_servicios" class="form-control form-control-sm bg-light"
                                        rows="3" readonly></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const modalView = new bootstrap.Modal(document.getElementById('modalVerServicios'));

                        // Escuchar clics en los botones de "Ver"
                        document.getElementById('habitacionesBody').addEventListener('click', function (e) {
                            if (e.target.classList.contains('ver-servicios-btn') || e.target.closest('.ver-servicios-btn')) {
                                const btn = e.target.classList.contains('ver-servicios-btn') ? e.target : e.target.closest('.ver-servicios-btn');
                                const cell = btn.closest('.services-cell');

                                // 1. Limpiar Modal
                                document.querySelectorAll('.view-cb').forEach(cb => cb.checked = false);
                                document.getElementById('view_obs_servicios').value = '';

                                // 2. Extraer datos de los data-attributes
                                const servicios = cell.dataset.services ? cell.dataset.services.split(';') : [];
                                const observacion = cell.dataset.description || '';

                                // 3. Marcar Checkboxes
                                document.querySelectorAll('.view-cb').forEach(cb => {
                                    if (servicios.includes(cb.value)) {
                                        cb.checked = true;
                                    }
                                });

                                // 4. Cargar Observación
                                document.getElementById('view_obs_servicios').value = observacion;

                                // 5. Mostrar Modal
                                modalView.show();
                            }
                        });
                    });
                </script>
                <div class="tab-pane fade" id="documentos" role="tabpanel">
                    <h3 class="mb-3">Gestión de Documentos y Galería</h3>

                    <h5 class="text-primary border-bottom pb-2">Documentación Legal</h5>
                    <div class="table-responsive mb-5">
                       <table class="table table-sm align-middle table-hover border small tabla-documentos-legales">
                            <thead class="table-dark">
                                <tr>
                                    <th class="col-tipo-documento">Tipo</th>
                                    <th>Nombre</th>
                                    <th>Vigencia</th>
                                    <th>Estado</th>
                                    <th>Días</th>
                                    <th class="text-center">Drive</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hay_docs = false;
                                $tipos_foto = ['Foto Promocional','Foto Fachada','Foto Habitaciones','Foto Piscina','Foto Zona Comun'];

                                foreach ($documentos as $doc):
                                    if (in_array($doc['tipo_documento'], $tipos_foto)) {
                                        continue;
                                    }

                                    $hay_docs = true;
                                    $estado = strtoupper($doc['estado_vigencia'] ?? 'UNKNOWN');
                                ?>
                                    <tr>
                                        <td class="col-tipo-documento">
                                            <span class="badge bg-secondary badge-tipo-documento">
                                                <?php echo htmlspecialchars($doc['tipo_documento']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]"
                                                value="<?php echo $doc['id_doc']; ?>">

                                            <input type="text" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                                        </td>
                                        <td>
                                            <?php echo !empty($doc['fecha_vigencia'])
                                                ? htmlspecialchars($doc['fecha_vigencia'])
                                                : '<span class="text-muted">Sin fecha</span>'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($estado === 'VALID') {
                                                echo '<span class="badge bg-success">Vigente</span>';
                                            } elseif ($estado === 'ABOUT TO EXPIRE') {
                                                echo '<span class="badge bg-warning text-dark">Por vencer</span>';
                                            } elseif ($estado === 'EXPIRED') {
                                                echo '<span class="badge bg-danger">Vencido</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">No identificado</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo isset($doc['dias_vencimiento']) && $doc['dias_vencimiento'] !== null
                                                ? htmlspecialchars($doc['dias_vencimiento'])
                                                : '-'; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo htmlspecialchars($doc['ruta_almacenamiento']); ?>"
                                            target="_blank"
                                            class="btn btn-xs btn-outline-primary">Ver</a>
                                        </td>
                                        <td class="text-end">
                                            <select name="docs_old[<?php echo $doc['id_doc']; ?>][accion]"
                                                class="form-select form-select-sm w-auto d-inline-block">
                                                <option value="keep">Mantener</option>
                                                <option value="delete">Eliminar</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (!$hay_docs): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-2">No hay documentos legales.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <h5 class="text-primary border-bottom pb-2">Catálogo de Fotos</h5>
                    <?php
                    $tipos_foto_extra = ['Foto Promocional','Foto Fachada','Foto Habitaciones','Foto Piscina','Foto Zona Comun'];
                    $etiquetas_foto = [
                        'Foto Fachada'       => 'Fachada',
                        'Foto Habitaciones'  => 'Habitaciones',
                        'Foto Piscina'       => 'Piscina / Zona recreativa',
                        'Foto Zona Comun'    => 'Zona Común',
                        'Foto Promocional'   => 'Foto',
                    ];

                    // Indexar documentos por tipo para las 4 principales
                    $fotos_por_tipo = [];
                    $fotos_adicionales = [];
                    foreach ($documentos as $doc) {
                        if (in_array($doc['tipo_documento'], $tipos_foto_extra)) {
                            // Solo guardamos la primera de cada tipo principal (4 slots)
                            $t = $doc['tipo_documento'];
                            if ($t === 'Foto Promocional') {
                                $fotos_adicionales[] = $doc; // las genéricas van abajo
                            } else {
                                $fotos_por_tipo[$t] = $doc;
                            }
                        }
                    }

                    // Orden fijo de las 4 principales
                    $slots_principales = [
                        'Foto Fachada'      => '🏨 Fachada',
                        'Foto Habitaciones' => '🛏️ Habitaciones',
                        'Foto Piscina'      => '🏊 Piscina / Zona recreativa',
                        'Foto Zona Comun'   => '🛎️ Zona Común',
                    ];
                    ?>

                    <!-- 4 FOTOS PRINCIPALES FIJAS -->
                    <p class="small text-muted mb-2">Fotos promocionales principales (usadas en el PDF):</p>
                    <div class="row g-3 mb-4">
                      <?php foreach ($slots_principales as $tipo => $etiqueta):
                        $doc = $fotos_por_tipo[$tipo] ?? null;
                        $imgSrc = $doc ? driveViewUrl($doc['ruta_almacenamiento']) : null;
                      ?>
                      <div class="col-12 col-sm-6 col-md-3">
                        <div class="card h-100 shadow-sm" style="border: 2px solid <?php echo $doc ? '#0d6efd' : '#dee2e6'; ?>;">
                          <div style="height:130px; overflow:hidden; background:#f0f4f8; position:relative;">
                            <?php if ($imgSrc): ?>
                              <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                   style="width:100%;height:100%;object-fit:cover;"
                                   onerror="this.src='https://placehold.co/300x130?text=Error'">
                            <?php else: ?>
                              <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                Sin foto
                              </div>
                            <?php endif; ?>
                          </div>
                          <div class="card-body p-2">
                            <div class="small fw-semibold text-primary mb-2"><?php echo $etiqueta; ?></div>

                            <?php if ($doc): ?>
                              <!-- Foto existente: opción de borrar -->
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]" value="<?php echo $doc['id_doc']; ?>">
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]" value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo htmlspecialchars($doc['ruta_almacenamiento']); ?>" target="_blank"
                                   class="btn btn-xs btn-outline-primary btn-sm">
                                  <i class="fa fa-external-link"></i>
                                </a>
                                <select name="docs_old[<?php echo $doc['id_doc']; ?>][accion]"
                                        class="form-select form-select-sm" style="width:100px;">
                                  <option value="keep">OK</option>
                                  <option value="delete">Borrar</option>
                                </select>
                              </div>
                            <?php else: ?>
                              <!-- Sin foto: input para subir -->
                              <div class="mb-1">
                                <input type="file" class="form-control form-control-sm"
                                       name="nueva_foto_principal[<?php echo htmlspecialchars($tipo); ?>]"
                                       accept="image/*">
                                <input type="hidden" name="nueva_foto_tipo[<?php echo htmlspecialchars($tipo); ?>]" value="<?php echo htmlspecialchars($tipo); ?>">
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- FOTOS ADICIONALES (Foto Promocional genérica) -->
                    <?php if (!empty($fotos_adicionales)): ?>
                    <p class="small text-muted mb-2">Fotos adicionales:</p>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
                      <?php foreach ($fotos_adicionales as $doc):
                        $imgSrc = driveViewUrl($doc['ruta_almacenamiento']); ?>
                        <div class="col">
                          <div class="card h-100 shadow-sm border-info">
                            <div style="height:130px;overflow:hidden;background:#eee;">
                              <img src="<?php echo htmlspecialchars($imgSrc ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   class="card-img-top" style="object-fit:cover;height:100%;"
                                   onerror="this.src='https://placehold.co/300x130?text=Error'">
                            </div>
                            <div class="card-body p-2">
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]" value="<?php echo $doc['id_doc']; ?>">
                              <input type="text" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]"
                                     class="form-control form-control-sm mb-2"
                                     value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo htmlspecialchars($doc['ruta_almacenamiento']); ?>" target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                  <i class="fa fa-external-link"></i>
                                </a>
                                <select name="docs_old[<?php echo $doc['id_doc']; ?>][accion]"
                                        class="form-select form-select-sm" style="width:100px;">
                                  <option value="keep">OK</option>
                                  <option value="delete">Borrar</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card mt-4 border-primary">
                        <div class="card-header bg-primary text-white py-1 small fw-bold">AÑADIR NUEVOS ARCHIVOS O FOTOS
                        </div>
                        <div class="card-body p-3 bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="small fw-bold">Categoría:</label>
                                    <select id="new_doc_type" class="form-select form-select-sm">
                                        <option value="Foto Promocional">📸 Foto Promocional</option>
                                        <option value="RUT">RUT</option>
                                        <option value="RNT">RNT</option>
                                        <option value="Camara de Comercio">Cámara de Comercio</option>
                                        <option value="Certificacion Bancaria">Certificación Bancaria</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="small fw-bold">Archivo:</label>
                                    <input type="file" id="new_doc_file" class="form-control form-control-sm"
                                        accept="image/*,.pdf,.zip,.rar">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-sm w-100 fw-bold"
                                        onclick="prepararSubidaDoc()">+ Añadir</button>
                                </div>
                            </div>
                            <ul id="lista_docs_previa" class="list-group mt-3 small shadow-sm"></ul>
                            <div id="container_nuevos_archivos" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-legal-firma" role="tabpanel" aria-labelledby="tab-legal-firma-tab">

                    <?php
                    // ===== Prefills desde BD =====
                    $razon_social = $datos_hotel['razon_social'] ?? '';

                    $nombre_hotel_legal = $datos_hotel['nombre_hotel_legal'] ?? '';
                    $ciudad_hotel_legal = $datos_hotel['ciudad_hotel_legal'] ?? '';
                    $nit_hotel_legal = $datos_hotel['nit_hotel_legal'] ?? '';

                    $nombre_rep_legal = $datos_hotel['nombre_rep_legal'] ?? '';
                    $ciudad_rep_legal = $datos_hotel['ciudad_rep_legal'] ?? '';
                    $num_documento_rep_legal = $datos_hotel['num_documento_rep_legal'] ?? '';
                    $ciudad_doc_rep_legal = $datos_hotel['ciudad_doc_rep_legal'] ?? '';

                    $acepta_declaracion = (int) ($datos_hotel['acepta_declaracion'] ?? 0);
                    $acepta_terminos = (int) ($datos_hotel['acepta_terminos'] ?? 0);
                    $acepta_politicas = (int) ($datos_hotel['acepta_politicas'] ?? 0);
                    $acepta_compromiso = (int) ($datos_hotel['acepta_compromiso'] ?? 0);

                    $tiene_certificado_sostenibilidad = $datos_hotel['tiene_certificado_sostenibilidad'] ?? '';

                    if (!function_exists('pnvFirmaFechaInput')) {
                        function pnvFirmaFechaInput($value)
                        {
                            $value = trim((string) $value);
                            if ($value === '' || $value === '0000-00-00') {
                                return date('Y-m-d');
                            }
                            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                                return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : date('Y-m-d');
                            }
                            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $m)) {
                                $day = (int) $m[1];
                                $month = (int) $m[2];
                                $year = (int) $m[3];
                                if (checkdate($month, $day, $year)) {
                                    return sprintf('%04d-%02d-%02d', $year, $month, $day);
                                }
                            }
                            $timestamp = strtotime($value);
                            return $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
                        }
                    }

                    $firma_nombre_completo = $datos_hotel['rep_legal_nombre'] ?? '';
                    $firma_cargo = $datos_hotel['rep_legal_cargo'] ?? '';
                    $firma_fecha = pnvFirmaFechaInput($datos_hotel['firma_fecha'] ?? '');
                    ?>

                    <!-- Switch para activar requeridos SOLO al firmar -->
                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">Legal y Firma</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modoFirmarEdit" name="modo_firmar"
                                    value="1">
                                <label class="form-check-label" for="modoFirmarEdit">
                                    <strong>Voy a firmar y aceptar términos</strong> (activará campos obligatorios)
                                </label>
                            </div>
                            <small style="color: var(--texto-suave);">
                                Si solo estás revisando, no actives el modo firma.
                            </small>
                        </div>
                    </div>

                    <div id="section7_edit" class="form-section">

                        <div class="card mb-4 black-theme-card" style="max-height: 600px; overflow: hidden;">
                            <div class="card-header bg-dark text-white">
                                <h4 class="mb-0">ACUERDO DE COLABORACIÓN HOTELERA Y SOSTENIBILIDAD</h4>
                            </div>
                            <div class="card-body" style="overflow-y: auto; max-height: 600px;">
                                <p>
                                    Entre los suscritos, de una parte, <strong>PANAMERICANA DE VIAJES S.A.S.</strong>,
                                    sociedad legalmente constituida en Colombia, con domicilio en la ciudad de Bogotá,
                                    D.C.,
                                    identificada con NIT 860.402.288-1, representada legalmente por el señor
                                    <strong>HUGO VELEZ LYNTON</strong>, persona mayor de edad, con vecindad y domicilio
                                    en la
                                    ciudad de Bogotá, D.C., identificado con la cédula de ciudadanía No. 17.080.154 de
                                    Bogotá,
                                    D.C.,
                                    facultado para celebrar el presente acuerdo, todo lo cual consta en el Certificado
                                    de
                                    Existencia y Representación Legal expedido por la Cámara de Comercio de Bogotá,
                                    D.C., que
                                    se anexa para que haga parte integral del presente acuerdo, en su calidad de AGENCIA
                                    DE
                                    VIAJES, quien para todos los efectos del presente acuerdo se denominará
                                    “PANAMERICANA”; y
                                    de otra parte, la sociedad hotelera que se identifica a continuación:
                                </p>
                                <p>
                                <p>
                                    <input type="text" name="general[nombre_hotel_legal]" placeholder="Nombre del Hotel"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($nombre_hotel_legal); ?>">

                                    sociedad legalmente constituida en, con domicilio en la ciudad de
                                    <input type="text" name="general[ciudad_hotel_legal]" placeholder="Ciudad"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($ciudad_hotel_legal); ?>">,

                                    identificada con el número de identificación fiscal
                                    <input type="text" name="general[nit_hotel_legal]"
                                        placeholder="Número de identificación fiscal"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($nit_hotel_legal); ?>">,

                                    representada legalmente por el(la) señor(a)
                                    <input type="text" name="general[nombre_rep_legal]"
                                        placeholder="Nombre del representante legal"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($nombre_rep_legal); ?>">,

                                    persona mayor de edad, con vecindad y domicilio en la ciudad de
                                    <input type="text" name="general[ciudad_rep_legal]"
                                        placeholder="Ciudad de residencia"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($ciudad_rep_legal); ?>">,

                                    identificado(a) con el número de identificación No.
                                    <input type="text" name="general[num_documento_rep_legal]"
                                        placeholder="Número del documento"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($num_documento_rep_legal); ?>"> expedido en

                                    <input type="text" name="general[ciudad_doc_rep_legal]"
                                        placeholder="Ciudad del documento"
                                        class="inline-block border rounded px-2 py-1 form-control-sm"
                                        value="<?php echo htmlspecialchars($ciudad_doc_rep_legal); ?>">, facultado(a)
                                    para celebrar el presente acuerdo, todo lo cual consta en el acta de
                                    constitución o documento equivalente, que se anexa para que haga parte integral del
                                    presente acuerdo, en su calidad de HOTEL, quien para todos los efectos del presente
                                    acuerdo se denominará “EL HOTEL”. PANAMERICANA y EL HOTEL, en adelante “LAS PARTES”,
                                    hemos
                                    celebrado el presente ACUERDO DE COLABORACIÓN HOTELERA Y SOSTENIBILIDAD (en
                                    adelante, el
                                    “Acuerdo”), el cual se regirá por las cláusulas que más adelante se establecen.
                                </p>

                                <p class="mt-3">
                                    <strong>Nota:</strong> Para más detalles sobre nuestro acuerdo de colaboración
                                    hotelera,
                                    puede consultar los documentos de soporte en el siguiente enlace:
                                    <a href="https://drive.google.com/drive/folders/1HsGCxxgxv_6vfuiYSsl48a4yFSih3oj2"
                                        target="_blank" rel="noopener">
                                        Acuerdo de colaboración hotelera y sostenibilidad
                                    </a>.
                                </p>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white">
                                <h4 class="mb-0">DECLARACIÓN Y AUTORIZACIÓN PARA VERIFICACIÓN DE INFORMACIÓN*</h4>
                            </div>
                            <div class="card-body scrollable-content" style="max-height: 250px; overflow-y: auto;">
                                <p>Declaro que la información contenida en este formulario,
                                    así como toda la documentación presentada,
                                    es verdadera, completa y proporciona la información de modo confiable y
                                    actualizado...</p>

                                <p>Durante la vigencia de la relación con Panamericana de Viajes S.A.S me comprometo a
                                    proveer de la documentación e información que me sea solicitada. Así también,
                                    declaro expresamente que las actividades de
                                    <strong><span id="razonSocialPreviewEdit">
                                            <?php echo htmlspecialchars($razon_social ?: '[Razón Social]'); ?>
                                        </span></strong>
                                    tienen un
                                    origen lícito y que los
                                    fondos
                                    recibidos no serán destinados a actividades ligadas con narcotráfico, lavado de
                                    dinero...
                                </p>
                                </p>
                                <p class="mt-3">
                                    <strong>Nota:</strong> Para más detalles sobre nuestras políticas de verificación,
                                    puede consultar los documentos de soporte en el siguiente enlace:

                                    <a href="https://drive.google.com/drive/folders/1anXJPA-WtaIzAPnjaE8mDCzCmvyB8NE-"
                                        target="_blank" rel="noopener">
                                        Documentación de verificación y cumplimiento
                                    </a>.
                                </p>
                                <div class="form-check mt-3">
                                    <input class="form-check-input firma-required-check" type="checkbox" name="general[acepta_declaracion]"
                                        value="1" id="politicasDeclaraciones" <?php echo ((int) ($datos_hotel['acepta_declaracion'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="politicasDeclaracionesEdit">
                                        <strong>Acepto Declaración y autorización.</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white">
                                <h4 class="mb-0">Términos y condiciones *</h4>
                            </div>
                            <div class="card-body scrollable-content" style="max-height: 250px; overflow-y: auto;">
                                <p><strong>Referencia: Ley de Protección de Datos Personales</strong></p>
                                <p>De acuerdo con la Ley 1581 de 2012 y en cumplimiento a lo establecido en el
                                    artículo 10 del decreto reglamentario 1377 de 2013,
                                    PANAMERICANA DE VIAJES, con nit. 860.402.288-1, manejan la política de privacidad...
                                </p>
                                <p>
                                    Puedes consultar el documento completo aquí:
                                    <a href="https://drive.google.com/file/d/1gecJ0LyQDS7xqPIkPYdZHQyVSXDsU3yX/view"
                                        target="_blank">Ver documento</a>
                                </p>
                                <div class="form-check mt-3">
                                    <input class="form-check-input firma-required-check" type="checkbox" name="general[acepta_terminos]"
                                        value="1" id="politicasGenerales" <?php echo ((int) ($datos_hotel['acepta_terminos'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="politicasGeneralesEdit">
                                        <strong>Acepto términos y condiciones.</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h4 class="mb-0">Políticas de Panamericana de Viajes *</h4>
                            </div>
                            <div class="card-body scrollable-content" style="max-height: 250px; overflow-y: auto;">
                                <p><strong>PANAMERICANA DE VIAJES S.A.S.</strong> es una empresa comprometida con
                                    la sostenibilidad, le dejamos saber nuestra política y así mismo lo invitamos a
                                    participar
                                    en pro de su cumplimiento.</p>

                                <p>En Panamericana de Viajes creemos momentos inolvidables y felices, para que los
                                    viajes de
                                    nuestros clientes marquen sus vidas, con personal competente, mejorando
                                    continuamente la
                                    funcionalidad de los procesos, la calidad de los servicios, la satisfacción del
                                    cliente en
                                    el marco de la sostenibilidad y la creación de valor para los accionistas.
                                    Respetamos la
                                    cultura y el medioambiente, buscando el bienestar social y económico, pensando
                                    siempre en
                                    minimizar el impacto negativo de nuestro entorno, apoyándonos y cumpliendo la
                                    legislación
                                    colombiana y nuestros requisitos organizacionales, trabajando con nuestros clientes
                                    y
                                    proveedores para lograrlo.</p>

                                <div class="form-check mt-3">
                                    <input class="form-check-input firma-required-check" type="checkbox" name="general[acepta_politicas]"
                                        value="1" id="politicasSeguridad" <?php echo ((int) ($datos_hotel['acepta_politicas'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="politicasSeguridadEdit">
                                        <strong>Acepto Políticas de Panamericana de Viajes *</strong>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <label for="certificadoSostenibilidadEdit" class="form-label">
                                        <strong>¿Cuenta con certificado de sostenibilidad?</strong>
                                    </label>
                                    <select class="form-select firma-required" id="certificadoSostenibilidadEdit"
                                        name="general[tiene_certificado_sostenibilidad]">
                                        <option value="">Seleccione una opción</option>
                                        <option value="si" <?php echo ($tiene_certificado_sostenibilidad === 'si') ? 'selected' : ''; ?>>Sí</option>
                                        <option value="no" <?php echo ($tiene_certificado_sostenibilidad === 'no') ? 'selected' : ''; ?>>No</option>
                                    </select>
                                    <div id="mensajeCertificadoEdit" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h4 class="mb-0">Compromiso como Proveedor</h4>
                            </div>
                            <div class="card-body scrollable-content" style="min-height: 300px;">
                                <p>Yo como Proveedor de Panamericana de Viajes informo y doy evidencia de las
                                    siguientes acciones en Sostenibilidad: </p>
                                <ul>
                                    <li>Tengo establecida una politica de sostenibilidad</li>
                                    <li>Desarrollo programas de sotenibilidad</li>
                                    <li>He implementado programas de ahorro de agua y energia</li>
                                    <li>Desarrollo programas de residuos generados</li>
                                    <li>Sensibilizo a turistas y visitantes en programas de sostenibilidad</li>
                                    <li>Cumplo con la normatividad respecto a seguridad y salud en el trabajo.
                                    </li>
                                    <li>Garantizo la seguridad de los clientes en la prestación del servicio.
                                    </li>
                                    <li>Evito el tráfico ilícito de flora, fauna y bienes culturales.</li>
                                    <li>Evita la explotación sexual y comercial con niños y niñas y adolescentes
                                        ESCNNA
                                    </li>
                                    <li>Respeta el patrimonio natural y cultural.</li>
                                    <li>Contrata con la comunidad local.</li>
                                </ul>


                                <div class="form-check mt-3">
                                    <input class="form-check-input firma-required-check" type="checkbox" name="general[acepta_compromiso]"
                                        value="1" id="compromisoProveedor" <?php echo ((int) ($datos_hotel['acepta_compromiso'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="compromisoProveedorEdit">
                                        <strong>Acepto el Compromiso como Proveedor</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <h2 class="text-center mb-4">Firma Digital o Subida de Imagen</h2>

                        <div class="form-group">
                            <p>Autorizo a Panamericana a utilizar los datos proporcionados en este documento.</p>
                            <h5 class="mb-3">Seleccione cómo desea proporcionar su firma:</h5>

                            <div class="form-check mb-2">
                                <input class="form-check-input firma-required" type="radio" name="firma_option"
                                    id="draw-signature-edit" value="draw" checked>
                                <label class="form-check-label" for="draw-signature-edit">Dibujar Firma Digital</label>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input firma-required" type="radio" name="firma_option"
                                    id="upload-image-edit" value="upload">
                                <label class="form-check-label" for="upload-image-edit">Subir Imagen de la Firma</label>
                            </div>
                        </div>

                        <div id="draw-signature-area-edit" class="mb-4">
                            <h5 class="mb-3">Firma Digital:</h5>
                            <canvas id="signature-pad-edit" width="400" height="150"
                                style="border:1px solid #000;"></canvas>
                            <input type="hidden" name="firma_dibujada_data" id="firma_dibujada_data_edit">
                            <div class="d-flex justify-content-between mt-2">
                                <button type="button" class="btn btn-secondary"
                                    id="clear-signature-edit">Limpiar</button>
                                <button type="button" class="btn btn-outline-primary" id="save-signature-edit">Guardar
                                    Firma</button>
                            </div>
                        </div>

                        <div id="upload-image-area-edit" class="mb-4" style="display:none;">
                            <h5 class="mb-3">Subir Imagen de Firma:</h5>
                            <input type="file" id="signature-image-edit" name="firma_imagen_file" accept="image/*"
                                class="form-control" disabled>
                            <img id="preview-image-edit" src="#" alt="Vista previa" class="img-fluid mt-3"
                                style="display:none;">
                        </div>

                        <div class="text-center mt-4">
                            <div id="signature-display-edit"
                                style="border-bottom: 1px solid black; min-height: 150px; line-height: 150px;">

                                <?php if (!empty($firma_img)): ?>
                                    <img src="<?php echo htmlspecialchars($firma_img, ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Firma Digital" style="max-width: 100%; height: 150px; object-fit: contain;">
                                <?php else: ?>
                                    ______________________________________________
                                <?php endif; ?>

                            </div>

                            <p id="signature-info-edit">Firma</p>

                            <div class="mt-3">
                                <div class="row justify-content-center">
                                    <div class="col-md-4 mb-2">
                                        <label for="fullNameEdit" class="form-label small">Nombre completo:</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-center firma-required"
                                            id="fullNameEdit" name="general[rep_legal_nombre]"
                                            value="<?php echo htmlspecialchars($firma_nombre_completo); ?>"
                                            placeholder="Ej. Juan Pérez">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="dateEdit" class="form-label small">Fecha:</label>
                                        <input type="date"
                                            class="form-control form-control-sm text-center firma-required"
                                            id="dateEdit" name="general[firma_fecha]"
                                            value="<?php echo htmlspecialchars($firma_fecha); ?>">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="positionEdit" class="form-label small">Cargo:</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-center firma-required"
                                            id="positionEdit" name="general[rep_legal_cargo]"
                                            value="<?php echo htmlspecialchars($firma_cargo); ?>"
                                            placeholder="Ej. Gerente General">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalDiligenciadorEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar cambios</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2 text-muted">Indique quién está realizando las modificaciones en la ficha.</p>
                            <div class="mb-3">
                                <label class="form-label"><strong>Nombre de quien edita *</strong></label>
                                <input type="text" class="form-control" id="diligencia_nombre_edit"
                                    name="diligencia_nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Correo *</strong></label>
                                <input type="email" class="form-control" id="diligencia_correo_edit"
                                    name="diligencia_correo" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label"><strong>Cargo / Área *</strong></label>
                                <input type="text" class="form-control" id="diligencia_cargo_edit"
                                    name="diligencia_cargo" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold" id="btnConfirmarYEnviar">Confirmar y
                                Guardar</button>

                        </div>
                    </div>
                </div>
            </div>








            <div class="d-flex justify-content-between mt-4">
                <a href="consultaHotel.php?id=<?php echo (int) $id_hotel; ?>" class="btn btn-secondary">Cancelar</a>
                <button type="button" id="btnAbrirModalEdit" class="btn btn-primary px-4 fw-bold">Guardar Cambios de la
                    Ficha</button>
            </div>

        </form>
    </div>
    <br>
    <br>
    <br>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ================= HABITACIONES =================
            let habIndex = <?php echo count($habitaciones); ?>;
            const habTbody = document.getElementById('habitacionesBody');
            const addHabBtn = document.getElementById('addHabitacionBtn');

            if (addHabBtn && habTbody) {
                addHabBtn.addEventListener('click', function () {
                    const idx = habIndex++;
                    const tr = document.createElement('tr');
                    tr.classList.add('hab-row');
                    tr.innerHTML = `
                        <input type="hidden" name="habitaciones[${idx}][id_hab]" value="">
                        <input type="hidden" name="habitaciones[${idx}][accion]" value="keep">
                        <td><input type="text" name="habitaciones[${idx}][tipo_habitacion]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="habitaciones[${idx}][total_habitaciones]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][max_adultos]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][max_ninos]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][max_total]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" step="0.01" name="habitaciones[${idx}][mts2]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="habitaciones[${idx}][cama_sencilla]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][cama_doble]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][cama_queen]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][cama_king]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="habitaciones[${idx}][camas_adicionales]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="text" name="habitaciones[${idx}][servicios_texto]" class="form-control form-control-sm" placeholder="Ej: WiFi, TV"></td>
                        <td><input type="text" name="habitaciones[${idx}][servicios_obs]" class="form-control form-control-sm"></td>
                        <td><textarea name="habitaciones[${idx}][observaciones]" rows="1" class="form-control form-control-sm"></textarea></td>
                        <td><button type="button" class="btn btn-xs btn-danger btn-del-hab">X</button></td>
                    `;
                    habTbody.appendChild(tr);
                });

                habTbody.addEventListener('click', function (e) {
                    if (e.target.classList.contains('btn-del-hab')) {
                        const row = e.target.closest('tr');
                        const idInput = row.querySelector('input[name*="[id_hab]"]');
                        const accionInput = row.querySelector('input[name*="[accion]"]');
                        if (idInput && idInput.value) {
                            if (accionInput) accionInput.value = 'delete';
                            row.classList.add('table-danger');
                            row.querySelectorAll('input, textarea, select').forEach(el => {
                                if (!el.name.includes('[id_hab]') && !el.name.includes('[accion]')) el.disabled = true;
                            });
                            e.target.disabled = true;
                        } else { row.remove(); }
                    }
                });
            }

            // ================= SALONES =================
            let salonIndex = <?php echo count($salones); ?>;
            const salonesTbody = document.getElementById('salonesBody');
            const addSalonBtn = document.getElementById('addSalonBtn');

            if (addSalonBtn && salonesTbody) {
                addSalonBtn.addEventListener('click', function () {
                    const idx = salonIndex++;
                    const tr = document.createElement('tr');
                    tr.classList.add('salon-row');
                    tr.innerHTML = `
                        <input type="hidden" name="salones[${idx}][id_salon]" value="">
                        <input type="hidden" name="salones[${idx}][accion]" value="keep">
                        <td><input type="text" name="salones[${idx}][nombre_salon]" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" name="salones[${idx}][m2]" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" name="salones[${idx}][largo]" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" name="salones[${idx}][ancho]" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" name="salones[${idx}][alto]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="salones[${idx}][cap_u_herradura]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="salones[${idx}][cap_aula]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="salones[${idx}][cap_auditorio]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="salones[${idx}][cap_banquete]" class="form-control form-control-sm" min="0"></td>
                        <td><input type="number" name="salones[${idx}][cap_coctel]" class="form-control form-control-sm" min="0"></td>
                        <td><button type="button" class="btn btn-xs btn-danger btn-del-salon">X</button></td>
                    `;
                    salonesTbody.appendChild(tr);
                });

                salonesTbody.addEventListener('click', function (e) {
                    if (e.target.classList.contains('btn-del-salon')) {
                        const row = e.target.closest('tr');
                        const idInput = row.querySelector('input[name*="[id_salon]"]');
                        const accionInput = row.querySelector('input[name*="[accion]"]');
                        if (idInput && idInput.value) {
                            if (accionInput) accionInput.value = 'delete';
                            row.classList.add('table-danger');
                            row.querySelectorAll('input, textarea, select').forEach(el => {
                                if (!el.name.includes('[id_salon]') && !el.name.includes('[accion]')) el.disabled = true;
                            });
                            e.target.disabled = true;
                        } else { row.remove(); }
                    }
                });
            }
        });

        // ================= DOCUMENTOS (Lógica de Pre-carga) =================
        // Reemplaza tu función prepararSubidaDoc por esta:
        function prepararSubidaDoc() {
            const tipoSelect = document.getElementById('new_doc_type');
            const tipo = tipoSelect.value;
            const tipoTexto = tipoSelect.options[tipoSelect.selectedIndex].text;
            const inputOriginal = document.getElementById('new_doc_file');

            if (inputOriginal.files.length === 0) {
                alert("Por favor seleccione un archivo para añadir.");
                return;
            }

            const list = document.getElementById('lista_docs_previa');
            const containerArchivos = document.getElementById('container_nuevos_archivos');
            const idx = list.children.length;
            const file = inputOriginal.files[0];
            const fileName = file.name;

            // 1. Crear el elemento visual en la lista
            const li = document.createElement('li');
            li.className = "list-group-item d-flex justify-content-between align-items-center bg-white py-2 border-start border-success border-4";
            li.id = "doc_item_" + idx;
            li.innerHTML = `
        <div>
            <span class="badge bg-primary me-2">${tipoTexto}</span>
            <span class="text-dark">${fileName}</span>
            <input type="hidden" name="nuevos_docs_tipos[${idx}]" value="${tipo}">
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="quitarArchivoDeLista(${idx})">
            <i class="fas fa-trash"></i> Quitar
        </button>
    `;
            list.appendChild(li);

            // 2. Crear un input oculto que REALMENTE contenga el archivo
            // Esto es necesario porque cloneNode() no siempre copia el contenido del archivo
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'file';
            hiddenInput.name = `nuevos_docs_files[${idx}]`; // Usamos el índice para sincronizar con el tipo
            hiddenInput.id = "file_real_" + idx;
            hiddenInput.style.display = "none";

            // Transferir el archivo usando DataTransfer
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            hiddenInput.files = dataTransfer.files;

            containerArchivos.appendChild(hiddenInput);

            // 3. Limpiar el input visual para el siguiente archivo
            inputOriginal.value = "";
        }

        // Función para quitar de la lista si el usuario se arrepiente antes de enviar
        function quitarArchivoDeLista(idx) {
            const itemVisual = document.getElementById("doc_item_" + idx);
            const itemArchivo = document.getElementById("file_real_" + idx);
            if (itemVisual) itemVisual.remove();
            if (itemArchivo) itemArchivo.remove();
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('signature-pad-edit');
            const ctx = canvas?.getContext('2d');

            const signatureDisplay = document.getElementById('signature-display-edit');
            const signatureDataInput = document.getElementById('firma_dibujada_data_edit');
            const signatureImageInput = document.getElementById('signature-image-edit');
            const previewImage = document.getElementById('preview-image-edit');

            const drawSignatureArea = document.getElementById('draw-signature-area-edit');
            const uploadImageArea = document.getElementById('upload-image-area-edit');
            const drawSignatureOption = document.getElementById('draw-signature-edit');
            const uploadImageOption = document.getElementById('upload-image-edit');

            const modoFirmar = document.getElementById('modoFirmarEdit');
            const firmaRequired = document.querySelectorAll('.firma-required');
            const firmaRequiredChecks = document.querySelectorAll('.firma-required-check');

            const setFirmaRequired = (enabled) => {
                firmaRequired.forEach(el => enabled ? el.setAttribute('required', 'required') : el.removeAttribute('required'));
                firmaRequiredChecks.forEach(el => enabled ? el.setAttribute('required', 'required') : el.removeAttribute('required'));

                if (signatureDataInput) {
                    if (enabled && drawSignatureOption?.checked) signatureDataInput.setAttribute('required', 'required');
                    else signatureDataInput.removeAttribute('required');
                }
                if (signatureImageInput) {
                    if (!enabled) signatureImageInput.removeAttribute('required');
                } if (signatureImageInput) {
                    if (enabled && uploadImageOption?.checked) {
                        signatureImageInput.removeAttribute('disabled');
                    } else if (!uploadImageOption?.checked) {
                        signatureImageInput.setAttribute('disabled', 'disabled');
                    }
                }
            };

            setFirmaRequired(false);
            modoFirmar?.addEventListener('change', (e) => setFirmaRequired(e.target.checked));

            if (!canvas || !ctx || !signatureDataInput || !signatureImageInput) return;

            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';

            let isDrawing = false;

            canvas.addEventListener('mousedown', (event) => {
                isDrawing = true;
                ctx.beginPath();
                ctx.moveTo(event.offsetX, event.offsetY);
            });
            canvas.addEventListener('mouseup', () => isDrawing = false);
            canvas.addEventListener('mouseout', () => isDrawing = false);
            canvas.addEventListener('mousemove', (event) => {
                if (!isDrawing) return;
                ctx.lineTo(event.offsetX, event.offsetY);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(event.offsetX, event.offsetY);
            });

            document.getElementById('clear-signature-edit')?.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureDisplay.innerHTML = '______________________________________________';
                signatureDataInput.value = '';
            });

            document.getElementById('save-signature-edit')?.addEventListener('click', () => {
                const dataURL = canvas.toDataURL();
                signatureDisplay.innerHTML =
                    `<img src="${dataURL}" alt="Firma Digital" style="max-width:100%; height:150px; object-fit:contain;">`;
                signatureDataInput.value = dataURL;
            });

            signatureImageInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    signatureDisplay.innerHTML =
                        `<img src="${e.target.result}" alt="Firma Digital" style="max-width:100%; height:150px; object-fit:contain;">`;
                    signatureDataInput.value = 'FILE_UPLOADED';
                };
                reader.readAsDataURL(file);
            });

            document.querySelectorAll('input[name="firma_option"]').forEach(option => {
                option.addEventListener('change', function () {
                    if (drawSignatureOption.checked) {
                        drawSignatureArea.style.display = 'block';
                        uploadImageArea.style.display = 'none';
                        signatureImageInput.value = '';
                        signatureImageInput.setAttribute('disabled', 'disabled');
                        signatureImageInput.removeAttribute('required');
                        if (modoFirmar?.checked) signatureDataInput.setAttribute('required', 'required');
                    } else {
                        drawSignatureArea.style.display = 'none';
                        uploadImageArea.style.display = 'block';
                        signatureImageInput.removeAttribute('disabled');
                        signatureDataInput.removeAttribute('required');
                        signatureDataInput.value = '';
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        signatureDisplay.innerHTML = '______________________________________________';
                        if (modoFirmar?.checked) signatureImageInput.setAttribute('required', 'required');
                    }
                });
            });

            if (drawSignatureOption.checked) signatureImageInput.setAttribute('disabled', 'disabled');
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Definimos los elementos
            const modalElement = document.getElementById('modalDiligenciadorEdit');
            const myModal = new bootstrap.Modal(modalElement);
            const btnAbrir = document.getElementById('btnAbrirModalEdit');
            const btnConfirmar = document.getElementById('btnConfirmarYEnviar');
            const mainForm = document.querySelector('form[action*="actualizarHotel.php"]');

            // Elementos de los inputs
            const nombreInput = document.getElementById('diligencia_nombre_edit');
            const correoInput = document.getElementById('diligencia_correo_edit');
            const cargoInput = document.getElementById('diligencia_cargo_edit');

            // 1. Al dar clic al botón azul de la ficha, mostramos la modal
            if (btnAbrir) {
                btnAbrir.addEventListener('click', () => {
                    myModal.show();
                });
            }

            // 2. Al dar clic al botón verde "Confirmar y Guardar" dentro de la modal
            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', () => {

                    // Limpiar estados de error previos
                    [nombreInput, correoInput, cargoInput].forEach(el => el.classList.remove('is-invalid'));

                    // VALIDACIÓN DE NOMBRE
                    if (!nombreInput.value.trim()) {
                        alert("Por favor, ingrese el nombre de quien realiza los cambios.");
                        nombreInput.classList.add('is-invalid');
                        nombreInput.focus();
                        return;
                    }

                    // VALIDACIÓN DE CORREO
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!correoInput.value.trim()) {
                        alert("El correo electrónico es obligatorio.");
                        correoInput.classList.add('is-invalid');
                        correoInput.focus();
                        return;
                    } else if (!emailRegex.test(correoInput.value)) {
                        alert("Por favor, ingrese un correo electrónico válido.");
                        correoInput.classList.add('is-invalid');
                        correoInput.focus();
                        return;
                    }

                    // VALIDACIÓN DE CARGO / ÁREA
                    if (!cargoInput.value.trim()) {
                        alert("Por favor, indique el cargo o área de quien edita.");
                        cargoInput.classList.add('is-invalid');
                        cargoInput.focus();
                        return;
                    }

                    // Si todos los campos están bien:
                    // Deshabilitamos el botón para evitar doble clic y enviamos
                    const modoFirmar = document.getElementById('modoFirmarEdit');
                    if (modoFirmar?.checked) {
                        const checksFirma = Array.from(document.querySelectorAll('.firma-required-check'));
                        const checkFaltante = checksFirma.find(check => !check.checked);
                        checksFirma.forEach(check => check.classList.toggle('is-invalid', !check.checked));
                        if (checkFaltante) {
                            alert("Para firmar debe aceptar todos los checks legales.");
                            checkFaltante.focus();
                            return;
                        }
                    }

                    btnConfirmar.disabled = true;
                    btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

                    mainForm.submit();
                });
            }
        });


    </script>
    <script>
        // --- Planes tarifarios: mostrar/ocultar tabla dinámicas según checkboxes ---
        function toggleDynamicTable() {
            const dinamica = document.getElementById('tarifaDinamica')?.checked;
            const ambas = document.getElementById('tarifaAmbas')?.checked;
            const tablaDinamica = document.getElementById('tablaDinamica');
            if (!tablaDinamica) return;
            tablaDinamica.style.display = (dinamica || ambas) ? 'block' : 'none';
        }

        // Exclusividad simple: si marca "Ambos", desmarca FIT y Dinámicas. Si marca FIT o Dinámicas, desmarca Ambos.
        function normalizeTarifaChecks(changedId) {
            const fit = document.getElementById('tarifaFIT');
            const din = document.getElementById('tarifaDinamica');
            const amb = document.getElementById('tarifaAmbas');
            if (!fit || !din || !amb) return;

            if (changedId === 'tarifaAmbas' && amb.checked) {
                fit.checked = false;
                din.checked = false;
            } else if ((changedId === 'tarifaFIT' || changedId === 'tarifaDinamica') && (fit.checked || din.checked)) {
                amb.checked = false;
            }
        }

        function agregarFilaTablaTarifas() {
            const tabla = document.getElementById('tablaTarifas')?.getElementsByTagName('tbody')[0];
            if (!tabla) return;
            const fila = tabla.insertRow();
            fila.innerHTML = `
            <td><input type="text" class="form-control" name="plan_tarifario_nombre[]" required></td>
            <td><input type="text" class="form-control" name="plan_tarifario_cancelacion[]" required></td>
            <td><input type="text" class="form-control" name="plan_tarifario_penalidad[]" required></td>
            <td><input type="text" class="form-control" name="plan_tarifario_no_show[]" required></td>
            <td><input type="text" class="form-control" name="plan_tarifario_salida_anticipada[]" required></td>
            <td class="text-center"><button class="btn btn-danger btn-sm" type="button" onclick="eliminarFilaTablaTarifas(this)">Eliminar</button></td>
        `;
        }

        function eliminarFilaTablaTarifas(boton) {
            const fila = boton.closest("tr");
            const tbody = document.querySelector('#tablaTarifas tbody');
            if (!fila || !tbody) return;
            if (tbody.rows.length > 1) {
                fila.remove();
            } else {
                fila.querySelectorAll('input').forEach(i => i.value = '');
            }
        }

        // --- Allotment (mismo comportamiento del formulario) ---
        function agregarFilaAllotment() {
            const tbody = document.querySelector('#tablaAllotment tbody');
            if (!tbody) return;

            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
              <input type="text" class="form-control form-control-sm"
                     name="allotment_tipo_habitacion[]" placeholder="Tipo de habitación" required>
            </td>
            <td style="max-width:160px;">
              <input type="number" class="form-control form-control-sm"
                     name="allotment_num_habitaciones[]" min="0" placeholder="0" required>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaAllotment(this)">Eliminar</button>
            </td>
        `;
            tbody.appendChild(tr);
        }

        function eliminarFilaAllotment(btn) {
            const tr = btn.closest('tr');
            const tbody = document.querySelector('#tablaAllotment tbody');
            if (tbody && tr && tbody.rows.length > 1) {
                tr.remove();
            } else if (tbody && tr) {
                tr.querySelectorAll('input').forEach(i => i.value = '');
            }
        }

        (function initTarifasAllotmentEdit() {
            const fit = document.getElementById('tarifaFIT');
            const din = document.getElementById('tarifaDinamica');
            const amb = document.getElementById('tarifaAmbas');

            [fit, din, amb].forEach(el => {
                if (!el) return;
                el.addEventListener('change', () => {
                    normalizeTarifaChecks(el.id);
                    toggleDynamicTable();
                });
            });
            toggleDynamicTable(); // estado inicial

            const allotmentCheckbox = document.getElementById('allotmentCheckbox');
            const allotmentFields = document.getElementById('allotmentFields');
            function updateAllotmentUI() {
                const visible = allotmentCheckbox && allotmentCheckbox.checked;
                if (allotmentFields) {
                    allotmentFields.style.display = visible ? 'block' : 'none';
                    allotmentFields.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = !visible);
                }
            }
            if (allotmentCheckbox) {
                allotmentCheckbox.addEventListener('change', updateAllotmentUI);
                updateAllotmentUI(); // estado inicial
            }
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        (function () {
            // --------- Monto crédito: mantener visual formateado, DB en oculto ----------
            const inputVisual = document.getElementById('monto_visual');
            const inputReal = document.getElementById('monto_real');
            if (inputVisual && inputReal) {
                const formatCOP = (raw) => {
                    if (!raw) return '';
                    try {
                        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(raw);
                    } catch (e) {
                        return raw;
                    }
                };

                // Inicial: si ya viene un número sin formato, lo formateamos
                const initial = (inputReal.value || inputVisual.value || '').toString().replace(/\D/g, '');
                if (initial) {
                    inputReal.value = initial;
                    inputVisual.value = formatCOP(parseInt(initial, 10));
                }

                inputVisual.addEventListener('input', (e) => {
                    let valor = (e.target.value || '').replace(/\D/g, '');
                    inputReal.value = valor;
                    e.target.value = valor ? formatCOP(parseInt(valor, 10)) : '';
                });
            }

            // --------- Forma conexión: mostrar select / input y bloquear allotment si no es extranet ----------
            const extranetRadio = document.getElementById('extranetRadio');
            const channelManagerRadio = document.getElementById('channelManagerRadio');
            const channelSelect = document.getElementById('channelManagerName');
            const otherChannel = document.getElementById('otroChannelInput');
            const allotmentFields = document.getElementById('allotmentFields');
            const allotmentCheckbox = document.getElementById('allotmentCheckbox');

            const setConnectionUI = () => {
                const isChannel = channelManagerRadio && channelManagerRadio.checked;
                const isExtranet = extranetRadio && extranetRadio.checked;

                if (channelSelect) channelSelect.style.display = isChannel ? 'block' : 'none';
                if (otherChannel) otherChannel.style.display = 'none';

                if (isExtranet) {
                    if (allotmentCheckbox) allotmentCheckbox.disabled = false;
                } else {
                    // si no es extranet, ocultar allotment y desmarcar
                    if (allotmentCheckbox) {
                        allotmentCheckbox.checked = false;
                        allotmentCheckbox.disabled = true;
                    }
                    if (allotmentFields) allotmentFields.style.display = 'none';
                }
            };

            if (extranetRadio) extranetRadio.addEventListener('change', setConnectionUI);
            if (channelManagerRadio) channelManagerRadio.addEventListener('change', setConnectionUI);

            // Si es channel manager: seleccionar valor actual y mostrar "Otro" si aplica
            if (channelSelect) {
                const current = <?php echo json_encode($channel_manager_actual ?? ''); ?>;
                if (current) {
                    let matched = false;
                    for (const opt of channelSelect.options) {
                        if (opt.value === current) { opt.selected = true; matched = true; break; }
                    }
                    // si no coincide, lo tratamos como "Otro"
                    if (!matched) {
                        for (const opt of channelSelect.options) {
                            if (opt.value === 'Otro') { opt.selected = true; break; }
                        }
                        if (otherChannel) {
                            otherChannel.style.display = 'block';
                            otherChannel.value = current;
                        }
                    }
                }

                channelSelect.addEventListener('change', () => {
                    if (channelSelect.value === 'Otro') {
                        if (otherChannel) otherChannel.style.display = 'block';
                    } else {
                        if (otherChannel) { otherChannel.style.display = 'none'; otherChannel.value = channelSelect.value; }
                    }
                });
            }

            // Allotment toggle
            if (allotmentCheckbox && allotmentFields) {
                const toggleAllotment = () => {
                    allotmentFields.style.display = allotmentCheckbox.checked ? 'block' : 'none';
                };
                allotmentCheckbox.addEventListener('change', toggleAllotment);
                toggleAllotment();
            }

            // --------- Tarifas dinámicas: mostrar tablaDinamica si Dinamicas o Ambos ----------
            const tablaDinamica = document.getElementById('tablaDinamica');
            const tarifaDinamica = document.getElementById('tarifaDinamica');
            const tarifaAmbas = document.getElementById('tarifaAmbas');

            const toggleTablaDinamica = () => {
                if (!tablaDinamica) return;
                const show = (tarifaDinamica && tarifaDinamica.checked) || (tarifaAmbas && tarifaAmbas.checked);
                tablaDinamica.style.display = show ? 'block' : 'none';
            };
            if (tarifaDinamica) tarifaDinamica.addEventListener('change', toggleTablaDinamica);
            if (tarifaAmbas) tarifaAmbas.addEventListener('change', toggleTablaDinamica);
            toggleTablaDinamica();

            // Inicializar UI de conexión
            setConnectionUI();
        })();
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function activarAnimacion(btnId, spinnerId, textId) {
                var btn = document.getElementById(btnId);
                if (!btn) return;

                var spinner = document.getElementById(spinnerId);
                var text = document.getElementById(textId);

                btn.addEventListener('click', function () {
                    if (btn.dataset.aprobacionBloqueada === '1') {
                        return;
                    }

                    if (spinner) spinner.classList.remove('d-none');
                    if (text) text.textContent = 'Enviando...';

                    btn.classList.add('disabled');
                    btn.setAttribute('aria-disabled', 'true');
                });
            }

            const btnAprobacion = document.getElementById('btnEnviarAprobacion');
            const avisoAprobacion = document.getElementById('avisoAprobacionBloqueada');
            if (btnAprobacion && btnAprobacion.dataset.aprobacionBloqueada === '1') {
                btnAprobacion.addEventListener('click', function () {
                    if (!avisoAprobacion) return;

                    avisoAprobacion.textContent = btnAprobacion.dataset.aprobacionMensaje || 'No se puede aprobar esta ficha.';
                    avisoAprobacion.classList.remove('d-none');
                    avisoAprobacion.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }

            activarAnimacion('btnEnviarAprobacion', 'spinnerAprobacion', 'textEnviarAprobacion');
            activarAnimacion('btnZoho', 'spinnerZoho', 'textZoho');
            activarAnimacion('btnZeus', 'spinnerZeus', 'textZeus');

            // Modal Rechazo: set id/nombre
            const modal = document.getElementById('modalRechazo');
            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    const id = btn.getAttribute('data-id');
                    const nombre = btn.getAttribute('data-nombre');

                    const idInput = document.getElementById('rechazo_id_hotel');
                    const nameEl = document.getElementById('rechazo_nombre_hotel');
                    const motivo = document.getElementById('motivo_rechazo');

                    if (idInput) idInput.value = id || '';
                    if (nameEl) nameEl.textContent = nombre || '---';
                    if (motivo) motivo.value = '';
                });

                const form = modal.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        const b = document.getElementById('btnEnviarRechazoModal');
                        if (b) { b.disabled = true; b.textContent = 'Enviando...'; }
                    });
                }
            }
        });
    </script>

    <script>
        // ---- MODO CONSULTA: deshabilitar edición pero conservar botones superiores ----
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formHotel');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    return false;
                });
            }

            // Deshabilitar inputs/select/textarea dentro del form (excepto hidden)
            document.querySelectorAll('#formHotel input, #formHotel select, #formHotel textarea').forEach(el => {
                if (el.type === 'hidden') return;
                el.disabled = true;
                el.readOnly = true;
            });


            // Ocultar botones de guardar/editar dentro del formulario (sin tocar botones de la cabecera)
            const hideSelectors = [
                'button[type="submit"]',
                '#btnAbrirModalEdit',
                '#save-signature-edit',
                '#saveSignatureBtn',
                '.btn-guardar',
                '.btn-actualizar',
                '[onclick*="agregarFila"]',
                '[onclick*="eliminarFila"]',
                '[onclick*="agregarFilaAllotment"]',
                '[onclick*="eliminarFilaAllotment"]',
                '[onclick*="agregarFilaTablaTarifas"]',
                '[onclick*="eliminarFilaTablaTarifas"]'
            ];
            hideSelectors.forEach(sel => {
                document.querySelectorAll('form ' + sel).forEach(btn => {
                    // No ocultar botones dentro del modal de rechazo
                    if (btn.closest('#modalRechazo')) return;
                    btn.style.display = 'none';
                });
            });

            // Fallback tabs: si por alguna razón no engancha Bootstrap, alternar manualmente
            document.querySelectorAll('#myTab [data-bs-toggle="tab"]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    // Si bootstrap existe, que lo maneje él
                    if (window.bootstrap && bootstrap.Tab) return;
                    e.preventDefault();
                    document.querySelectorAll('#myTab .nav-link').forEach(x => x.classList.remove('active'));
                    btn.classList.add('active');

                    const targetSel = btn.getAttribute('data-bs-target');
                    if (!targetSel) return;
                    document.querySelectorAll('#myTabContent .tab-pane').forEach(p => {
                        p.classList.remove('show', 'active');
                    });
                    const target = document.querySelector(targetSel);
                    if (target) target.classList.add('show', 'active');
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
