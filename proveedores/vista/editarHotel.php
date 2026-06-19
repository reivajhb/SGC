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
$isProveedor = (bool) ($_SESSION['PROV_AUTH'] ?? false);

// ================== PERMISOS ==================
$rolesPermitidos = [ROL_ADMIN, ROL_2, ROL_8, ROL_CADENA, ROL_GESTORAS, ROL_PROVEEDOR];

if (!in_array($idRol, $rolesPermitidos, true)) {
    echo "<script>
            alert('Acceso denegado: No tienes permisos para editar esta ficha.');
            window.history.back();
          </script>";
    exit;
}

// ================== SIDEBAR ==================
if ($idRol === ROL_ADMIN) {
    include_once $ROOT . "/facturacion/config/sidebar3.php";
} elseif (!in_array($idRol, [ROL_CADENA, ROL_PROVEEDOR], true)) {
    include_once $ROOT . "/facturacion/config/sidebar.php";
    include_once $ROOT . "/facturacion/config/boton_volver.php";
}

// ================== HEADER ==================
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
if ($idRol === ROL_CADENA || $idRol === ROL_PROVEEDOR) {
    $usuarioProveedor = $_SESSION['usuario'] ?? null;
    if (!$usuarioProveedor) {
        die("<div class='container mt-4 alert alert-danger'>No se pudo identificar el usuario proveedor.</div>");
    }

    // Permitir acceso al proveedor/cadena cuando la sesión coincida con:
    // 1) usuario_creacion: usuario que creó la ficha
    // 2) nit: NIT principal del hotel/proveedor
    // 3) nit_consecutivo: NIT interno usado para cadenas hoteleras
    // Esto mantiene la seguridad por id_hotel, pero evita bloquear fichas creadas por usuarios internos.
    $sql_general = "SELECT *,
        salones_eventos_count, centro_negocios_count, espacios_externos_count, forma_conexion,
        channel_manager_nombre, descuento_dinamico, rep_legal_nombre, rep_legal_cargo, acepta_declaracion,
        acepta_terminos, acepta_politicas, acepta_compromiso, tiene_certificado_sostenibilidad
        FROM tbl_alojamiento_general
        WHERE id_hotel = ?
        AND (
            usuario_creacion = ?
            OR nit = ?
            OR nit_consecutivo = ?
        )
        LIMIT 1";
    $stmt_general = $conn->prepare($sql_general);
    $stmt_general->bind_param("isss", $id_hotel, $usuarioProveedor, $usuarioProveedor, $usuarioProveedor);
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

$estado_firma_actual = strtoupper(trim((string) ($datos_hotel['estado_firma'] ?? '')));
$fichaYaFirmada = ($estado_firma_actual === 'FIRMADO');

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
$sql_docs = "SELECT id_doc, tipo_documento, nombre_archivo, ruta_almacenamiento
             FROM tbl_alojamiento_documentos
             WHERE id_hotel = ? ORDER BY tipo_documento, id_doc";
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

function pnv_norm_doc_tipo($value)
{
    $value = strtolower(trim((string) $value));
    $value = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
        $value
    );
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
    return trim(preg_replace('/\s+/', ' ', $value));
}

