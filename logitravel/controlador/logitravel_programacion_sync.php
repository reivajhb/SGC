<?php
// controlador/logitravel_programacion_sync.php

// Cargar seguridad y conexión (como ya usas en el proyecto)
include_once "../../seguridad.php";
include_once "../../conexion.php";

header('Content-Type: application/json; charset=utf-8');

try {
    // Leer cuerpo RAW
    $raw = file_get_contents("php://input");
    if (!$raw) {
        throw new Exception("Cuerpo de la petición vacío");
    }

    $json = json_decode($raw, true);
    if ($json === null) {
        throw new Exception("JSON inválido: " . json_last_error_msg());
    }

    if (!isset($json["data"]) || !is_array($json["data"])) {
        throw new Exception("Estructura inválida, falta 'data'");
    }

    $data = $json["data"];

    // === Obtener conexión ===
    // Ajusta según cómo se llame tu variable en conexion.php
    // Ejemplos típicos: $conexion, $link, $mysqli, $cn, etc.
    if (isset($conexion)) {
        $cn = $conexion;
    } elseif (isset($mysqli)) {
        $cn = $mysqli;
    } elseif (isset($cnx)) {
        $cn = $cnx;
    } else {
        throw new Exception("No se encontró la variable de conexión en conexion.php");
    }

    // Asegurarnos que sea mysqli
    if (!($cn instanceof mysqli)) {
        throw new Exception("La conexión no es instancia de mysqli");
    }

    // === Mapear campos ===
    $id_crm                  = isset($data["ID_Crm"]) ? $data["ID_Crm"] : null;
    $localizador             = isset($data["Localizador"]) ? $data["Localizador"] : null;
    $pasajero_principal      = isset($data["Pasajero_principal"]) ? $data["Pasajero_principal"] : null;
    $cant_adultos            = isset($data["Cantidad_adultos"]) ? (int)$data["Cantidad_adultos"] : 0;
    $cant_ninos              = isset($data["Cantidad_ni_os"]) ? (int)$data["Cantidad_ni_os"] : 0;

    $fecha_inicio_viaje      = isset($data["Fecha_inicio_del_viaje"]) ? $data["Fecha_inicio_del_viaje"] : null;
    $fecha_fin_viaje         = isset($data["Fecha_fin_del_viaje"]) ? $data["Fecha_fin_del_viaje"] : null;
    $fecha_reserva           = isset($data["Fecha_de_reserva"]) ? $data["Fecha_de_reserva"] : null;

    $fecha_hora_inicio_viaje = isset($data["Fecha_y_Hora_inicio_del_viaje"]) ? $data["Fecha_y_Hora_inicio_del_viaje"] : null;
    $fecha_hora_fin_viaje    = isset($data["Fecha_y_Hora_fin_del_viaje"]) ? $data["Fecha_y_Hora_fin_del_viaje"] : null;
    $hora_vuelo              = isset($data["Hora_del_vuelo"]) ? $data["Hora_del_vuelo"] : null;

    $capacidad_vehiculo      = isset($data["Capacidad_del_veh_culo"]) ? (int)$data["Capacidad_del_veh_culo"] : 0;
    $obs_tarifa              = isset($data["Observaciones_Tarifa_Editas"]) ? $data["Observaciones_Tarifa_Editas"] : null;
    $observaciones           = isset($data["Observaciones"]) ? $data["Observaciones"] : null;

    $zona_servicio           = isset($data["Zona_del_servicio"]) ? $data["Zona_del_servicio"] : null;
    $tipo_servicio           = isset($data["Tipo_de_servicio"]) ? $data["Tipo_de_servicio"] : null;
    $categoria_servicio      = isset($data["Categoria_de_Servicio"]) ? $data["Categoria_de_Servicio"] : null;

    $numero_vuelo            = isset($data["Numero_Vuelo"]) ? $data["Numero_Vuelo"] : null;
    $descripcion_servicio    = isset($data["Descripci_n_del_servicio"]) ? $data["Descripci_n_del_servicio"] : null;

    if (!$id_crm) {
        throw new Exception("Falta ID_Crm, no se puede guardar");
    }

    // === Insertar / actualizar (idempotente) ===
    $sql = "
        INSERT INTO programacion_servicios (
            id_crm,
            localizador,
            pasajero_principal,
            cantidad_adultos,
            cantidad_ninos,
            fecha_inicio_viaje,
            fecha_fin_viaje,
            fecha_reserva,
            fecha_hora_inicio_viaje,
            fecha_hora_fin_viaje,
            hora_vuelo,
            capacidad_vehiculo,
            observaciones_tarifa,
            observaciones,
            zona_servicio,
            tipo_servicio,
            categoria_servicio,
            numero_vuelo,
            descripcion_servicio,
            creado_en,
            actualizado_en
        ) VALUES (
            ?,?,?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?,
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            localizador = VALUES(localizador),
            pasajero_principal = VALUES(pasajero_principal),
            cantidad_adultos = VALUES(cantidad_adultos),
            cantidad_ninos = VALUES(cantidad_ninos),
            fecha_inicio_viaje = VALUES(fecha_inicio_viaje),
            fecha_fin_viaje = VALUES(fecha_fin_viaje),
            fecha_reserva = VALUES(fecha_reserva),
            fecha_hora_inicio_viaje = VALUES(fecha_hora_inicio_viaje),
            fecha_hora_fin_viaje = VALUES(fecha_hora_fin_viaje),
            hora_vuelo = VALUES(hora_vuelo),
            capacidad_vehiculo = VALUES(capacidad_vehiculo),
            observaciones_tarifa = VALUES(observaciones_tarifa),
            observaciones = VALUES(observaciones),
            zona_servicio = VALUES(zona_servicio),
            tipo_servicio = VALUES(tipo_servicio),
            categoria_servicio = VALUES(categoria_servicio),
            numero_vuelo = VALUES(numero_vuelo),
            descripcion_servicio = VALUES(descripcion_servicio),
            actualizado_en = NOW()
    ";

    $stmt = $cn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en prepare: " . $cn->error);
    }

    $stmt->bind_param(
        "sssiiissssissssssss",
        $id_crm,
        $localizador,
        $pasajero_principal,
        $cant_adultos,
        $cant_ninos,
        $fecha_inicio_viaje,
        $fecha_fin_viaje,
        $fecha_reserva,
        $fecha_hora_inicio_viaje,
        $fecha_hora_fin_viaje,
        $hora_vuelo,
        $capacidad_vehiculo,
        $obs_tarifa,
        $observaciones,
        $zona_servicio,
        $tipo_servicio,
        $categoria_servicio,
        $numero_vuelo,
        $descripcion_servicio
    );

    if (!$stmt->execute()) {
        throw new Exception("Error en execute: " . $stmt->error);
    }

    $stmt->close();

    echo json_encode(["status" => "ok", "message" => "Registro guardado/actualizado"]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
