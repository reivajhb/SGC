<?php
include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php";

require_once __DIR__ . '/validar_vigencia_funcion.php';

$ROOT_PATH = dirname(__DIR__, 2);
$VENDOR_AUTOLOAD = $ROOT_PATH . '/google-api-php-client--PHP8.0/vendor/autoload.php';
$GOOGLE_JSON = $ROOT_PATH . '/drive-contabilidad-a46b4f106c64.json';

if (!file_exists($VENDOR_AUTOLOAD)) {
    error_log("[BOOT] No existe autoload de Google: $VENDOR_AUTOLOAD");
}
require_once $VENDOR_AUTOLOAD;

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive as DriveService;

if (!file_exists($GOOGLE_JSON)) {
    error_log("[BOOT] No existe JSON de credenciales: $GOOGLE_JSON");
}
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $GOOGLE_JSON);

ini_set('display_errors', 1);
error_reporting(E_ALL);

$DEBUG = (isset($_GET['debug_docs']) && $_GET['debug_docs'] === '1');
$DOCS_FATAL = false;
$LOCAL_LOG = __DIR__ . '/docs_debug.log';
ini_set('log_errors', 1);
ini_set('error_log', $LOCAL_LOG);

function dlog($msg)
{
    global $LOCAL_LOG;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents($LOCAL_LOG, $line, FILE_APPEND);
    error_log($msg);
}

function redirectFormulario($id_hotel = null)
{
    $url = '../vista/formularioIncripHotel.php';
    if (!empty($id_hotel)) {
        $url .= '?id=' . (int) $id_hotel;
    }
    header('Location: ' . $url);
    exit();
}

if (!isset($conn) || $conn->connect_error) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    die("❌ Error de conexión a la base de datos: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['usuario'] ?? null;
if (!$user_id) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    die("❌ Error: ID de usuario no encontrado en la sesión.");
}

// ============================================================
// OPTIMIZACIÓN PRINCIPAL
// Este archivo ya NO vuelve a guardar toda la ficha.
// guardarBorradorAjax.php ya guardó datos generales, contactos,
// servicios, habitaciones y salones por secciones.
// Aquí solo se finaliza el BORRADOR, se procesan documentos nuevos
// y se reutilizan documentos VALID existentes.
// ============================================================

$id_hotel = (int) ($_POST['id_hotel_borrador'] ?? $_GET['id'] ?? 0);
if ($id_hotel <= 0) {
    $_SESSION['flash_error'] = 'No se recibió el ID del borrador. Guarda nuevamente la ficha antes de finalizar.';
    redirectFormulario();
}

