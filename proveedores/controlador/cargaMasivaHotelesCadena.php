<?php
include_once '../seguridad_proveedores.php';
include_once '../../facturacion/config/conexion.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idRol = (int) ($_SESSION['id_rol'] ?? 0);
$usuarioSesion = $_SESSION['usuario'] ?? null;
$rolesAdminMasivo = [1, 2, 8, 9];
$esRolCadena = $idRol === 7;
$esAdminMasivo = in_array($idRol, $rolesAdminMasivo, true);

if ((!$esRolCadena && !$esAdminMasivo) || !$usuarioSesion) {
    http_response_code(403);
    exit('Acceso denegado.');
}

function cmhRedirectTo(string $returnTo): void
{
    if ($returnTo === 'consulta_fichas') {
        header('Location: /facturacion/facturacion/project/contabilidad/proveedores/consultaFichaProveedores.php?bulk=1');
        exit;
    }

    header('Location: ../vista/listadoHotelesCadena.php?bulk=1');
    exit;
}

function cmhColExists(mysqli $conn, string $table, string $col): bool
{
    $dbRes = $conn->query("SELECT DATABASE() AS db");
    $dbRow = $dbRes ? $dbRes->fetch_assoc() : null;
    $db = $dbRow['db'] ?? '';
    if ($db === '') {
        return false;
    }

    $stmt = $conn->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1");
    $stmt->bind_param("sss", $db, $table, $col);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = ($res && $res->num_rows > 0);
    $stmt->close();

    return $ok;
}

function cmhCell(?Worksheet $sheet, string $coord): string
{
    if (!$sheet) {
        return '';
    }

    $cell = $sheet->getCell($coord);
    $value = $cell->getCalculatedValue();

    if ($value instanceof DateTimeInterface) {
        return $value->format('H:i');
    }

    if (is_numeric($value) && $value > 0 && $value < 1) {
        try {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('H:i');
        } catch (Throwable $e) {
            return trim((string) $value);
        }
    }

    return trim(preg_replace('/\s+/', ' ', (string) $value));
}

function cmhInt($value): int
{
    if (is_numeric($value)) {
        return (int) $value;
    }

    if (preg_match('/\d+/', (string) $value, $m)) {
        return (int) $m[0];
    }

    return 0;
}

function cmhFloat($value): float
{
    if (is_numeric($value)) {
        return (float) $value;
    }

    $txt = str_replace(',', '.', (string) $value);
    if (preg_match('/\d+(?:\.\d+)?/', $txt, $m)) {
        return (float) $m[0];
    }

    return 0.0;
}

function cmhNormalizeNit(string $nit): string
{
    $nit = preg_replace('/[^0-9]/', '', trim($nit));

    if (strlen($nit) > 1) {
        $nit = substr($nit, 0, -1);
    }

    return $nit;
}

function cmhNitConsecutivoEnUso(mysqli $conn, string $nitConsecutivo, ?int $ignorarId = null): bool
{
    $nitConsecutivo = trim($nitConsecutivo);
    if ($nitConsecutivo === '') {
        return false;
    }

    if ($ignorarId) {
        $stmt = $conn->prepare("SELECT id_hotel FROM tbl_alojamiento_general WHERE nit_consecutivo=? AND id_hotel<>? LIMIT 1");
        $stmt->bind_param("si", $nitConsecutivo, $ignorarId);
    } else {
        $stmt = $conn->prepare("SELECT id_hotel FROM tbl_alojamiento_general WHERE nit_consecutivo=? LIMIT 1");
        $stmt->bind_param("s", $nitConsecutivo);
    }

    $stmt->execute();
    $stmt->bind_result($idHotelEncontrado);
    $stmt->fetch();
    $stmt->close();

    return !empty($idHotelEncontrado);
}