function pnv_tiene_doc_tipo(array $documentos, array $aliases): bool
{
    $aliasesNorm = array_map('pnv_norm_doc_tipo', $aliases);
    foreach ($documentos as $doc) {
        $tipo = pnv_norm_doc_tipo($doc['tipo_documento'] ?? '');
        if (in_array($tipo, $aliasesNorm, true)) {
            return true;
        }
    }
    return false;
}

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
    <title>Editar Hotel: <?php echo htmlspecialchars($datos_hotel['nombre']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="estilos_hotel_moderno.css?v=habitaciones-20260612">
    <link rel="icon" type="image/x-icon" href="/facturacion/img/favicon.jpg">
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
        #loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        }

        #loading-overlay.active {
            display: flex;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .spinner {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .spinner::before {
            content: '';
            position: absolute;
            inset: 0;
            border: 4px solid rgba(255,255,255,0.2);
            border-top: 4px solid #27ae60;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .spinner img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
<?php if ($headerFile) include_once $headerFile; ?>
    <div class="container-fluid page-card mt-5 mb-5">
        <h1 class="mb-2">Editar Ficha de Proveedor: <?php echo htmlspecialchars($datos_hotel['nombre']); ?></h1>
        <p class="lead text-muted">
            ID de Registro: <?php echo htmlspecialchars($datos_hotel['id_hotel']); ?>
            | NIT: <?php echo htmlspecialchars($datos_hotel['nit']); ?>
        </p>

        <form action="../controlador/actualizarHotel.php" id="formEditarHotel" method="post" enctype="multipart/form-data">
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
                <?php if (!$fichaYaFirmada): ?>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-legal-firma-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-legal-firma" type="button" role="tab" aria-controls="tab-legal-firma"
                            aria-selected="false">
                            7. Legal y Firma
                        </button>
                    </li>
                <?php endif; ?>

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
                                    ?>

                                    <tr>
                                        <td class="required-label">Nombre del Hotel:</td>
                                        <td>
                                            <input type="text" name="general[nombre]" class="form-control" required
                                                value="<?php echo htmlspecialchars($datos_hotel['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>

                                        <td class="required-label">NIT:</td>
                                        <td>
                                            <?php if ($esCadena): ?>
                                                <!-- Vista especial para CADENA -->
                                                <div class="input-group">
                                                    <span class="input-group-text">NIT</span>

                                                    <!-- NIT numérico real -->
                                                    <input type="text" class="form-control" name="general[nit]" id="nitBase"
                                                        value="<?php echo htmlspecialchars($datos_hotel['nit'] ?? $nit_base_sugerido, ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="860402288" required>

                                                    <span class="input-group-text">Sufijo</span>
                                                    <input type="text" class="form-control text-center fw-bold sufijo-input"
                                                        id="nitSufijo" maxlength="2"
                                                        value="<?php echo htmlspecialchars($nit_sufijo_sugerido); ?>"
                                                        required readonly>
                                                </div>

                                                <!-- NIT interno para contabilidad: 860402288A, 860402288B, etc. -->
                                                <input type="hidden" name="general[nit_consecutivo]" id="nitConsecutivo"
                                                    value="<?php echo htmlspecialchars($datos_hotel['nit_consecutivo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                                                <small class="form-text text-muted">
                                                    El sistema asigna automáticamente un consecutivo interno para este NIT
                                                    de cadena.
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
                                        <?php $categoria_sel = $datos_hotel['categoria'] ?? ''; ?>

                                        <td class="required-label">Categoría:</td>
                                        <td>
                                            <select name="general[categoria]" class="form-control" required>
                                                <option value="" <?php echo ($categoria_sel === '') ? 'selected' : ''; ?>>
                                                    -----</option>

                                                <option value="3-star" <?php echo ($categoria_sel === '3-star') ? 'selected' : ''; ?>>3 Estrellas</option>
                                                <option value="4-star" <?php echo ($categoria_sel === '4-star') ? 'selected' : ''; ?>>4 Estrellas</option>
                                                <option value="5-star" <?php echo ($categoria_sel === '5-star') ? 'selected' : ''; ?>>5 Estrellas</option>
                                                <option value="boutique" <?php echo ($categoria_sel === 'boutique') ? 'selected' : ''; ?>>Boutique</option>
                                                <option value="glamping" <?php echo ($categoria_sel === 'glamping') ? 'selected' : ''; ?>>Glamping</option>
                                                <option value="luxury" <?php echo ($categoria_sel === 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                                            </select>
                                        </td>

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
                                                class="form-check-input" <?php if (!empty($datos_hotel['accesibilidad_espacios_comunes']) && (int) $datos_hotel['accesibilidad_espacios_comunes'] === 1)
                                                    echo 'checked'; ?>>

                                            <label for="espaciosAccesibles" class="form-check-label">
                                                Espacios comunes accesibles
                                            </label>
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
                            <tbody id="contactosBody">
                                    <?php foreach ($contactos as $i => $c): ?>
                                    <tr>
                                        <input type="hidden" name="contactos[<?php echo $i; ?>][id_contacto]"
                                            value="<?php echo (int) $c['id_contacto']; ?>">
                                        <td><input type="text" name="contactos[<?php echo $i; ?>][tipo_contacto]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($c['tipo_contacto']); ?>" readonly></td>
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
                                    <td><select name="servicios[parqueadero]" class="form-select" required><?php echo $opts_0123('parqueadero'); ?></select></td>

                                    <td class="required-label">Minibar</td>
                                    <td><select name="servicios[minibar]" class="form-select" required><?php echo $opts_0123('minibar'); ?></select></td>

                                    <td class="required-label">Con cocina</td>
                                    <td><select name="servicios[con_cocina]" class="form-select" required><?php echo $opts_012('con_cocina'); ?></select></td>

                                    <td class="required-label">Cafetera de cortesía</td>
                                    <td><select name="servicios[cafetera_cortesia]" class="form-select" required><?php echo $opts_012('cafetera_cortesia'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Servicio a la habitación</td>
                                    <td><select name="servicios[servicio_habitacion]" class="form-select" required><?php echo $opts_0123('servicio_habitacion'); ?></select></td>

                                    <td class="required-label">Servicio a la habitación 24 hrs</td>
                                    <td><select name="servicios[servicio_habitacion_24_hrs]" class="form-select" required><?php echo $opts_0123('servicio_habitacion_24_hrs'); ?></select></td>

                                    <td class="required-label">Recepción 24 hrs</td>
                                    <td><select name="servicios[recepcion_24_hrs]" class="form-select" required><?php echo $opts_0123('recepcion_24_hrs'); ?></select></td>

                                    <td class="required-label">Transfer Aero-Htl-Aero</td>
                                    <td><select name="servicios[transfer_aero_htl]" class="form-select" required><?php echo $opts_012('transfer_aero_htl'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Aire Acondicionado en el hotel</td>
                                    <td><select name="servicios[aire_acondicionado]" class="form-select" required><?php echo $opts_012('aire_acondicionado'); ?></select></td>

                                    <td class="required-label">Turco</td>
                                    <td><select name="servicios[turco]" class="form-select" required><?php echo $opts_012('turco'); ?></select></td>

                                    <td class="required-label">Servicio de Lavandería</td>
                                    <td><select name="servicios[servicio_lavanderia]" class="form-select" required><?php echo $opts_0123('servicio_lavanderia'); ?></select></td>

                                    <td class="required-label">Transfer Htl - Playa - Htl</td>
                                    <td><select name="servicios[transfer_htl_playa]" class="form-select" required><?php echo $opts_0123('transfer_htl_playa'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Lobby Lounge</td>
                                    <td><select name="servicios[lobby_lounge]" class="form-select" required><?php echo $opts_012('lobby_lounge'); ?></select></td>

                                    <td class="required-label">Bar</td>
                                    <td><select name="servicios[bar]" class="form-select" required><?php echo $opts_012('bar'); ?></select></td>

                                    <td class="required-label">Guarda equipaje</td>
                                    <td><select name="servicios[guarda_equipaje]" class="form-select" required><?php echo $opts_0123('guarda_equipaje'); ?></select></td>

                                    <td class="required-label">Asoleadoras</td>
                                    <td><select name="servicios[asoleadoras]" class="form-select" required><?php echo $opts_0123('asoleadoras'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Terraza</td>
                                    <td><select name="servicios[terraza]" class="form-select" required><?php echo $opts_012('terraza'); ?></select></td>

                                    <td class="required-label">Bar en la piscina</td>
                                    <td><select name="servicios[bar_piscina]" class="form-select" required><?php echo $opts_012('bar_piscina'); ?></select></td>

                                    <td class="required-label">Servicios de Niñera (Cargo Adicional)</td>
                                    <td><select name="servicios[servicios_ninera]" class="form-select" required><?php echo $opts_012('servicios_ninera'); ?></select></td>

                                    <td class="required-label">Muelle Privado</td>
                                    <td><select name="servicios[muelle_privado]" class="form-select" required><?php echo $opts_012('muelle_privado'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Café-Bar</td>
                                    <td><select name="servicios[cafe_bar]" class="form-select" required><?php echo $opts_012('cafe_bar'); ?></select></td>

                                    <td class="required-label">Concierge</td>
                                    <td><select name="servicios[concierge]" class="form-select" required><?php echo $opts_012('concierge'); ?></select></td>

                                    <td class="required-label">Super/Minimercado/ Tienda de regalos</td>
                                    <td><select name="servicios[super_minimercado]" class="form-select" required><?php echo $opts_012('super_minimercado'); ?></select></td>

                                    <td class="required-label">Sendero Ecológico</td>
                                    <td><select name="servicios[sendero_ecologico]" class="form-select" required><?php echo $opts_012('sendero_ecologico'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Discoteca</td>
                                    <td><select name="servicios[discoteca]" class="form-select" required><?php echo $opts_012('discoteca'); ?></select></td>

                                    <td class="required-label">Playa</td>
                                    <td><select name="servicios[playa]" class="form-select" required><?php echo $opts_012('playa'); ?></select></td>

                                    <td class="required-label">Alquiler de bicicletas</td>
                                    <td><select name="servicios[alquiler_bicicletas]" class="form-select" required><?php echo $opts_0123('alquiler_bicicletas'); ?></select></td>

                                    <td class="required-label">Mini Golf</td>
                                    <td><select name="servicios[mini_golf]" class="form-select" required><?php echo $opts_012('mini_golf'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Café, Agua Saborizada y Aromática en la recepción</td>
                                    <td><select name="servicios[cafe_recepcion]" class="form-select" required><?php echo $opts_012('cafe_recepcion'); ?></select></td>

                                    <td class="required-label">Piscina</td>
                                    <td><select name="servicios[piscina]" class="form-select" required><?php echo $opts_012('piscina'); ?></select></td>

                                    <td class="required-label">Cajero Automático</td>
                                    <td><select name="servicios[cajero_automatico]" class="form-select" required><?php echo $opts_012('cajero_automatico'); ?></select></td>

                                    <td class="required-label">Snack-bar</td>
                                    <td><select name="servicios[snack_bar]" class="form-select" required><?php echo $opts_012('snack_bar'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Capilla</td>
                                    <td><select name="servicios[capilla]" class="form-select" required><?php echo $opts_012('capilla'); ?></select></td>

                                    <td class="required-label">Piscina infantil</td>
                                    <td><select name="servicios[piscina_infantil]" class="form-select" required><?php echo $opts_012('piscina_infantil'); ?></select></td>

                                    <td class="required-label">Cambio de moneda</td>
                                    <td><select name="servicios[cambio_moneda]" class="form-select" required><?php echo $opts_012('cambio_moneda'); ?></select></td>

                                    <td class="required-label">Salón de Fitness</td>
                                    <td><select name="servicios[salon_fitness]" class="form-select" required><?php echo $opts_012('salon_fitness'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Club de Niños</td>
                                    <td><select name="servicios[club_ninos]" class="form-select" required><?php echo $opts_012('club_ninos'); ?></select></td>

                                    <td class="required-label">Pesca</td>
                                    <td><select name="servicios[pesca]" class="form-select" required><?php echo $opts_012('pesca'); ?></select></td>

                                    <td class="required-label">Enfermería y/o Servicio Médico</td>
                                    <td><select name="servicios[enfermeria_medico]" class="form-select" required><?php echo $opts_012('enfermeria_medico'); ?></select></td>

                                    <td class="required-label">Zona de Juegos Infantiles</td>
                                    <td><select name="servicios[zona_juegos_infantiles]" class="form-select" required><?php echo $opts_0123('zona_juegos_infantiles'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Sala de Masajes</td>
                                    <td><select name="servicios[sala_masajes]" class="form-select" required><?php echo $opts_012('sala_masajes'); ?></select></td>

                                    <td class="required-label">Salón de juegos</td>
                                    <td><select name="servicios[salon_juegos]" class="form-select" required><?php echo $opts_012('salon_juegos'); ?></select></td>

                                    <td class="required-label">Personal Bilingüe</td>
                                    <td><select name="servicios[personal_bilingue]" class="form-select" required><?php echo $opts_012('personal_bilingue'); ?></select></td>

                                    <td class="required-label">Sauna</td>
                                    <td><select name="servicios[sauna]" class="form-select" required><?php echo $opts_012('sauna'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Salón de belleza</td>
                                    <td><select name="servicios[salon_belleza]" class="form-select" required><?php echo $opts_012('salon_belleza'); ?></select></td>

                                    <td class="required-label">Casino</td>
                                    <td><select name="servicios[casino]" class="form-select" required><?php echo $opts_012('casino'); ?></select></td>

                                    <td class="required-label">Lobby con sala de espera</td>
                                    <td><select name="servicios[lobby_sala_espera]" class="form-select" required><?php echo $opts_012('lobby_sala_espera'); ?></select></td>

                                    <td class="required-label">Spa</td>
                                    <td><select name="servicios[spa]" class="form-select" required><?php echo $opts_012('spa'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Gimnasio</td>
                                    <td><select name="servicios[gimnasio]" class="form-select" required><?php echo $opts_gimnasio('gimnasio'); ?></select></td>

                                    <td class="required-label">Juegos de Mesa (Ping Pong, Billar)</td>
                                    <td><select name="servicios[juegos_mesa]" class="form-select" required><?php echo $opts_012('juegos_mesa'); ?></select></td>

                                    <td class="required-label">Ascensor</td>
                                    <td><select name="servicios[ascensor]" class="form-select" required><?php echo $opts_ascensor('ascensor'); ?></select></td>

                                    <td class="required-label">Toallas para la playa y piscina</td>
                                    <td><select name="servicios[toallas_playa_piscina]" class="form-select" required><?php echo $opts_0123('toallas_playa_piscina'); ?></select></td>
                                </tr>

                                <tr>
                                    <td class="required-label">Jacuzzi</td>
                                    <td><select name="servicios[jacuzzi]" class="form-select" required><?php echo $opts_012('jacuzzi'); ?></select></td>

                                    <td class="required-label">Ventilador de techo</td>
                                    <td><select name="servicios[ventilador_techo]" class="form-select" required><?php echo $opts_012('ventilador_techo'); ?></select></td>

                                    <td class="required-label">Cuenta con agua caliente en habitaciones</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="servicios[agua_caliente_hab]" id="agua_caliente_si" value="1" required <?php echo $radio('agua_caliente_hab', '1'); ?>>
                                            <label class="form-check-label" for="agua_caliente_si">SI</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="servicios[agua_caliente_hab]" id="agua_caliente_no" value="0" <?php echo $radio('agua_caliente_hab', '0'); ?>>
                                            <label class="form-check-label" for="agua_caliente_no">No</label>
                                        </div>
                                    </td>

                                    <td colspan="2"></td>
                                </tr>

                                <tr>
                                    <td colspan="1">ALGUN OTRO SERVICIO?</td>
                                    <td colspan="7">
                                        <textarea id="otroservicio" name="servicios[otro_servicio]" class="form-control" rows="2" placeholder="Describa el servicio."><?php echo htmlspecialchars($servicios['otro_servicio'] ?? ''); ?></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center align-middle section-title required-label" rowspan="2">INTERNET</td>

                                    <td>Wifi:</td>
                                    <td><select name="servicios[internet_wifi]" class="form-select" required><?php echo $opts_internet('internet_wifi'); ?></select></td>

                                    <td class="required-label">Cable:</td>
                                    <td><select name="servicios[internet_cable]" class="form-select" required><?php echo $opts_internet('internet_cable'); ?></select></td>

                                    <td class="required-label">Área de cobertura del internet:</td>
                                    <td colspan="2">
                                        <?php
                                        $cobertura = $cobertura_internet_data ?? [];
                                        if (empty($cobertura) && !empty($servicios['cobertura_internet'])) {
                                            $raw = $servicios['cobertura_internet'];
                                            if (is_string($raw)) {
                                                $tmp = json_decode($raw, true);
                                                $cobertura = (json_last_error() === JSON_ERROR_NONE && is_array($tmp))
                                                    ? $tmp
                                                    : array_filter(array_map('trim', explode(',', $raw)));
                                            } elseif (is_array($raw)) {
                                                $cobertura = $raw;
                                            }
                                        }
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="servicios[cobertura_internet][]" id="internet_habitaciones" value="habitaciones" <?php echo in_array('habitaciones', $cobertura, true) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="internet_habitaciones">Habitaciones</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="servicios[cobertura_internet][]" id="internet_areas" value="areas_especificas" <?php echo in_array('areas_especificas', $cobertura, true) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="internet_areas">Áreas específicas</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="servicios[cobertura_internet][]" id="internet_publicas" value="areas_publicas" <?php echo in_array('areas_publicas', $cobertura, true) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="internet_publicas">Áreas Públicas</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="servicios[cobertura_internet][]" id="no_hay_internet" value="no_hay_internet" <?php echo in_array('no_hay_internet', $cobertura, true) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="no_hay_internet">No hay internet</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="required-label">Canal dedicado:</td>
                                    <td colspan="2"><select name="servicios[canal_dedicado]" class="form-select" required><?php echo $opts_internet('canal_dedicado'); ?></select></td>

                                    <td class="required-label" colspan="2" style="text-align:center;">Wi-Fi Zonas Comunes</td>
                                    <td colspan="2"><select name="servicios[wifi_zonas_comunes]" class="form-select" required><?php echo $opts_internet('wifi_zonas_comunes'); ?></select></td>
                                </tr>
                            </tbody>
                        </table>

                    <?php else: ?>
                        <div class="alert alert-info">No se encontraron servicios registrados.</div>
                    <?php endif; ?>
                </div>


                <div class="tab-pane fade" id="habitaciones" role="tabpanel">
                    <h3 class="mb-3">Tipos de Habitación</h3>
                    <button type="button" class="btn btn-sm btn-success mb-2" id="addHabitacionBtn">+ Agregar
                        habitación</button>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm align-middle small habitaciones-table">
                            <caption class="visually-hidden">Lista de tipos de habitaciones y sus especificaciones
                            </caption>
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
                                    <th>Servicios Generales</th>
                                    <th>Observaciones</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="habitacionesBody">
                                <?php foreach ($habitaciones as $i => $h):
                                    // Decodificar el JSON de servicios genéricos de la base de datos
                                    $raw_servicios_json = $h['servicios_gen_json'] ?? '{"servicios":[],"obs":""}';
                                    $servicios_data = json_decode($raw_servicios_json, true) ?: ['servicios' => [], 'obs' => ''];

                                    // Preparar el resumen visual para la celda
                                    $lista_servicios_texto = implode(', ', $servicios_data['servicios']);
                                    $resumen_visual = !empty($lista_servicios_texto) ? $lista_servicios_texto : 'Sin servicios';
                                    if (!empty($servicios_data['obs'])) {
                                        $resumen_visual .= " [Obs: " . $servicios_data['obs'] . "]";
                                    }
                                    ?>
                                    <tr class="hab-row">
                                        <input type="hidden" name="habitaciones[<?php echo $i; ?>][id_hab]"
                                            value="<?php echo (int) $h['id_hab']; ?>">
                                        <input type="hidden" name="habitaciones[<?php echo $i; ?>][accion]" value="keep">

                                        <td><input type="text" name="habitaciones[<?php echo $i; ?>][tipo_habitacion]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['tipo_habitacion']); ?>"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][total_habitaciones]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['total_habitaciones']); ?>" min="0">
                                        </td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][max_adultos]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_adultos']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][max_ninos]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_ninos']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][max_total]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['max_total']); ?>" min="0"></td>
                                        <td><input type="number" step="0.01" name="habitaciones[<?php echo $i; ?>][mts2]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['mts2']); ?>"></td>

                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][cama_sencilla]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_sencilla']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][cama_doble]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_doble']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][cama_queen]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_queen']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][cama_king]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['cama_king']); ?>" min="0"></td>
                                        <td><input type="number" name="habitaciones[<?php echo $i; ?>][camas_adicionales]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($h['camas_adicionales']); ?>" min="0">
                                        </td>

                                        <td class="services-cell"
                                            data-services="<?php echo htmlspecialchars(implode(';', $servicios_data['servicios'])); ?>"
                                            data-description="<?php echo htmlspecialchars($servicios_data['obs']); ?>">

                                            <div class="service-display small text-dark mb-1"
                                                style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                title="<?php echo htmlspecialchars($resumen_visual); ?>">
                                                <?php echo htmlspecialchars($resumen_visual); ?>
                                            </div>

                                            <button type="button" class="btn btn-primary btn-xs add-service-btn">
                                                <i class="fas fa-edit"></i> Add Servicio
                                            </button>

                                            <input type="hidden" class="hidden-services-json"
                                                name="habitaciones[<?php echo $i; ?>][servicios_gen_json]"
                                                value='<?php echo $raw_servicios_json; ?>'>
                                        </td>

                                        <td><textarea name="habitaciones[<?php echo $i; ?>][observaciones]" rows="1"
                                                class="form-control form-control-sm"><?php echo htmlspecialchars($h['observaciones']); ?></textarea>
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-danger btn-del-hab">X</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="modalServiciosHab" tabindex="-1" aria-labelledby="modalServiciosHabLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalServiciosHabLabel">SERVICIOS GENERALES DE LA HABITACIÓN
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <fieldset class="border p-2 mb-3">
                                            <legend class="h6 fw-bold">Equipamiento</legend>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Closet"
                                                    id="m_closet"><label class="small" for="m_closet">Closet</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Baño Privado"
                                                    id="m_bano"><label class="small" for="m_bano">Baño Privado</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Cocineta"
                                                    id="m_cocineta"><label class="small"
                                                    for="m_cocineta">Cocineta</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Escritorio de Trabajo"
                                                    id="m_escritorio"><label class="small"
                                                    for="m_escritorio">Escritorio</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Balcón o Terraza"
                                                    id="m_balcon"><label class="small"
                                                    for="m_balcon">Balcón/Terraza</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Comedor"
                                                    id="m_comedor"><label class="small" for="m_comedor">Comedor</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Minibar"
                                                    id="m_minibar"><label class="small" for="m_minibar">Minibar</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Amenidades"
                                                    id="m_amenidades"><label class="small"
                                                    for="m_amenidades">Amenidades</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Sofá Cama"
                                                    id="m_sofacama"><label class="small" for="m_sofacama">Sofá
                                                    Cama</label></div>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <fieldset class="border p-2 mb-3">
                                            <legend class="h6 fw-bold">Tecnología</legend>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Teléfono" id="m_tel"><label
                                                    class="small" for="m_tel">Teléfono</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Cajilla de Seguridad"
                                                    id="m_seguridad"><label class="small" for="m_seguridad">Cajilla
                                                    Seguridad</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Wifi Habitaci6n"
                                                    id="m_wifi"><label class="small" for="m_wifi">Wifi Hab.</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Nevera"
                                                    id="m_nevera"><label class="small" for="m_nevera">Nevera</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="iPad" id="m_ipad"><label
                                                    class="small" for="m_ipad">iPad</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Smart TV" id="m_tv"><label
                                                    class="small" for="m_tv">Smart TV</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb"
                                                    value="Base para conectar IPOD/MP3" id="m_ipod"><label class="small"
                                                    for="m_ipod">Base iPod/MP3</label></div>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <fieldset class="border p-2 mb-3">
                                            <legend class="h6 fw-bold">Comodidades</legend>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Batas de Baño y Pantuflas"
                                                    id="m_batas"><label class="small"
                                                    for="m_batas">Batas/Pantuflas</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Cuna para bebé disponible"
                                                    id="m_cuna"><label class="small" for="m_cuna">Cuna bebé</label>
                                            </div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Secador para pelo"
                                                    id="m_secador"><label class="small" for="m_secador">Secador
                                                    pelo</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Máquina de Café"
                                                    id="m_cafetera"><label class="small" for="m_cafetera">Máquina
                                                    Café</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb"
                                                    value="Plancha y tabla de planchar" id="m_plancha"><label
                                                    class="small" for="m_plancha">Plancha/Tabla</label></div>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <fieldset class="border p-2 mb-3">
                                            <legend class="h6 fw-bold">Servicios Adic.</legend>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Servicio a la habitación"
                                                    id="m_roomservice"><label class="small" for="m_roomservice">Room
                                                    Service</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Servicio Despertador"
                                                    id="m_despertador"><label class="small"
                                                    for="m_despertador">Despertador</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Llamadas Locales"
                                                    id="m_locales"><label class="small" for="m_locales">Llam.
                                                    Locales</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Llamadas Internacionales"
                                                    id="m_internac"><label class="small" for="m_internac">Llam.
                                                    Internac.</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb" value="Tina de Hidromasajes"
                                                    id="m_tina"><label class="small" for="m_tina">Tina
                                                    Hidromasaje</label></div>
                                            <div class="form-check"><input type="checkbox"
                                                    class="form-check-input modal-cb"
                                                    value="Aire Acondicionado Habitación" id="m_ac_hab"><label
                                                    class="small" for="m_ac_hab">A/C Habitación</label></div>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="form-group mt-2">
                                    <label for="modal_descripcion_hab" class="fw-bold small">Observaciones de
                                        servicios:</label>
                                    <textarea class="form-control form-control-sm" id="modal_descripcion_hab" rows="3"
                                        placeholder="Relacione servicios adicionales y su costo si aplica..."></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-info btn-sm text-white"
                                    id="modalCheckAll">Seleccionar Todas</button>
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary btn-sm"
                                    id="btnActualizarServiciosRow">Actualizar Servicios</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        let targetRow = null;
                        const modalElement = document.getElementById('modalServiciosHab');
                        const bModal = new bootstrap.Modal(modalElement);

                        // 1. Abrir Modal y Cargar Datos
                        document.getElementById('habitacionesBody').addEventListener('click', function (e) {
                            if (e.target.classList.contains('add-service-btn')) {
                                targetRow = e.target.closest('tr');
                                const cell = targetRow.querySelector('.services-cell');

                                // Limpiar Modal
                                document.querySelectorAll('.modal-cb').forEach(cb => cb.checked = false);
                                document.getElementById('modal_descripcion_hab').value = '';

                                // Obtener datos guardados en la celda (separados por ;)
                                const savedServices = cell.dataset.services ? cell.dataset.services.split(';') : [];
                                const savedObs = cell.dataset.description || '';

                                // Marcar Checkboxes
                                document.querySelectorAll('.modal-cb').forEach(cb => {
                                    if (savedServices.includes(cb.value)) cb.checked = true;
                                });
                                document.getElementById('modal_descripcion_hab').value = savedObs;

                                bModal.show();
                            }
                        });

                        // 2. Guardar del Modal a la Fila
                        document.getElementById('btnActualizarServiciosRow').addEventListener('click', function () {
                            if (!targetRow) return;

                            const selected = Array.from(document.querySelectorAll('.modal-cb:checked')).map(cb => cb.value);
                            const obs = document.getElementById('modal_descripcion_hab').value.trim();
                            const cell = targetRow.querySelector('.services-cell');

                            // Actualizar data attributes para futura edición
                            cell.dataset.services = selected.join(';');
                            cell.dataset.description = obs;

                            // Actualizar Resumen Visual
                            const display = targetRow.querySelector('.service-display');
                            let textoResumen = selected.length > 0 ? selected.join(', ') : 'Sin servicios';
                            if (obs) textoResumen += ` [Obs: ${obs}]`;

                            display.textContent = textoResumen;
                            display.title = textoResumen;

                            // Actualizar el INPUT HIDDEN para el envío POST a PHP
                            const hiddenInput = targetRow.querySelector('.hidden-services-json');
                            hiddenInput.value = JSON.stringify({
                                servicios: selected,
                                obs: obs
                            });

                            bModal.hide();
                        });

                        // 3. Botón Seleccionar Todas
                        let allChecked = false;
                        document.getElementById('modalCheckAll').addEventListener('click', function () {
                            allChecked = !allChecked;
                            document.querySelectorAll('.modal-cb').forEach(cb => cb.checked = allChecked);
                            this.textContent = allChecked ? "Desmarcar Todas" : "Seleccionar Todas";
                        });
                    });
                </script>

                <div class="tab-pane fade" id="salones" role="tabpanel">
                    <h3 class="mb-3">Salones y Espacios para Eventos</h3>
                    <button type="button" class="btn btn-sm btn-success mb-2" id="addSalonBtn">+ Agregar salón</button>
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
                                        <td><button type="button" class="btn btn-xs btn-danger btn-del-salon">X</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="documentos" role="tabpanel">
                    <h3 class="mb-3">Gestión de Documentos y Galería</h3>

                    <h5 class="text-primary border-bottom pb-2">Documentación Legal</h5>
                    <div class="table-responsive mb-5">
                        <table class="table table-sm align-middle table-hover border small">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th class="text-center">Drive</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hay_docs = false;
                                foreach ($documentos as $doc):
                                    // Excluimos las fotos de esta tabla
                                    if ($doc['tipo_documento'] === 'Foto Promocional')
                                        continue;
                                    $hay_docs = true;
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo $doc['tipo_documento']; ?></span>
                                        </td>
                                        <td>
                                            <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]"
                                                value="<?php echo $doc['id_doc']; ?>">
                                            <input type="text" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]"
                                                class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                                        </td>
                                        <td class="text-center"><a href="<?php echo $doc['ruta_almacenamiento']; ?>"
                                                target="_blank" class="btn btn-xs btn-outline-primary">Ver</a></td>
                                        <td class="text-end">
                                            <select name="docs_old[<?php echo $doc['id_doc']; ?>][accion]"
                                                class="form-select form-select-sm w-auto d-inline-block">
                                                <option value="keep">Mantener</option>
                                                <option value="delete">Eliminar</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach;
                                if (!$hay_docs)
                                    echo '<tr><td colspan="4" class="text-center py-2">No hay documentos legales.</td></tr>';
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    $documentos_esperados_edicion = [
                        'rut' => ['label' => 'RUT', 'tipo' => 'RUT', 'aliases' => ['RUT'], 'required' => true],
                        'rnt' => ['label' => 'RNT / Registro Nacional de Turismo', 'tipo' => 'RNT', 'aliases' => ['RNT', 'Registro Nacional de Turismo', 'Registro Turismo'], 'required' => true],
                        'camara_comercio' => ['label' => 'Camara de Comercio', 'tipo' => 'Camara de Comercio', 'aliases' => ['Camara de Comercio', 'Camara Comercio'], 'required' => true],
                        'certificacion_bancaria' => ['label' => 'Certificacion Bancaria', 'tipo' => 'Certificacion Bancaria', 'aliases' => ['Certificacion Bancaria'], 'required' => true],
                        'sostenibilidad' => ['label' => 'Sostenibilidad', 'tipo' => 'Sostenibilidad', 'aliases' => ['Sostenibilidad', 'Certificados Sostenibilidad', 'Certificados de Sostenibilidad'], 'required' => false],
                        'bomberos' => ['label' => 'Bomberos', 'tipo' => 'Bomberos', 'aliases' => ['Bomberos', 'Certificado Bomberos', 'Certificado de Seguridad de Bomberos'], 'required' => false],
                        'credito_actual' => ['label' => 'Credito Actual', 'tipo' => 'Credito Actual', 'aliases' => ['Credito Actual', 'Informacion Credito', 'Informacion sobre Credito Actual'], 'required' => false],
                        'concepto_sanitario' => ['label' => 'Concepto Sanitario', 'tipo' => 'Concepto Sanitario', 'aliases' => ['Concepto Sanitario', 'Concepto Tecnico Sanitario'], 'required' => false],
                        'mantenimiento_piscinas' => ['label' => 'Mantenimiento Piscinas', 'tipo' => 'Mantenimiento Piscinas', 'aliases' => ['Mantenimiento Piscinas', 'Certificado de Mantenimiento de Piscinas'], 'required' => false],
                        'mantenimiento_ascensores' => ['label' => 'Mantenimiento Ascensores', 'tipo' => 'Mantenimiento Ascensores', 'aliases' => ['Mantenimiento Ascensores', 'Certificado de Mantenimiento de Ascensores'], 'required' => false],
                        'sg_sst' => ['label' => 'SG-SST', 'tipo' => 'SG-SST', 'aliases' => ['SG-SST', 'Certificado de Implementacion de SG-SST'], 'required' => false],
                        'arl' => ['label' => 'ARL', 'tipo' => 'ARL', 'aliases' => ['ARL'], 'required' => false],
                    ];

                    $documentos_faltantes_edicion = [];
                    foreach ($documentos_esperados_edicion as $key => $docEsperado) {
                        if (!pnv_tiene_doc_tipo($documentos, $docEsperado['aliases'])) {
                            $documentos_faltantes_edicion[$key] = $docEsperado;
                        }
                    }
                    ?>

                    <?php if (!empty($documentos_faltantes_edicion)): ?>
                        <div class="card mb-5 border-warning">
                            <div class="card-header bg-warning fw-bold py-2">
                                Documentos faltantes
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php foreach ($documentos_faltantes_edicion as $key => $docFaltante): ?>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">
                                                <?php echo htmlspecialchars($docFaltante['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if ($docFaltante['required']): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <input type="hidden"
                                                name="nuevos_docs_tipos[faltante_<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"
                                                value="<?php echo htmlspecialchars($docFaltante['tipo'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="file"
                                                class="form-control form-control-sm <?php echo $docFaltante['required'] ? 'doc-faltante-obligatorio' : ''; ?>"
                                                name="nuevos_docs_files[faltante_<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"
                                                accept=".pdf">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h5 class="text-primary border-bottom pb-2">Catálogo de Fotos</h5>

                    <?php
                    $tipos_foto_extra = ['Foto Promocional','Foto Fachada','Foto Habitaciones','Foto Piscina','Foto Zona Comun'];

                    // Indexar documentos por tipo
                    $fotos_por_tipo   = [];
                    $fotos_adicionales = [];
                    foreach ($documentos as $doc) {
                        if (!in_array($doc['tipo_documento'], $tipos_foto_extra)) continue;
                        if ($doc['tipo_documento'] === 'Foto Promocional') {
                            $fotos_adicionales[] = $doc;
                        } else {
                            $fotos_por_tipo[$doc['tipo_documento']] = $doc;
                        }
                    }

                    $slots_principales = [
                        'Foto Fachada'      => 'Fachada',
                        'Foto Habitaciones' => 'Habitaciones',
                        'Foto Piscina'      => 'Piscina / Zona recreativa',
                        'Foto Zona Comun'   => 'Zona Común',
                    ];
                    ?>

                    <!-- 4 FOTOS PRINCIPALES FIJAS -->
                    <p class="small text-muted mb-2">Fotos promocionales principales (usadas en el PDF):</p>
                    <div class="row g-3 mb-4">
                      <?php foreach ($slots_principales as $tipo => $etiqueta):
                        $doc    = $fotos_por_tipo[$tipo] ?? null;
                        $imgSrc = $doc ? driveViewUrl($doc['ruta_almacenamiento']) : null;
                      ?>
                      <div class="col-12 col-sm-6 col-md-3">
                        <div class="card h-100 shadow-sm" style="border:2px solid <?php echo $doc ? '#0d6efd' : '#dee2e6'; ?>;">
                          <div style="height:130px;overflow:hidden;background:#f0f4f8;">
                            <?php if ($imgSrc): ?>
                              <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                   style="width:100%;height:100%;object-fit:cover;"
                                   onerror="this.src='https://placehold.co/300x130?text=Error'">
                            <?php else: ?>
                              <div class="d-flex align-items-center justify-content-center h-100 text-muted small">Sin foto</div>
                            <?php endif; ?>
                          </div>
                          <div class="card-body p-2">
                            <div class="small fw-semibold text-primary mb-2"><?php echo $etiqueta; ?></div>
                            <?php if ($doc): ?>
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]" value="<?php echo $doc['id_doc']; ?>">
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]" value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo htmlspecialchars($doc['ruta_almacenamiento']); ?>" target="_blank"
                                   class="btn btn-sm btn-outline-primary"><i class="fa fa-external-link"></i></a>
                                <select name="docs_old[<?php echo $doc['id_doc']; ?>][accion]"
                                        class="form-select form-select-sm" style="width:100px;">
                                  <option value="keep">OK</option>
                                  <option value="delete">Borrar</option>
                                </select>
                              </div>
                            <?php else: ?>
                              <input type="file" class="form-control form-control-sm"
                                     name="nueva_foto_principal[<?php echo htmlspecialchars($tipo); ?>]"
                                     accept="image/*">
                              <input type="hidden" name="nueva_foto_tipo[<?php echo htmlspecialchars($tipo); ?>]" value="<?php echo htmlspecialchars($tipo); ?>">
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- FOTOS ADICIONALES -->
                    <?php if (!empty($fotos_adicionales)): ?>
                    <p class="small text-muted mb-2">Fotos adicionales:</p>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
                      <?php foreach ($fotos_adicionales as $doc):
                        $imgSrc = driveViewUrl($doc['ruta_almacenamiento']); ?>
                        <div class="col">
                          <div class="card h-100 shadow-sm border-info">
                            <div style="height:130px;overflow:hidden;background:#eee;">
                              <img src="<?php echo htmlspecialchars($imgSrc ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   style="object-fit:cover;height:100%;width:100%;"
                                   onerror="this.src='https://placehold.co/300x130?text=Error'">
                            </div>
                            <div class="card-body p-2">
                              <input type="hidden" name="docs_old[<?php echo $doc['id_doc']; ?>][id]" value="<?php echo $doc['id_doc']; ?>">
                              <input type="text" name="docs_old[<?php echo $doc['id_doc']; ?>][nombre]"
                                     class="form-control form-control-sm mb-2"
                                     value="<?php echo htmlspecialchars($doc['nombre_archivo']); ?>">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo htmlspecialchars($doc['ruta_almacenamiento']); ?>" target="_blank"
                                   class="btn btn-sm btn-outline-primary"><i class="fa fa-external-link"></i></a>
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

                <?php if (!$fichaYaFirmada): ?>
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
                                        <option value="Si" <?php echo ($tiene_certificado_sostenibilidad === 'Si') ? 'selected' : ''; ?>>Sí</option>
                                        <option value="No" <?php echo ($tiene_certificado_sostenibilidad === 'No') ? 'selected' : ''; ?>>No</option>
                                    </select>
                                    <div id="bloquePoliticaSostenibilidad" style="display:none; margin-top:10px;">
                                        <p> <strong> NOTA: </strong>
                                    <a href="https://forms.zohopublic.com/panamericadeviajes/form/FORMULARIOCOMPROMISOSOSTENIBILIDAD/formperma/NJSbJSOm2DCS23YXcpQ-cU5HEQAad3H0HQjHW0c5lTU"
                                        target="_blank">
                                            De click aqui para ver nuestro formulario de Certificación de Sostenibilidad
                                        </a>
                                    </p>
                                    </div>
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
                <?php endif; ?>

            </div>




            <div class="modal fade" id="modalDiligenciadorEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" style="color: #ffffff !important;">Confirmar cambios</h5>
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

            // ================= HABITACIONES (Ajustado para Modal) =================
            if (addHabBtn && habTbody) {
                addHabBtn.addEventListener('click', function () {
                    const idx = habIndex++;
                    const tr = document.createElement('tr');
                    tr.classList.add('hab-row');

                    // Definimos la estructura HTML de la nueva fila
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
            
            <td class="services-cell" data-services="" data-description="">
                <div class="service-display small text-dark mb-1" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    Sin servicios
                </div>
                
                <button type="button" class="btn btn-primary btn-xs add-service-btn">
                    Add Servicio
                </button>

                <input type="hidden" class="hidden-services-json" 
                       name="habitaciones[${idx}][servicios_gen_json]" 
                       value='{"servicios":[],"obs":""}'>
            </td>

            <td><textarea name="habitaciones[${idx}][observaciones]" rows="1" class="form-control form-control-sm"></textarea></td>
            
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-danger btn-del-hab">X</button>
            </td>
        `;

                    habTbody.appendChild(tr);
                });

                // Evento para eliminar (o marcar para eliminar)
                habTbody.addEventListener('click', function (e) {
                    if (e.target.classList.contains('btn-del-hab')) {
                        const row = e.target.closest('tr');
                        const idInput = row.querySelector('input[name*="[id_hab]"]');
                        const accionInput = row.querySelector('input[name*="[accion]"]');

                        if (idInput && idInput.value) {
                            // Si tiene ID, es una fila de la DB: marcar para borrar
                            if (accionInput) accionInput.value = 'delete';
                            row.classList.add('table-danger');
                            row.querySelectorAll('input, textarea, select, button:not(.btn-del-hab)').forEach(el => {
                                el.disabled = true;
                            });
                            e.target.textContent = 'Deshacer';
                            e.target.classList.replace('btn-danger', 'btn-warning');
                            e.target.classList.replace('btn-del-hab', 'btn-undo-hab');
                        } else {
                            // Si no tiene ID, es una fila nueva: quitar del DOM
                            row.remove();
                        }
                    } else if (e.target.classList.contains('btn-undo-hab')) {
                        // Lógica para deshacer el borrado
                        const row = e.target.closest('tr');
                        const accionInput = row.querySelector('input[name*="[accion]"]');
                        if (accionInput) accionInput.value = 'keep';
                        row.classList.remove('table-danger');
                        row.querySelectorAll('input, textarea, select, button').forEach(el => el.disabled = false);
                        e.target.textContent = 'X';
                        e.target.classList.replace('btn-warning', 'btn-danger');
                        e.target.classList.replace('btn-undo-hab', 'btn-del-hab');
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
                // Activar automáticamente el modo firma al empezar a dibujar
                if (modoFirmar && !modoFirmar.checked) {
                    modoFirmar.checked = true;
                    modoFirmar.dispatchEvent(new Event('change'));
                }
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
                    // Auto-capturar canvas si el usuario dibujó pero no presionó "Guardar Firma"
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

                    const canvasEl = document.getElementById('signature-pad-edit');
                    const sigDataInput = document.getElementById('firma_dibujada_data_edit');
                    const drawOpt = document.getElementById('draw-signature-edit');
                    if (canvasEl && sigDataInput && drawOpt?.checked && !sigDataInput.value) {
                        // Solo capturar si hay algo dibujado en el canvas
                        const ctx2 = canvasEl.getContext('2d');
                        const blank = document.createElement('canvas');
                        blank.width = canvasEl.width;
                        blank.height = canvasEl.height;
                        if (canvasEl.toDataURL() !== blank.toDataURL()) {
                            sigDataInput.value = canvasEl.toDataURL();
                        }
                    }

                    // Deshabilitamos el botón para evitar doble clic y enviamos
                    btnConfirmar.disabled = true;
                    btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

                    const overlay = document.getElementById('loading-overlay');
                    if (overlay) overlay.classList.add('active');

                    setTimeout(() => {
                        mainForm.submit();
                    }, 100);
                });
            }
        });


    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>


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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div id="loading-overlay">
    <div class="loading-content">
        <div class="spinner">
            <img src="/facturacion/img/faviconxd.png" alt="Cargando..." />
        </div>
        <div class="loading-text">Estamos actualizando la ficha del proveedor</div>
        <div class="loading-subtext">Por favor espere un momento</div>
    </div>
</div>
</body>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formEditarHotel");
    const overlay = document.getElementById("loading-overlay");

    if (form && overlay) {
        form.addEventListener("submit", function () {
            overlay.classList.add("active");
        });
    }
});
</script>
<script>
function togglePoliticaSostenibilidad() {
    const select = document.getElementById('certificadoSostenibilidadEdit');
    const bloque = document.getElementById('bloquePoliticaSostenibilidad');

    if (!select || !bloque) return;

    bloque.style.display = select.value === 'Si' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('certificadoSostenibilidadEdit');

    if (select) {
        select.addEventListener('change', togglePoliticaSostenibilidad);
        togglePoliticaSostenibilidad();
    }
});
</script>
<script>
let contactoIndex = <?php echo count($contactos); ?>;

function agregarContacto() {
    const tbody = document.getElementById('contactosBody');

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <input type="hidden" name="contactos[${contactoIndex}][id_contacto]" value="0">

        <td><input type="text" name="contactos[${contactoIndex}][tipo_contacto]" class="form-control form-control-sm"></td>
        <td><input type="text" name="contactos[${contactoIndex}][nombre]" class="form-control form-control-sm"></td>
        <td><input type="text" name="contactos[${contactoIndex}][movil]" class="form-control form-control-sm"></td>
        <td><input type="email" name="contactos[${contactoIndex}][email]" class="form-control form-control-sm"></td>
        <td><input type="text" name="contactos[${contactoIndex}][telefono]" class="form-control form-control-sm"></td>
    `;

    tbody.appendChild(tr);
    contactoIndex++;
}
</script>
</html>
