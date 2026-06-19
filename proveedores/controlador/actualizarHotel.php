<?php
include_once "../../facturacion/config/seguridad.php";
include_once "../../facturacion/config/conexion.php";
define('ZOHO_CALLED_AS_LIBRARY', true);
require_once __DIR__ . '/enviar_zoho.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONFIGURACIÓN DE RUTAS Y GOOGLE API
$ROOT_PATH = dirname(__DIR__, 2);
$VENDOR_AUTOLOAD = $ROOT_PATH . '/google-api-php-client--PHP8.0/vendor/autoload.php';
$GOOGLE_JSON = $ROOT_PATH . '/drive-contabilidad-a46b4f106c64.json';

if (file_exists($VENDOR_AUTOLOAD)) {
    require_once $VENDOR_AUTOLOAD;
}

use Google\Service\Drive\DriveFile;
use Google\Service\Drive as DriveService;

// ==================== PHPMailer (AGREGADO) ====================
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. SEGURIDAD Y ROLES
define('ROL_ADMIN', 1);
define('ROL_CADENA', 7);
define('ROL_PROVEEDOR', 6);
define('ROL_GESTORA', 9);
$idRol = (int) ($_SESSION['id_rol'] ?? 0);
$nitCadena = $_SESSION['usuario'] ?? null;
$isProveedor = $_SESSION['PROV_AUTH'] ?? false;
function colExists(mysqli $conn, string $table, string $col): bool
{
    $dbRes = $conn->query("SELECT DATABASE() AS db");
    $dbRow = $dbRes ? $dbRes->fetch_assoc() : null;
    $db = $dbRow['db'] ?? '';
    if ($db === '')
        return false;

    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st)
        return false;
    $st->bind_param("sss", $db, $table, $col);
    $st->execute();
    $res = $st->get_result();
    $ok = ($res && $res->num_rows > 0);
    $st->close();
    return $ok;
}

function upsertMarker(string $text, string $marker, string $value): string
{
    // marker example: [[REGIMEN_ALIMENTICIO]]
    $pattern = '/^' . preg_quote($marker, '/') . ':.+$/m';
    $line = $marker . ': ' . $value;
    if (preg_match($pattern, $text)) {
        return preg_replace($pattern, $line, $text);
    }
    $sep = ($text === '' ? '' : "\n");
    return $text . $sep . $line;
}

function fechaFirmaSql($value): string
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return '';
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : '';
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
    return $timestamp ? date('Y-m-d', $timestamp) : '';
}

$rolesPermitidos = [ROL_ADMIN, ROL_CADENA, 2, 8, ROL_PROVEEDOR, ROL_GESTORA];

if (!in_array($idRol, $rolesPermitidos, true)) {
    http_response_code(403);
    exit("Acceso denegado.");
}

// 3. VALIDACIÓN DE ID HOTEL
$id_hotel = $_POST['id_hotel'] ?? null;
if (!$id_hotel || !ctype_digit((string) $id_hotel)) {
    exit("ID de hotel no válido.");
}
$id_hotel = (int) $id_hotel;

// INICIAR TRANSACCIÓN
$conn->begin_transaction();

