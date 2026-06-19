<?php
// ==================== BOOT / INCLUDES ====================
include_once "../../facturacion/config/seguridad.php";
include_once "../../facturacion/config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== FUNCION WEBHOOK ZOHO ====================

function enviarDatosWebhook($url, array $datos, $debug = false)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    error_log("ZOHO RESPONSE: " . $response);
    error_log("ZOHO HTTP CODE: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => $error, 'response' => null];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['ok' => true, 'error' => null, 'response' => $debug ? $response : null];
    }

    return [
        'ok'       => false,
        'error'    => "HTTP $httpCode",
        'response' => $debug ? $response : null
    ];
}

// ==================== HELPER FUNCTIONS ====================

/** Convierte valores con guion bajo a texto legible: 'solo_adultos' -> 'Solo Adultos' */
function limpiarValor($valor) {
    return ucwords(str_replace('_', ' ', $valor));
}

/** Aplica limpiarValor() a todos los elementos de un array */
function limpiarArray(array $arr) {
    return array_map('limpiarValor', $arr);
}

function convertirSiNo($valor) {
    if ($valor === null || $valor === '' || $valor === 0 || $valor === '0') {
        return '';
    }
    if ($valor == 1) return true;
    if ($valor == 2) return false;
    return '';
}

function convertirValorServicio($valor) {
    if ($valor === null || $valor === '' || $valor === 0 || $valor === '0') {
        return '';
    }
    if ($valor == 1) return 'Si';
    if ($valor == 2) return 'No';
    if ($valor == 3) return 'Si, Con Costo';
    // Para valores especiales: wifi="Gratis", ascensor="Directo Al Piso", etc.
    return (string)$valor;
}

// ==================== FUNCIÓN PRINCIPAL ====================

function construirYEnviarZoho($id_hotel, $conn, $debug = false, $accion = 'registrar') {

// ==================== CONSULTAS A LA BD ====================

// -- Consulta CRÍTICA: datos del hotel + contactos (si falla, abortamos) --
try {
    $sql_hotel = "
        SELECT 
            g.*,
            s.*,
            c.id_contacto,
            c.tipo_contacto,
            c.nombre      AS contacto_nombre,
            c.movil       AS contacto_movil,
            c.email       AS contacto_email,
            c.telefono    AS contacto_telefono
        FROM tbl_alojamiento_general g
        LEFT JOIN tbl_alojamiento_servicios s ON s.id_hotel = g.id_hotel
        LEFT JOIN tbl_alojamiento_contactos c ON c.id_hotel = g.id_hotel
        WHERE g.id_hotel = ?
        ORDER BY c.id_contacto ASC
    ";
    $stmt = $conn->prepare($sql_hotel);
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $res  = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($rows)) {
        throw new Exception("No se encontró el hotel con ID {$id_hotel}");
    }
} catch (Exception $e) {
    return ['ok' => false, 'error' => $e->getMessage(), 'payload' => null];
}

// Separar datos del hotel (primera fila) y recopilar todos los contactos
$hotel     = $rows[0];
$contactos = [];
foreach ($rows as $row) {
    if (!empty($row['id_contacto'])) {
        $contactos[] = [
            'tipo'     => $row['tipo_contacto'],
            'nombre'   => $row['contacto_nombre'],
            'email'    => $row['contacto_email'],
            'movil'    => $row['contacto_movil'],
            'telefono' => $row['contacto_telefono'],
        ];
    }
}

// Contacto comercial principal
$contacto = null;
foreach ($contactos as $c) {
    if (strtolower($c['tipo']) === 'comercial' && !empty($c['email'])) {
        $contacto = $c;
        break;
    }
}
if (!$contacto && !empty($contactos)) {
    $contacto = $contactos[0];
}

// -- Consulta OPCIONAL: tbl_proveedores --
$proveedor = null;
try {
    $stmt_p = $conn->prepare("SELECT tipo_proveedor, email_contabilidad, email_cartera, tipo_proveedor_hotel FROM tbl_proveedores WHERE nit_identificacion = ? LIMIT 1");
    if ($stmt_p) {
        $stmt_p->bind_param("s", $hotel['nit']);
        $stmt_p->execute();
        $proveedor = $stmt_p->get_result()->fetch_assoc();
        $stmt_p->close();
    }
} catch (Exception $e) {
    error_log("[ZOHO] tbl_proveedores query failed: " . $e->getMessage());
}

