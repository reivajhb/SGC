<?php
header('Content-Type: application/json; charset=utf-8');

include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['usuario'] ?? null;
if (!$user_id) {
    echo json_encode(['ok' => false, 'error' => 'No session user']);
    exit;
}

$idRolActual = (int) ($_SESSION['id_rol'] ?? 0);
$esCadenaHotelera = ($idRolActual === 7);

function pnvNitConsecutivoEnUso($conn, $nitConsecutivo, $ignorarId = null)
{
    $nitConsecutivo = trim((string) $nitConsecutivo);
    if ($nitConsecutivo === '') {
        return false;
    }

    if ($ignorarId) {
        $stmt = $conn->prepare("
            SELECT id_hotel
            FROM tbl_alojamiento_general
            WHERE nit_consecutivo = ?
              AND id_hotel <> ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $nitConsecutivo, $ignorarId);
    } else {
        $stmt = $conn->prepare("
            SELECT id_hotel
            FROM tbl_alojamiento_general
            WHERE nit_consecutivo = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $nitConsecutivo);
    }

    $stmt->execute();
    $stmt->bind_result($idHotelEncontrado);
    $stmt->fetch();
    $stmt->close();

    return !empty($idHotelEncontrado);
}

function pnvSiguienteNitConsecutivo($conn, $nitBase, $ignorarId = null)
{
    $nitBase = trim((string) $nitBase);
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

        $suffix = substr($nc, strlen($nitBase));
        $suffix = strtoupper(preg_replace('/[^A-Z]/', '', $suffix));
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

function pnvColExists(mysqli $conn, string $table, string $col): bool
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

try {
    error_log(">>> HIT guardarBorradorAjax.php VERSION=2026-01-22 A");
    error_log("channelName POST: " . ($_POST['channelName'] ?? 'NO_LLEGA'));
    error_log("connectionType POST: " . ($_POST['connectionType'] ?? 'NO_LLEGA'));


    $wizard_step = isset($_POST['wizard_step']) ? (int) $_POST['wizard_step'] : 0;

    $id_hotel_borrador = (isset($_POST['id_hotel_borrador']) && $_POST['id_hotel_borrador'] !== '')
        ? (int) $_POST['id_hotel_borrador']
        : null;

    // FK usuario
    $id_usuario_creacion = null;
    $stmt_user = $conn->prepare("SELECT id_usuario FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
    $stmt_user->bind_param("s", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($id_usuario_creacion);
    $stmt_user->fetch();
    $stmt_user->close();

    // --- 1) Determinar id_hotel a reutilizar (si existe) ---
    // Regla:
    // 1. Si llega id_hotel_borrador, se intenta actualizar ese mismo registro.
    // 2. Si no llega id, se busca un registro existente por las llaves reales:
    //    nit_consecutivo, NIT + nombre o NIT + razon_social.
    // 3. La busqueda se limita a BORRADOR del usuario para no modificar fichas finalizadas.
    // 4. Solo si no existe nada, se crea un borrador nuevo.
    $id_hotel = null;
    $registro_reutilizado = false;

    if ($id_hotel_borrador) {
        $stmt_chk = $conn->prepare("
            SELECT id_hotel
            FROM tbl_alojamiento_general
            WHERE id_hotel=?
              AND usuario_creacion=?
              AND estado_registro='BORRADOR'
            LIMIT 1
        ");
        $stmt_chk->bind_param("is", $id_hotel_borrador, $user_id);
        $stmt_chk->execute();
        $stmt_chk->bind_result($id_hotel);
        $stmt_chk->fetch();
        $stmt_chk->close();

        if ($id_hotel) {
            $registro_reutilizado = true;
        }
    }

    // --- 2) Si no hay id todavía, REUSAR si ya existe registro con la misma llave, si no, INSERT mínimo ---
    if (!$id_hotel) {
        $nombre = trim(preg_replace('/\s+/', ' ', (string) ($_POST['nombre'] ?? '')));
        $nit = trim((string) ($_POST['nit'] ?? ''));
        $nit_consecutivo = trim((string) ($_POST['nit_consecutivo'] ?? ''));
        $razon_social = trim(preg_replace('/\s+/', ' ', (string) ($_POST['razon_social'] ?? '')));

        if ($nombre === '') {
            $nombre = "Hotel sin Nombre (NIT: " . ($nit !== '' ? $nit : 'N/D') . ")";
        }

        $id_existente = null;

        // 2A) Buscar primero por nit_consecutivo, porque es la llave mas precisa para cadenas.
        if ($nit_consecutivo !== '') {
            $stmt_find = $conn->prepare("
                SELECT id_hotel
                FROM tbl_alojamiento_general
                WHERE nit_consecutivo=?
                  AND usuario_creacion=?
                  AND estado_registro='BORRADOR'
                ORDER BY
                    updated_at DESC,
                    id_hotel DESC
                LIMIT 1
            ");
            $stmt_find->bind_param("ss", $nit_consecutivo, $user_id);
            $stmt_find->execute();
            $stmt_find->bind_result($id_existente);
            $stmt_find->fetch();
            $stmt_find->close();
        }

        // 2B) Si no encontro, buscar por NIT + nombre o razon_social.
        if (!$id_existente && $nit !== '' && $nombre !== '') {
            $stmt_find = $conn->prepare("
                SELECT id_hotel
                FROM tbl_alojamiento_general
                WHERE nit=?
                  AND (nombre=? OR razon_social=? OR razon_social=?)
                  AND usuario_creacion=?
                  AND estado_registro='BORRADOR'
                ORDER BY
                    updated_at DESC,
                    id_hotel DESC
                LIMIT 1
            ");
            $razon_busqueda = ($razon_social !== '') ? $razon_social : $nombre;
            $stmt_find->bind_param("sssss", $nit, $nombre, $nombre, $razon_busqueda, $user_id);
            $stmt_find->execute();
            $stmt_find->bind_result($id_existente);
            $stmt_find->fetch();
            $stmt_find->close();
        }

        if ($id_existente) {
            // Existe: se reutiliza y todo lo que sigue sera UPDATE.
            $id_hotel = (int) $id_existente;
            $registro_reutilizado = true;
        } else {
            // No existe: crear borrador nuevo una sola vez.
            if ($esCadenaHotelera && $nit !== '') {
                if ($nit_consecutivo === '' || pnvNitConsecutivoEnUso($conn, $nit_consecutivo)) {
                    $nit_consecutivo = pnvSiguienteNitConsecutivo($conn, $nit);
                    $_POST['nit_consecutivo'] = $nit_consecutivo;
                }
            }

            try {
                $stmt_ins = $conn->prepare("
                    INSERT INTO tbl_alojamiento_general
                    (usuario_creacion, id_usuario_creacion, nombre, nit, nit_consecutivo, estado_registro, wizard_step, updated_at)
                    VALUES (?, ?, ?, ?, NULLIF(?, ''), 'BORRADOR', ?, NOW())
                ");
                $stmt_ins->bind_param("sisssi", $user_id, $id_usuario_creacion, $nombre, $nit, $nit_consecutivo, $wizard_step);
                $stmt_ins->execute();
                $id_hotel = $conn->insert_id;
                $stmt_ins->close();
            } catch (mysqli_sql_exception $dup) {
                // Si aun asi aparece un duplicado por llave unica, no se rompe el guardado:
                // se vuelve a buscar el registro real y se continua con UPDATE.
                if ((int) $dup->getCode() !== 1062) {
                    throw $dup;
                }

                $stmt_find = $conn->prepare("
                    SELECT id_hotel
                    FROM tbl_alojamiento_general
                    WHERE (
                        (nit_consecutivo IS NOT NULL AND nit_consecutivo<>'' AND nit_consecutivo=?)
                        OR (nit=? AND (nombre=? OR razon_social=? OR razon_social=?))
                    )
                      AND usuario_creacion=?
                      AND estado_registro='BORRADOR'
                    ORDER BY
                        updated_at DESC,
                        id_hotel DESC
                    LIMIT 1
                ");
                $razon_busqueda = ($razon_social !== '') ? $razon_social : $nombre;
                $stmt_find->bind_param("ssssss", $nit_consecutivo, $nit, $nombre, $nombre, $razon_busqueda, $user_id);
                $stmt_find->execute();
                $stmt_find->bind_result($id_hotel);
                $stmt_find->fetch();
                $stmt_find->close();

                if (!$id_hotel) {
                    throw $dup;
                }

                $registro_reutilizado = true;
            }
        }
    }


    // --- 3) UPDATE dinámico de SOLO columnas que llegaron (NO pisa secciones anteriores) ---
    // Mapa: name del FORM -> columna en tbl_alojamiento_general
    if ($esCadenaHotelera) {
        $nit_para_consecutivo = trim((string) ($_POST['nit'] ?? ''));
        $nit_consecutivo_post = trim((string) ($_POST['nit_consecutivo'] ?? ''));
        $id_ignorar_consecutivo = $id_hotel ? (int) $id_hotel : null;

        if ($nit_para_consecutivo !== '') {
            if (
                $nit_consecutivo_post === '' ||
                pnvNitConsecutivoEnUso($conn, $nit_consecutivo_post, $id_ignorar_consecutivo)
            ) {
                $_POST['nit_consecutivo'] = pnvSiguienteNitConsecutivo(
                    $conn,
                    $nit_para_consecutivo,
                    $id_ignorar_consecutivo
                );
            }
        }
    }

    $generalMap = [
        'cadena_hotelera' => 'cadena_hotelera',
        'nombre' => 'nombre',
        'nit' => 'nit',
        'nit_consecutivo' => 'nit_consecutivo',
        'razon_social' => 'razon_social',
        'telefono' => 'telefono',
        'direccion' => 'direccion',
        'ciudad' => 'ciudad',
        'pais' => 'pais',
        'website' => 'website',
        'categoria' => 'categoria',
        'descripcion_producto' => 'descripcion_producto',
        'incluye_desayuno' => 'incluye_desayuno',
        'numero_habitaciones' => 'numero_habitaciones',
        'precio_desayuno' => 'precio_desayuno',
        'tipo_desayuno' => 'tipo_desayuno',
        'hora_check_in' => 'hora_check_in',
        'hora_check_out' => 'hora_check_out',
        'pet_friendly' => 'es_pet_friendly',
        'politica_mascotas' => 'politica_mascotas',
        'habitaciones_discapacidad' => 'habitaciones_discapacidad',
        'habitaciones_connecting' => 'habitaciones_connecting',
        'informacion_adicional' => 'informacion_adicional',

        'monto_credito' => 'monto_credito',
        'tiempo_credito' => 'tiempo_credito',
        'porcentaje_reteica' => 'reteica',
        'porcentaje_retefuente' => 'retefuente',

        'salones_eventos_count' => 'salones_eventos_count',
        'centro_negocios_count' => 'centro_negocios_count',
        'espacios_externos_count' => 'espacios_externos_count',

        'connectionType' => 'forma_conexion',
        'channelName' => 'channel_manager_nombre',
        'descuento_dinamico' => 'descuento_dinamico',

        'nombre_hotel_legal' => 'nombre_hotel_legal',
        'ciudad_hotel_legal' => 'ciudad_hotel_legal',
        'nit_hotel_legal' => 'nit_hotel_legal',
        'nombre_rep_legal' => 'nombre_rep_legal',
        'ciudad_rep_legal' => 'ciudad_rep_legal',
        'num_documento_rep_legal' => 'num_documento_rep_legal',
        'ciudad_doc_rep_legal' => 'ciudad_doc_rep_legal',
        'diligencia_nombre' => 'diligencia_nombre',
        'diligencia_correo' => 'diligencia_correo',
        'diligencia_cargo' => 'diligencia_cargo',

        'firma_nombre_completo' => 'rep_legal_nombre',
        'firma_cargo' => 'rep_legal_cargo',

        'tiene_certificado_sostenibilidad' => 'tiene_certificado_sostenibilidad',

        'ciiu' => 'ciiu',
        'tipo_contribuyente' => 'tipo_contribuyente',
        'numero_cuenta' => 'numero_cuenta',

        'politica_ninos' => 'politica_ninos',
        'politica_grupos' => 'politica_grupos',

        // Amenidades
        'amenidad_restaurante' => 'amenidad_restaurante',
        'amenidad_bar_lounge' => 'amenidad_bar_lounge',
        'amenidad_hab_especiales' => 'amenidad_hab_especiales',
        'amenidad_gay_friendly' => 'amenidad_gay_friendly',
        'amenidad_planes_boda' => 'amenidad_planes_boda',

        // Accesibilidad
        'accesibilidad_banos' => 'accesibilidad_banos',
        'accesibilidad_habitaciones' => 'accesibilidad_habitaciones',
        'accesibilidad_espacios_comunes' => 'accesibilidad_espacios_comunes'



    ];

    $set = [];
    $types = '';
    $vals = [];

    // 1. Procesar Tabla Dinámica de Tarifas (JSON)
    if (isset($_POST['plan_tarifario_nombre']) && is_array($_POST['plan_tarifario_nombre'])) {
        $planes = [];
        foreach ($_POST['plan_tarifario_nombre'] as $idx => $nombre) {
            if (!empty(trim($nombre))) {
                $planes[] = [
                    'nombre' => $nombre,
                    'cancelacion' => $_POST['plan_tarifario_cancelacion'][$idx] ?? '',
                    'penalidad' => $_POST['plan_tarifario_penalidad'][$idx] ?? '',
                    'no_show' => $_POST['plan_tarifario_no_show'][$idx] ?? '',
                    'salida' => $_POST['plan_tarifario_salida_anticipada'][$idx] ?? ''
                ];
            }
        }
        // ✅ ESTA ES LA LÍNEA QUE TE FALTA PARA QUE SE INCLUYA EN EL UPDATE SQL
        $set[] = "planes_tarifarios_json=?";
        $types .= 's';
        $vals[] = json_encode($planes, JSON_UNESCAPED_UNICODE);
    }

    if (array_key_exists('regimen_alimenticio', $_POST)) {
        $arr = $_POST['regimen_alimenticio'];
        if (!is_array($arr))
            $arr = [];
        $arr = array_values(array_filter($arr, fn($v) => $v !== '__EMPTY__' && $v !== ''));
        $set[] = "regimen_alimenticio_json=?";
        $types .= 's';
        $vals[] = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }


    // especiales array->json
    if (array_key_exists('tipo_hotel', $_POST)) {
        $arr = $_POST['tipo_hotel'];
        if (!is_array($arr))
            $arr = [];
        // si viene __EMPTY__ significa “vaciar”
        $arr = array_values(array_filter($arr, fn($v) => $v !== '__EMPTY__'));
        $set[] = "tipo_hotel_json=?";
        $types .= 's';
        $vals[] = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    if (array_key_exists('mercado_distribucion', $_POST)) {
        $arr = $_POST['mercado_distribucion'];
        if (!is_array($arr))
            $arr = [];
        $arr = array_values(array_filter($arr, fn($v) => $v !== '__EMPTY__'));
        $set[] = "mercados_distribucion_json=?";
        $types .= 's';
        $vals[] = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    // ✅ tarifa_tipo[] -> tarifa_tipo_json (nuevo)
    if (array_key_exists('tarifa_tipo', $_POST)) {
        $arr = $_POST['tarifa_tipo'];
        if (!is_array($arr))
            $arr = [];
        $arr = array_values(array_filter($arr, fn($v) => $v !== '__EMPTY__'));

        $set[] = "tarifa_tipo_json=?";
        $types .= 's';
        $vals[] = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }


    
    // =========================
    // ✅ ALLOTMENT (BORRADOR) — guardar allotment_selected + allotment_json
    // =========================
    $tocaAllotment = array_key_exists('connectionType', $_POST)
        || array_key_exists('allotment_selected', $_POST)
        || array_key_exists('allotment_tipo_habitacion', $_POST)
        || array_key_exists('allotment_num_habitaciones', $_POST);

    if ($tocaAllotment) {
        $forma_conexion = $_POST['connectionType'] ?? null;
        $allotment_selected = isset($_POST['allotment_selected']) ? 1 : 0;

        $allotment_rows = [];
        if ($forma_conexion === 'extranet' && $allotment_selected === 1) {
            $tipos = $_POST['allotment_tipo_habitacion'] ?? [];
            $nums  = $_POST['allotment_num_habitaciones'] ?? [];

            if (!is_array($tipos)) $tipos = [];
            if (!is_array($nums))  $nums  = [];

            $n = min(count($tipos), count($nums));
            for ($i = 0; $i < $n; $i++) {
                $tipo = trim((string)($tipos[$i] ?? ''));
                $num  = (int)($nums[$i] ?? 0);

                // ignorar filas vacías
                if ($tipo === '' && $num === 0) continue;

                $allotment_rows[] = [
                    'tipo_habitacion' => $tipo,
                    'num_habitaciones' => $num,
                ];
            }
        }

        $set[] = "allotment_selected=?";
        $types .= 'i';
        $vals[] = $allotment_selected;

        $set[] = "allotment_json=?";
        $types .= 's';
        $vals[] = json_encode($allotment_rows, JSON_UNESCAPED_UNICODE);
    }

foreach ($generalMap as $postKey => $col) {
        if (!array_key_exists($postKey, $_POST))
            continue;

        $val = $_POST[$postKey];

        // normalizaciones
        if (
            in_array($postKey, [
                'incluye_desayuno',
                'numero_habitaciones',
                'pet_friendly',
                'habitaciones_discapacidad',
                'habitaciones_connecting',
                'salones_eventos_count',
                'centro_negocios_count',
                'espacios_externos_count',
                'tiene_certificado_sostenibilidad',
                'ciiu',
                'numero_cuenta',

                // ✅ NUEVOS: Amenidades
                'amenidad_restaurante',
                'amenidad_bar_lounge',
                'amenidad_hab_especiales',
                'amenidad_gay_friendly',
                'amenidad_planes_boda',

                // ✅ NUEVOS: Accesibilidad
                'accesibilidad_banos',
                'accesibilidad_habitaciones',
                'accesibilidad_espacios_comunes'
            ], true)
        ) {
            $val = (int) $val;
            $set[] = "$col=?";
            $types .= 'i';
            $vals[] = $val;
            continue;
        }

        $set[] = "$col=?";
        $types .= 's';
        $vals[] = $val;
    }

    // siempre actualizar wizard_step/estado
    $set[] = "estado_registro='BORRADOR'";
    $set[] = "wizard_step=?";
    $types .= 'i';
    $vals[] = $wizard_step;

    $set[] = "updated_at=NOW()";

    if (!empty($set)) {
        $sql = "UPDATE tbl_alojamiento_general SET " . implode(',', $set) . " WHERE id_hotel=?";
        $types .= 'i';
        $vals[] = $id_hotel;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        error_log("UPDATE general affected_rows=" . $affected);
        error_log("UPDATE general id_hotel=" . $id_hotel . " user_id=" . $user_id . " reutilizado=" . ($registro_reutilizado ? '1' : '0'));
        $stmt->close();
    }

    // --- 4) Contactos (si llegaron inputs de contactos) ---
    $contactos_map = [
        'Gerencia' => 'gerencia',
        'Comercial' => 'comercial',
        'Reservas' => 'reservas',
        'Grupos' => 'grupos',
        'Pagos' => 'pagos',
        'Reclamaciones' => 'reclamaciones',
        'Extranet' => 'extranet'
    ];

    $tocoContactos = false;
    foreach ($contactos_map as $tipo => $prefix) {
        if (
            array_key_exists("contacto_$prefix", $_POST) ||
            array_key_exists("movil_$prefix", $_POST) ||
            array_key_exists("email_$prefix", $_POST) ||
            array_key_exists("telefono_$prefix", $_POST)
        ) {
            $tocoContactos = true;
            break;
        }
    }

    if ($tocoContactos) {
        foreach ($contactos_map as $tipo => $prefix) {
            $nombre = $_POST["contacto_$prefix"] ?? null;
            $movil = $_POST["movil_$prefix"] ?? null;
            $email = $_POST["email_$prefix"] ?? null;
            $telefono = $_POST["telefono_$prefix"] ?? null;

            // si no hay nada, borramos ese contacto si existía
            $stmt_del = $conn->prepare("DELETE FROM tbl_alojamiento_contactos WHERE id_hotel=? AND tipo_contacto=?");
            $stmt_del->bind_param("is", $id_hotel, $tipo);
            $stmt_del->execute();
            $stmt_del->close();

            if ($nombre === null && $movil === null && $email === null && $telefono === null) {
                continue;
            }

            $stmt_ins = $conn->prepare("INSERT INTO tbl_alojamiento_contactos (id_hotel, tipo_contacto, nombre, movil, email, telefono) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("isssss", $id_hotel, $tipo, $nombre, $movil, $email, $telefono);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
    }

    // --- 5) Servicios (si llegaron checkboxes de servicios) ---
    $servMap = [
        'recepcion_24_hrs' => 'recepcion_24_hrs',
        'parqueadero' => 'parqueadero',
        'minibar' => 'minibar',
        'con_cocina' => 'con_cocina',
        'cafetera_cortesia' => 'cafetera_cortesia',
        'ventilador_techo' => 'ventilador_techo',
        'servicio_habitacion' => 'servicio_habitacion',
        'servicio_habitacion_24_hrs' => 'servicio_habitacion_24_hrs',
        'transfer_aero_htl_aero' => 'transfer_aero_htl',
        'aire_acondicionado_hotel' => 'aire_acondicionado',
        'turco' => 'turco',
        'servicio_lavanderia' => 'servicio_lavanderia',
        'transfer_htl_playa_htl' => 'transfer_htl_playa',
        'lobby_lounge' => 'lobby_lounge',
        'bar' => 'bar',
        'guarda_equipaje' => 'guarda_equipaje',
        'asoleadoras' => 'asoleadoras',
        'terraza' => 'terraza',
        'bar_en_la_piscina' => 'bar_piscina',
        'servicio_ninera' => 'servicios_ninera',
        'muelle_privado' => 'muelle_privado',
        'cafe_bar' => 'cafe_bar',
        'concierge' => 'concierge',
        'tienda_regalos' => 'super_minimercado',
        'sendero_ecologico' => 'sendero_ecologico',
        'discoteca' => 'discoteca',
        'playa' => 'playa',
        'alquiler_bicicletas' => 'alquiler_bicicletas',
        'mini_golf' => 'mini_golf',
        'bebidas_recepcion' => 'cafe_recepcion',
        'piscina' => 'piscina',
        'cajero_automatico' => 'cajero_automatico',
        'snack_bar' => 'snack_bar',
        'capilla' => 'capilla',
        'piscina_infantil' => 'piscina_infantil',
        'cambio_moneda' => 'cambio_moneda',
        'salon_fitness' => 'salon_fitness',
        'club_ninos' => 'club_ninos',
        'pesca' => 'pesca',
        'servicio_medico' => 'enfermeria_medico',
        'zona_juegos_infantiles' => 'zona_juegos_infantiles',
        'sala_masajes' => 'sala_masajes',
        'salon_juegos' => 'salon_juegos',
        'personal_bilingue' => 'personal_bilingue',
        'sauna' => 'sauna',
        'salon_belleza' => 'salon_belleza',
        'gimnasio_general' => 'gimnasio',
        'juegos_mesa' => 'juegos_mesa',
        'ascensor' => 'ascensor',
        'toallas_playa_piscina' => 'toallas_playa_piscina',
        'jacuzzi' => 'jacuzzi',
        'wifi' => 'internet_wifi',
        'cable' => 'internet_cable',
        'wifi_zonas_comunes' => 'wifi_zonas_comunes',
        'casino' => 'Casino',
        'lobby_sala_espera' => 'lobby_sala_espera',
        'spa' => 'spa',
        'cobertura_internet' => 'cobertura_internet',
        'canal_dedicado' => 'canal_dedicado',
        'otroservicio' => 'otro_servicio'
    ];

    $tocoServicios = false;
    foreach ($servMap as $k => $col) {
        if (array_key_exists($k, $_POST)) {
            $tocoServicios = true;
            break;
        }
    }
    if (array_key_exists('agua_caliente_habitaciones', $_POST))
        $tocoServicios = true;

    if ($tocoServicios) {
        // existe fila?
        $exists = 0;
        $stmt = $conn->prepare("SELECT 1 FROM tbl_alojamiento_servicios WHERE id_hotel=? LIMIT 1");
        $stmt->bind_param("i", $id_hotel);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows ? 1 : 0;
        $stmt->close();

        if (!$exists) {
            $stmt = $conn->prepare("INSERT INTO tbl_alojamiento_servicios (id_hotel) VALUES (?)");
            $stmt->bind_param("i", $id_hotel);
            $stmt->execute();
            $stmt->close();
        }

        $setS = [];
        $typesS = '';
        $valsS = [];

        foreach ($servMap as $k => $col) {
            if (!array_key_exists($k, $_POST))
                continue;
            if (!pnvColExists($conn, 'tbl_alojamiento_servicios', $col))
                continue;

            // 1. Campos que son TEXTO (Strings)
            $camposTexto = ['wifi_zonas_comunes', 'wifi', 'cable', 'canal_dedicado', 'otroservicio', 'cobertura_internet'];

            if (in_array($k, $camposTexto)) {
                $setS[] = "$col=?";
                $typesS .= 's'; // Cambiamos a 's' para que MySQL reciba el texto

                if ($k === 'cobertura_internet') {
                    $arr = $_POST['cobertura_internet'] ?? [];
                    if (!is_array($arr)) {
                        $arr = [$arr];
                    }
                    $arr = array_values(array_filter(array_map('strval', $arr), fn($x) => $x !== ''));
                    $valsS[] = json_encode($arr, JSON_UNESCAPED_UNICODE);
                } else {
                    // Aquí caerán wifi_zonas_comunes, wifi, cable y canal_dedicado
                    // Guardará directamente "Gratis", "Con costo", etc.
                    $valsS[] = $_POST[$k];
                }
            }
            // 2. Todos los demás campos que siguen siendo booleanos (Sí/No)
            else {
                $setS[] = "$col=?";
                $typesS .= 'i';
                $valsS[] = (int) $_POST[$k];
            }
        }

        if (array_key_exists('agua_caliente_habitaciones', $_POST)) {
            $setS[] = "agua_caliente_hab=?";
            $typesS .= 'i';
            $valsS[] = ($_POST['agua_caliente_habitaciones'] === 'si') ? 1 : 0;
        }

        if (!empty($setS)) {
            $sqlS = "UPDATE tbl_alojamiento_servicios SET " . implode(',', $setS) . " WHERE id_hotel=?";
            $typesS .= 'i';
            $valsS[] = $id_hotel;

            $stmt = $conn->prepare($sqlS);
            $stmt->bind_param($typesS, ...$valsS);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 6) Habitaciones (si llegaron arrays) ---
    if (isset($_POST['tipo_habitacion']) && is_array($_POST['tipo_habitacion'])) {
        $conn->query("DELETE FROM tbl_alojamiento_habitaciones WHERE id_hotel=" . (int) $id_hotel);

        $sql = "INSERT INTO tbl_alojamiento_habitaciones
            (id_hotel, tipo_habitacion, total_habitaciones, max_adultos, max_ninos, max_total, mts2,
             cama_sencilla, cama_doble, cama_queen, cama_king, camarote_sencillo, camarote_doble, camas_adicionales,
             observaciones, servicios_gen_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $n = count($_POST['tipo_habitacion']);
        for ($i = 0; $i < $n; $i++) {
            $tipo = $_POST['tipo_habitacion'][$i] ?? null;
            $total = (int) ($_POST['total_habitaciones'][$i] ?? 0);
            $ma = (int) ($_POST['max_adultos'][$i] ?? 0);
            $mn = (int) ($_POST['max_ninos'][$i] ?? 0);
            $mt = (int) ($_POST['max_total'][$i] ?? 0);
            $mts2 = (float) ($_POST['mts2'][$i] ?? 0);
            $cs = (int) ($_POST['cama_sencilla'][$i] ?? 0);
            $cd = (int) ($_POST['cama_doble'][$i] ?? 0);
            $cq = (int) ($_POST['cama_queen'][$i] ?? 0);
            $ck = (int) ($_POST['cama_king'][$i] ?? 0);
            $cas = (int) ($_POST['camarote_sencillo'][$i] ?? 0);
            $cad = (int) ($_POST['camarote_doble'][$i] ?? 0);
            $ca = (int) ($_POST['camas_adicionales'][$i] ?? 0);
            $obs = $_POST['observaciones_hab'][$i] ?? null;
            $srv = $_POST['habitacion_servicios'][$i] ?? null;

            $stmt->bind_param("isiiiiidiiiiisss", $id_hotel, $tipo, $total, $ma, $mn, $mt, $mts2, $cs, $cd, $cq, $ck, $cas, $cad, $ca, $obs, $srv);
            $stmt->execute();
        }
        $stmt->close();
    }

    // --- 7) Salones (si llegaron arrays) ---
    if (isset($_POST['nombre_salon']) && is_array($_POST['nombre_salon'])) {
        $conn->query("DELETE FROM tbl_alojamiento_salones WHERE id_hotel=" . (int) $id_hotel);

        $sql = "INSERT INTO tbl_alojamiento_salones
            (id_hotel, nombre_salon, m2, largo, ancho, alto, cap_u_herradura, cap_aula, cap_auditorio, cap_banquete, cap_imperial, cap_coctel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $n = count($_POST['nombre_salon']);
        for ($i = 0; $i < $n; $i++) {
            $nom = $_POST['nombre_salon'][$i] ?? null;
            $m2 = (float) ($_POST['m2_salon'][$i] ?? 0);
            $l = (float) ($_POST['largo_salon'][$i] ?? 0);
            $a = (float) ($_POST['ancho_salon'][$i] ?? 0);
            $h = (float) ($_POST['alto_salon'][$i] ?? 0);
            $uh = (int) ($_POST['cap_u_herradura'][$i] ?? 0);
            $au = (int) ($_POST['cap_aula'][$i] ?? 0);
            $ad = (int) ($_POST['cap_auditorio'][$i] ?? 0);
            $ba = (int) ($_POST['cap_banquete'][$i] ?? 0);
            $im = (int) ($_POST['cap_imperial'][$i] ?? 0);
            $co = (int) ($_POST['cap_coctel'][$i] ?? 0);

            $stmt->bind_param("isddddiiiiii", $id_hotel, $nom, $m2, $l, $a, $h, $uh, $au, $ad, $ba, $im, $co);
            $stmt->execute();
        }
        $stmt->close();
    }

    echo json_encode([
        'ok' => true,
        'id_hotel' => (int) $id_hotel,
        'debug_file' => __FILE__,
        'debug_channelName' => $_POST['channelName'] ?? 'NO_LLEGA',
        'debug_connectionType' => $_POST['connectionType'] ?? 'NO_LLEGA',
        'registro_reutilizado' => (bool) $registro_reutilizado,
        'nit_consecutivo' => $_POST['nit_consecutivo'] ?? null,
    ]);


} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
