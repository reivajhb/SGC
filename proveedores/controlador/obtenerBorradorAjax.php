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

$id_hotel = isset($_POST['id_hotel']) ? (int) $_POST['id_hotel'] : 0;
if (!$id_hotel) {
    echo json_encode(['ok' => false, 'error' => 'Falta id_hotel']);
    exit;
}

try {
    // 1) General
    $stmt = $conn->prepare("
        SELECT *
        FROM tbl_alojamiento_general
        WHERE id_hotel = ? AND usuario_creacion = ? AND estado_registro = 'BORRADOR'
        LIMIT 1
    ");
    $stmt->bind_param("is", $id_hotel, $user_id);
    $stmt->execute();
    $general = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$general) {
        echo json_encode(['ok' => false, 'error' => 'No existe borrador para ese id']);
        exit;
    }

    // --- devolver en “nombres del form” ---
    $general_form = [
        'cadena_hotelera' => $general['cadena_hotelera'] ?? null,
        'nombre' => $general['nombre'] ?? null,
        'nit' => $general['nit'] ?? null,
        'nit_consecutivo' => $general['nit_consecutivo'] ?? null,
        'razon_social' => $general['razon_social'] ?? null,
        'telefono' => $general['telefono'] ?? null,
        'direccion' => $general['direccion'] ?? null,
        'ciudad' => $general['ciudad'] ?? null,
        'pais' => $general['pais'] ?? null,
        'website' => $general['website'] ?? null,
        'categoria' => $general['categoria'] ?? null,
        'descripcion_producto' => $general['descripcion_producto'] ?? null,
        'incluye_desayuno' => (string) ($general['incluye_desayuno'] ?? 0),
        'numero_habitaciones' => $general['numero_habitaciones'] ?? 0,
        'precio_desayuno' => $general['precio_desayuno'] ?? null,
        'tipo_desayuno' => $general['tipo_desayuno'] ?? null,
        'hora_check_in' => $general['hora_check_in'] ?? null,
        'hora_check_out' => $general['hora_check_out'] ?? null,
        'pet_friendly' => (string) ($general['es_pet_friendly'] ?? 0),
        'politica_mascotas' => $general['politica_mascotas'] ?? null,
        'habitaciones_discapacidad' => (string) ($general['habitaciones_discapacidad'] ?? 0),
        'habitaciones_connecting' => (string) ($general['habitaciones_connecting'] ?? 0),
        'informacion_adicional' => $general['informacion_adicional'] ?? null,
        'tipo_hotel' => json_decode($general['tipo_hotel_json'] ?? '[]', true) ?: [],
        'mercado_distribucion' => json_decode($general['mercados_distribucion_json'] ?? '[]', true) ?: [],
        'monto_credito' => $general['monto_credito'] ?? null,
        'tiempo_credito' => $general['tiempo_credito'] ?? null,
        'porcentaje_reteica' => $general['reteica'] ?? null,
        'porcentaje_retefuente' => $general['retefuente'] ?? null,

        'salones_eventos_count' => (string) ($general['salones_eventos_count'] ?? 0),
        'centro_negocios_count' => (string) ($general['centro_negocios_count'] ?? 0),
        'espacios_externos_count' => (string) ($general['espacios_externos_count'] ?? 0),

        'connectionType' => $general['forma_conexion'] ?? null,
        'channelName' => $general['channel_manager_nombre'] ?? null,
        'descuento_dinamico' => $general['descuento_dinamico'] ?? null,

        'nombre_hotel_legal' => $general['nombre_hotel_legal'] ?? null,
        'ciudad_hotel_legal' => $general['ciudad_hotel_legal'] ?? null,
        'nit_hotel_legal' => $general['nit_hotel_legal'] ?? null,
        'nombre_rep_legal' => $general['nombre_rep_legal'] ?? null,
        'ciudad_rep_legal' => $general['ciudad_rep_legal'] ?? null,
        'num_documento_rep_legal' => $general['num_documento_rep_legal'] ?? null,
        'ciudad_doc_rep_legal' => $general['ciudad_doc_rep_legal'] ?? null,

        'diligencia_nombre' => $general['diligencia_nombre'] ?? null,
        'diligencia_correo' => $general['diligencia_correo'] ?? null,
        'diligencia_cargo' => $general['diligencia_cargo'] ?? null,

        'firma_nombre_completo' => $general['rep_legal_nombre'] ?? null,
        'firma_cargo' => $general['rep_legal_cargo'] ?? null,

        'tiene_certificado_sostenibilidad' => (string) ($general['tiene_certificado_sostenibilidad'] ?? 0),
        'tarifa_tipo' => json_decode($general['tarifa_tipo_json'] ?? '[]', true) ?: [],

        // ✅ AGREGAR ESTOS 3 CAMPOS AQUÍ:
        'ciiu' => $general['ciiu'] ?? null,
        'tipo_contribuyente' => $general['tipo_contribuyente'] ?? null,
        'numero_cuenta' => $general['numero_cuenta'] ?? null,

        'allotment_selected' => (string) ($general['allotment_selected'] ?? 0),
        'allotment' => json_decode($general['allotment_json'] ?? '[]', true) ?: [],

        // ... tus otros campos ...
        'politica_ninos' => $general['politica_ninos'] ?? '',
        'politica_grupos' => $general['politica_grupos'] ?? '',
        // Decodificamos el JSON para que llegue como array de objetos al Front
        'planes_tarifarios' => json_decode($general['planes_tarifarios_json'] ?? '[]', true) ?: [],

        // Amenidades
        'amenidad_restaurante' => (string) ($general['amenidad_restaurante'] ?? 0),
        'amenidad_bar_lounge' => (string) ($general['amenidad_bar_lounge'] ?? 0),
        'amenidad_hab_especiales' => (string) ($general['amenidad_hab_especiales'] ?? 0),
        'amenidad_gay_friendly' => (string) ($general['amenidad_gay_friendly'] ?? 0),
        'amenidad_planes_boda' => (string) ($general['amenidad_planes_boda'] ?? 0),

        // Accesibilidad
        'accesibilidad_banos' => (string) ($general['accesibilidad_banos'] ?? 0),
        'accesibilidad_habitaciones' => (string) ($general['accesibilidad_habitaciones'] ?? 0),
        'accesibilidad_espacios_comunes' => (string) ($general['accesibilidad_espacios_comunes'] ?? 0),

        'regimen_alimenticio' => json_decode($general['regimen_alimenticio_json'] ?? '[]', true) ?: []




    ];

    // 2) Contactos
    $contactos_form = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_contactos WHERE id_hotel = ?");
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($row = $rs->fetch_assoc()) {
        $tipo = strtolower($row['tipo_contacto']); // Gerencia -> gerencia
        $contactos_form["contacto_{$tipo}"] = $row['nombre'] ?? null;
        $contactos_form["movil_{$tipo}"] = $row['movil'] ?? null;
        $contactos_form["email_{$tipo}"] = $row['email'] ?? null;
        $contactos_form["telefono_{$tipo}"] = $row['telefono'] ?? null;
    }
    $stmt->close();

    // 3) Servicios
    $servicios_form = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_servicios WHERE id_hotel = ? LIMIT 1");
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $srv = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($srv) {


        // devolvemos los nombres del form
        $servicios_form = [
            'recepcion_24_hrs' => (string) ($srv['recepcion_24_hrs'] ?? 0),
            'transfer_aero_htl_aero' => (string) ($srv['transfer_aero_htl'] ?? 0),
            'aire_acondicionado_hotel' => (string) ($srv['aire_acondicionado'] ?? 0),
            'turco' => (string) ($srv['turco'] ?? 0),
            'servicio_lavanderia' => (string) ($srv['servicio_lavanderia'] ?? 0),
            'transfer_htl_playa_htl' => (string) ($srv['transfer_htl_playa'] ?? 0),
            'lobby_lounge' => (string) ($srv['lobby_lounge'] ?? 0),
            'bar' => (string) ($srv['bar'] ?? 0),
            'guarda_equipaje' => (string) ($srv['guarda_equipaje'] ?? 0),
            'asoleadoras' => (string) ($srv['asoleadoras'] ?? 0),
            'terraza' => (string) ($srv['terraza'] ?? 0),
            'bar_en_la_piscina' => (string) ($srv['bar_piscina'] ?? 0),
            'servicio_ninera' => (string) ($srv['servicios_ninera'] ?? 0),
            'muelle_privado' => (string) ($srv['muelle_privado'] ?? 0),
            'cafe_bar' => (string) ($srv['cafe_bar'] ?? 0),
            'concierge' => (string) ($srv['concierge'] ?? 0),
            'tienda_regalos' => (string) ($srv['super_minimercado'] ?? 0),
            'sendero_ecologico' => (string) ($srv['sendero_ecologico'] ?? 0),
            'discoteca' => (string) ($srv['discoteca'] ?? 0),
            'playa' => (string) ($srv['playa'] ?? 0),
            'alquiler_bicicletas' => (string) ($srv['alquiler_bicicletas'] ?? 0),
            'mini_golf' => (string) ($srv['mini_golf'] ?? 0),
            'bebidas_recepcion' => (string) ($srv['cafe_recepcion'] ?? 0),
            'piscina' => (string) ($srv['piscina'] ?? 0),
            'cajero_automatico' => (string) ($srv['cajero_automatico'] ?? 0),
            'snack_bar' => (string) ($srv['snack_bar'] ?? 0),
            'capilla' => (string) ($srv['capilla'] ?? 0),
            'piscina_infantil' => (string) ($srv['piscina_infantil'] ?? 0),
            'cambio_moneda' => (string) ($srv['cambio_moneda'] ?? 0),
            'salon_fitness' => (string) ($srv['salon_fitness'] ?? 0),
            'club_ninos' => (string) ($srv['club_ninos'] ?? 0),
            'pesca' => (string) ($srv['pesca'] ?? 0),
            'servicio_medico' => (string) ($srv['enfermeria_medico'] ?? 0),
            'zona_juegos_infantiles' => (string) ($srv['zona_juegos_infantiles'] ?? 0),
            'sala_masajes' => (string) ($srv['sala_masajes'] ?? 0),
            'salon_juegos' => (string) ($srv['salon_juegos'] ?? 0),
            'personal_bilingue' => (string) ($srv['personal_bilingue'] ?? 0),
            'sauna' => (string) ($srv['sauna'] ?? 0),
            'salon_belleza' => (string) ($srv['salon_belleza'] ?? 0),
            'gimnasio_general' => (string) ($srv['gimnasio'] ?? 0),
            'juegos_mesa' => (string) ($srv['juegos_mesa'] ?? 0),
            'ascensor' => (string) ($srv['ascensor'] ?? 0),
            'toallas_playa_piscina' => (string) ($srv['toallas_playa_piscina'] ?? 0),
            'jacuzzi' => (string) ($srv['jacuzzi'] ?? 0),
            'agua_caliente_habitaciones' => ((int) ($srv['agua_caliente_hab'] ?? 0) === 1) ? 'si' : 'no',
            'wifi' => $srv['internet_wifi'] ?? '------',
            'cable' => $srv['internet_cable'] ?? '------',
            'wifi_zonas_comunes' => $srv['wifi_zonas_comunes'] ?? '------',
            'casino' => (string) ($srv['casino'] ?? 0),
            'lobby_sala_espera' => (string) ($srv['lobby_sala_espera'] ?? 0),
            'spa' => (string) ($srv['spa'] ?? 0),
            'cobertura_internet' => (function () use ($srv) {
                $raw = $srv['cobertura_internet'] ?? null;
                if ($raw === null || $raw === '')
                    return [];
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    return $decoded;
                if (is_string($raw)) {
                    $parts = preg_split('/[;,]+/', $raw);
                    $parts = array_values(array_filter(array_map('trim', $parts), fn($x) => $x !== ''));
                    return $parts;
                }
                return [];
            })(),
            'canal_dedicado' => $srv['canal_dedicado'] ?? '------',
            'otroservicio' => $srv['otro_servicio'] ?? null,
        ];
    }

    // 4) Habitaciones
    $habitaciones = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_habitaciones WHERE id_hotel = ? ORDER BY id_hab ASC");
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($row = $rs->fetch_assoc()) {
        $habitaciones[] = $row;
    }
    $stmt->close();

    // 5) Salones
    $salones = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_salones WHERE id_hotel = ? ORDER BY id_salon ASC");
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($row = $rs->fetch_assoc()) {
        $salones[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'ok' => true,
        'id_hotel' => $id_hotel,
        'wizard_step' => (int) ($general['wizard_step'] ?? 0),
        'general_form' => $general_form,
        'contactos_form' => $contactos_form,
        'servicios_form' => $servicios_form,
        'habitaciones' => $habitaciones,
        'salones' => $salones,
    ]);

} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}