// -- Consulta OPCIONAL: tbl_usuarios --
$usuario_data = null;
try {
    $stmt_u = $conn->prepare("SELECT id_rol, nombre AS nombre_usuario FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
    if ($stmt_u) {
        $stmt_u->bind_param("s", $hotel['usuario_creacion']);
        $stmt_u->execute();
        $usuario_data = $stmt_u->get_result()->fetch_assoc();
        $stmt_u->close();
    }
} catch (Exception $e) {
    error_log("[ZOHO] tbl_usuarios query failed: " . $e->getMessage());
}

// -- Consulta OPCIONAL: documentos --
$documentos_rows = [];
$docs = [
    'rut' => null,
    'registro_turismo' => null,
    'rnt' => null,
    'camara_comercio' => null,
    'certificacion_bancaria' => null,
    'certificados_sostenibilidad' => null,
    'certificado_bomberos' => null,
    'informacion_credito' => null,
    'concepto_sanitario' => null,
    'mantenimiento_piscinas' => null,
    'mantenimiento_ascensores' => null,
    'sg_sst' => null,
    'arl' => null
];
if (!function_exists('normalizarTipoDocZoho')) {
function normalizarTipoDocZoho($txt) {
    $txt = strtolower(trim((string)$txt));
    $txt = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $txt);
    $txt = preg_replace('/[^a-z0-9\s\-]/', ' ', $txt);
    return preg_replace('/\s+/', ' ', $txt);
}}
if (!function_exists('detectarClaveDocZoho')) {
function detectarClaveDocZoho($tipo) {
    $t = normalizarTipoDocZoho($tipo);

    if (strpos($t, 'rut') !== false) return 'rut';
    if (strpos($t, 'rnt') !== false || strpos($t, 'registro turismo') !== false || strpos($t, 'registro nacional') !== false) return 'rnt';
    if (strpos($t, 'camara') !== false && strpos($t, 'comercio') !== false) return 'camara_comercio';
    if (strpos($t, 'certificacion') !== false && strpos($t, 'bancaria') !== false) return 'certificacion_bancaria';
    if (strpos($t, 'sostenibilidad') !== false) return 'certificados_sostenibilidad';
    if (strpos($t, 'bombero') !== false) return 'certificado_bomberos';
    if (strpos($t, 'credito') !== false) return 'informacion_credito';
    if (strpos($t, 'sanitario') !== false) return 'concepto_sanitario';
    if (strpos($t, 'piscina') !== false) return 'mantenimiento_piscinas';
    if (strpos($t, 'ascensor') !== false) return 'mantenimiento_ascensores';
    if (strpos($t, 'sg') !== false && strpos($t, 'sst') !== false) return 'sg_sst';
    if (strpos($t, 'arl') !== false) return 'arl';

    return null;
}}

if (!function_exists('fechaDocZoho')) {
function fechaDocZoho($doc) {
    if (!$doc) return null;

    if (!empty($doc['fecha_vigencia'])) {
        return $doc['fecha_vigencia'];
    }

    if (!empty($doc['fecha_vencimiento'])) {
        return $doc['fecha_vencimiento'];
    }

    if (!empty($doc['validacion_ia_json'])) {
        $json = json_decode($doc['validacion_ia_json'], true);
        if (!empty($json['best_validity_date'])) return $json['best_validity_date'];
        if (!empty($json['generation_date'])) return $json['generation_date'];
        if (!empty($json['issue_date'])) return $json['issue_date'];
        if (!empty($json['expiration_date'])) return $json['expiration_date'];
        if (!empty($json['valid_until'])) return $json['valid_until'];
    }

    return null;
}}