$stmtHotel = $conn->prepare("
    SELECT id_hotel, usuario_creacion, nombre, nit, nit_consecutivo, razon_social, ciudad, pais,
           categoria, numero_habitaciones, forma_conexion, channel_manager_nombre,
           diligencia_nombre, diligencia_correo, diligencia_cargo, estado_registro, estado_firma
    FROM tbl_alojamiento_general
    WHERE id_hotel = ?
      AND usuario_creacion = ?
      AND estado_registro = 'BORRADOR'
    LIMIT 1
");
$stmtHotel->bind_param("is", $id_hotel, $user_id);
$stmtHotel->execute();
$hotelData = $stmtHotel->get_result()->fetch_assoc();
$stmtHotel->close();

if (!$hotelData) {
    $_SESSION['flash_error'] = 'No se encontró el borrador o no pertenece al usuario.';
    redirectFormulario($id_hotel);
}

$nombre = $hotelData['nombre'] ?? '';
$nit = trim((string) ($hotelData['nit'] ?? ''));
$razon_social = $hotelData['razon_social'] ?? '';
$ciudad = $hotelData['ciudad'] ?? '';
$pais = $hotelData['pais'] ?? '';
$categoria = $hotelData['categoria'] ?? '';
$numero_habitaciones = $hotelData['numero_habitaciones'] ?? '';
$forma_conexion = $hotelData['forma_conexion'] ?? '';
$channel_manager_nombre = $hotelData['channel_manager_nombre'] ?? '';
$diligencia_nombre = $hotelData['diligencia_nombre'] ?? '';
$diligencia_correo = $hotelData['diligencia_correo'] ?? '';
$diligencia_cargo = $hotelData['diligencia_cargo'] ?? '';
$ESTADO_REGISTRO_FINAL = 'FINALIZADO';

function pnvArchivoSubidoOk(string $inputName): bool
{
    if (!isset($_FILES[$inputName]) || is_array($_FILES[$inputName]['error'] ?? null)) {
        return false;
    }
    return (int) ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
        && !empty($_FILES[$inputName]['tmp_name']);
}

function pnvCantidadArchivosSubidosOk(string $inputName): int
{
    if (!isset($_FILES[$inputName]) || !is_array($_FILES[$inputName]['error'] ?? null)) {
        return 0;
    }
    $total = 0;
    foreach ($_FILES[$inputName]['error'] as $idx => $error) {
        if ((int) $error === UPLOAD_ERR_OK && !empty($_FILES[$inputName]['tmp_name'][$idx] ?? '')) {
            $total++;
        }
    }
    return $total;
}

function existeDocumentoTipo(mysqli $conn, int $id_hotel, string $tipo_doc_db): bool
{
    $stmt = $conn->prepare("
        SELECT id_doc
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
          AND tipo_documento = ?
          AND ruta_almacenamiento IS NOT NULL
          AND ruta_almacenamiento <> ''
        ORDER BY id_doc DESC
        LIMIT 1
    ");
    $stmt->bind_param("is", $id_hotel, $tipo_doc_db);
    $stmt->execute();
    $stmt->bind_result($id_doc);
    $stmt->fetch();
    $stmt->close();
    return !empty($id_doc);
}

function obtenerUltimoDocumentoValidado(mysqli $conn, int $id_hotel, string $tipo_doc_db): ?array
{
    $stmt = $conn->prepare("
        SELECT id_doc, tipo_documento, nombre_archivo, ruta_almacenamiento,
               fecha_vigencia, fuente_vigencia, estado_vigencia, dias_vencimiento, validacion_ia_json
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ? AND tipo_documento = ?
        ORDER BY id_doc DESC
        LIMIT 1
    ");
    $stmt->bind_param("is", $id_hotel, $tipo_doc_db);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function obtenerDocumentoValidoProveedor(mysqli $conn, int $id_hotel, string $tipo_doc_db, string $nit = ''): ?array
{
    // 1) Primero busca documento VALID en el hotel actual.
    $stmt = $conn->prepare("
        SELECT id_doc, id_hotel, tipo_documento, nombre_archivo, ruta_almacenamiento,
               fecha_vigencia, fuente_vigencia, estado_vigencia, dias_vencimiento, validacion_ia_json
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
          AND tipo_documento = ?
          AND UPPER(COALESCE(estado_vigencia, '')) = 'VALID'
        ORDER BY id_doc DESC
        LIMIT 1
    ");
    $stmt->bind_param("is", $id_hotel, $tipo_doc_db);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $row['_fuente_reuso'] = 'MISMO_HOTEL';
        return $row;
    }

    // 2) Si no existe en el hotel actual, busca documento VALID de otro registro con el mismo NIT.
    // Esto evita pedir de nuevo RUT/RNT/Cámara/Certificación Bancaria cuando el proveedor ya los tenía.
    if ($nit !== '') {
        $stmt = $conn->prepare("
            SELECT d.id_doc, d.id_hotel, d.tipo_documento, d.nombre_archivo, d.ruta_almacenamiento,
                   d.fecha_vigencia, d.fuente_vigencia, d.estado_vigencia, d.dias_vencimiento, d.validacion_ia_json
            FROM tbl_alojamiento_documentos d
            INNER JOIN tbl_alojamiento_general g ON g.id_hotel = d.id_hotel
            WHERE g.nit = ?
              AND d.tipo_documento = ?
              AND UPPER(COALESCE(d.estado_vigencia, '')) = 'VALID'
              AND d.ruta_almacenamiento IS NOT NULL
              AND d.ruta_almacenamiento <> ''
            ORDER BY d.id_doc DESC
            LIMIT 1
        ");
        $stmt->bind_param("ss", $nit, $tipo_doc_db);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $row['_fuente_reuso'] = 'MISMO_NIT';
            return $row;
        }
    }

    return null;
}

function asegurarDocumentoValidoEnHotelActual(mysqli $conn, int $id_hotel, string $tipo_doc_db, string $nit): ?array
{
    $doc = obtenerDocumentoValidoProveedor($conn, $id_hotel, $tipo_doc_db, $nit);
    if (!$doc) {
        return null;
    }

    if ((int) ($doc['id_hotel'] ?? 0) === $id_hotel) {
        return $doc;
    }

    // Copia la referencia del documento válido anterior al hotel actual.
    // No sube de nuevo a Drive ni llama IA; solo reutiliza la URL y metadatos ya validados.
    $stmt = $conn->prepare("
        INSERT INTO tbl_alojamiento_documentos
            (id_hotel, tipo_documento, nombre_archivo, ruta_almacenamiento,
             fecha_vigencia, fuente_vigencia, estado_vigencia, dias_vencimiento, validacion_ia_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $tipo = $doc['tipo_documento'] ?? $tipo_doc_db;
    $nombre_archivo = $doc['nombre_archivo'] ?? null;
    $ruta = $doc['ruta_almacenamiento'] ?? null;
    $fecha = $doc['fecha_vigencia'] ?? null;
    $fuente = $doc['fuente_vigencia'] ?? 'REUTILIZADO_MISMO_NIT';
    $estado = $doc['estado_vigencia'] ?? 'VALID';
    $dias = is_numeric($doc['dias_vencimiento'] ?? null) ? (int) $doc['dias_vencimiento'] : null;
    $json = $doc['validacion_ia_json'] ?? null;

    $stmt->bind_param(
        "issssssis",
        $id_hotel,
        $tipo,
        $nombre_archivo,
        $ruta,
        $fecha,
        $fuente,
        $estado,
        $dias,
        $json
    );
    $stmt->execute();
    $stmt->close();

    dlog("DOCUMENTO REUTILIZADO -> {$tipo_doc_db} copiado al hotel {$id_hotel} desde hotel " . (int) $doc['id_hotel']);

    return obtenerDocumentoValidoProveedor($conn, $id_hotel, $tipo_doc_db, $nit);
}

// Fotos obligatorias: se aceptan si llegan nuevas o si ya existen en la base de datos.
$fotosObligatorias = [
    'foto_fachada' => ['label' => 'foto de fachada', 'tipo' => 'Foto Fachada'],
    'foto_habitaciones' => ['label' => 'foto de habitaciones', 'tipo' => 'Foto Habitaciones'],
    'foto_piscina' => ['label' => 'foto de piscina o zona recreativa', 'tipo' => 'Foto Piscina'],
    'foto_zona_comun' => ['label' => 'foto de zona comun', 'tipo' => 'Foto Zona Comun']
];

$fotosFaltantes = [];
foreach ($fotosObligatorias as $inputFoto => $infoFoto) {
    if (!pnvArchivoSubidoOk($inputFoto) && !existeDocumentoTipo($conn, $id_hotel, $infoFoto['tipo'])) {
        $fotosFaltantes[] = $infoFoto['label'];
    }
}

if (pnvCantidadArchivosSubidosOk('fotos_hotel') < 1 && !existeDocumentoTipo($conn, $id_hotel, 'Foto Promocional')) {
    $fotosFaltantes[] = 'fotos adicionales de la propiedad';
}

if (!empty($fotosFaltantes)) {
    $_SESSION['flash_error'] = 'Debe subir las fotos obligatorias antes de enviar la ficha: ' . implode(', ', $fotosFaltantes) . '.';
    redirectFormulario($id_hotel);
}

$__LOGS = [];
$log = function ($msg) use (&$__LOGS) {
    $line = '[DOCS] ' . (is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE));
    $__LOGS[] = $line;
    error_log($line);
    dlog($line);
};

$UPLOAD_ERR_MAP = [
    UPLOAD_ERR_OK => 'OK',
    UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE',
    UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
    UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL',
    UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE',
    UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
    UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
    UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION',
];

$folderIdDrive = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$credJson = @json_decode(@file_get_contents($GOOGLE_JSON), true);

$conn->begin_transaction();
$transaccion_iniciada = true;

try {
    dlog("================================================");
    dlog("INICIO FINALIZACION OPTIMIZADA");
    dlog("Hotel ID: " . $id_hotel);
    dlog("Usuario: " . ($user_id ?? 'NULL'));
    dlog("FILES RECIBIDOS: " . json_encode(array_keys($_FILES)));
    dlog("================================================");

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    $service = new DriveService($client);
    $log("Cliente Drive inicializado.");

    $uploadFileToDrive = function (DriveService $service, array $fileDetails, string $folderId) use ($log, $UPLOAD_ERR_MAP) {
        $code = $fileDetails['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($code !== UPLOAD_ERR_OK) {
            $log("Upload abortado: {$code} - " . ($UPLOAD_ERR_MAP[$code] ?? 'DESCONOCIDO'));
            return [null, "Upload:$code"];
        }

        $tmp = $fileDetails['tmp_name'];
        if (!is_readable($tmp)) {
            $log("tmp_name no legible: {$tmp}");
            return [null, "TmpNotReadable"];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        try {
            $df = new DriveFile();
            $df->setName($fileDetails['name']);
            $df->setParents([$folderId]);
            $df->setMimeType($mime);

            $res = $service->files->create($df, [
                'data' => file_get_contents($tmp),
                'mimeType' => $mime,
                'uploadType' => 'media'
            ]);

            $log("Subida Drive OK -> id={$res->id} name={$fileDetails['name']} mime={$mime}");
            return [$res, null, $mime];
        } catch (\Exception $e) {
            $log("Error subiendo a Drive: " . $e->getMessage());
            return [null, $e->getMessage(), null];
        }
    };

    $uploadedFiles_map = [
        'rut' => 'RUT',
        'registro_turismo' => 'RNT',
        'camara_comercio' => 'Camara de Comercio',
        'certificacion_bancaria' => 'Certificacion Bancaria',
        'certificados_sostenibilidad' => 'Sostenibilidad',
        'certificado_bomberos' => 'Bomberos',
        'informacion_credito' => 'Credito Actual',
        'concepto_sanitario' => 'Concepto Sanitario',
        'mantenimiento_piscinas' => 'Mantenimiento Piscinas',
        'mantenimiento_ascensores' => 'Mantenimiento Ascensores',
        'sg_sst' => 'SG-SST',
        'arl' => 'ARL',
        'fotos_hotel' => 'Foto Promocional',
        'foto_fachada' => 'Foto Fachada',
        'foto_habitaciones' => 'Foto Habitaciones',
        'foto_piscina' => 'Foto Piscina',
        'foto_zona_comun' => 'Foto Zona Comun',
    ];

    $documentosValidables = [
        'rut',
        'registro_turismo',
        'camara_comercio',
        'certificacion_bancaria'
    ];

    if (!empty($_POST['firma_dibujada_data']) && $_POST['firma_dibujada_data'] !== 'FILE_UPLOADED') {
        $base64_img = explode(',', $_POST['firma_dibujada_data']);
        $img_data = base64_decode(end($base64_img));
        $temp_file_name = "firma_hotel_{$nit}_" . time() . ".png";
        $temp_file_path = sys_get_temp_dir() . '/' . $temp_file_name;

        if (file_put_contents($temp_file_path, $img_data)) {
            $_FILES['firma_dibujada'] = [
                'name' => $temp_file_name,
                'type' => 'image/png',
                'tmp_name' => $temp_file_path,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($temp_file_path)
            ];
            $uploadedFiles_map['firma_dibujada'] = 'Firma Digital';
            $log("Firma dibujada convertida: {$temp_file_path}");
        }
    } elseif (!empty($_FILES['firma_imagen_file']['name'])) {
        $_FILES['firma_dibujada'] = $_FILES['firma_imagen_file'];
        $uploadedFiles_map['firma_dibujada'] = 'Firma Digital';
    }

    $sql_doc = "INSERT INTO tbl_alojamiento_documentos
        (id_hotel, tipo_documento, nombre_archivo, ruta_almacenamiento, fecha_vigencia,
         fuente_vigencia, estado_vigencia, dias_vencimiento, validacion_ia_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_doc = $conn->prepare($sql_doc);

    $total_intentos = 0;
    $total_subidos = 0;
    $total_insertados = 0;
    $total_presentes = 0;
    $totalEnviadosIA = 0;
    $inputs_vistos = [];

    foreach ($uploadedFiles_map as $input_name => $tipo_doc_db) {
        if (!isset($_FILES[$input_name])) {
            if (in_array($input_name, $documentosValidables, true)) {
                $docPrevioValido = asegurarDocumentoValidoEnHotelActual($conn, $id_hotel, $tipo_doc_db, $nit);
                if ($docPrevioValido) {
                    $log("SKIP DOCUMENTO -> {$tipo_doc_db} ya existe VALID. No se pide nuevamente.");
                    continue;
                }
            } elseif (existeDocumentoTipo($conn, $id_hotel, $tipo_doc_db)) {
                $log("SKIP DOCUMENTO -> {$tipo_doc_db} ya existe en BD. No se pide nuevamente.");
                continue;
            }

            $log("NO HAY ARCHIVO NUEVO -> {$tipo_doc_db}");
            continue;
        }

        $total_presentes++;
        $inputs_vistos[] = $input_name;

        $fd = $_FILES[$input_name];
        $names = is_array($fd['name']) ? $fd['name'] : [$fd['name']];
        $tmps = is_array($fd['tmp_name']) ? $fd['tmp_name'] : [$fd['tmp_name']];
        $errs = is_array($fd['error']) ? $fd['error'] : [$fd['error']];
        $sizes = is_array($fd['size'] ?? null) ? $fd['size'] : [($fd['size'] ?? null)];

        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $total_intentos++;
            $ename = $names[$i];
            $etmp = $tmps[$i];
            $eerr = $errs[$i];
            $esz = $sizes[$i];

            $log("Detectado -> {$input_name}[{$i}] name={$ename} size={$esz} err={$eerr} tmp={$etmp}");

            [$res, $err, $mimeDetectado] = $uploadFileToDrive($service, [
                'name' => $ename,
                'tmp_name' => $etmp,
                'error' => $eerr
            ], $folderIdDrive);

            if (!$res) {
                $log("No subió a Drive -> {$input_name}[{$i}] motivo={$err}");
                continue;
            }

            $total_subidos++;
            $drive_id = $res->id;
            $drive_url = "https://drive.google.com/open?id={$drive_id}";
            $nombre_db = $ename ?: 'SIN_NOMBRE';

            $fecha_vigencia = null;
            $fuente_vigencia = null;
            $estado_vigencia = null;
            $dias_vencimiento = null;
            $validacion_ia_json = null;

            if (in_array($input_name, $documentosValidables, true)) {
                // Nuevo archivo reemplaza el documento validable anterior de ese tipo.
                $stmtDelDoc = $conn->prepare("
                    DELETE FROM tbl_alojamiento_documentos
                    WHERE id_hotel = ? AND tipo_documento = ?
                ");
                $stmtDelDoc->bind_param("is", $id_hotel, $tipo_doc_db);
                $stmtDelDoc->execute();
                $stmtDelDoc->close();

                $totalEnviadosIA++;
                $log("ENVIANDO DOCUMENTO A IA #{$totalEnviadosIA} -> {$tipo_doc_db} / {$ename}");

                try {
                    $mime = $mimeDetectado ?: mime_content_type($etmp);
                    $ia = validarVigenciaOpenAI($etmp, $ename, $mime);

                    if ($input_name === 'certificacion_bancaria') {
                        $fechaCert = $ia['issue_date'] ?? $ia['best_validity_date'] ?? '';
                        if ($fechaCert) {
                            $diasTrans = (int) floor((time() - strtotime($fechaCert)) / 86400);
                            $diasRest = 365 - $diasTrans;
                            if ($diasTrans > 365) {
                                $ia['status'] = 'EXPIRED';
                                $ia['days_until_expiration'] = '';
                            } else {
                                $ia['status'] = 'VALID';
                                $ia['days_until_expiration'] = (string) $diasRest;
                            }
                            $log("Certificacion Bancaria recalculada -> diasTrans={$diasTrans} estado={$ia['status']}");
                        } else {
                            $ia['status'] = 'UNKNOWN';
                            $log("Certificacion Bancaria sin fecha -> UNKNOWN");
                        }
                    }

                    $fecha_vigencia = !empty($ia['best_validity_date']) ? $ia['best_validity_date'] : null;
                    $fuente_vigencia = $ia['validity_source'] ?? null;
                    $estado_vigencia = $ia['status'] ?? null;
                    $dias_vencimiento = is_numeric($ia['days_until_expiration'] ?? null)
                        ? (int) $ia['days_until_expiration']
                        : null;
                    $validacion_ia_json = json_encode($ia, JSON_UNESCAPED_UNICODE);

                    $log("RESPUESTA IA {$tipo_doc_db}: " . json_encode($ia, JSON_UNESCAPED_UNICODE));
                } catch (Exception $e) {
                    $log("ERROR IA {$tipo_doc_db}: " . $e->getMessage());
                }
            } else {
                $log("NO IA -> {$tipo_doc_db} no está en documentosValidables");
            }

            $stmt_doc->bind_param(
                "issssssis",
                $id_hotel,
                $tipo_doc_db,
                $nombre_db,
                $drive_url,
                $fecha_vigencia,
                $fuente_vigencia,
                $estado_vigencia,
                $dias_vencimiento,
                $validacion_ia_json
            );
            $stmt_doc->execute();
            $total_insertados++;
            $log("Documento guardado -> {$tipo_doc_db} | Estado={$estado_vigencia} | Vigencia={$fecha_vigencia}");
        }
    }

    $stmt_doc->close();

    $_SESSION['docs_debug_last'] = [
        'inputs_presentes' => $total_presentes,
        'intentos' => $total_intentos,
        'subidos' => $total_subidos,
        'insertados' => $total_insertados,
        'enviados_ia' => $totalEnviadosIA,
        'inputs_vistos' => $inputs_vistos,
        'ts' => date('Y-m-d H:i:s'),
    ];

    $log("RESUMEN DOCUMENTOS: presentes={$total_presentes}, intentos={$total_intentos}, subidos={$total_subidos}, insertados={$total_insertados}, enviados_ia={$totalEnviadosIA}");

    // Validación final: acepta documentos VALID actuales o reutilizados por mismo NIT.
    $documentosObligatorios = ['RUT', 'RNT', 'Camara de Comercio', 'Certificacion Bancaria'];
    $rechazados = [];

    foreach ($documentosObligatorios as $tipoObligatorio) {
        $docValido = asegurarDocumentoValidoEnHotelActual($conn, $id_hotel, $tipoObligatorio, $nit);
        dlog("VALIDACION FINAL -> {$tipoObligatorio} | Estado: " . ($docValido ? 'VALID' : 'NO_VALID'));
        if (!$docValido) {
            $rechazados[] = $tipoObligatorio;
        }
    }

    if (!empty($rechazados)) {
        $conn->rollback();
        $transaccion_iniciada = false;
        $_SESSION['flash_error'] = "Debes volver a subir estos documentos rechazados o vencidos: " . implode(', ', $rechazados);
        redirectFormulario($id_hotel);
    }

    $stmtFin = $conn->prepare("
        UPDATE tbl_alojamiento_general
        SET estado_registro = 'FINALIZADO',
            estado_firma = 'PENDIENTE',
            updated_at = NOW()
        WHERE id_hotel = ?
          AND usuario_creacion = ?
          AND estado_registro = 'BORRADOR'
        LIMIT 1
    ");
    $stmtFin->bind_param("is", $id_hotel, $user_id);
    $stmtFin->execute();
    $stmtFin->close();

    $conn->commit();
    $transaccion_iniciada = false;

    if ($DEBUG) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>Reporte de Debug - Documentos</h2>";
        echo "<p><strong>Hotel ID:</strong> " . htmlspecialchars((string) $id_hotel) . "</p>";
        echo "<p><strong>Documentos enviados a IA:</strong> " . intval($totalEnviadosIA) . "</p>";
        echo "<p><strong>Service Account:</strong> " . htmlspecialchars($credJson['client_email'] ?? 'NO LEÍDO') . "</p>";
        echo "<h3>Resumen</h3><ul>";
        echo "<li>Inputs presentes: <strong>" . htmlspecialchars(implode(', ', $inputs_vistos)) . "</strong></li>";
        echo "<li>Intentos: <strong>" . intval($total_intentos) . "</strong></li>";
        echo "<li>Subidos Drive: <strong>" . intval($total_subidos) . "</strong></li>";
        echo "<li>Insertados DB: <strong>" . intval($total_insertados) . "</strong></li>";
        echo "<li>Enviados IA: <strong>" . intval($totalEnviadosIA) . "</strong></li>";
        echo "</ul>";
        echo "<h3>Claves reales en \$_FILES</h3>";
        echo "<pre>" . htmlspecialchars(print_r(array_keys($_FILES), true)) . "</pre>";
        echo "<h3>Logs</h3><pre>";
        foreach ($__LOGS as $l) {
            echo htmlspecialchars($l) . "\n";
        }
        echo "</pre>";
        exit;
    }
} catch (\Throwable $e) {
    if (!empty($transaccion_iniciada)) {
        $conn->rollback();
        $transaccion_iniciada = false;
    }
    $DOCS_FATAL = true;
    dlog("EXCEPCION FINALIZACION: " . $e->getMessage());

    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Error: " . $e->getMessage();
        exit();
    }

    $_SESSION['flash_error'] = 'Se presentó un error al finalizar la ficha. Revisa el log o contacta a sistemas.';
    redirectFormulario($id_hotel);
}

// ==================== CORREO ====================
try {
    $documentosCorreo = [];
    $stmt_docs_mail = $conn->prepare("
        SELECT tipo_documento, nombre_archivo, ruta_almacenamiento, fecha_vigencia, estado_vigencia, dias_vencimiento
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
        ORDER BY tipo_documento ASC, id_doc DESC
    ");
    $stmt_docs_mail->bind_param("i", $id_hotel);
    $stmt_docs_mail->execute();
    $res_docs_mail = $stmt_docs_mail->get_result();
    while ($doc_mail = $res_docs_mail->fetch_assoc()) {
        $documentosCorreo[] = $doc_mail;
    }
    $stmt_docs_mail->close();

    $filasDocumentos = '';
    if (!empty($documentosCorreo)) {
        foreach ($documentosCorreo as $docCorreo) {
            $tipoDocCorreo = htmlspecialchars((string) ($docCorreo['tipo_documento'] ?? ''), ENT_QUOTES, 'UTF-8');
            $nombreDocCorreo = htmlspecialchars((string) ($docCorreo['nombre_archivo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $estadoDocCorreo = htmlspecialchars((string) ($docCorreo['estado_vigencia'] ?? 'NO VALIDADO'), ENT_QUOTES, 'UTF-8');
            $fechaDocCorreo = htmlspecialchars((string) ($docCorreo['fecha_vigencia'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
            $diasDocCorreo = htmlspecialchars((string) ($docCorreo['dias_vencimiento'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
            $urlDocCorreo = htmlspecialchars((string) ($docCorreo['ruta_almacenamiento'] ?? '#'), ENT_QUOTES, 'UTF-8');

            $filasDocumentos .= '<tr>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;">' . $tipoDocCorreo . '</td>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;">' . $nombreDocCorreo . '</td>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;">' . $estadoDocCorreo . '</td>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;">' . $fechaDocCorreo . '</td>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;">' . $diasDocCorreo . '</td>';
            $filasDocumentos .= '<td style="padding:8px;border:1px solid #ddd;"><a href="' . $urlDocCorreo . '">Ver documento</a></td>';
            $filasDocumentos .= '</tr>';
        }
    } else {
        $filasDocumentos = '<tr><td colspan="6" style="padding:8px;border:1px solid #ddd;">No se encontraron documentos asociados.</td></tr>';
    }

    $mail = new PHPMailer(true);
    $smtp = require __DIR__ . '/../../aws.php';
    $subject = "FICHA DE INSCRIPCIÓN: Nuevo Proveedor de Alojamiento - PENDIENTE DE FIRMA - " . $nombre;
    $linkConsulta = "https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=" . (int) $id_hotel;

    $datosHotel = [
        'Cordial saludo,',
        '<br><br>',
        'Se ha registrado una nueva Ficha de Inscripción de Proveedor de Alojamiento y queda pendiente de firma por parte del proveedor.',
        '<h3>Detalles del Hotel: ' . htmlspecialchars((string) $nombre, ENT_QUOTES, 'UTF-8') . '</h3>',
        '<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;">',
        '<tr><th colspan="2" style="background-color:#0d6efd;color:white;padding:10px;text-align:left;">Resumen de Registro</th></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;width:30%;"><strong>ID Hotel</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $id_hotel, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>NIT</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $nit, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Razón Social</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $razon_social, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Ciudad/País</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $ciudad, ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars((string) $pais, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Categoría</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $categoria, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Total Habitaciones</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $numero_habitaciones, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Forma de Conexión</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $forma_conexion, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Channel Manager</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) ($channel_manager_nombre ?: 'No aplica'), ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Estado Registro</strong></td><td style="padding:8px;border:1px solid #ddd;">FINALIZADO</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Estado Firma</strong></td><td style="padding:8px;border:1px solid #ddd;">PENDIENTE DE FIRMA</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Siguiente Estado</strong></td><td style="padding:8px;border:1px solid #ddd;">Luego de firmar, quedará PENDIENTE DE APROBACIÓN</td></tr>',
        '<tr><th colspan="2" style="background-color:#f8f9fa;color:#333;padding:10px;text-align:left;border:1px solid #ddd;">Datos de quien diligencia</th></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Nombre</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) $diligencia_nombre, ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Correo</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) ($diligencia_correo ?: 'No proporcionado'), ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Cargo</strong></td><td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars((string) ($diligencia_cargo ?: 'No proporcionado'), ENT_QUOTES, 'UTF-8') . '</td></tr>',
        '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Link de Consulta</strong></td><td style="padding:8px;border:1px solid #ddd;"><a href="' . htmlspecialchars($linkConsulta, ENT_QUOTES, 'UTF-8') . '">Ver Ficha Completa</a></td></tr>',
        '</table><br>',
        '<h3>Documentos cargados y validación</h3>',
        '<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;">',
        '<tr style="background-color:#f8f9fa;color:#333;">',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Tipo</th>',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Archivo</th>',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Estado IA</th>',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Fecha Vigencia</th>',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Días Vencimiento</th>',
        '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Documento</th>',
        '</tr>',
        $filasDocumentos,
        '</table>',
        '<br>⚠️ <strong>Estado actual:</strong> PENDIENTE DE FIRMA.<br>',
        '<br>Después de que el proveedor firme, el registro debe quedar <strong>PENDIENTE DE APROBACIÓN</strong> para revisión interna.<br>',
        '<p>Gracias,</p>',
        '<p>Atentamente,<br><strong>Equipo Panamericana de Viajes</strong></p>'
    ];

    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
    $mail->addAddress('negociaciones@panamericanaviajes.com');
    $mail->addAddress('director.sistemas@panamericanaviajes.com');
    $mail->addAddress('director.negociaciones@panamericanaviajes.com');
    $mail->addAddress('calidad@panamericanaviajes.com');
    $mail->addCC('negociaciones@panamericanaviajes.com');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = implode('', $datosHotel);
    $mail->AltBody = "Nueva ficha de hotel registrada. ID Hotel: " . (int) $id_hotel . " | Hotel: " . $nombre . " | Estado actual: PENDIENTE DE FIRMA.";
    $mail->send();
    dlog("PHPMailer OK: correo enviado para Hotel ID " . (int) $id_hotel);
} catch (\Throwable $e) {
    dlog("PHPMailer Exception: " . $e->getMessage());
}

if (!$DEBUG && !$DOCS_FATAL) {
    header("Location: ../vista/consultaHotel.php?id=" . (int) $id_hotel);
    exit();
}

if (!$DEBUG && $DOCS_FATAL) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Se presentó un error al procesar los documentos. Revisa el log en " . $LOCAL_LOG;
    exit();
}