try {
    // ==================== 1) DATOS GENERALES (TAB 1) ====================
    $g = $_POST['general'] ?? [];
    $modo_firmar = !empty($_POST['modo_firmar']);

    // Compatibilidad: si el front envía campos planos, mapearlos a general[]
    $flatKeys = [
        'incluye_desayuno',
        'numero_habitaciones',
        'precio_desayuno',
        'tipo_desayuno',
        'hora_check_in',
        'hora_check_out',
        'habitaciones_discapacidad',
        'habitaciones_connecting',
        'es_pet_friendly',
        'politica_mascotas',
        'amenidad_restaurante',
        'amenidad_bar_lounge',
        'amenidad_hab_especiales',
        'amenidad_gay_friendly',
        'amenidad_planes_boda',
        'accesibilidad_banos',
        'accesibilidad_habitaciones',
        'accesibilidad_espacios_comunes',
        'informacion_adicional',
        'descripcion_producto'
    ];
    foreach ($flatKeys as $k) {
        if (!array_key_exists($k, $g) && array_key_exists($k, $_POST)) {
            $g[$k] = $_POST[$k];
        }
    }


    // --- Compatibilidad / No pisar datos si el formulario antiguo no envía los campos nuevos ---
    $sql_prev = "SELECT ciiu, tipo_contribuyente, numero_cuenta, tarifa_tipo_json, planes_tarifarios_json, allotment_json, politica_ninos, politica_grupos, firma_fecha
                 FROM tbl_alojamiento_general WHERE id_hotel = ?";
    $stmt_prev = $conn->prepare($sql_prev);
    $stmt_prev->bind_param("i", $id_hotel);
    $stmt_prev->execute();
    $prev = $stmt_prev->get_result()->fetch_assoc() ?: [];
    $stmt_prev->close();



    $cadena_hotelera = trim($g['cadena_hotelera'] ?? '');
    $nombre = trim($g['nombre'] ?? '');
    $razon_social = trim($g['razon_social'] ?? '');
    $telefono = trim($g['telefono'] ?? '');
    $direccion = trim($g['direccion'] ?? '');
    $ciudad = trim($g['ciudad'] ?? '');
    $pais = trim($g['pais'] ?? '');
    $website = trim($g['website'] ?? '');
    $categoria = trim($g['categoria'] ?? '');
    $numero_habitaciones = (string) (int) ($g['numero_habitaciones'] ?? 0);
    $habitaciones_discapacidad = (string) (int) ($g['habitaciones_discapacidad'] ?? 0);
    $habitaciones_connecting = (string) (int) ($g['habitaciones_connecting'] ?? 0);
    $hora_check_in = trim($g['hora_check_in'] ?? '');
    $hora_check_out = trim($g['hora_check_out'] ?? '');
    $incluye_desayuno_raw = $g['incluye_desayuno'] ?? '';
    if ((string) $incluye_desayuno_raw === '2') {
        $incluye_desayuno_raw = '0';
    }
    $incluye_desayuno = ($incluye_desayuno_raw === '' ? '' : (string) (int) $incluye_desayuno_raw);
    $precio_desayuno = trim($g['precio_desayuno'] ?? '');
    $tipo_desayuno = trim($g['tipo_desayuno'] ?? '');
    $es_pet_friendly_raw = $g['es_pet_friendly'] ?? '';
    if ((string) $es_pet_friendly_raw === '2') {
        $es_pet_friendly_raw = '0';
    }
    $es_pet_friendly = ($es_pet_friendly_raw === '' ? '' : (string) (int) $es_pet_friendly_raw);
    $politica_mascotas = trim($g['politica_mascotas'] ?? '');
    $descripcion_producto = trim($g['descripcion_producto'] ?? '');
    $informacion_adicional = trim($g['informacion_adicional'] ?? '');

    // Amenidades (checkboxes)
    $amenidad_restaurante = (string) (int) ($g['amenidad_restaurante'] ?? 0);
    $amenidad_bar_lounge = (string) (int) ($g['amenidad_bar_lounge'] ?? 0);
    $amenidad_hab_especiales = (string) (int) ($g['amenidad_hab_especiales'] ?? 0);
    $amenidad_gay_friendly = (string) (int) ($g['amenidad_gay_friendly'] ?? 0);
    $amenidad_planes_boda = (string) (int) ($g['amenidad_planes_boda'] ?? 0);

    // Accesibilidad (checkboxes)
    $accesibilidad_banos = (string) (int) ($g['accesibilidad_banos'] ?? 0);
    $accesibilidad_habitaciones = (string) (int) ($g['accesibilidad_habitaciones'] ?? 0);
    $accesibilidad_espacios_comunes = (string) (int) ($g['accesibilidad_espacios_comunes'] ?? 0);


    // ===== NUEVOS CAMPOS (checks múltiples) =====
    $regimen_alimenticio_arr = [];
    if (!empty($_POST['regimen_alimenticio']) && is_array($_POST['regimen_alimenticio'])) {
        $regimen_alimenticio_arr = array_values(array_filter(array_map('trim', $_POST['regimen_alimenticio'])));
    }
    $cobertura_internet_arr = [];
    if (!empty($_POST['cobertura_internet']) && is_array($_POST['cobertura_internet'])) {
        $cobertura_internet_arr = array_values(array_filter(array_map('trim', $_POST['cobertura_internet'])));
    }
    $modal_service_arr = [];
    if (!empty($_POST['modal_service']) && is_array($_POST['modal_service'])) {
        $modal_service_arr = array_values(array_filter(array_map('trim', $_POST['modal_service'])));
    }

    $regimen_alimenticio_json = json_encode($regimen_alimenticio_arr, JSON_UNESCAPED_UNICODE);
    $cobertura_internet_json = json_encode($cobertura_internet_arr, JSON_UNESCAPED_UNICODE);
    $modal_service_json = json_encode($modal_service_arr, JSON_UNESCAPED_UNICODE);

    $regimen_alimenticio_csv = implode(', ', $regimen_alimenticio_arr);
    $cobertura_internet_csv = implode(', ', $cobertura_internet_arr);
    $modal_service_csv = implode(', ', $modal_service_arr);

    // Tipo de hotel (prioriza checks tipo_hotel[]; fallback a texto legacy)
    $tipo_arr = [];
    if (!empty($_POST['tipo_hotel']) && is_array($_POST['tipo_hotel'])) {
        $tipo_arr = array_values(array_filter(array_map('trim', $_POST['tipo_hotel'])));
    } else {
        $tipo_arr = array_filter(array_map('trim', explode(',', $g['tipo_hotel_texto'] ?? '')));
    }
    $tipo_hotel_json = json_encode(array_values($tipo_arr), JSON_UNESCAPED_UNICODE);

    // Mercados de distribución: viene del form como mercado_distribucion[] (aunque sea radio)
    $mercados_arr = [];
    if (isset($_POST['mercado_distribucion'])) {
        $md = $_POST['mercado_distribucion'];
        if (!is_array($md)) {
            $md = [$md];
        }
        $mercados_arr = $md;
    } elseif (isset($g['mercados_distribucion'])) {
        $md = $g['mercados_distribucion'];
        if (!is_array($md)) {
            $md = [$md];
        }
        $mercados_arr = $md;
    } elseif (isset($g['mercados_distribucion_texto']) && $g['mercados_distribucion_texto'] !== '') {
        $mercados_arr = array_filter(array_map('trim', explode(',', $g['mercados_distribucion_texto'])));
    }
    $mercados_arr = array_values(array_filter(array_map('trim', $mercados_arr), fn($x) => $x !== ''));
    $mercados_distribucion_json = json_encode($mercados_arr, JSON_UNESCAPED_UNICODE);

    $monto_credito = trim($g['monto_credito'] ?? ($_POST['monto_credito'] ?? ''));
    $tiempo_credito = trim($g['tiempo_credito'] ?? ($_POST['tiempo_credito'] ?? ''));
    $reteica = trim(($g['reteica'] ?? $g['porcentaje_reteica'] ?? $_POST['porcentaje_reteica'] ?? ''));
    $retefuente = trim(($g['retefuente'] ?? $g['porcentaje_retefuente'] ?? $_POST['porcentaje_retefuente'] ?? ''));
    // Nuevos campos (tributario / bancario / dinámicos / políticas)
    $ciiu = trim($g['ciiu'] ?? '');
    $tipo_contribuyente = trim($g['tipo_contribuyente'] ?? '');
    $numero_cuenta = trim($g['numero_cuenta'] ?? '');

    // Tipos de tarifas (checkboxes fuera de 'general')
    $tarifa_tipo_arr = $_POST['tarifa_tipo'] ?? null; // puede no venir en formularios viejos
    if (is_array($tarifa_tipo_arr)) {
        $tarifa_tipo_arr = array_values(array_filter(array_map('trim', $tarifa_tipo_arr)));
        $tarifa_tipo_json = json_encode($tarifa_tipo_arr, JSON_UNESCAPED_UNICODE);
    } else {
        $tarifa_tipo_json = $prev['tarifa_tipo_json'] ?? '[]';
    }

    // Planes tarifarios (tabla dinámica)
    $nombres = $_POST['plan_tarifario_nombre'] ?? null;
    $cancel = $_POST['plan_tarifario_cancelacion'] ?? null;
    $penal = $_POST['plan_tarifario_penalidad'] ?? null;
    $noshow = $_POST['plan_tarifario_no_show'] ?? null;
    $salida = $_POST['plan_tarifario_salida_anticipada'] ?? null;

    if (is_array($nombres)) {
        $planes = [];
        $count = count($nombres);
        for ($i = 0; $i < $count; $i++) {
            $nombre_i = trim($nombres[$i] ?? '');
            if ($nombre_i === '')
                continue;
            $planes[] = [
                'nombre' => $nombre_i,
                'cancelacion' => trim($cancel[$i] ?? ''),
                'penalidad' => trim($penal[$i] ?? ''),
                'no_show' => trim($noshow[$i] ?? ''),
                'salida' => trim($salida[$i] ?? '')
            ];
        }
        $planes_tarifarios_json = json_encode($planes, JSON_UNESCAPED_UNICODE);
    } else {
        $planes_tarifarios_json = $prev['planes_tarifarios_json'] ?? '[]';
    }

        // Allotment (tabla dinámica)
// En editarHotel.php el checkbox puede llegar como general[allotment_selected]
// Las filas llegan como allotment_tipo_habitacion[] y allotment_num_habitaciones[] (fuera de general[])
$forma_conexion = $g['forma_conexion'] ?? ($g['connectionType'] ?? ($_POST['connectionType'] ?? null));

// Detectar si el formulario envió explícitamente el bloque de allotment
$hasAllotmentPost =
    array_key_exists('allotment_selected', $g) ||
    array_key_exists('allotment_selected', $_POST) ||
    array_key_exists('allotment_tipo_habitacion', $_POST) ||
    array_key_exists('allotment_num_habitaciones', $_POST);

// Regla: si NO es extranet -> siempre se limpia allotment
if ($forma_conexion !== 'extranet') {
    $allotment_selected = 0;
    $allotment_json = '[]';
} else {
    // Si el bloque NO vino en el POST, conservamos lo que ya existe (para no pisar datos)
    if (!$hasAllotmentPost) {
        $allotment_selected = (int)($prev['allotment_selected'] ?? 0);
        $allotment_json = $prev['allotment_json'] ?? '[]';
    } else {
        // checkbox: si viene el key => 1, si no viene => 0
        $allotment_selected = (isset($g['allotment_selected']) || isset($_POST['allotment_selected'])) ? 1 : 0;

        $all_tipo = $_POST['allotment_tipo_habitacion'] ?? [];
        $all_num  = $_POST['allotment_num_habitaciones'] ?? [];

        if (!is_array($all_tipo)) $all_tipo = [];
        if (!is_array($all_num))  $all_num  = [];

        $rows = [];
        if ($allotment_selected === 1) {
            $n = min(count($all_tipo), count($all_num));
            for ($i = 0; $i < $n; $i++) {
                $tipo = trim((string)($all_tipo[$i] ?? ''));
                $num  = (int)($all_num[$i] ?? 0);

                // ignorar filas vacías
                if ($tipo === '' && $num === 0) continue;

                $rows[] = [
                    'tipo_habitacion'  => $tipo,
                    'num_habitaciones' => $num,
                ];
            }
        }

        // siempre guardamos algo (si no aplica -> [])
        $allotment_json = json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}

// Políticas (inputs fuera de 'general' en el formulario dinámico)
    // Si vienen, actualizamos. Si no, conservamos.
    // Primero capturamos el array general para facilitar la lectura
    $datos_generales = $_POST['general'] ?? [];

    // Validación para política de niños
    if (isset($datos_generales['politica_ninos'])) {
        $politica_ninos = trim($datos_generales['politica_ninos']);
    } else {
        // Si no viene en el POST (por ejemplo, si el campo estuviera deshabilitado), 
        // mantenemos el valor que ya existía en la base de datos
        $politica_ninos = $prev['politica_ninos'] ?? '';
    }

    // Validación para política de grupos
    if (isset($datos_generales['politica_grupos'])) {
        $politica_grupos = trim($datos_generales['politica_grupos']);
    } else {
        $politica_grupos = $prev['politica_grupos'] ?? '';
    }

    // Si los campos nuevos vienen dentro de general[] (por compatibilidad), priorizamos general[]
    if ($ciiu === '' && isset($prev['ciiu']))
        $ciiu = $prev['ciiu'];
    if ($tipo_contribuyente === '' && isset($prev['tipo_contribuyente']))
        $tipo_contribuyente = $prev['tipo_contribuyente'];
    if ($numero_cuenta === '' && isset($prev['numero_cuenta']))
        $numero_cuenta = $prev['numero_cuenta'];


    $salones_eventos_count = (string) (int) ($g['salones_eventos_count'] ?? 0);
    $centro_negocios_count = (string) (int) ($g['centro_negocios_count'] ?? 0);
    $espacios_externos_count = (string) (int) ($g['espacios_externos_count'] ?? 0);
    $forma_conexion = trim($g['forma_conexion'] ?? '');
    $channel_manager_nombre = trim($g['channel_manager_nombre'] ?? '');
    $descuento_dinamico = trim($g['descuento_dinamico'] ?? '');
    $rep_legal_nombre = trim($g['rep_legal_nombre'] ?? '');
    $rep_legal_cargo = trim($g['rep_legal_cargo'] ?? '');
    $firma_fecha = fechaFirmaSql($g['firma_fecha'] ?? '');
    $firma_fecha_anterior = fechaFirmaSql($prev['firma_fecha'] ?? '');
    if ($firma_fecha === '') {
        $firma_fecha = $modo_firmar ? date('Y-m-d') : $firma_fecha_anterior;
    }
    $tiene_certificado_sostenibilidad = trim($g['tiene_certificado_sostenibilidad'] ?? '');
    $acepta_declaracion = (($g['acepta_declaracion'] ?? '') === '' ? '' : (string) (int) ($g['acepta_declaracion'] ?? 0));
    $acepta_terminos = (($g['acepta_terminos'] ?? '') === '' ? '' : (string) (int) ($g['acepta_terminos'] ?? 0));
    $acepta_politicas = (($g['acepta_politicas'] ?? '') === '' ? '' : (string) (int) ($g['acepta_politicas'] ?? 0));
    $acepta_compromiso = (($g['acepta_compromiso'] ?? '') === '' ? '' : (string) (int) ($g['acepta_compromiso'] ?? 0));
    if ($modo_firmar && (
        $acepta_declaracion !== '1' ||
        $acepta_terminos !== '1' ||
        $acepta_politicas !== '1' ||
        $acepta_compromiso !== '1'
    )) {
        throw new Exception("Para firmar debe aceptar todos los checks legales.");
    }
    $nombre_hotel_legal = trim($g['nombre_hotel_legal'] ?? '');
    $ciudad_hotel_legal = trim($g['ciudad_hotel_legal'] ?? '');
    $nit_hotel_legal = trim($g['nit_hotel_legal'] ?? '');
    $nombre_rep_legal = trim($g['nombre_rep_legal'] ?? '');
    $ciudad_rep_legal = trim($g['ciudad_rep_legal'] ?? '');
    $num_documento_rep_legal = trim($g['num_documento_rep_legal'] ?? '');
    $ciudad_doc_rep_legal = trim($g['ciudad_doc_rep_legal'] ?? '');
    $diligencia_nombre = trim($_POST['diligencia_nombre'] ?? '');
    $diligencia_correo = trim($_POST['diligencia_correo'] ?? '');
    $diligencia_cargo = trim($_POST['diligencia_cargo'] ?? '');

    $sql_general = "UPDATE tbl_alojamiento_general SET 
        cadena_hotelera=?, nombre=?, razon_social=?, telefono=?, direccion=?, ciudad=?, pais=?, website=?, categoria=?, 
        numero_habitaciones=?, habitaciones_discapacidad=?, habitaciones_connecting=?, hora_check_in=?, hora_check_out=?, 
        incluye_desayuno=?, precio_desayuno=?, tipo_desayuno=?, es_pet_friendly=?, politica_mascotas=?, 
        descripcion_producto=?, informacion_adicional=?, tipo_hotel_json=?, mercados_distribucion_json=?, 
        monto_credito=?, tiempo_credito=?, reteica=?, retefuente=?, ciiu=?, tipo_contribuyente=?, numero_cuenta=?, tarifa_tipo_json=?, planes_tarifarios_json=?, allotment_selected=?, allotment_json=?, politica_ninos=?, politica_grupos=?, amenidad_restaurante=?, amenidad_bar_lounge=?, amenidad_hab_especiales=?, amenidad_gay_friendly=?, amenidad_planes_boda=?, accesibilidad_banos=?, accesibilidad_habitaciones=?, accesibilidad_espacios_comunes=?, salones_eventos_count=?, centro_negocios_count=?, 
        espacios_externos_count=?, forma_conexion=?, channel_manager_nombre=?, descuento_dinamico=?, 
        rep_legal_nombre=?, rep_legal_cargo=?, acepta_declaracion=?, acepta_terminos=?, acepta_politicas=?, 
        acepta_compromiso=?, tiene_certificado_sostenibilidad=?, nombre_hotel_legal=?, ciudad_hotel_legal=?, 
        nit_hotel_legal=?, nombre_rep_legal=?, ciudad_rep_legal=?, num_documento_rep_legal=?, ciudad_doc_rep_legal=?, firma_fecha=?, 
        diligencia_nombre=?,diligencia_correo=?, diligencia_cargo=?";

    // Campos opcionales: guardamos en la primera columna disponible (general o servicios). Si no existe, se guarda como marcador en informacion_adicional.
    $generalExtra = [];
    $generalExtraVals = [];

    if (colExists($conn, 'tbl_alojamiento_general', 'regimen_alimenticio_json')) {
        $generalExtra[] = "regimen_alimenticio_json=?";
        $generalExtraVals[] = $regimen_alimenticio_json;
    } elseif (colExists($conn, 'tbl_alojamiento_general', 'regimen_alimenticio')) {
        $generalExtra[] = "regimen_alimenticio=?";
        $generalExtraVals[] = $regimen_alimenticio_csv;
    } else {
        $informacion_adicional = upsertMarker($informacion_adicional, '[[REGIMEN_ALIMENTICIO]]', $regimen_alimenticio_csv);
    }

    if (colExists($conn, 'tbl_alojamiento_general', 'cobertura_internet_json')) {
        $generalExtra[] = "cobertura_internet_json=?";
        $generalExtraVals[] = $cobertura_internet_json;
    } elseif (colExists($conn, 'tbl_alojamiento_general', 'cobertura_internet')) {
        $generalExtra[] = "cobertura_internet=?";
        $generalExtraVals[] = $cobertura_internet_csv;
    } else {
        $informacion_adicional = upsertMarker($informacion_adicional, '[[COBERTURA_INTERNET]]', $cobertura_internet_csv);
    }

    if (colExists($conn, 'tbl_alojamiento_general', 'modal_service_json')) {
        $generalExtra[] = "modal_service_json=?";
        $generalExtraVals[] = $modal_service_json;
    } elseif (colExists($conn, 'tbl_alojamiento_general', 'modal_service')) {
        $generalExtra[] = "modal_service=?";
        $generalExtraVals[] = $modal_service_csv;
    } else {
        $informacion_adicional = upsertMarker($informacion_adicional, '[[MODAL_SERVICE]]', $modal_service_csv);
    }

    if (!empty($generalExtra)) {
        $sql_general .= ", " . implode(', ', $generalExtra);
    }

    $sql_general .= " WHERE id_hotel=?";

    $stmt_general = $conn->prepare($sql_general);
    // Bind dinámico (base + extras)
    $baseParams = [
        $cadena_hotelera,
        $nombre,
        $razon_social,
        $telefono,
        $direccion,
        $ciudad,
        $pais,
        $website,
        $categoria,
        $numero_habitaciones,
        $habitaciones_discapacidad,
        $habitaciones_connecting,
        $hora_check_in,
        $hora_check_out,
        $incluye_desayuno,
        $precio_desayuno,
        $tipo_desayuno,
        $es_pet_friendly,
        $politica_mascotas,
        $descripcion_producto,
        $informacion_adicional,
        $tipo_hotel_json,
        $mercados_distribucion_json,
        $monto_credito,
        $tiempo_credito,
        $reteica,
        $retefuente,
        $ciiu,
        $tipo_contribuyente,
        $numero_cuenta,
        $tarifa_tipo_json,
        $planes_tarifarios_json,
        $allotment_selected,
        $allotment_json,
        $politica_ninos,
        $politica_grupos,
        $amenidad_restaurante,
        $amenidad_bar_lounge,
        $amenidad_hab_especiales,
        $amenidad_gay_friendly,
        $amenidad_planes_boda,
        $accesibilidad_banos,
        $accesibilidad_habitaciones,
        $accesibilidad_espacios_comunes,
        $salones_eventos_count,
        $centro_negocios_count,
        $espacios_externos_count,
        $forma_conexion,
        $channel_manager_nombre,
        $descuento_dinamico,
        $rep_legal_nombre,
        $rep_legal_cargo,
        $acepta_declaracion,
        $acepta_terminos,
        $acepta_politicas,
        $acepta_compromiso,
        $tiene_certificado_sostenibilidad,
        $nombre_hotel_legal,
        $ciudad_hotel_legal,
        $nit_hotel_legal,
        $nombre_rep_legal,
        $ciudad_rep_legal,
        $num_documento_rep_legal,
        $ciudad_doc_rep_legal,
        $firma_fecha,
        $diligencia_nombre,
        $diligencia_correo,
        $diligencia_cargo
    ];

    $params = array_merge($baseParams, $generalExtraVals);
    $params[] = $id_hotel;

    $types = str_repeat('s', count($params) - 1) . 'i';
    $stmt_general->bind_param($types, ...$params);
    $stmt_general->execute();
    $stmt_general->close();

    // ==================== 2) CONTACTOS ====================
if (!empty($_POST['contactos'])) {
    $stmt_upd = $conn->prepare("
        UPDATE tbl_alojamiento_contactos 
        SET tipo_contacto=?, nombre=?, movil=?, email=?, telefono=? 
        WHERE id_contacto=? AND id_hotel=?
    ");

    $stmt_ins = $conn->prepare("
        INSERT INTO tbl_alojamiento_contactos 
        (id_hotel, tipo_contacto, nombre, movil, email, telefono) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($_POST['contactos'] as $c) {
        $idc = (int)($c['id_contacto'] ?? 0);

        $tipo = trim($c['tipo_contacto'] ?? '');
        $nombre = trim($c['nombre'] ?? '');
        $movil = trim($c['movil'] ?? '');
        $email = trim($c['email'] ?? '');
        $telefono = trim($c['telefono'] ?? '');

        if ($tipo === '' && $nombre === '' && $movil === '' && $email === '' && $telefono === '') {
            continue;
        }

        if ($idc > 0) {
            $stmt_upd->bind_param("sssssii", $tipo, $nombre, $movil, $email, $telefono, $idc, $id_hotel);
            $stmt_upd->execute();
        } else {
            $stmt_ins->bind_param("isssss", $id_hotel, $tipo, $nombre, $movil, $email, $telefono);
            $stmt_ins->execute();
        }
    }

    $stmt_upd->close();
    $stmt_ins->close();
}

    // ==================== 3) SERVICIOS ====================
    // Nota: en tbl_alojamiento_servicios hay mezcla de columnas tinyint(1) y columnas varchar/json.
    $servs = $_POST['servicios'] ?? [];
    if (!is_array($servs)) {
        $servs = [];
    }
    $flatServiceKeys = [
        'parqueadero',
        'minibar',
        'con_cocina',
        'cafetera_cortesia',
        'ventilador_techo',
        'servicio_habitacion',
        'servicio_habitacion_24_hrs',
        'bar'
    ];

    foreach ($flatServiceKeys as $key) {
        if (isset($_POST[$key])) {
            $servs[$key] = $_POST[$key];
        }
    }

    // Cobertura de internet puede venir como:
    // 1) cobertura_internet[]              formulario crear
    // 2) servicios[cobertura_internet][]   formulario editar
    $cov = null;

    if (isset($servs['cobertura_internet'])) {
        $cov = $servs['cobertura_internet'];
    } elseif (isset($_POST['cobertura_internet'])) {
        $cov = $_POST['cobertura_internet'];
    }

    if ($cov !== null) {
        if (!is_array($cov)) {
            $cov = [$cov];
        }

    $cov = array_values(array_filter(array_map('trim', $cov), fn($x) => $x !== ''));

    if (colExists($conn, 'tbl_alojamiento_servicios', 'cobertura_internet')) {
        $servs['cobertura_internet'] = json_encode($cov, JSON_UNESCAPED_UNICODE);
    }
}

    // Agua caliente: si no viene, se fuerza a 0 (para permitir desmarcar/poner NO)
    if (colExists($conn, 'tbl_alojamiento_servicios', 'agua_caliente_hab')) {
        if (isset($servs['agua_caliente_hab'])) {
            $servs['agua_caliente_hab'] = ((string) $servs['agua_caliente_hab'] === '1') ? '1' : '0';
        } else {
            $servs['agua_caliente_hab'] = '0';
        }
    }

    // Asegurar que exista fila en servicios para el hotel (si no existe, la creamos)
    $has_serv_row = false;
    $chk = $conn->prepare('SELECT id_servicio FROM tbl_alojamiento_servicios WHERE id_hotel=? LIMIT 1');
    $chk->bind_param('i', $id_hotel);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $has_serv_row = true;
    }
    $chk->free_result();
    $chk->close();
    if (!$has_serv_row) {
        $ins = $conn->prepare('INSERT INTO tbl_alojamiento_servicios (id_hotel) VALUES (?)');
        $ins->bind_param('i', $id_hotel);
        $ins->execute();
        $ins->close();
    }

    if (!empty($servs)) {
        $up_parts = [];
        $pars = [];

        // Columnas que deben guardarse como string (NO int)
        $stringCols = ['internet_wifi', 'internet_cable', 'wifi_zonas_comunes', 'canal_dedicado', 'otro_servicio', 'cobertura_internet'];

        foreach ($servs as $k => $v) {
            if ($k === 'id_hotel')
                continue;
            if (!colExists($conn, 'tbl_alojamiento_servicios', $k))
                continue;
            $up_parts[] = "$k = ?";
            if (in_array($k, $stringCols, true)) {
                if (is_array($v)) {
                    $v = json_encode(array_values(array_filter(array_map('trim', $v))), JSON_UNESCAPED_UNICODE);
                }
                $pars[] = trim((string) $v);
            } else {
                // tinyint(1) / int
                $pars[] = (string) ((int) $v);
            }
        }
        if (!empty($up_parts)) {
            $pars[] = $id_hotel;
            $stmt_s = $conn->prepare('UPDATE tbl_alojamiento_servicios SET ' . implode(', ', $up_parts) . ' WHERE id_hotel = ?');
            $stmt_s->bind_param(str_repeat('s', count($pars) - 1) . 'i', ...$pars);
            $stmt_s->execute();
            $stmt_s->close();
        }
    }

    // ==================== 4) HABITACIONES ====================
    // ==================== 4) HABITACIONES ====================
    if (!empty($_POST['habitaciones'])) {
        $st_del_h = $conn->prepare("DELETE FROM tbl_alojamiento_habitaciones WHERE id_hab=? AND id_hotel=?");

        // Ajustamos el UPDATE para recibir el JSON directamente
        $st_upd_h = $conn->prepare("UPDATE tbl_alojamiento_habitaciones SET tipo_habitacion=?, total_habitaciones=?, max_adultos=?, max_ninos=?, max_total=?, mts2=?, cama_sencilla=?, cama_doble=?, cama_queen=?, cama_king=?, camas_adicionales=?, observaciones=?, servicios_gen_json=? WHERE id_hab=? AND id_hotel=?");

        // Ajustamos el INSERT para recibir el JSON directamente
        $st_ins_h = $conn->prepare("INSERT INTO tbl_alojamiento_habitaciones (id_hotel, tipo_habitacion, total_habitaciones, max_adultos, max_ninos, max_total, mts2, cama_sencilla, cama_doble, cama_queen, cama_king, camarote_sencillo, camarote_doble, camas_adicionales, observaciones, servicios_gen_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        foreach ($_POST['habitaciones'] as $h) {
            $id_hab = (int) ($h['id_hab'] ?? 0);

            // Acción de eliminar
            if (($h['accion'] ?? '') === 'delete' && $id_hab > 0) {
                $st_del_h->bind_param("ii", $id_hab, $id_hotel);
                $st_del_h->execute();
                continue;
            }

            // --- CAMBIO CLAVE AQUÍ ---
            // Capturamos el JSON que viene del Modal. Si no existe, enviamos un JSON vacío válido.
            $s_json = $h['servicios_gen_json'] ?? '{"servicios":[],"obs":""}';

            if ($id_hab > 0) {
                // UPDATE: "siiiiidiiiissi i" (13 strings/ints + 2 ids al final)
                // Usamos h['observaciones'] para la columna observaciones y $s_json para servicios_gen_json
                $st_upd_h->bind_param(
                    "siiiiidiiiissii",
                    $h['tipo_habitacion'],
                    $h['total_habitaciones'],
                    $h['max_adultos'],
                    $h['max_ninos'],
                    $h['max_total'],
                    $h['mts2'],
                    $h['cama_sencilla'],
                    $h['cama_doble'],
                    $h['cama_queen'],
                    $h['cama_king'],
                    $h['camas_adicionales'],
                    $h['observaciones'],
                    $s_json,
                    $id_hab,
                    $id_hotel
                );
                $st_upd_h->execute();
            } elseif (trim($h['tipo_habitacion'] ?? '') !== '') {
                // INSERT
                $camar_s = 0; // Valores por defecto para camarotes si no están en el form
                $camar_d = 0;
                $st_ins_h->bind_param(
                    "isiiiiidiiiiisss",
                    $id_hotel,
                    $h['tipo_habitacion'],
                    $h['total_habitaciones'],
                    $h['max_adultos'],
                    $h['max_ninos'],
                    $h['max_total'],
                    $h['mts2'],
                    $h['cama_sencilla'],
                    $h['cama_doble'],
                    $h['cama_queen'],
                    $h['cama_king'],
                    $camar_s,
                    $camar_d,
                    $h['camas_adicionales'],
                    $h['observaciones'],
                    $s_json
                );
                $st_ins_h->execute();
            }
        }
        $st_del_h->close();
        $st_upd_h->close();
        $st_ins_h->close();
    }

    // ==================== 5) SALONES ====================
    if (!empty($_POST['salones'])) {
        $st_del_s = $conn->prepare("DELETE FROM tbl_alojamiento_salones WHERE id_salon=? AND id_hotel=?");
        $st_upd_s = $conn->prepare("UPDATE tbl_alojamiento_salones SET nombre_salon=?, m2=?, largo=?, ancho=?, alto=?, cap_u_herradura=?, cap_aula=?, cap_auditorio=?, cap_banquete=?, cap_coctel=? WHERE id_salon=? AND id_hotel=?");
        $st_ins_s = $conn->prepare("INSERT INTO tbl_alojamiento_salones (id_hotel, nombre_salon, m2, largo, ancho, alto, cap_u_herradura, cap_aula, cap_auditorio, cap_banquete, cap_imperial, cap_coctel) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($_POST['salones'] as $sl) {
            $id_s = (int) ($sl['id_salon'] ?? 0);
            if (($sl['accion'] ?? '') === 'delete' && $id_s > 0) {
                $st_del_s->bind_param("ii", $id_s, $id_hotel);
                $st_del_s->execute();
                continue;
            }
            if ($id_s > 0) {
                $st_upd_s->bind_param("sddddiiiiiii", $sl['nombre_salon'], $sl['m2'], $sl['largo'], $sl['ancho'], $sl['alto'], $sl['cap_u_herradura'], $sl['cap_aula'], $sl['cap_auditorio'], $sl['cap_banquete'], $sl['cap_coctel'], $id_s, $id_hotel);
                $st_upd_s->execute();
            } elseif (trim($sl['nombre_salon'] ?? '') !== '') {
                $imp = '0';
                $st_ins_s->bind_param("isddddiiiiii", $id_hotel, $sl['nombre_salon'], $sl['m2'], $sl['largo'], $sl['ancho'], $sl['alto'], $sl['cap_u_herradura'], $sl['cap_aula'], $sl['cap_auditorio'], $sl['cap_banquete'], $imp, $sl['cap_coctel']);
                $st_ins_s->execute();
            }
        }
        $st_del_s->close();
        $st_upd_s->close();
        $st_ins_s->close();
    }

    // ==================== 6) DOCUMENTOS (DRIVE) ====================
    if (!empty($_POST['docs_old'])) {
        $st_del_d = $conn->prepare("DELETE FROM tbl_alojamiento_documentos WHERE id_doc=? AND id_hotel=?");
        $st_upd_d = $conn->prepare("UPDATE tbl_alojamiento_documentos SET nombre_archivo=? WHERE id_doc=? AND id_hotel=?");
        foreach ($_POST['docs_old'] as $id_doc => $info) {
            if (($info['accion'] ?? '') === 'delete') {
                $st_del_d->bind_param("ii", $id_doc, $id_hotel);
                $st_del_d->execute();
            } else {
                $st_upd_d->bind_param("sii", $info['nombre'], $id_doc, $id_hotel);
                $st_upd_d->execute();
            }
        }
        $st_del_d->close();
        $st_upd_d->close();
    }

    // B. Subir nuevos archivos a Drive (Versión Auditada)
    if (!empty($_FILES)) {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $GOOGLE_JSON);
        $client = new Google\Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $service = new DriveService($client);
        $folderIdDrive = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';

        $st_ins_d = $conn->prepare("INSERT INTO tbl_alojamiento_documentos (id_hotel, tipo_documento, nombre_archivo, ruta_almacenamiento) VALUES (?,?,?,?)");

        // Recorremos los tipos enviados por POST
        $sostenibilidad_tmp  = null;
        $sostenibilidad_name = null;
        $sostenibilidad_mime = null;
        if (isset($_POST['nuevos_docs_tipos'])) {
            foreach ($_POST['nuevos_docs_tipos'] as $idx => $tipoDoc) {
                $fileKey = "nuevos_docs_files";
                if (isset($_FILES[$fileKey]['name'][$idx]) && $_FILES[$fileKey]['error'][$idx] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES[$fileKey]['name'][$idx];
                    $tmpPath = $_FILES[$fileKey]['tmp_name'][$idx];
                    $mime = $_FILES[$fileKey]['type'][$idx];

                    $fileMetadata = new DriveFile([
                        'name' => $fileName,
                        'parents' => [$folderIdDrive]
                    ]);

                    $content = file_get_contents($tmpPath);

                    $driveFile = $service->files->create($fileMetadata, [
                        'data' => $content,
                        'mimeType' => $mime,
                        'uploadType' => 'media',
                        'fields' => 'id'
                    ]);

                    $url = "https://drive.google.com/open?id=" . $driveFile->id;

                    // Capturar sostenibilidad para email
                    if ($tipoDoc === 'Sostenibilidad' && file_exists($tmpPath)) {
                        $sostenibilidad_tmp  = $tmpPath;
                        $sostenibilidad_name = $fileName;
                        $sostenibilidad_mime = $mime;
                    }

                    $st_ins_d->bind_param("isss", $id_hotel, $tipoDoc, $fileName, $url);
                    $st_ins_d->execute();
                }
            }
        }

        // Fotos principales individuales (Foto Fachada, Foto Habitaciones, Foto Piscina, Foto Zona Comun)
        $tipos_principales_validos = ['Foto Fachada', 'Foto Habitaciones', 'Foto Piscina', 'Foto Zona Comun'];
        if (isset($_FILES['nueva_foto_principal']) && is_array($_FILES['nueva_foto_principal']['name'])) {
            foreach ($_FILES['nueva_foto_principal']['name'] as $tipoSlot => $fileName) {
                if (!in_array($tipoSlot, $tipos_principales_validos)) continue;
                if (empty($fileName) || $_FILES['nueva_foto_principal']['error'][$tipoSlot] !== UPLOAD_ERR_OK) continue;

                $tmpPath = $_FILES['nueva_foto_principal']['tmp_name'][$tipoSlot];
                $mime    = $_FILES['nueva_foto_principal']['type'][$tipoSlot];

                $fileMetadata = new DriveFile([
                    'name'    => $fileName,
                    'parents' => [$folderIdDrive]
                ]);
                $content   = file_get_contents($tmpPath);
                $driveFile = $service->files->create($fileMetadata, [
                    'data'       => $content,
                    'mimeType'   => $mime,
                    'uploadType' => 'media',
                    'fields'     => 'id'
                ]);
                $url = "https://drive.google.com/open?id=" . $driveFile->id;

                $st_ins_d->bind_param("isss", $id_hotel, $tipoSlot, $fileName, $url);
                $st_ins_d->execute();
            }
        }

        $st_ins_d->close();
    }

    // ==================== 7) FIRMA (DRIVE) ====================
    // Solo si el usuario activó modo_firmar (checkbox)
    $modo_firmar = !empty($_POST['modo_firmar']); // acepta 'on' o '1'

    // Bandera para correo y estado (AGREGADO)
    $firma_guardada = false;

    if ($modo_firmar) {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $GOOGLE_JSON);
        $client = new Google\Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $service = new DriveService($client);

        $folderIdDrive = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';

        // Eliminar firma anterior de DB (para mantener solo 1)
        $conn->query("DELETE FROM tbl_alojamiento_documentos 
                  WHERE id_hotel = {$id_hotel} AND tipo_documento = 'Firma Digital'");

        // Fuente de firma: canvas (base64) o archivo
        $firma_option = $_POST['firma_option'] ?? 'draw';
        $tmpPath = null;
        $fileName = null;
        $mime = null;

        // A) Firma dibujada (base64)
        if ($firma_option === 'draw') {
            $dataURL = $_POST['firma_dibujada_data'] ?? '';

            if (is_string($dataURL) && strpos($dataURL, 'data:image') === 0) {
                $parts = explode(',', $dataURL, 2);
                $base64 = $parts[1] ?? '';
                $binary = base64_decode($base64);

                if ($binary !== false) {
                    $tmpPath = tempnam(sys_get_temp_dir(), 'sig_');
                    file_put_contents($tmpPath, $binary);

                    $fileName = "firma_hotel_{$id_hotel}_" . date('Ymd_His') . ".png";
                    $mime = "image/png";
                }
            }
        }

        // B) Imagen subida
        if ($firma_option === 'upload' && isset($_FILES['firma_imagen_file']) && $_FILES['firma_imagen_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['firma_imagen_file']['tmp_name'];
            $fileName = $_FILES['firma_imagen_file']['name'];
            $mime = $_FILES['firma_imagen_file']['type'] ?: 'application/octet-stream';
        }

        // Si tenemos firma lista, subir a Drive + guardar en DB
        if ($tmpPath && $fileName) {
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderIdDrive]
            ]);

            $content = file_get_contents($tmpPath);

            $driveFile = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mime,
                'uploadType' => 'media',
                'fields' => 'id'
            ]);

            $url = "https://drive.google.com/open?id=" . $driveFile->id;

            $stmtFirma = $conn->prepare("
                INSERT INTO tbl_alojamiento_documentos (id_hotel, tipo_documento, nombre_archivo, ruta_almacenamiento)
                VALUES (?, 'Firma Digital', ?, ?)
            ");
            $stmtFirma->bind_param("iss", $id_hotel, $fileName, $url);
            $stmtFirma->execute();
            $stmtFirma->close();

            // ✅ Marca firma guardada (AGREGADO)
            $firma_guardada = true;
        }

        // Limpieza si fue temp file
        if ($firma_option === 'draw' && $tmpPath && file_exists($tmpPath)) {
            @unlink($tmpPath);
        }
    }

    // ==================== 7.1) ESTADO FIRMADO (AGREGADO) ====================
    if ($firma_guardada) {
        $conn->query("UPDATE tbl_alojamiento_general SET estado_firma ='FIRMADO', updated_at=NOW() WHERE id_hotel=" . (int) $id_hotel);
    }

    // COMMIT DB
    $conn->commit();

    // ==================== EMAIL SOSTENIBILIDAD ====================
    if (!empty($sostenibilidad_tmp) && file_exists($sostenibilidad_tmp)) {
        try {
            $smtp = include __DIR__ . '/../../aws.php';

            $mailSost = new PHPMailer(true);
            $mailSost->isSMTP();
            $mailSost->Host       = $smtp['ses_host'];
            $mailSost->SMTPAuth   = true;
            $mailSost->Username   = $smtp['ses_user'];
            $mailSost->Password   = $smtp['ses_pass'];
            $mailSost->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailSost->Port       = $smtp['ses_port'];
            $mailSost->CharSet    = 'UTF-8';
            $mailSost->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
            $mailSost->addAddress('calidad@panamericanaviajes.com');
            $mailSost->isHTML(true);
            $mailSost->Subject = 'Certificado de Sostenibilidad - Hotel ID ' . $id_hotel;
            $mailSost->Body    = '<p>Se ha adjuntado el certificado de sostenibilidad del hotel con ID <strong>' . $id_hotel . '</strong>.</p>';
            $mailSost->addAttachment($sostenibilidad_tmp, $sostenibilidad_name ?: 'certificado_sostenibilidad');
            $mailSost->send();
        } catch (Exception $e) {
            error_log('[actualizarHotel] Email sostenibilidad error: ' . $e->getMessage());
        }
    }

    // ==================== 7.2) CORREO SOLO SI FIRMÓ (AGREGADO) ====================
    if ($firma_guardada) {
        try {
            $smtp = include __DIR__ . '/../../aws.php';

            // Traer info para correo (seguro)
            $stmtInfo = $conn->prepare("SELECT nombre, nit, razon_social, ciudad, pais, categoria, numero_habitaciones FROM tbl_alojamiento_general WHERE id_hotel=? LIMIT 1");
            $stmtInfo->bind_param("i", $id_hotel);
            $stmtInfo->execute();
            $info = $stmtInfo->get_result()->fetch_assoc();
            $stmtInfo->close();

            $nombreMail = $info['nombre'] ?? '';
            $nitMail = $info['nit'] ?? '';
            $razonMail = $info['razon_social'] ?? '';
            $ciudadMail = $info['ciudad'] ?? '';
            $paisMail = $info['pais'] ?? '';
            $catMail = $info['categoria'] ?? '';
            $habMail = $info['numero_habitaciones'] ?? '';

            $mail = new PHPMailer(true);

            $correo = "negociaciones@panamericanaviajes.com";
            $subject = "FICHA DE INSCRIPCIÓN: Nuevo Proveedor de Alojamiento - FIRMADO - " . $nombreMail;

            $datosHotel = array(
                'Cordial saludo,',
                '✅ La Ficha de Inscripción de Proveedor de Alojamiento ha sido ACTUALIZADA Y FIRMADA.',
                '<h3>Detalles del Hotel: ' . htmlspecialchars($nombreMail) . '</h3>',
                '<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">',
                '<tr><th colspan="2" style="background-color: #198754; color: white; padding: 10px; text-align: left;">Confirmación de Firma y Edición</th></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left; width: 30%;"><strong>NIT</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($nitMail) . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Razón Social</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($razonMail) . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Ciudad/País</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($ciudadMail) . '/' . htmlspecialchars($paisMail) . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Categoría</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($catMail) . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Total Habitaciones</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($habMail) . '</td></tr>',

                // --- NUEVA SECCIÓN: QUIEN EDITÓ ---
                '<tr><th colspan="2" style="background-color: #f8f9fa; color: #333; padding: 10px; text-align: left; border: 1px solid #ddd;">Datos de quien realizó los cambios</th></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Nombre</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($diligencia_nombre) . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Correo</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($diligencia_correo ?: 'No proporcionado') . '</td></tr>',
                '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Cargo</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($diligencia_cargo ?: 'No proporcionado') . '</td></tr>',
                // ---------------------------------

                '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Link de Consulta</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><a href="https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=' . (int) $id_hotel . '">Ver Ficha Actualizada</a></td></tr>',
                '</table>',
                '<br><p>Gracias,</p>',
                '<p>Atentamente,<br><strong>Equipo Panamericana de Viajes</strong></p>'
            );
            
            $smtp = include __DIR__ . '/../../aws.php';

            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = $smtp['ses_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp['ses_user'];
            $mail->Password   = $smtp['ses_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp['ses_port'];

            $mail->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
            $mail->addAddress($correo);
            $mail->addAddress('director.sistemas@panamericanaviajes.com');
            $mail->addAddress('director.negociaciones@panamericanaviajes.com');
            $mail->addAddress('calidad@panamericanaviajes.com');
            $mail->addCC('negociaciones@panamericanaviajes.com');

            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = implode("", $datosHotel);
            $mail->AltBody = 'Ficha de Hotel firmada.';

            $mail->send();

        } catch (Exception $e) {
            error_log("PHPMailer firmado Exception: " . $e->getMessage());
            // No detenemos flujo: ya se guardó y firmó.
        }
    }


     $zohoResult = construirYEnviarZoho($id_hotel, $conn, false, 'editar');
     if (!$zohoResult['ok']) {
         error_log("[actualizarHotel] Zoho webhook error para hotel $id_hotel: " . ($zohoResult['error'] ?? 'desconocido'));
    } else {
        error_log("[actualizarHotel] Zoho webhook OK para hotel $id_hotel");
    }

    header("Location: ../vista/consultaHotel.php?id=$id_hotel&msg=updated");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("[actualizarHotel] Error: " . $e->getMessage());
    die("Error crítico: " . $e->getMessage());
}