try {
    $stmt_d = $conn->prepare("
        SELECT 
            tipo_documento,
            nombre_archivo,
            ruta_almacenamiento,
            fecha_vigencia,
            estado_vigencia,
            dias_vencimiento,
            fuente_vigencia,
            validacion_ia_json
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
        ORDER BY id_doc DESC
    ");

    if ($stmt_d) {
        $stmt_d->bind_param("i", $id_hotel);
        $stmt_d->execute();
        $documentos_rows = $stmt_d->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_d->close();

        foreach ($documentos_rows as $doc) {
            $key = detectarClaveDocZoho($doc['tipo_documento']);

            if ($key !== null && $docs[$key] === null) {
                $docs[$key] = $doc;

                if ($key === 'rnt') {
                    $docs['registro_turismo'] = $doc;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("[ZOHO] tbl_alojamiento_documentos query failed: " . $e->getMessage());
}

// -- Consulta OPCIONAL: habitaciones --
$hab_rows = [];
try {
    $stmt_h = $conn->prepare("SELECT servicios_gen_json FROM tbl_alojamiento_habitaciones WHERE id_hotel = ?");
    if ($stmt_h) {
        $stmt_h->bind_param("i", $id_hotel);
        $stmt_h->execute();
        $hab_rows = $stmt_h->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_h->close();
    }
} catch (Exception $e) {
    error_log("[ZOHO] tbl_alojamiento_habitaciones query failed: " . $e->getMessage());
}

// ==================== ARMAR PAYLOAD PARA ZOHO ====================

// Extraer todos los campos del hotel

$nombre_hotel    = $hotel['nombre'] ?? '';
$nit             = $hotel['nit'] ?? '';
$nit_consecutivo = $hotel['nit_consecutivo'] ?? '';
if (empty($nit_consecutivo)) {
    $nit_consecutivo = $nit;
}

$razon_social    = $hotel['razon_social'] ?? '';
$direccion       = $hotel['direccion'] ?? '';
$telefono        = $hotel['telefono'] ?? '';
$ciudad          = $hotel['ciudad'] ?? '';
$pais            = $hotel['pais'] ?? '';
$categoria_raw   = $hotel['categoria'] ?? '';
$categoria       = !empty($categoria_raw) ? [$categoria_raw] : [];
$habitaciones    = (string)($hotel['numero_habitaciones'] ?? '');
$sitio_web       = $hotel['website'] ?? '';
$cuenta_bancaria = $hotel['numero_cuenta'] ?? '';
$ciiu            = $hotel['ciiu'] ?? '';
// cadena_hotelera se resuelve después de extraer los datos del usuario (ver abajo)

// Extraer TODOS los campos adicionales del formulario de la BD
$descripcion_producto = $hotel['descripcion_producto'] ?? '';
$incluye_desayuno = convertirSiNo($hotel['incluye_desayuno'] ?? '');
$precio_desayuno = $hotel['precio_desayuno'] ?? '';
$tipo_desayuno_raw = $hotel['tipo_desayuno'] ?? '';
$tipo_desayuno = !empty($tipo_desayuno_raw) ? [limpiarValor($tipo_desayuno_raw)] : [];
$hora_check_in_raw  = $hotel['hora_check_in'] ?? '';
$hora_check_in      = !empty($hora_check_in_raw) ? [$hora_check_in_raw] : [];
$hora_check_out_raw = $hotel['hora_check_out'] ?? '';
$hora_check_out     = !empty($hora_check_out_raw) ? [$hora_check_out_raw] : [];
$pet_friendly = convertirSiNo($hotel['pet_friendly'] ?? '');
$politica_mascotas = $hotel['politica_mascotas'] ?? '';
$habitaciones_discapacidad = $hotel['habitaciones_discapacidad'] ?? '';
$habitaciones_connecting = $hotel['habitaciones_connecting'] ?? '';
$informacion_adicional = $hotel['informacion_adicional'] ?? '';
$tipo_contribuyente = $hotel['tipo_contribuyente'] ?? '';
// Amenidades (checkboxes 0/1 → true/false booleanos)
$amenidad_restaurante = ($hotel['amenidad_restaurante'] ?? 0) ? true : false;
$amenidad_hab_especiales = ($hotel['amenidad_hab_especiales'] ?? 0) ? true : false;
$amenidad_gay_friendly = ($hotel['amenidad_gay_friendly'] ?? 0) ? true : false;
$amenidad_planes_boda = ($hotel['amenidad_planes_boda'] ?? 0) ? true : false;

// Régimen alimenticio (JSON array en BD → array para Zoho)
$regimen_alimenticio_raw = $hotel['regimen_alimenticio_json'] ?? '';
$regimen_alimenticio_arr = !empty($regimen_alimenticio_raw) ? json_decode($regimen_alimenticio_raw, true) : [];
$regimen_alimenticio = !empty($regimen_alimenticio_arr) ? $regimen_alimenticio_arr : [];

// Accesibilidad (checkboxes 0/1 → true/false booleanos)
$accesibilidad_banos = ($hotel['accesibilidad_banos'] ?? 0) ? true : false;
$accesibilidad_habitaciones = ($hotel['accesibilidad_habitaciones'] ?? 0) ? true : false;
$accesibilidad_espacios_comunes = ($hotel['accesibilidad_espacios_comunes'] ?? 0) ? true : false;

// Accesibilidad como texto separado por comas para Zoho CRM
$accesibilidad = implode(', ', array_filter([
    $accesibilidad_banos            ? 'Baños accesibles'            : null,
    $accesibilidad_habitaciones     ? 'Habitaciones accesibles'     : null,
    $accesibilidad_espacios_comunes ? 'Espacios comunes accesibles' : null,
]));

// Tipo de hotel (JSON en BD)
$tipo_hotel_raw = $hotel['tipo_hotel_json'] ?? '';
$tipo_hotel = !empty($tipo_hotel_raw) ? limpiarArray(json_decode($tipo_hotel_raw, true)) : [];

// Servicios con SOLO Sí/No → true/false
// NOTA: nombres de columna en tbl_alojamiento_servicios pueden diferir del nombre del formulario
$transfer_aero_htl_aero_raw = convertirValorServicio($hotel['transfer_aero_htl'] ?? '');
$transfer_aero_htl_aero = !empty($transfer_aero_htl_aero_raw) ? [$transfer_aero_htl_aero_raw] : [];
$aire_acondicionado_hotel = convertirSiNo($hotel['aire_acondicionado'] ?? '');
$turco = convertirValorServicio($hotel['turco'] ?? '');
$parqueadero = convertirValorServicio($hotel['parqueadero'] ?? '');
$minibar = convertirSiNo($hotel['minibar'] ?? '');
$con_cocina = convertirSiNo($hotel['con_cocina'] ?? '');
$cafetera_cortesia = convertirSiNo($hotel['cafetera_cortesia'] ?? '');
$ventilador_techo = convertirSiNo($hotel['ventilador_techo'] ?? '');
$servicio_habitacion_propiedad = convertirSiNo($hotel['servicio_habitacion'] ?? '');
$servicio_habitacion_24_hrs = convertirSiNo($hotel['servicio_habitacion_24_hrs'] ?? '');
$lobby_lounge = convertirSiNo($hotel['lobby_lounge'] ?? '');
$bar = convertirValorServicio($hotel['bar'] ?? '');
$terraza = convertirSiNo($hotel['terraza'] ?? '');
$bar_en_la_piscina = convertirSiNo($hotel['bar_piscina'] ?? '');
$muelle_privado = convertirSiNo($hotel['muelle_privado'] ?? '');
$cafe_bar = convertirSiNo($hotel['cafe_bar'] ?? '');
$concierge = convertirSiNo($hotel['concierge'] ?? '');
$tienda_regalos = convertirSiNo($hotel['super_minimercado'] ?? '');
$sendero_ecologico = convertirSiNo($hotel['sendero_ecologico'] ?? '');
$discoteca = convertirSiNo($hotel['discoteca'] ?? '');
$playa = convertirValorServicio($hotel['playa'] ?? '');
$mini_golf = convertirSiNo($hotel['mini_golf'] ?? '');
$bebidas_recepcion = convertirSiNo($hotel['cafe_recepcion'] ?? '');
$piscina = convertirSiNo($hotel['piscina'] ?? '');
$cajero_automatico = convertirSiNo($hotel['cajero_automatico'] ?? '');
$snack_bar = convertirSiNo($hotel['snack_bar'] ?? '');
$capilla = convertirSiNo($hotel['capilla'] ?? '');
$piscina_infantil = convertirSiNo($hotel['piscina_infantil'] ?? '');
$cambio_moneda = convertirSiNo($hotel['cambio_moneda'] ?? '');
$salon_fitness = convertirSiNo($hotel['salon_fitness'] ?? '');
$pesca = convertirSiNo($hotel['pesca'] ?? '');
$servicio_medico = convertirSiNo($hotel['enfermeria_medico'] ?? '');
$sala_masajes = convertirSiNo($hotel['sala_masajes'] ?? '');
$salon_juegos = convertirSiNo($hotel['salon_juegos'] ?? '');
$personal_bilingue = convertirSiNo($hotel['personal_bilingue'] ?? '');
$sauna = convertirSiNo($hotel['sauna'] ?? '');
$salon_belleza = convertirSiNo($hotel['salon_belleza'] ?? '');
$casino = convertirSiNo($hotel['casino'] ?? '');
$lobby_sala_espera = convertirSiNo($hotel['lobby_sala_espera'] ?? '');
$spa = convertirSiNo($hotel['spa'] ?? '');
$jacuzzi = convertirSiNo($hotel['jacuzzi'] ?? '');

// Servicios con Sí/No/Con Costo → string
$recepcion_24_hrs = convertirValorServicio($hotel['recepcion_24_hrs'] ?? '');
$servicio_lavanderia = convertirValorServicio($hotel['servicio_lavanderia'] ?? '');
$transfer_htl_playa_htl = convertirValorServicio($hotel['transfer_htl_playa'] ?? '');
$guarda_equipaje = convertirValorServicio($hotel['guarda_equipaje'] ?? '');
$asoleadoras = convertirValorServicio($hotel['asoleadoras'] ?? '');
$alquiler_bicicletas = convertirValorServicio($hotel['alquiler_bicicletas'] ?? '');
$zona_juegos_infantiles = convertirValorServicio($hotel['zona_juegos_infantiles'] ?? '');
$toallas_playa_piscina = convertirValorServicio($hotel['toallas_playa_piscina'] ?? '');

// Servicios adicionales con opciones especiales
// gimnasio en BD = gimnasio_general del formulario (Sí / No / "Sí, Abierto las 24 horas")
$gimnasio_val = $hotel['gimnasio'] ?? '';
if ($gimnasio_val == 1) {
    $gimnasio_general = 'Si';
    $gimnasio = true;
} elseif ($gimnasio_val == 2) {
    $gimnasio_general = 'No';
    $gimnasio = false;
} elseif ($gimnasio_val == 3) {
    $gimnasio_general = 'Sí, Abierto las 24 horas';
    $gimnasio = 'Sí, Abierto las 24 horas';
} else {
    $gimnasio_general = '';
    $gimnasio = '';
}

$ascensor_val = $hotel['ascensor'] ?? '';
if ($ascensor_val == 1) {
    $ascensor = 'Directo Al Piso';
} elseif ($ascensor_val == 2) {
    $ascensor = 'Interpiso';
} else {
    $ascensor = '';
}

$juegos_mesa = convertirSiNo($hotel['juegos_mesa'] ?? '');
$otroservicio = $hotel['otro_servicio'] ?? '';
$agua_caliente_habitaciones = convertirSiNo($hotel['agua_caliente_hab'] ?? '');

// Internet (valores string: "Gratis", "Con costo", "No hay internet")
$wifi = $hotel['internet_wifi'] ?? '';
$cable = $hotel['internet_cable'] ?? '';
$canal_dedicado = $hotel['canal_dedicado'] ?? '';
$wifi_zonas_comunes = $hotel['wifi_zonas_comunes'] ?? '';
$cobertura_internet_raw = $hotel['cobertura_internet'] ?? '';
$cobertura_internet = !empty($cobertura_internet_raw) ? limpiarArray(json_decode($cobertura_internet_raw, true)) : [];

// Nuevos campos: condiciones de negociación
$salones_eventos = $hotel['salones_eventos_count'] ?? '';
$valor_credito_aprobado = $hotel['monto_credito'] ?? '';
$tiempo_credito_raw = $hotel['tiempo_credito'] ?? '';
$tiempo_credito = ($tiempo_credito_raw !== '' && $tiempo_credito_raw !== null) ? (int)$tiempo_credito_raw : null;
$forma_conexion = $hotel['forma_conexion'] ?? '';
$channel_manager = $hotel['channel_manager_nombre'] ?? '';
$descuento_dinamico_val = $hotel['descuento_dinamico'] ?? '';
$tarifa_tipo_raw = $hotel['tarifa_tipo_json'] ?? '';
$tarifa_tipo = ($forma_conexion === 'extranet')
    ? ['Extranet']
    : (!empty($tarifa_tipo_raw) ? json_decode($tarifa_tipo_raw, true) : []);

// Conexión por tipo de tarifa: mismo picklist que el channel manager en CRM
$tarifa_tipo_vals = is_array($tarifa_tipo) ? $tarifa_tipo : [];
$tiene_fit      = in_array('FIT',      $tarifa_tipo_vals) || in_array('Ambos', $tarifa_tipo_vals);
$tiene_dinamica = in_array('Dinamicas', $tarifa_tipo_vals) || in_array('Ambos', $tarifa_tipo_vals);
$conexion_tarifas_fit      = ($tiene_fit && $channel_manager)      ? $channel_manager : null;
$conexion_tarifas_dinamicas = ($tiene_dinamica && $channel_manager) ? $channel_manager : null;

// Manejo de extranet: 'Sí' si la forma de conexión es extranet, de lo contrario 'No'
$manejo_extranet = ($forma_conexion === 'extranet') ? 'Sí' : 'No';
$diligencia_nombre = $hotel['diligencia_nombre'] ?? '';
$diligencia_correo = $hotel['diligencia_correo'] ?? '';
$diligencia_cargo = $hotel['diligencia_cargo'] ?? '';

// Servicio a la habitación: verificar en los servicios de habitaciones
// El JSON tiene estructura {"servicios": [...], "obs": "..."}, hay que acceder a la clave 'servicios'
$servicio_habitacion_habitaciones = false;
foreach ($hab_rows as $hab) {
    $decoded_hab = !empty($hab['servicios_gen_json']) ? json_decode($hab['servicios_gen_json'], true) : [];
    $servicios_hab = $decoded_hab['servicios'] ?? (is_array($decoded_hab) ? $decoded_hab : []);
    if (in_array('Servicio a la habitación', $servicios_hab)) {
        $servicio_habitacion_habitaciones = true;
        break;
    }
}
$servicio_habitacion = $servicio_habitacion_propiedad !== '' ? $servicio_habitacion_propiedad : $servicio_habitacion_habitaciones;

// Material fotográfico: true si tiene al menos una foto subida
$tiene_fotos = false;
$foto_tipos_check = ['foto promocional', 'foto fachada', 'foto habitaciones', 'foto piscina', 'foto zona comun'];
foreach ($documentos_rows as $doc) {
    if (in_array(strtolower($doc['tipo_documento']), $foto_tipos_check)) {
        $tiene_fotos = true;
        break;
    }
}

// Datos del proveedor
$tipo_proveedor = 'Hoteles'; // Siempre 'Hoteles' para el formulario de alojamiento
$email_contabilidad = $proveedor['email_contabilidad'] ?? '';
$email_cartera = $proveedor['email_cartera'] ?? '';

// Datos del usuario
$id_rol = $usuario_data['id_rol'] ?? null;
$nombre_usuario_logueado = $usuario_data['nombre_usuario'] ?? '';
$usuario_creacion = $hotel['usuario_creacion'] ?? '';
$juniper_id = $hotel['juniper_id'] ?? '';

// Cada hotel registrado es siempre de tipo 'Hotel'.
// Si lo registra una cadena hotelera (rol 7), el hotel sigue siendo 'Hotel';
// la identidad de la cadena queda en cadena_hotelera.
$tipo_proveedor_hotel = $proveedor['tipo_proveedor_hotel'] ?? '';

// cadena_hotelera: se envía el NIT del usuario creador (rol 7 = cadena).
// El lookup del ID en Zoho CRM lo realiza la función Deluge del Flow.
$cadena_hotelera = ($id_rol == 7 && $usuario_creacion !== '') ? $usuario_creacion : null;

// Organizar contactos por tipo
$contactosByTipo = [];
foreach ($contactos as $c) {
    $tipo = strtolower($c['tipo']);
    $contactosByTipo[$tipo] = $c;
}

// URL de la ficha
$link_ficha = "https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=" . $id_hotel;

// URL del webhook de Zoho
$ZOHO_WEBHOOK_URL = 'https://flow.zoho.com/793286638/flow/webhook/incoming?zapikey=1001.6e66ad276f5f6a2ad4061c73059dbd72.2f489c941916bebe10fb8ac9276e1190&isdebug=false';

// Payload completo
$payload = [
    // Estado para actualizar o no en zoho CRM: 'Aprobado', 'Rechazado', 'Pendiente'    
    'estado_aprobacion' => $hotel['estado_aprobacion'] ?? '',
    'estado_firma'      => $hotel['estado_firma'] ?? '',

    // ========== INFORMACIÓN GENERAL ==========
    'hotel_id' => $id_hotel,
    'nombre' => $nombre_hotel,
    // Grupo_hotelero_PNV: lookup field — se envía solo cuando se obtuvo el ID de Zoho CRM
    'cadena_hotelera' => $cadena_hotelera,
    'nit' => $nit,
    'nit_consecutivo' => $nit_consecutivo,
    'razon_social' => $razon_social,
    'telefono' => $telefono,
    'direccion' => $direccion,
    'ciudad' => $ciudad,
    'pais' => $pais,
    'website' => $sitio_web,
    'categoria' => $categoria,
    'numero_habitaciones' => $habitaciones,
    'numero_cuenta' => $cuenta_bancaria,
    'ciiu' => $ciiu,
    // ========== DESCRIPCIÓN Y TARIFAS ==========
    'descripcion_producto' => $descripcion_producto,
    'incluye_desayuno' => $incluye_desayuno,
    'precio_desayuno' => $precio_desayuno,
    'tipo_desayuno' => $tipo_desayuno,
    'hora_check_in' => $hora_check_in,
    'hora_check_out' => $hora_check_out,
    'tipo_contribuyente' => $tipo_contribuyente,
    // ========== AMENIDADES ==========
    'amenidad_restaurante' => $amenidad_restaurante,
    'amenidad_hab_especiales' => $amenidad_hab_especiales,
    'amenidad_gay_friendly' => $amenidad_gay_friendly,
    'amenidad_planes_boda' => $amenidad_planes_boda,
    'regimen_alimenticio' => $regimen_alimenticio,
    
    // ========== POLÍTICA DE MASCOTAS ==========
    'pet_friendly' => $pet_friendly,
    'politica_mascotas' => $politica_mascotas,
    
    // ========== ACCESIBILIDAD ==========
    'accesibilidad' => $accesibilidad,
    'accesibilidad_banos' => $accesibilidad_banos,
    'accesibilidad_habitaciones' => $accesibilidad_habitaciones,
    'accesibilidad_espacios_comunes' => $accesibilidad_espacios_comunes,
    'habitaciones_discapacidad' => $habitaciones_discapacidad,
    'habitaciones_connecting' => $habitaciones_connecting,
    
    // ========== TIPO DE HOTEL ==========
    'tipo_hotel' => $tipo_hotel,
    
    // ========== INFORMACIÓN ADICIONAL ==========
    'informacion_adicional' => $informacion_adicional,
    
    // ========== SERVICIOS DE LA PROPIEDAD ==========
    'recepcion_24_hrs' => $recepcion_24_hrs,
    'transfer_aero_htl_aero' => $transfer_aero_htl_aero,
    'aire_acondicionado_hotel' => $aire_acondicionado_hotel,
    'parqueadero' => $parqueadero,
    'minibar' => $minibar,
    'con_cocina' => $con_cocina,
    'cafetera_cortesia' => $cafetera_cortesia,
    'ventilador_techo' => $ventilador_techo,
    'servicio_habitacion_24_hrs' => $servicio_habitacion_24_hrs,
    'bar' => $bar,
    'turco' => $turco,
    'servicio_lavanderia' => $servicio_lavanderia,
    'transfer_htl_playa_htl' => $transfer_htl_playa_htl,
    'lobby_lounge' => $lobby_lounge,
    'guarda_equipaje' => $guarda_equipaje,
    'asoleadoras' => $asoleadoras,
    'terraza' => $terraza,
    'bar_en_la_piscina' => $bar_en_la_piscina,
    'muelle_privado' => $muelle_privado,
    'cafe_bar' => $cafe_bar,
    'concierge' => $concierge,
    'tienda_regalos' => $tienda_regalos,
    'sendero_ecologico' => $sendero_ecologico,
    'playa' => $playa,
    'discoteca' => $discoteca,
    'alquiler_bicicletas' => $alquiler_bicicletas,
    'mini_golf' => $mini_golf,
    'bebidas_recepcion' => $bebidas_recepcion,
    'piscina' => $piscina,
    'cajero_automatico' => $cajero_automatico,
    'snack_bar' => $snack_bar,
    'capilla' => $capilla,
    'piscina_infantil' => $piscina_infantil,
    'cambio_moneda' => $cambio_moneda,
    'salon_fitness' => $salon_fitness,
    'pesca' => $pesca,
    'servicio_medico' => $servicio_medico,
    'zona_juegos_infantiles' => $zona_juegos_infantiles,
    'sala_masajes' => $sala_masajes,
    'salon_juegos' => $salon_juegos,
    'personal_bilingue' => $personal_bilingue,
    'sauna' => $sauna,
    'salon_belleza' => $salon_belleza,
    'casino' => $casino,
    'lobby_sala_espera' => $lobby_sala_espera,
    'spa' => $spa,
    'gimnasio' => $gimnasio,
    'gimnasio_general' => $gimnasio_general,
    'ascensor' => $ascensor,
    'juegos_mesa' => $juegos_mesa,
    'toallas_playa_piscina' => $toallas_playa_piscina,
    'jacuzzi' => $jacuzzi,
    'otroservicio' => $otroservicio,
    'agua_caliente_habitaciones' => $agua_caliente_habitaciones,
    
    // ========== INTERNET ==========
    'wifi' => $wifi,
    'cable' => $cable,
    'canal_dedicado' => $canal_dedicado,
    'wifi_zonas_comunes' => $wifi_zonas_comunes,
    'cobertura_internet' => $cobertura_internet,
    
    // ========== CONTACTOS (FORMATO INDIVIDUAL) ==========
    'contacto_gerencia' => $contactosByTipo['gerencia']['nombre'] ?? '',
    'movil_gerencia' => $contactosByTipo['gerencia']['movil'] ?? '',
    'email_gerencia' => $contactosByTipo['gerencia']['email'] ?? '',
    'telefono_gerencia' => $contactosByTipo['gerencia']['telefono'] ?? '',
    
    'contacto_comercial' => $contactosByTipo['comercial']['nombre'] ?? '',
    'movil_comercial' => $contactosByTipo['comercial']['movil'] ?? '',
    'email_comercial' => $contactosByTipo['comercial']['email'] ?? '',
    'telefono_comercial' => $contactosByTipo['comercial']['telefono'] ?? '',
    
    'contacto_reservas' => $contactosByTipo['reservas']['nombre'] ?? '',
    'movil_reservas' => $contactosByTipo['reservas']['movil'] ?? '',
    'email_reservas' => $contactosByTipo['reservas']['email'] ?? '',
    'telefono_reservas' => $contactosByTipo['reservas']['telefono'] ?? '',
    
    'contacto_grupos' => $contactosByTipo['grupos']['nombre'] ?? '',
    'movil_grupos' => $contactosByTipo['grupos']['movil'] ?? '',
    'email_grupos' => $contactosByTipo['grupos']['email'] ?? '',
    'telefono_grupos' => $contactosByTipo['grupos']['telefono'] ?? '',
    
    'contacto_pagos' => $contactosByTipo['pagos']['nombre'] ?? '',
    'movil_pagos' => $contactosByTipo['pagos']['movil'] ?? '',
    'email_pagos' => $contactosByTipo['pagos']['email'] ?? '',
    'telefono_pagos' => $contactosByTipo['pagos']['telefono'] ?? '',
    
    'contacto_reclamaciones' => $contactosByTipo['reclamaciones']['nombre'] ?? '',
    'movil_reclamaciones' => $contactosByTipo['reclamaciones']['movil'] ?? '',
    'email_reclamaciones' => $contactosByTipo['reclamaciones']['email'] ?? '',
    'telefono_reclamaciones' => $contactosByTipo['reclamaciones']['telefono'] ?? '',
    
    'contacto_extranet' => $contactosByTipo['extranet']['nombre'] ?? '',
    'movil_extranet' => $contactosByTipo['extranet']['movil'] ?? '',
    'email_extranet' => $contactosByTipo['extranet']['email'] ?? '',
    'telefono_extranet' => $contactosByTipo['extranet']['telefono'] ?? '',
    
    // ========== ARRAY DE CONTACTOS ==========
    'contactos' => $contactos,
    
    // ========== CONDICIONES DE NEGOCIACIÓN ==========
    'salones_eventos' => $salones_eventos,
    'valor_credito_aprobado' => $valor_credito_aprobado,
    'tiempo_credito' => $tiempo_credito,
    'forma_conexion' => $forma_conexion,
    'manejo_extranet' => $manejo_extranet,
    'channel_manager_nombre' => $channel_manager,
    'tarifa_tipo' => $tarifa_tipo,
    'conexion_tarifas_fit' => $conexion_tarifas_fit,
    'conexion_tarifas_dinamicas' => $conexion_tarifas_dinamicas,
    'descuento_dinamico' => $descuento_dinamico_val,
    
    // ========== SERVICIO A LA HABITACIÓN ==========
    'servicio_habitacion' => $servicio_habitacion,
    
    // ========== DOCUMENTOS (TRUE/FALSE) ==========
    'has_rut' => ($docs['rut'] !== null),
    'fecha_vigencia_rut' => fechaDocZoho($docs['rut']), 

    'has_registro_turismo' => ($docs['registro_turismo'] !== null || $docs['rnt'] !== null),
    'has_rnt' => ($docs['rnt'] !== null || $docs['registro_turismo'] !== null),
    'fecha_vigencia_rnt' => fechaDocZoho($docs['rnt'] ?: $docs['registro_turismo']),

    'has_camara_comercio' => ($docs['camara_comercio'] !== null),
    'fecha_vigencia_camara_comercio' => fechaDocZoho($docs['camara_comercio']),

    'has_certificacion_bancaria' => ($docs['certificacion_bancaria'] !== null),
    'fecha_vigencia_certificacion_bancaria' => fechaDocZoho($docs['certificacion_bancaria']),

    'has_certificados_sostenibilidad' => ($docs['certificados_sostenibilidad'] !== null),
    'has_certificado_bomberos' => ($docs['certificado_bomberos'] !== null),
    'has_informacion_credito' => ($docs['informacion_credito'] !== null),
    'has_concepto_sanitario' => ($docs['concepto_sanitario'] !== null),
    'has_mantenimiento_piscinas' => ($docs['mantenimiento_piscinas'] !== null),
    'has_mantenimiento_ascensores' => ($docs['mantenimiento_ascensores'] !== null),
    'has_sg_sst' => ($docs['sg_sst'] !== null),
    'has_arl' => ($docs['arl'] !== null),
    'material_fotografico' => $tiene_fotos,
    
    // ========== PROVEEDOR Y USUARIO ==========
    'tipo_proveedor' => $tipo_proveedor,
    'tipo_proveedor_hotel' => $tipo_proveedor_hotel,
    'email_contabilidad' => $email_contabilidad,
    'email_cartera' => $email_cartera,
    'usuario_creacion' => $usuario_creacion,
    'diligenciado_por' => $diligencia_nombre,
    'diligencia_nombre' => $diligencia_nombre,
    'diligencia_correo' => $diligencia_correo,
    'diligencia_cargo' => $diligencia_cargo,
    'rol_usuario' => $id_rol,
    'juniper_id' => $juniper_id,
    
    // ========== OTROS ==========
    'action' => $accion,
    'accion' => $accion,
    'ficha_inscripcion' => true,
    'acuerdo'           => ($hotel['estado_firma'] ?? '') === 'FIRMADO',
    'link_ficha' => $link_ficha,
    'fecha_envio' => date('Y-m-d H:i:s'),
    'usuario_sesion' => $_SESSION['usuario'] ?? '',
];

// ==================== ENVIAR A ZOHO ====================
$config = require __DIR__ . '/../../aws.php';

$ZOHO_WEBHOOK_URL_REGISTRO = $config['zoho_url_webhook_registro'] ?? '';

$ZOHO_WEBHOOK_URL_DELETE = $config['zoho_url_webhook_delete'] ?? '';

if (in_array($accion, ['editar', 'actualizar'], true)) {

    $payloadEditar = $payload;
    $payloadEditar['action'] = 'editar';
    $payloadEditar['accion'] = 'editar';

    $resultEditar = enviarDatosWebhook($ZOHO_WEBHOOK_URL_DELETE, $payloadEditar, $debug);

    return [
        'ok' => $resultEditar['ok'],
        'error' => $resultEditar['error'] ?? null,
        'payload' => $debug ? $payloadEditar : null,
    ];
}

$urlWebhook = ($accion === 'delete')
    ? $ZOHO_WEBHOOK_URL_DELETE
    : $ZOHO_WEBHOOK_URL_REGISTRO;

$result = enviarDatosWebhook($urlWebhook, $payload, $debug);

return [
    'ok'      => $result['ok'],
    'error'   => $result['error'] ?? null,
    'payload' => $debug ? $payload : null,
];

} // end construirYEnviarZoho

// ==================== EJECUCIÓN DIRECTA (vía GET) ====================
if (!defined('ZOHO_CALLED_AS_LIBRARY')) {
    $DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

    if (!isset($conn) || $conn->connect_error) {
        if ($DEBUG) {
            header('Content-Type: text/plain; charset=utf-8');
            die("❌ Error de conexión a la base de datos: " . ($conn->connect_error ?? ''));
        }
        $_SESSION['flash_error'] = "❌ Error de conexión a la base de datos.";
        header("Location: ../vista/consultaHotel.php");
        exit();
    }

    $conn->set_charset('utf8mb4');

    $id_hotel = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id_hotel <= 0) {
        if ($DEBUG) {
            header('Content-Type: text/plain; charset=utf-8');
            die("❌ ID de hotel inválido.");
        }
        $_SESSION['flash_error'] = "❌ ID de hotel inválido.";
        header("Location: ../vista/consultaHotel.php");
        exit();
    }

    $result = construirYEnviarZoho($id_hotel, $conn, $DEBUG);

    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "PAYLOAD ENVIADO A ZOHO:\n";
        print_r($result['payload']);
        echo "\nRESULTADO:\n";
        print_r($result);
        exit();
    }

    if ($result['ok']) {
        $_SESSION['flash_success'] = "✅ Datos enviados a Zoho CRM correctamente.";
    } else {
        $_SESSION['flash_error'] = "❌ Error al enviar datos a Zoho CRM: " . ($result['error'] ?? 'Desconocido');
    }

    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}