function cmhSiguienteNitConsecutivo(mysqli $conn, string $nitBase, ?int $ignorarId = null): string
{
    $nitBase = trim($nitBase);
    if ($nitBase === '') {
        return '';
    }

    if ($ignorarId) {
        $stmt = $conn->prepare("
            SELECT nit_consecutivo
            FROM tbl_alojamiento_general
            WHERE nit = ?
              AND id_hotel <> ?
              AND nit_consecutivo IS NOT NULL
              AND nit_consecutivo <> ''
        ");
        $stmt->bind_param("si", $nitBase, $ignorarId);
    } else {
        $stmt = $conn->prepare("
            SELECT nit_consecutivo
            FROM tbl_alojamiento_general
            WHERE nit = ?
              AND nit_consecutivo IS NOT NULL
              AND nit_consecutivo <> ''
        ");
        $stmt->bind_param("s", $nitBase);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $usados = [];

    while ($row = $res->fetch_assoc()) {
        $nc = (string) ($row['nit_consecutivo'] ?? '');
        if (strpos($nc, $nitBase) !== 0) {
            continue;
        }

        $suffix = strtoupper(preg_replace('/[^A-Z]/', '', substr($nc, strlen($nitBase))));
        if ($suffix !== '') {
            $usados[$suffix] = true;
        }
    }
    $stmt->close();

    $candidatos = [];
    for ($i = 0; $i < 26; $i++) {
        $candidatos[] = chr(ord('A') + $i);
    }
    for ($i = 0; $i < 26; $i++) {
        for ($j = 0; $j < 26; $j++) {
            $candidatos[] = chr(ord('A') + $i) . chr(ord('A') + $j);
        }
    }

    foreach ($candidatos as $suffix) {
        if (!isset($usados[$suffix])) {
            return $nitBase . $suffix;
        }
    }

    return $nitBase . 'ZZ';
}

function cmhGetUsuarioId(mysqli $conn, string $usuario): ?int
{
    $id = null;
    $stmt = $conn->prepare("SELECT id_usuario FROM tbl_usuarios WHERE usuario=? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    return $id ? (int) $id : null;
}

function cmhCrearUsuarioHotelSiNoExiste(mysqli $conn, string $nit, string $nombre, string $telefono = '', string $direccion = ''): ?int
{
    $nit = trim($nit);

    if ($nit === '') {
        return null;
    }

    $idUsuario = null;

    $stmt = $conn->prepare("SELECT id_usuario FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $nit);
    $stmt->execute();
    $stmt->bind_result($idUsuario);
    $stmt->fetch();
    $stmt->close();

    if ($idUsuario) {
        return (int) $idUsuario;
    }

    $usuario = $nit;
    $contrasena = password_hash($nit, PASSWORD_BCRYPT);
    $idRolProveedorHotel = 6;
    $estado = 1;

    $stmt = $conn->prepare("
        INSERT INTO tbl_usuarios
        (
            usuario,
            contraseña,
            nombre,
            telefono,
            direccion,
            id_rol,
            estado
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssii",
        $usuario,
        $contrasena,
        $nombre,
        $telefono,
        $direccion,
        $idRolProveedorHotel,
        $estado
    );

    $stmt->execute();
    $nuevoId = (int) $conn->insert_id;
    $stmt->close();

    return $nuevoId;
}

function cmhGetCadenaById(mysqli $conn, int $idCadena): ?array
{
    $stmt = $conn->prepare("SELECT id_usuario, usuario, nombre FROM tbl_usuarios WHERE id_usuario=? AND id_rol=7 LIMIT 1");
    $stmt->bind_param("i", $idCadena);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function cmhFindHotel(mysqli $conn, ?string $usuarioDestino, ?int $idUsuarioCreacion, string $nit, string $nombre, string $razonSocial, bool $independiente): ?int
{
    $id = null;

    if ($independiente) {
        if (cmhColExists($conn, 'tbl_alojamiento_general', 'id_usuario_creacion')) {
            $stmt = $conn->prepare("
                SELECT id_hotel
                FROM tbl_alojamiento_general
                WHERE nit = ?
                  AND (nombre = ? OR razon_social = ? OR razon_social = ?)
                  AND (id_usuario_creacion IS NULL OR id_usuario_creacion = 0)
                  AND (usuario_creacion IS NULL OR usuario_creacion = '')
                ORDER BY id_hotel DESC
                LIMIT 1
            ");
            $stmt->bind_param("ssss", $nit, $nombre, $nombre, $razonSocial);
        } else {
            $usuarioVacio = '';
            $stmt = $conn->prepare("
                SELECT id_hotel
                FROM tbl_alojamiento_general
                WHERE usuario_creacion = ?
                  AND nit = ?
                  AND (nombre = ? OR razon_social = ? OR razon_social = ?)
                ORDER BY id_hotel DESC
                LIMIT 1
            ");
            $stmt->bind_param("sssss", $usuarioVacio, $nit, $nombre, $nombre, $razonSocial);
        }
    } elseif ($idUsuarioCreacion !== null && cmhColExists($conn, 'tbl_alojamiento_general', 'id_usuario_creacion')) {
        $usuarioDestino = (string) $usuarioDestino;
        $stmt = $conn->prepare("
            SELECT id_hotel
            FROM tbl_alojamiento_general
            WHERE (usuario_creacion = ? OR id_usuario_creacion = ?)
              AND nit = ?
              AND (nombre = ? OR razon_social = ? OR razon_social = ?)
            ORDER BY id_hotel DESC
            LIMIT 1
        ");
        $stmt->bind_param("sissss", $usuarioDestino, $idUsuarioCreacion, $nit, $nombre, $nombre, $razonSocial);
    } else {
        $usuarioDestino = (string) $usuarioDestino;
        $stmt = $conn->prepare("
            SELECT id_hotel
            FROM tbl_alojamiento_general
            WHERE usuario_creacion = ?
              AND nit = ?
              AND (nombre = ? OR razon_social = ? OR razon_social = ?)
            ORDER BY id_hotel DESC
            LIMIT 1
        ");
        $stmt->bind_param("sssss", $usuarioDestino, $nit, $nombre, $nombre, $razonSocial);
    }

    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    return $id ? (int) $id : null;
}

function cmhInsertMinimalHotel(mysqli $conn, ?string $usuarioDestino, ?int $idUsuarioCreacion, array $data, string $estadoAprobacion): int
{
    $cols = ['usuario_creacion', 'nombre', 'nit', 'estado_registro', 'wizard_step', 'updated_at'];
    $placeholders = ['?', '?', '?', "'FINALIZADO'", '6', 'NOW()'];
    $types = 'sss';
    $vals = [$usuarioDestino ?? '', $data['nombre'], $data['nit']];

    if ($idUsuarioCreacion !== null && cmhColExists($conn, 'tbl_alojamiento_general', 'id_usuario_creacion')) {
        array_splice($cols, 1, 0, 'id_usuario_creacion');
        array_splice($placeholders, 1, 0, '?');
        $types = 'siss';
        array_splice($vals, 1, 0, $idUsuarioCreacion);
    }

    if (($data['nit_consecutivo'] ?? '') !== '' && cmhColExists($conn, 'tbl_alojamiento_general', 'nit_consecutivo')) {
        $cols[] = 'nit_consecutivo';
        $placeholders[] = '?';
        $types .= 's';
        $vals[] = $data['nit_consecutivo'];
    }

    if (cmhColExists($conn, 'tbl_alojamiento_general', 'estado_firma')) {
        $cols[] = 'estado_firma';
        $placeholders[] = "'PENDIENTE'";
    }

    if (cmhColExists($conn, 'tbl_alojamiento_general', 'estado_aprobacion')) {
        $cols[] = 'estado_aprobacion';
        $placeholders[] = '?';
        $types .= 's';
        $vals[] = $estadoAprobacion;
    }

    $sql = 'INSERT INTO tbl_alojamiento_general (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();
    $idHotel = (int) $conn->insert_id;
    $stmt->close();

    return $idHotel;
}

function cmhUpdateGeneral(mysqli $conn, int $idHotel, ?string $usuarioDestino, ?int $idUsuarioCreacion, array $data, string $estadoAprobacion): void
{
    $map = [
        'usuario_creacion' => $usuarioDestino ?? '',
        'id_usuario_creacion' => $idUsuarioCreacion,
        'cadena_hotelera' => $data['cadena_hotelera'],
        'nombre' => $data['nombre'],
        'nit' => $data['nit'],
        'nit_consecutivo' => $data['nit_consecutivo'],
        'razon_social' => $data['razon_social'],
        'telefono' => $data['telefono'],
        'direccion' => $data['direccion'],
        'ciudad' => $data['ciudad'],
        'pais' => $data['pais'],
        'website' => $data['website'],
        'categoria' => $data['categoria'],
        'descripcion_producto' => $data['descripcion_producto'],
        'numero_habitaciones' => $data['numero_habitaciones'],
        'incluye_desayuno' => $data['incluye_desayuno'],
        'precio_desayuno' => $data['precio_desayuno'],
        'tipo_desayuno' => $data['tipo_desayuno'],
        'habitaciones_discapacidad' => $data['habitaciones_discapacidad'],
        'habitaciones_connecting' => $data['habitaciones_connecting'],
        'accesibilidad_banos' => $data['accesibilidad_banos'],
        'accesibilidad_habitaciones' => $data['accesibilidad_habitaciones'],
        'accesibilidad_espacios_comunes' => $data['accesibilidad_espacios_comunes'],
        'informacion_adicional' => $data['informacion_adicional'],
        'tipo_hotel_json' => $data['tipo_hotel_json'],
        'hora_check_in' => $data['hora_check_in'],
        'hora_check_out' => $data['hora_check_out'],
        'es_pet_friendly' => $data['es_pet_friendly'],
        'politica_mascotas' => $data['politica_mascotas'],
        'amenidad_restaurante' => $data['amenidad_restaurante'],
        'amenidad_bar_lounge' => $data['amenidad_bar_lounge'],
        'amenidad_hab_especiales' => $data['amenidad_hab_especiales'],
        'amenidad_gay_friendly' => $data['amenidad_gay_friendly'],
        'amenidad_planes_boda' => $data['amenidad_planes_boda'],
        'estado_registro' => 'FINALIZADO',
        'wizard_step' => 6,
    ];

    if (cmhColExists($conn, 'tbl_alojamiento_general', 'estado_firma')) {
        $map['estado_firma'] = 'PENDIENTE';
    }
    if (cmhColExists($conn, 'tbl_alojamiento_general', 'estado_aprobacion')) {
        $map['estado_aprobacion'] = $estadoAprobacion;
    }

    $set = [];
    $types = '';
    $vals = [];

    foreach ($map as $col => $value) {
        if ($value === null || !cmhColExists($conn, 'tbl_alojamiento_general', $col)) {
            continue;
        }
        $set[] = "$col=?";
        if (in_array($col, ['id_usuario_creacion', 'numero_habitaciones', 'wizard_step', 'incluye_desayuno', 'habitaciones_discapacidad', 'habitaciones_connecting', 'accesibilidad_banos', 'accesibilidad_habitaciones', 'accesibilidad_espacios_comunes', 'es_pet_friendly', 'amenidad_restaurante', 'amenidad_bar_lounge', 'amenidad_hab_especiales', 'amenidad_gay_friendly', 'amenidad_planes_boda'], true)) {
            $types .= 'i';
            $vals[] = (int) $value;
        } else {
            $types .= 's';
            $vals[] = (string) $value;
        }
    }

    $set[] = 'updated_at=NOW()';
    $sql = 'UPDATE tbl_alojamiento_general SET ' . implode(',', $set) . ' WHERE id_hotel=?';
    $types .= 'i';
    $vals[] = $idHotel;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();
    $stmt->close();
}

function cmhImportContacts(mysqli $conn, int $idHotel, Worksheet $hotelSheet): int
{
    $rows = [15, 17, 19, 21, 23, 25];
    $labels = [
        'COMERCIAL' => 'Comercial',
        'RESERVAS' => 'Reservas',
        'GRUPOS' => 'Grupos',
        'PAGOS' => 'Pagos',
        'RECLAMACIONES' => 'Reclamaciones',
        'EXTRANET' => 'Extranet',
        'GERENCIA' => 'Gerencia',
    ];

    $stmtDel = $conn->prepare("DELETE FROM tbl_alojamiento_contactos WHERE id_hotel=?");
    $stmtDel->bind_param("i", $idHotel);
    $stmtDel->execute();
    $stmtDel->close();

    $stmtIns = $conn->prepare("INSERT INTO tbl_alojamiento_contactos (id_hotel, tipo_contacto, nombre, movil, email, telefono) VALUES (?, ?, ?, ?, ?, ?)");
    $count = 0;

    foreach ($rows as $row) {
        $rawLabel = strtoupper(cmhCell($hotelSheet, 'A' . $row));
        $tipo = null;
        foreach ($labels as $needle => $mapped) {
            if (strpos($rawLabel, $needle) !== false) {
                $tipo = $mapped;
                break;
            }
        }

        if (!$tipo) {
            continue;
        }

        $nombre = cmhCell($hotelSheet, 'C' . $row);
        $movil = cmhCell($hotelSheet, 'J' . $row);
        $email = cmhCell($hotelSheet, 'C' . ($row + 1));
        $telefono = cmhCell($hotelSheet, 'J' . ($row + 1));

        if ($nombre === '' && $movil === '' && $email === '' && $telefono === '') {
            continue;
        }

        $stmtIns->bind_param("isssss", $idHotel, $tipo, $nombre, $movil, $email, $telefono);
        $stmtIns->execute();
        $count++;
    }

    $stmtIns->close();
    return $count;
}

function cmhRoomServices(?Worksheet $sheet): string
{
    if (!$sheet) {
        return '{"servicios":[],"obs":""}';
    }

    $services = [];
    foreach (range(3, 12) as $row) {
        foreach (['C', 'E', 'F', 'G'] as $col) {
            $value = cmhCell($sheet, $col . $row);
            if ($value !== '') {
                $services[] = $value;
            }
        }
    }

    return json_encode([
        'servicios' => array_values(array_unique($services)),
        'obs' => cmhCell($sheet, 'A14'),
    ], JSON_UNESCAPED_UNICODE);
}

function cmhImportHabitaciones(mysqli $conn, int $idHotel, ?Worksheet $roomTypesSheet, ?Worksheet $roomServicesSheet): int
{
    if (!$roomTypesSheet) {
        return 0;
    }

    $conn->query("DELETE FROM tbl_alojamiento_habitaciones WHERE id_hotel=" . (int) $idHotel);

    $sql = "INSERT INTO tbl_alojamiento_habitaciones
        (id_hotel, tipo_habitacion, total_habitaciones, max_adultos, max_ninos, max_total, mts2,
         cama_sencilla, cama_doble, cama_queen, cama_king, camarote_sencillo, camarote_doble, camas_adicionales,
         observaciones, servicios_gen_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $serviciosJson = cmhRoomServices($roomServicesSheet);
    $count = 0;

    for ($row = 4; $row <= $roomTypesSheet->getHighestDataRow(); $row++) {
        $tipo = cmhCell($roomTypesSheet, 'A' . $row);
        if ($tipo === '' || strpos($tipo, '**') === 0) {
            continue;
        }

        $total = cmhInt(cmhCell($roomTypesSheet, 'C' . $row));
        $maxAdultos = cmhInt(cmhCell($roomTypesSheet, 'D' . $row));
        $maxNinos = cmhInt(cmhCell($roomTypesSheet, 'F' . $row));
        $maxTotal = cmhInt(cmhCell($roomTypesSheet, 'H' . $row));
        $mts2 = cmhFloat(cmhCell($roomTypesSheet, 'J' . $row));
        $camaSencilla = cmhInt(cmhCell($roomTypesSheet, 'K' . $row));
        $camaDoble = cmhInt(cmhCell($roomTypesSheet, 'L' . $row));
        $camaQueen = cmhInt(cmhCell($roomTypesSheet, 'M' . $row));
        $camaKing = cmhInt(cmhCell($roomTypesSheet, 'N' . $row));
        $camaroteSencillo = cmhInt(cmhCell($roomTypesSheet, 'O' . $row));
        $camaroteDoble = cmhInt(cmhCell($roomTypesSheet, 'P' . $row));
        $camasAdicionales = 0;
        $observaciones = cmhCell($roomTypesSheet, 'Q' . $row);

        $stmt->bind_param(
            "isiiiiidiiiiisss",
            $idHotel,
            $tipo,
            $total,
            $maxAdultos,
            $maxNinos,
            $maxTotal,
            $mts2,
            $camaSencilla,
            $camaDoble,
            $camaQueen,
            $camaKing,
            $camaroteSencillo,
            $camaroteDoble,
            $camasAdicionales,
            $observaciones,
            $serviciosJson
        );
        $stmt->execute();
        $count++;
    }

    $stmt->close();
    return $count;
}

function cmhNormalizeLabel(string $text): string
{
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }

    $text = strtr($text, [
        "\xC3\xA1" => 'a', "\xC3\xA9" => 'e', "\xC3\xAD" => 'i', "\xC3\xB3" => 'o', "\xC3\xBA" => 'u', "\xC3\xBC" => 'u', "\xC3\xB1" => 'n',
        "\xC3\x81" => 'A', "\xC3\x89" => 'E', "\xC3\x8D" => 'I', "\xC3\x93" => 'O', "\xC3\x9A" => 'U', "\xC3\x9C" => 'U', "\xC3\x91" => 'N',
    ]);
    $text = strtoupper($text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text);
    return trim(preg_replace('/\s+/', ' ', $text));
}
function cmhServicioBool(string $value): int
{
    $value = cmhNormalizeLabel($value);
    return strpos($value, 'SI') !== false ? 1 : 0;
}

function cmhServicioSelectValue(string $value): int
{
    return cmhServicioBool($value) === 1 ? 1 : 2;
}

function cmhFirstNonEmpty(Worksheet $sheet, array $coords): string
{
    foreach ($coords as $coord) {
        $value = cmhCell($sheet, $coord);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function cmhSiNoDbValue(string $value, ?int $fallback = null): string
{
    $normalized = cmhNormalizeLabel($value);
    if ($normalized === '') {
        return $fallback === null ? '' : (string) $fallback;
    }

    if (strpos($normalized, 'SI') !== false || in_array($normalized, ['1', 'YES', 'TRUE'], true)) {
        return '1';
    }

    if (strpos($normalized, 'NO') !== false || in_array($normalized, ['0', '2', 'FALSE'], true)) {
        return '2';
    }

    return $fallback === null ? '' : (string) $fallback;
}

function cmhInternetOption(string $value): string
{
    $normalized = cmhNormalizeLabel($value);
    if ($normalized === '') {
        return 'No hay internet';
    }

    if (strpos($normalized, 'COSTO') !== false) {
        return 'Con costo';
    }

    if (strpos($normalized, 'SI') !== false || strpos($normalized, 'GRATIS') !== false || $normalized === '1') {
        return 'Gratis';
    }

    return 'No hay internet';
}

function cmhMapTipoDesayuno(string $value): string
{
    $normalized = cmhNormalizeLabel($value);
    if ($normalized === '') {
        return '';
    }

    if (strpos($normalized, 'CARTA') !== false) {
        return 'a_la_carta';
    }
    if (strpos($normalized, 'AMERICANO') !== false) {
        return 'americano';
    }
    if (strpos($normalized, 'BUFFET') !== false) {
        return 'buffet';
    }
    if (strpos($normalized, 'CONTINENTAL') !== false) {
        return 'continental';
    }

    return strtolower(str_replace(' ', '_', $normalized));
}

function cmhCheckedLabelsByRowFromXlsx(string $xlsxPath): array
{
    if (!class_exists('ZipArchive') || !is_file($xlsxPath)) {
        return [];
    }

    $zip = new ZipArchive();
    if ($zip->open($xlsxPath) !== true) {
        return [];
    }

    $vml = $zip->getFromName('xl/drawings/vmlDrawing1.vml');
    $zip->close();
    if (!$vml) {
        return [];
    }

    preg_match_all('/<v:shape\b.*?<\/v:shape>/si', $vml, $matches);
    $rows = [];
    foreach ($matches[0] as $shape) {
        if (stripos($shape, 'ObjectType="Checkbox"') === false || stripos($shape, '<x:Checked>1</x:Checked>') === false) {
            continue;
        }

        if (!preg_match('/<x:Anchor>\s*(.*?)\s*<\/x:Anchor>/si', $shape, $anchorMatch)) {
            continue;
        }

        $parts = array_map('trim', explode(',', preg_replace('/\s+/', ' ', $anchorMatch[1])));
        if (count($parts) < 4 || !is_numeric($parts[2])) {
            continue;
        }
        $row = ((int) $parts[2]) + 1;

        preg_match_all('/<font[^>]*>(.*?)<\/font>/si', $shape, $textMatches);
        $label = html_entity_decode(strip_tags(implode(' ', $textMatches[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $label = trim(preg_replace('/\s+/', ' ', $label));
        if ($label === '') {
            continue;
        }

        $rows[$row][] = $label;
    }

    return $rows;
}

function cmhCheckedLabel(array $checkedRows, int $row, array $labels): bool
{
    $checked = $checkedRows[$row] ?? [];
    $targets = array_map('cmhNormalizeLabel', $labels);
    foreach ($checked as $label) {
        $normalized = cmhNormalizeLabel($label);
        foreach ($targets as $target) {
            if ($target !== '' && strpos($normalized, $target) !== false) {
                return true;
            }
        }
    }

    return false;
}

function cmhFirstCheckedLabel(array $checkedRows, int $row, array $labels): string
{
    foreach ($labels as $label) {
        if (cmhCheckedLabel($checkedRows, $row, [$label])) {
            return $label;
        }
    }

    return '';
}

function cmhMapTipoHotelJson(string $tipoHotel, string $otros): string
{
    $raw = trim($tipoHotel . ' ' . $otros);
    if ($raw === '') {
        return '[]';
    }

    $normalized = cmhNormalizeLabel($raw);
    $items = [];
    $known = [
        'FAMILIAR' => 'familiar',
        'FAMLIAR' => 'familiar',
        'GAY ONLY' => 'gay_only',
        'CORPORATIVO' => 'corporativo',
        'BOUTIQUE' => 'boutique',
        'SOLO ADULTOS' => 'solo_adultos',
    ];

    foreach ($known as $needle => $value) {
        if (strpos($normalized, $needle) !== false) {
            $items[] = $value;
        }
    }

    if ($otros !== '') {
        $items[] = $otros;
    } elseif (!$items && $tipoHotel !== '') {
        $items[] = $tipoHotel;
    }

    return json_encode(array_values(array_unique($items)), JSON_UNESCAPED_UNICODE);
}

function cmhRoomHas(?Worksheet $sheet, array $needles): bool
{
    if (!$sheet) {
        return false;
    }

    $targets = array_map('cmhNormalizeLabel', $needles);
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
    foreach (range(1, $sheet->getHighestDataRow()) as $row) {
        for ($col = 1; $col <= $highestColumn; $col++) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $value = cmhNormalizeLabel(cmhCell($sheet, $coord));
            if ($value === '') {
                continue;
            }
            foreach ($targets as $target) {
                if ($target !== '' && strpos($value, $target) !== false) {
                    return true;
                }
            }
        }
    }

    return false;
}

function cmhBuildServiciosData(?Worksheet $hotelSheet, ?Worksheet $roomServicesSheet): array
{
    $updates = [];
    $integerDefaults = [
        'recepcion_24_hrs', 'parqueadero', 'minibar', 'con_cocina', 'cafetera_cortesia', 'ventilador_techo',
        'servicio_habitacion', 'servicio_habitacion_24_hrs', 'transfer_aero_htl', 'aire_acondicionado', 'turco',
        'servicio_lavanderia', 'transfer_htl_playa', 'lobby_lounge', 'bar', 'guarda_equipaje', 'asoleadoras',
        'terraza', 'bar_piscina', 'servicios_ninera', 'muelle_privado', 'cafe_bar', 'concierge', 'super_minimercado',
        'sendero_ecologico', 'discoteca', 'playa', 'alquiler_bicicletas', 'mini_golf', 'cafe_recepcion', 'piscina',
        'cajero_automatico', 'snack_bar', 'capilla', 'piscina_infantil', 'cambio_moneda', 'salon_fitness',
        'club_ninos', 'pesca', 'enfermeria_medico', 'zona_juegos_infantiles', 'sala_masajes', 'salon_juegos',
        'personal_bilingue', 'sauna', 'salon_belleza', 'casino', 'lobby_sala_espera', 'spa', 'gimnasio',
        'juegos_mesa', 'ascensor', 'toallas_playa_piscina', 'jacuzzi', 'agua_caliente_hab'
    ];
    foreach ($integerDefaults as $col) {
        $updates[$col] = in_array($col, ['agua_caliente_hab', 'ascensor'], true) ? 0 : 2;
    }

    $updates['internet_wifi'] = 'No hay internet';
    $updates['internet_cable'] = 'No hay internet';
    $updates['wifi_zonas_comunes'] = 'No hay internet';
    $updates['canal_dedicado'] = 'No hay internet';
    $updates['cobertura_internet'] = json_encode(['no_hay_internet'], JSON_UNESCAPED_UNICODE);

    $labelMap = [
        'ALQUILER DE BICICLETAS' => 'alquiler_bicicletas',
        'TRANSFER AERO HTL AERO' => 'transfer_aero_htl',
        'AIRE ACONDICIONADO EN EL HOTEL' => 'aire_acondicionado',
        'ASCENSOR' => 'ascensor',
        'CAJERO AUTOMATICO' => 'cajero_automatico',
        'TRANSFER HTL PLAYA HTL' => 'transfer_htl_playa',
        'BAR' => 'bar',
        'CAMBIO DE MONEDA' => 'cambio_moneda',
        'CAFE AGUA SABORIZADA Y AROMATICA EN LA RECEPCION' => 'cafe_recepcion',
        'BAR EN LA PISCINA' => 'bar_piscina',
        'CASINO' => 'casino',
        'ASOLEADORAS' => 'asoleadoras',
        'CAPILLA' => 'capilla',
        'CLUB DE NINOS' => 'club_ninos',
        'ENFERMERIA Y O SERVICIO MEDICO' => 'enfermeria_medico',
        'LOBBY CON SALA DE ESPERA' => 'lobby_sala_espera',
        'DISCOTECA' => 'discoteca',
        'CONCIERGE' => 'concierge',
        'GUARDA EQUIPAJE' => 'guarda_equipaje',
        'JACUZZI' => 'jacuzzi',
        'GIMNASIO' => 'gimnasio',
        'PERSONAL BILINGUE' => 'personal_bilingue',
        'MUELLE PRIVADO' => 'muelle_privado',
        'JUEGOS DE MESA' => 'juegos_mesa',
        'RECEPCION 24 HRS' => 'recepcion_24_hrs',
        'LOBBY LOUNGE' => 'lobby_lounge',
        'MINI GOLF' => 'mini_golf',
        'SALON DE JUEGOS' => 'salon_juegos',
        'SALA DE MASAJES' => 'sala_masajes',
        'PARQUE INFANTIL' => 'zona_juegos_infantiles',
        'SERVICIO A LA HABITACION' => 'servicio_habitacion',
        'SENDERO ECOLOGICO' => 'sendero_ecologico',
        'SALON DE BELLEZA' => 'salon_belleza',
        'PARQUEADERO' => 'parqueadero',
        'SERVICIO A LA HABITACION 24 HORAS' => 'servicio_habitacion_24_hrs',
        'SALON DE FITNESS' => 'salon_fitness',
        'PESCA' => 'pesca',
        'SERVICIO DE LAVANDERIA' => 'servicio_lavanderia',
        'SAUNA' => 'sauna',
        'PISCINA' => 'piscina',
        'SERVICIOS DE NINERA CARGO ADICIONAL' => 'servicios_ninera',
        'SNACK BAR' => 'snack_bar',
        'PISCINA INFANTIL' => 'piscina_infantil',
        'SUPER MINIMERCADO TIENDA DE REGALOS' => 'super_minimercado',
        'TERRAZA' => 'terraza',
        'PLAYA' => 'playa',
        'TOALLAS PARA LA PLAYA Y PISCINA' => 'toallas_playa_piscina',
        'ZONA DE JUEGOS INFANTILES' => 'zona_juegos_infantiles',
        'TURCO' => 'turco',
        'CUENTA CON AGUA CALIENTE EN HABITACIONES' => 'agua_caliente_hab',
        'MESA DE BILLAR' => 'juegos_mesa',
        'MESA DE PING PONG' => 'juegos_mesa',
    ];

    $otrosServiciosSi = [];

    if ($hotelSheet) {
        foreach (range(36, 52) as $row) {
            foreach ([['A', 'C'], ['D', 'F'], ['G', 'I'], ['J', 'K']] as $pair) {
                [$labelCol, $valueCol] = $pair;
                $rawLabel = cmhCell($hotelSheet, $labelCol . $row);
                $label = cmhNormalizeLabel($rawLabel);
                $value = cmhCell($hotelSheet, $valueCol . $row);
                if ($label === '' || $value === '') {
                    continue;
                }
                $column = $labelMap[$label] ?? null;
                if ($column) {
                    $updates[$column] = ($column === 'agua_caliente_hab') ? cmhServicioBool($value) : cmhServicioSelectValue($value);
                } elseif (cmhServicioBool($value) === 1 && $rawLabel !== '') {
                    $otrosServiciosSi[] = $rawLabel;
                }
            }
        }

        $updates['internet_wifi'] = cmhInternetOption(cmhCell($hotelSheet, 'D54'));
        $updates['canal_dedicado'] = cmhInternetOption(cmhCell($hotelSheet, 'H54'));
        $updates['internet_cable'] = cmhInternetOption(cmhCell($hotelSheet, 'D55'));
        $updates['wifi_zonas_comunes'] = cmhInternetOption(cmhCell($hotelSheet, 'H55'));

        $internetValues = [$updates['internet_wifi'], $updates['internet_cable'], $updates['wifi_zonas_comunes'], $updates['canal_dedicado']];
        $hasInternet = count(array_filter($internetValues, static fn($value) => $value !== 'No hay internet')) > 0;
        $updates['cobertura_internet'] = json_encode($hasInternet ? ['habitaciones', 'areas_publicas'] : ['no_hay_internet'], JSON_UNESCAPED_UNICODE);
    }

    if (cmhRoomHas($roomServicesSheet, ['Minibar'])) {
        $updates['minibar'] = 1;
    }
    if (cmhRoomHas($roomServicesSheet, ['Cocineta', 'Cocina'])) {
        $updates['con_cocina'] = 1;
    }
    if (cmhRoomHas($roomServicesSheet, ['Maquina de Cafe', 'Cafetera'])) {
        $updates['cafetera_cortesia'] = 1;
    }
    if (cmhRoomHas($roomServicesSheet, ['Servicio a la habitacion'])) {
        $updates['servicio_habitacion'] = 1;
    }
    if (cmhRoomHas($roomServicesSheet, ['Aire Acondicionado'])) {
        $updates['aire_acondicionado'] = 1;
    }
    if (cmhRoomHas($roomServicesSheet, ['Internet Inalambrico', 'Wifi'])) {
        $updates['internet_wifi'] = 'Gratis';
        $updates['cobertura_internet'] = json_encode(['habitaciones'], JSON_UNESCAPED_UNICODE);
    }

    $otroServicioBase = cmhCell($hotelSheet, 'A53');
    $updates['otro_servicio'] = implode(', ', array_values(array_unique(array_filter(array_merge([$otroServicioBase], $otrosServiciosSi)))));
    return $updates;
}

function cmhImportServicios(mysqli $conn, int $idHotel, ?Worksheet $hotelSheet, ?Worksheet $roomServicesSheet): int
{
    cmhEnsureServiciosRow($conn, $idHotel);
    $updates = cmhBuildServiciosData($hotelSheet, $roomServicesSheet);
    $set = [];
    $types = '';
    $vals = [];
    $stringCols = ['internet_wifi', 'internet_cable', 'wifi_zonas_comunes', 'canal_dedicado', 'otro_servicio', 'cobertura_internet'];

    foreach ($updates as $col => $value) {
        if (!cmhColExists($conn, 'tbl_alojamiento_servicios', $col)) {
            continue;
        }
        $set[] = "$col=?";
        if (in_array($col, $stringCols, true)) {
            $types .= 's';
            $vals[] = (string) $value;
        } else {
            $types .= 'i';
            $vals[] = (int) $value;
        }
    }

    if (!$set) {
        return 0;
    }

    $sql = 'UPDATE tbl_alojamiento_servicios SET ' . implode(',', $set) . ' WHERE id_hotel=?';
    $types .= 'i';
    $vals[] = $idHotel;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();
    $stmt->close();

    return count($set);
}

function cmhImportSalones(mysqli $conn, int $idHotel, ?Worksheet $hotelSheet): int
{
    if (!$hotelSheet) {
        return 0;
    }

    $conn->query("DELETE FROM tbl_alojamiento_salones WHERE id_hotel=" . (int) $idHotel);
    $stmt = $conn->prepare("INSERT INTO tbl_alojamiento_salones
        (id_hotel, nombre_salon, m2, largo, ancho, alto, cap_u_herradura, cap_aula, cap_auditorio, cap_banquete, cap_imperial, cap_coctel)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $count = 0;

    for ($row = 60; $row <= $hotelSheet->getHighestDataRow(); $row++) {
        $nombre = cmhCell($hotelSheet, 'A' . $row);
        if ($nombre === '' || strpos($nombre, '**') === 0) {
            continue;
        }

        $m2 = cmhFloat(cmhCell($hotelSheet, 'B' . $row));
        $largo = cmhFloat(cmhCell($hotelSheet, 'C' . $row));
        $ancho = cmhFloat(cmhCell($hotelSheet, 'D' . $row));
        $alto = cmhFloat(cmhCell($hotelSheet, 'E' . $row));
        $capAuditorio = cmhInt(cmhCell($hotelSheet, 'F' . $row));
        $capAula = cmhInt(cmhCell($hotelSheet, 'G' . $row));
        $capHerradura = cmhInt(cmhCell($hotelSheet, 'H' . $row));
        $capBanquete = cmhInt(cmhCell($hotelSheet, 'I' . $row));
        $capImperial = cmhInt(cmhCell($hotelSheet, 'J' . $row));
        $capCoctel = cmhInt(cmhCell($hotelSheet, 'K' . $row));

        $stmt->bind_param(
            "isddddiiiiii",
            $idHotel,
            $nombre,
            $m2,
            $largo,
            $ancho,
            $alto,
            $capHerradura,
            $capAula,
            $capAuditorio,
            $capBanquete,
            $capImperial,
            $capCoctel
        );
        $stmt->execute();
        $count++;
    }

    $stmt->close();
    return $count;
}
function cmhEnsureServiciosRow(mysqli $conn, int $idHotel): void
{
    $stmt = $conn->prepare("SELECT 1 FROM tbl_alojamiento_servicios WHERE id_hotel=? LIMIT 1");
    $stmt->bind_param("i", $idHotel);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO tbl_alojamiento_servicios (id_hotel) VALUES (?)");
        $stmt->bind_param("i", $idHotel);
        $stmt->execute();
        $stmt->close();
    }
}

function cmhExtractHotelData($spreadsheet, ?string $xlsxPath = null): array
{
    $hotelSheet = $spreadsheet->getSheetByName('HOTEL');
    if (!$hotelSheet) {
        throw new RuntimeException('La ficha no tiene hoja HOTEL.');
    }

    $nit = cmhNormalizeNit(cmhCell($hotelSheet, 'I9'));
    $nombre = cmhCell($hotelSheet, 'C8');
    $razonSocial = cmhCell($hotelSheet, 'C9');

    if ($nombre === '') {
        throw new RuntimeException('No se encontro nombre del hotel en HOTEL!C8.');
    }
    if ($nit === '') {
        throw new RuntimeException('No se encontro NIT en HOTEL!I9.');
    }

    $checkedRows = $xlsxPath ? cmhCheckedLabelsByRowFromXlsx($xlsxPath) : [];
    $precioDesayuno = cmhFirstNonEmpty($hotelSheet, ['F29', 'G29']);
    $incluyeDesayuno = cmhSiNoDbValue(cmhFirstNonEmpty($hotelSheet, ['D29', 'J29', 'K29']), $precioDesayuno !== '' ? 0 : null);
    if (cmhCheckedLabel($checkedRows, 29, ['SI'])) {
        $incluyeDesayuno = '1';
    } elseif (cmhCheckedLabel($checkedRows, 29, ['NO'])) {
        $incluyeDesayuno = '0';
    }
    $tipoDesayuno = cmhMapTipoDesayuno(cmhFirstNonEmpty($hotelSheet, ['I29', 'J29', 'K29']));
    $tipoDesayunoVml = cmhMapTipoDesayuno(cmhFirstCheckedLabel($checkedRows, 29, ['A LA CARTA', 'AMERICANO', 'BUFFET', 'CONTINENTAL']));
    if ($tipoDesayunoVml !== '') {
        $tipoDesayuno = $tipoDesayunoVml;
    }
    $tipoHotel = cmhFirstNonEmpty($hotelSheet, ['B30', 'C30']);
    $tipoHotelVml = cmhFirstCheckedLabel($checkedRows, 30, ['FAMILIAR', 'FAMLIAR', 'GAY ONLY', 'CORPORATIVO', 'BOUTIQUE', 'SOLO ADULTOS']);
    if ($tipoHotelVml !== '') {
        $tipoHotel = $tipoHotelVml;
    }
    $tipoHotelOtro = cmhFirstNonEmpty($hotelSheet, ['B31', 'C31', 'D31', 'E31', 'F31', 'G31', 'H31', 'I31', 'J31', 'K31']);
    $habitacionesDiscapacidad = cmhInt(cmhCell($hotelSheet, 'H32'));
    $habitacionesConnecting = cmhInt(cmhCell($hotelSheet, 'K32'));
    if ($habitacionesConnecting === 0 && cmhCheckedLabel($checkedRows, 31, ['Habitaciones Conecting'])) {
        $habitacionesConnecting = 1;
    }
    $accesibilidadRaw = cmhFirstNonEmpty($hotelSheet, ['B32', 'C32', 'D32', 'E32']);
    $accesibilidadSi = cmhServicioBool($accesibilidadRaw) === 1;
    $adaptadoMovilidad = cmhServicioBool(cmhCell($hotelSheet, 'F38')) === 1;
    $accesibilidadBanos = ($accesibilidadSi || cmhCheckedLabel($checkedRows, 32, ['Bano accesible', 'Baño accesible'])) ? 1 : 0;
    $accesibilidadHabitaciones = ($accesibilidadSi || $habitacionesDiscapacidad > 0 || cmhCheckedLabel($checkedRows, 32, ['Habitacion accesible', 'Habitación accesible'])) ? 1 : 0;
    $accesibilidadEspacios = ($accesibilidadSi || $adaptadoMovilidad || cmhCheckedLabel($checkedRows, 32, ['Rampa para discapacitados'])) ? 1 : 0;

    return [
        'cadena_hotelera' => cmhCell($hotelSheet, 'C7'),
        'nombre' => $nombre,
        'nit' => $nit,
        'nit_consecutivo' => '',
        'razon_social' => $razonSocial !== '' ? $razonSocial : $nombre,
        'telefono' => cmhCell($hotelSheet, 'C10'),
        'direccion' => cmhCell($hotelSheet, 'C11'),
        'ciudad' => cmhCell($hotelSheet, 'C12'),
        'pais' => cmhCell($hotelSheet, 'I12'),
        'website' => cmhCell($hotelSheet, 'C13'),
        'categoria' => cmhCell($hotelSheet, 'I13'),
        'descripcion_producto' => cmhCell($hotelSheet, 'B28'),
        'numero_habitaciones' => cmhInt(cmhCell($hotelSheet, 'B29')),
        'incluye_desayuno' => $incluyeDesayuno,
        'precio_desayuno' => $precioDesayuno,
        'tipo_desayuno' => $tipoDesayuno,
        'habitaciones_discapacidad' => $habitacionesDiscapacidad,
        'habitaciones_connecting' => $habitacionesConnecting,
        'accesibilidad_banos' => $accesibilidadBanos,
        'accesibilidad_habitaciones' => $accesibilidadHabitaciones,
        'accesibilidad_espacios_comunes' => $accesibilidadEspacios,
        'informacion_adicional' => cmhCell($hotelSheet, 'B33'),
        'tipo_hotel_json' => cmhMapTipoHotelJson($tipoHotel, $tipoHotelOtro),
        'hora_check_in' => cmhCell($hotelSheet, 'H30'),
        'hora_check_out' => cmhCell($hotelSheet, 'K30'),
        'es_pet_friendly' => cmhCheckedLabel($checkedRows, 31, ['El Hotel es Pet Friendly?']) ? 1 : 0,
        'politica_mascotas' => cmhCell($hotelSheet, 'E30'),
        'amenidad_restaurante' => cmhCheckedLabel($checkedRows, 31, ['Restaurante Especializado']) ? 1 : 0,
        'amenidad_bar_lounge' => cmhCheckedLabel($checkedRows, 31, ['Bar / Lounge']) ? 1 : 0,
        'amenidad_hab_especiales' => cmhCheckedLabel($checkedRows, 31, ['Habitaciones o pisos especiales']) ? 1 : 0,
        'amenidad_gay_friendly' => cmhCheckedLabel($checkedRows, 31, ['El Hotel es Gay Friendly?']) ? 1 : 0,
        'amenidad_planes_boda' => cmhCheckedLabel($checkedRows, 30, ['Planes especiales de Boda']) ? 1 : 0,
        '_hotel_sheet' => $hotelSheet,
        '_room_types_sheet' => $spreadsheet->getSheetByName('Room types'),
        '_room_services_sheet' => $spreadsheet->getSheetByName('Room 1'),
    ];
}

function cmhImportWorkbook(mysqli $conn, string $tmpPath, string $originalName, ?string $usuarioDestino, ?int $idUsuarioCreacion, string $estadoAprobacion, bool $independiente, ?string $nombreCadenaDestino): array
{
    $spreadsheet = IOFactory::load($tmpPath);
    $data = cmhExtractHotelData($spreadsheet, $tmpPath);

    if ($independiente) {
        $data['cadena_hotelera'] = '';
    } elseif ($nombreCadenaDestino !== null && trim($nombreCadenaDestino) !== '') {
        $data['cadena_hotelera'] = trim($nombreCadenaDestino);
    }

    if ($independiente) {
    $idUsuarioHotel = cmhCrearUsuarioHotelSiNoExiste(
        $conn,
        $data['nit'],
        $data['nombre'],
        $data['telefono'],
        $data['direccion']
    );

    $usuarioDestino = $data['nit'];
    $idUsuarioCreacion = $idUsuarioHotel;
    }

    $idHotel = cmhFindHotel($conn, $usuarioDestino, $idUsuarioCreacion, $data['nit'], $data['nombre'], $data['razon_social'], $independiente);
    $accion = 'creado';

    if (!$idHotel) {
    $data['nit_consecutivo'] = $data['nit'];

    $idHotel = cmhInsertMinimalHotel($conn, $usuarioDestino, $idUsuarioCreacion, $data, $estadoAprobacion);
    } else {
        $accion = 'actualizado';
        $stmt = $conn->prepare("SELECT nit_consecutivo FROM tbl_alojamiento_general WHERE id_hotel=? LIMIT 1");
        $stmt->bind_param("i", $idHotel);
        $stmt->execute();
        $stmt->bind_result($nitConsecutivoActual);
        $stmt->fetch();
        $stmt->close();

        $data['nit_consecutivo'] = $data['nit'];
    }

    cmhUpdateGeneral($conn, $idHotel, $usuarioDestino, $idUsuarioCreacion, $data, $estadoAprobacion);
    $contactos = cmhImportContacts($conn, $idHotel, $data['_hotel_sheet']);
    $habitaciones = cmhImportHabitaciones($conn, $idHotel, $data['_room_types_sheet'], $data['_room_services_sheet']);
    $servicios = cmhImportServicios($conn, $idHotel, $data['_hotel_sheet'], $data['_room_services_sheet']);
    $salones = cmhImportSalones($conn, $idHotel, $data['_hotel_sheet']);

    return [
        'archivo' => $originalName,
        'hotel' => $data['nombre'],
        'nit' => $data['nit'],
        'id_hotel' => $idHotel,
        'accion' => $accion,
        'contactos' => $contactos,
        'habitaciones' => $habitaciones,
        'servicios' => $servicios,
        'salones' => $salones,
        'estado_aprobacion' => $estadoAprobacion,
    ];
}

$returnTo = (string) ($_POST['return_to'] ?? 'listado_cadena');
$modoCarga = (string) ($_POST['modo_carga'] ?? 'cadena');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cmhRedirectTo($returnTo);
}

$usuarioDestino = (string) $usuarioSesion;
$idUsuarioCreacion = cmhGetUsuarioId($conn, $usuarioDestino);
$nombreCadenaDestino = null;
$independiente = false;
$estadoAprobacion = 'PENDIENTE';

if ($returnTo === 'consulta_fichas') {
    if (!$esAdminMasivo) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    $estadoAprobacion = 'APROBADO';

    if ($modoCarga === 'cadena') {
        $idCadenaDestino = (int) ($_POST['id_cadena_destino'] ?? 0);
        $cadenaDestino = $idCadenaDestino > 0 ? cmhGetCadenaById($conn, $idCadenaDestino) : null;

        if (!$cadenaDestino) {
            $_SESSION['bulk_import_result'] = [
                'ok' => false,
                'mensaje' => 'Debes seleccionar una cadena valida para la carga masiva.',
                'items' => [],
                'errores' => [],
            ];
            cmhRedirectTo($returnTo);
        }

        $usuarioDestino = (string) $cadenaDestino['usuario'];
        $idUsuarioCreacion = (int) $cadenaDestino['id_usuario'];
        $nombreCadenaDestino = (string) $cadenaDestino['nombre'];
    } elseif ($modoCarga === 'independiente') {
        $usuarioDestino = '';
        $idUsuarioCreacion = null;
        $nombreCadenaDestino = '';
        $independiente = true;
    } else {
        $_SESSION['bulk_import_result'] = [
            'ok' => false,
            'mensaje' => 'Tipo de carga masiva no valido.',
            'items' => [],
            'errores' => [],
        ];
        cmhRedirectTo($returnTo);
    }
} elseif (!$esRolCadena) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$files = $_FILES['fichas_hoteles'] ?? null;
if (!$files || empty($files['name'])) {
    $_SESSION['bulk_import_result'] = [
        'ok' => false,
        'mensaje' => 'Debes seleccionar al menos una ficha Excel.',
        'items' => [],
        'errores' => [],
    ];
    cmhRedirectTo($returnTo);
}

$items = [];
$errores = [];
$creados = 0;
$actualizados = 0;
$totalContactos = 0;
$totalHabitaciones = 0;
$totalServicios = 0;
$totalSalones = 0;

$names = is_array($files['name']) ? $files['name'] : [$files['name']];
$tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
$errors = is_array($files['error']) ? $files['error'] : [$files['error']];

foreach ($names as $idx => $name) {
    $tmp = $tmpNames[$idx] ?? '';
    $error = (int) ($errors[$idx] ?? UPLOAD_ERR_NO_FILE);

    if ($error !== UPLOAD_ERR_OK) {
        $errores[] = $name . ': error al subir el archivo.';
        continue;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xlsm', 'xls'], true)) {
        $errores[] = $name . ': formato no permitido. Usa .xlsx, .xlsm o .xls.';
        continue;
    }

    try {
        $conn->begin_transaction();
        $item = cmhImportWorkbook($conn, $tmp, $name, $usuarioDestino, $idUsuarioCreacion, $estadoAprobacion, $independiente, $nombreCadenaDestino);
        $conn->commit();
        $items[] = $item;

        if ($item['accion'] === 'creado') {
            $creados++;
        } else {
            $actualizados++;
        }
        $totalContactos += (int) ($item['contactos'] ?? 0);
        $totalHabitaciones += (int) ($item['habitaciones'] ?? 0);
        $totalServicios += (int) ($item['servicios'] ?? 0);
        $totalSalones += (int) ($item['salones'] ?? 0);
    } catch (Throwable $e) {
        $conn->rollback();
        $errores[] = $name . ': ' . $e->getMessage();
    }
}

$_SESSION['bulk_import_result'] = [
    'ok' => empty($errores),
    'mensaje' => 'Carga finalizada. Creados: ' . $creados . ' | Actualizados: ' . $actualizados . ' | Contactos: ' . $totalContactos . ' | Habitaciones: ' . $totalHabitaciones . ' | Servicios: ' . $totalServicios . ' | Salones: ' . $totalSalones . ' | Errores: ' . count($errores),
    'items' => $items,
    'errores' => $errores,
];

cmhRedirectTo($returnTo);
