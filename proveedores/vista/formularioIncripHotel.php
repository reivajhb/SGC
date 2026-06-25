<?php
// Incluir seguridad para proveedores
include_once '../seguridad_proveedores.php';
include_once '../../facturacion/config/conexion.php';

// Determinar header según rol (se incluirá dentro de <body>)
if (isset($_SESSION['id_rol']) && ((int) $_SESSION['id_rol'] === 7 || (int) $_SESSION['id_rol'] === 6)) {
    $headerFile = "../vista/headercadena.php";
} else {
    $headerFile = "../vista/header.php";
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 7) {

    // Usuario de Cadena/Rol 7
    include "../vista/headercadena.php";

    // 2. Verificar si es un PROVEEDOR (usando la nueva variable de sesión)
} else if (isset($_SESSION['PROV_AUTH']) && $_SESSION['PROV_AUTH'] === true) {

    // Usuario logueado como Proveedor
    // Asegúrate de que este archivo exista en /vista/
    include "../vista/headercadena.php";

    // 3. Condición por defecto (cualquier otro rol o no logueado como proveedor)
} else {

    // Otros roles o la condición por defecto que usaba antes
    include "../vista/header.php";
}

// Obtener el id del usuario desde la sesión
$user_id = $_SESSION['usuario'] ?? null;

$esCadena = (isset($_SESSION['id_rol']) && (int) $_SESSION['id_rol'] === 7);
$requested_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$nuevo = (isset($_GET['nuevo']) && $_GET['nuevo'] === '1');

// Rol 6 (proveedor hotel individual) solo puede tener una ficha.
// Si ya existe, no se permite abrir un formulario vacio ni crear otro hotel.
if ((int) ($_SESSION['id_rol'] ?? 0) === 6 && $user_id) {
    $idUsuarioSesion = (int) ($_SESSION['id_usuario'] ?? 0);
    $hotelProveedor = null;

    if ($idUsuarioSesion > 0) {
        $stmtHotelProveedor = $conn->prepare("
            SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro
            FROM tbl_alojamiento_general
            WHERE id_usuario_creacion = ? OR usuario_creacion = ? OR nit = ?
            ORDER BY id_hotel DESC
            LIMIT 1
        ");
        $stmtHotelProveedor->bind_param("iss", $idUsuarioSesion, $user_id, $user_id);
    } else {
        $stmtHotelProveedor = $conn->prepare("
            SELECT id_hotel, COALESCE(estado_registro, 'FINALIZADO') AS estado_registro
            FROM tbl_alojamiento_general
            WHERE usuario_creacion = ? OR nit = ?
            ORDER BY id_hotel DESC
            LIMIT 1
        ");
        $stmtHotelProveedor->bind_param("ss", $user_id, $user_id);
    }

    $stmtHotelProveedor->execute();
    $hotelProveedor = $stmtHotelProveedor->get_result()->fetch_assoc();
    $stmtHotelProveedor->close();

    if ($hotelProveedor) {
        $hotelProveedorId = (int) $hotelProveedor['id_hotel'];
        $estadoRegistroProveedor = strtoupper(trim((string) $hotelProveedor['estado_registro']));

        if ($estadoRegistroProveedor === 'BORRADOR') {
            if ($requested_id !== $hotelProveedorId || $nuevo) {
                header("Location: formularioIncripHotel.php?id=" . $hotelProveedorId);
                exit();
            }
        } else {
            header("Location: consultaHotel.php?id=" . $hotelProveedorId);
            exit();
        }
    }
}

$datos_borrador = null;
$borradores_list = [];

if ($user_id) {
    // Lista de borradores (para cadenas, o para debug)
    $stmt_list = $conn->prepare("
        SELECT id_hotel, nombre, nit, updated_at, wizard_step
        FROM tbl_alojamiento_general
        WHERE usuario_creacion = ? AND estado_registro = 'BORRADOR'
        ORDER BY updated_at DESC, id_hotel DESC
        LIMIT 50
    ");
    $stmt_list->bind_param("s", $user_id);
    $stmt_list->execute();
    $borradores_list = $stmt_list->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_list->close();

    // Si NO es “nuevo”, cargamos un borrador
    if (!$nuevo) {
        if ($requested_id > 0) {
            $stmt_b = $conn->prepare("
                SELECT * 
                FROM tbl_alojamiento_general 
                WHERE id_hotel = ? AND usuario_creacion = ? AND estado_registro = 'BORRADOR'
                LIMIT 1
            ");
            $stmt_b->bind_param("is", $requested_id, $user_id);
            $stmt_b->execute();
            $datos_borrador = $stmt_b->get_result()->fetch_assoc();
            $stmt_b->close();
        } else {
            // Por defecto: último borrador
            $stmt_b = $conn->prepare("
                SELECT * 
                FROM tbl_alojamiento_general 
                WHERE usuario_creacion = ? AND estado_registro = 'BORRADOR'
                ORDER BY updated_at DESC, id_hotel DESC
                LIMIT 1
            ");
            $stmt_b->bind_param("s", $user_id);
            $stmt_b->execute();
            $datos_borrador = $stmt_b->get_result()->fetch_assoc();
            $stmt_b->close();
        }
    }
}

// ================== PRECARGA DE INFORMACION EXISTENTE ==================
// Prioridad: BORRADOR encontrado > registro existente por id > formulario vacio.
// Este bloque NO cambia el guardado; solo prepara datos para mostrarlos en el formulario.
$datos_precarga = $datos_borrador;
$precarga_origen = $datos_borrador ? 'BORRADOR' : '';
$precarga_contactos = [];
$precarga_servicios = [];
$precarga_habitaciones = [];
$precarga_salones = [];

if (!$datos_precarga && !$nuevo && $requested_id > 0) {
    $idRolActual = (int) ($_SESSION['id_rol'] ?? 0);

    if (in_array($idRolActual, [7, 6], true)) {
        $usuarioProveedor = $_SESSION['usuario'] ?? '';
        $stmt_existente = $conn->prepare("\n            SELECT *\n            FROM tbl_alojamiento_general\n            WHERE id_hotel = ?\n              AND (usuario_creacion = ? OR nit = ? OR nit_consecutivo = ?)\n            LIMIT 1\n        ");
        $stmt_existente->bind_param("isss", $requested_id, $usuarioProveedor, $usuarioProveedor, $usuarioProveedor);
    } else {
        $stmt_existente = $conn->prepare("\n            SELECT *\n            FROM tbl_alojamiento_general\n            WHERE id_hotel = ?\n            LIMIT 1\n        ");
        $stmt_existente->bind_param("i", $requested_id);
    }

    $stmt_existente->execute();
    $datos_precarga = $stmt_existente->get_result()->fetch_assoc();
    $stmt_existente->close();

    if ($datos_precarga) {
        $precarga_origen = 'EXISTENTE';
    }
}

if ($datos_precarga && !empty($datos_precarga['id_hotel'])) {
    $id_precarga = (int) $datos_precarga['id_hotel'];

    $stmt_pc = $conn->prepare("SELECT tipo_contacto, nombre, movil, email, telefono FROM tbl_alojamiento_contactos WHERE id_hotel = ?");
    $stmt_pc->bind_param("i", $id_precarga);
    $stmt_pc->execute();
    $res_pc = $stmt_pc->get_result();
    while ($row = $res_pc->fetch_assoc()) {
        $precarga_contactos[] = $row;
    }
    $stmt_pc->close();

    $stmt_ps = $conn->prepare("SELECT * FROM tbl_alojamiento_servicios WHERE id_hotel = ? LIMIT 1");
    $stmt_ps->bind_param("i", $id_precarga);
    $stmt_ps->execute();
    $precarga_servicios = $stmt_ps->get_result()->fetch_assoc() ?: [];
    $stmt_ps->close();

    $stmt_ph = $conn->prepare("SELECT * FROM tbl_alojamiento_habitaciones WHERE id_hotel = ?");
    $stmt_ph->bind_param("i", $id_precarga);
    $stmt_ph->execute();
    $res_ph = $stmt_ph->get_result();
    while ($row = $res_ph->fetch_assoc()) {
        $precarga_habitaciones[] = $row;
    }
    $stmt_ph->close();

    $stmt_psa = $conn->prepare("SELECT * FROM tbl_alojamiento_salones WHERE id_hotel = ?");
    $stmt_psa->bind_param("i", $id_precarga);
    $stmt_psa->execute();
    $res_psa = $stmt_psa->get_result();
    while ($row = $res_psa->fetch_assoc()) {
        $precarga_salones[] = $row;
    }
    $stmt_psa->close();
}

function pnv_json_array($value)
{
    if (is_array($value))
        return $value;
    if ($value === null || $value === '')
        return [];
    $decoded = json_decode((string) $value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
        return $decoded;
    return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
}

function pnv_contact_key($tipo)
{
    $tipo = mb_strtolower(trim((string) $tipo), 'UTF-8');
    $tipo = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $tipo);

    if (strpos($tipo, 'gerencia') !== false)
        return 'gerencia';
    if (strpos($tipo, 'comercial') !== false)
        return 'comercial';
    if (strpos($tipo, 'reserva') !== false)
        return 'reservas';
    if (strpos($tipo, 'grupo') !== false)
        return 'grupos';
    if (strpos($tipo, 'pago') !== false || strpos($tipo, 'administrativo') !== false)
        return 'pagos';
    if (strpos($tipo, 'reclam') !== false || strpos($tipo, 'pqr') !== false)
        return 'reclamaciones';
    if (strpos($tipo, 'extranet') !== false || strpos($tipo, 'channel') !== false)
        return 'extranet';
    return '';
}

$precarga_general = $datos_precarga ?: [];

if ($precarga_general) {
    $precarga_general['tipo_hotel'] = pnv_json_array($precarga_general['tipo_hotel_json'] ?? ($precarga_general['tipo_hotel'] ?? ''));
    $precarga_general['mercado_distribucion'] = pnv_json_array($precarga_general['mercados_distribucion_json'] ?? ($precarga_general['mercado_distribucion'] ?? ''));
    $precarga_general['tarifa_tipo'] = pnv_json_array($precarga_general['tarifa_tipo_json'] ?? ($precarga_general['tarifa_tipo'] ?? ''));
    $precarga_general['planes_tarifarios'] = pnv_json_array($precarga_general['planes_tarifarios_json'] ?? '');
    $precarga_general['allotment'] = pnv_json_array($precarga_general['allotment_json'] ?? '');
    $precarga_general['regimen_alimenticio'] = pnv_json_array($precarga_general['regimen_alimenticio_json'] ?? ($precarga_general['regimen_alimenticio'] ?? ''));

    // Alias para nombres actuales del formulario de registro.
    $precarga_general['connectionType'] = $precarga_general['forma_conexion'] ?? ($precarga_general['connectionType'] ?? '');
    $precarga_general['channelName'] = $precarga_general['channel_manager_nombre'] ?? ($precarga_general['channelName'] ?? '');
    $precarga_general['porcentaje_reteica'] = $precarga_general['reteica'] ?? ($precarga_general['porcentaje_reteica'] ?? '');
    $precarga_general['porcentaje_retefuente'] = $precarga_general['retefuente'] ?? ($precarga_general['porcentaje_retefuente'] ?? '');

    if (isset($precarga_general['es_pet_friendly']) && !isset($precarga_general['pet_friendly'])) {
        $precarga_general['pet_friendly'] = ((string) $precarga_general['es_pet_friendly'] === '1') ? '1' : (((string) $precarga_general['es_pet_friendly'] === '0') ? '2' : '0');
    }
}

$precarga_contactos_form = [];
foreach ($precarga_contactos as $c) {
    $key = pnv_contact_key($c['tipo_contacto'] ?? '');
    if ($key === '')
        continue;
    $precarga_contactos_form['contacto_' . $key] = $c['nombre'] ?? '';
    $precarga_contactos_form['movil_' . $key] = $c['movil'] ?? '';
    $precarga_contactos_form['email_' . $key] = $c['email'] ?? '';
    $precarga_contactos_form['telefono_' . $key] = $c['telefono'] ?? '';
}

$precarga_servicios_form = $precarga_servicios ?: [];
if ($precarga_servicios_form) {
    $aliases_servicios = [
        'transfer_aero_htl' => 'transfer_aero_htl_aero',
        'aire_acondicionado' => 'aire_acondicionado_hotel',
        'transfer_htl_playa' => 'transfer_htl_playa_htl',
        'bar_piscina' => 'bar_en_la_piscina',
        'servicios_ninera' => 'servicio_ninera',
        'super_minimercado' => 'tienda_regalos',
        'cafe_recepcion' => 'bebidas_recepcion',
        'enfermeria_medico' => 'servicio_medico',
        'gimnasio' => 'gimnasio_general',
        'agua_caliente_hab' => 'agua_caliente_habitaciones',
        'internet_wifi' => 'wifi',
        'internet_cable' => 'cable',
        'otro_servicio' => 'otroservicio'
    ];
    foreach ($aliases_servicios as $origen => $destino) {
        if (array_key_exists($origen, $precarga_servicios_form) && !array_key_exists($destino, $precarga_servicios_form)) {
            $precarga_servicios_form[$destino] = $precarga_servicios_form[$origen];
        }
    }

    if (isset($precarga_servicios_form['agua_caliente_hab'])) {
        $precarga_servicios_form['agua_caliente_habitaciones'] = ((string) $precarga_servicios_form['agua_caliente_hab'] === '1') ? 'si' : (((string) $precarga_servicios_form['agua_caliente_hab'] === '0') ? 'no' : $precarga_servicios_form['agua_caliente_hab']);
    }

    $precarga_servicios_form['cobertura_internet'] = pnv_json_array($precarga_servicios_form['cobertura_internet'] ?? ($precarga_general['cobertura_internet_json'] ?? ($precarga_general['cobertura_internet'] ?? '')));
}

$precarga_payload = [
    'ok' => !empty($precarga_general),
    'origen' => $precarga_origen,
    'general_form' => $precarga_general,
    'contactos_form' => $precarga_contactos_form,
    'servicios_form' => $precarga_servicios_form,
    'habitaciones' => $precarga_habitaciones,
    'salones' => $precarga_salones,
    'wizard_step' => (int) ($precarga_general['wizard_step'] ?? 0)
];


// ================== DOCUMENTOS EXISTENTES ==================
// Se consultan únicamente para mostrar al usuario qué documentos ya están cargados.
// No cambia el guardado existente ni impide subir archivos nuevos.
$documentos_existentes = [];

if (!empty($datos_precarga['id_hotel'])) {
    $id_docs_existentes = (int) $datos_precarga['id_hotel'];

    $stmt_docs_existentes = $conn->prepare("
        SELECT id_doc, tipo_documento, nombre_archivo, ruta_almacenamiento, estado_vigencia, fecha_vigencia, dias_vencimiento
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
        ORDER BY tipo_documento, id_doc DESC
    ");
    $stmt_docs_existentes->bind_param("i", $id_docs_existentes);
    $stmt_docs_existentes->execute();
    $res_docs_existentes = $stmt_docs_existentes->get_result();

    while ($doc_existente = $res_docs_existentes->fetch_assoc()) {
        $tipo_doc_existente = trim((string) ($doc_existente['tipo_documento'] ?? ''));
        if ($tipo_doc_existente === '') {
            continue;
        }
        $documentos_existentes[$tipo_doc_existente][] = $doc_existente;
    }

    $stmt_docs_existentes->close();
}

function pnv_docs_por_tipos($documentos_existentes, array $tipos)
{
    $docs = [];
    foreach ($tipos as $tipo) {
        if (!empty($documentos_existentes[$tipo]) && is_array($documentos_existentes[$tipo])) {
            foreach ($documentos_existentes[$tipo] as $doc) {
                $docs[] = $doc;
            }
        }
    }
    return $docs;
}

function pnv_doc_estado_valid($doc): bool
{
    return strtoupper(trim((string) ($doc['estado_vigencia'] ?? ''))) === 'VALID';
}

function pnv_docs_validos_por_tipos($documentos_existentes, array $tipos)
{
    $validos = [];
    foreach (pnv_docs_por_tipos($documentos_existentes, $tipos) as $doc) {
        if (pnv_doc_estado_valid($doc)) {
            $validos[] = $doc;
        }
    }
    return $validos;
}

function pnv_render_docs_existentes($documentos_existentes, array $tipos, $hidden_name)
{
    $docs = pnv_docs_por_tipos($documentos_existentes, $tipos);
    if (empty($docs)) {
        return;
    }

    $docs_validos = pnv_docs_validos_por_tipos($documentos_existentes, $tipos);
    $tiene_doc_valido = !empty($docs_validos);
    ?>
    <div class="small mb-2">
        <?php if ($tiene_doc_valido): ?>
            <span class="badge bg-success">Documento vigente cargado</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark">Documento cargado no vigente</span>
        <?php endif; ?>

        <?php foreach ($docs as $doc): ?>
            <?php
            $url_doc = trim((string) ($doc['ruta_almacenamiento'] ?? ''));
            $nombre_doc = trim((string) ($doc['nombre_archivo'] ?? 'Documento'));
            $estado_doc = strtoupper(trim((string) ($doc['estado_vigencia'] ?? 'SIN ESTADO')));
            $fecha_vigencia_doc = trim((string) ($doc['fecha_vigencia'] ?? ''));
            if ($url_doc === '') {
                continue;
            }
            ?>
            <a href="<?= htmlspecialchars($url_doc, ENT_QUOTES, 'UTF-8') ?>" target="_blank"
                class="btn btn-sm <?= pnv_doc_estado_valid($doc) ? 'btn-outline-primary' : 'btn-outline-warning' ?> ms-1">
                Ver documento
            </a>
            <span class="text-muted ms-1">
                <?= htmlspecialchars($nombre_doc, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($estado_doc !== ''): ?>
                    - Estado: <?= htmlspecialchars($estado_doc, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
                <?php if ($fecha_vigencia_doc !== ''): ?>
                    - Vigencia: <?= htmlspecialchars($fecha_vigencia_doc, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </span>
        <?php endforeach; ?>
    </div>

    <?php if ($tiene_doc_valido): ?>
        <input type="hidden" name="<?= htmlspecialchars($hidden_name, ENT_QUOTES, 'UTF-8') ?>" value="1">
    <?php endif; ?>
<?php
}

// Consultar el nombre del usuario si está disponible
$nombre = '';
if ($user_id) {
    $sql = "SELECT nombre FROM tbl_usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($nombre);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- <link rel="stylesheet" type="text/css" href="estilosformulario.css"> -->
    <link rel="stylesheet" type="text/css" href="../../estilos/estilos.css">
    <link rel="stylesheet" type="text/css" href="estilos_hotel_moderno.css?v=habitaciones-20260612">
    <link rel="icon" type="image/x-icon" href="../../img/pnv.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>


    <title>Formulario Proveedor de Alojamiento</title>

    <style>
        /* Estilos para las tabs de navegación (solo visual) */
        .nav-tabs {
            border-bottom: 2px solid #1565C0;
            margin-bottom: 2rem;
        }

        .nav-tabs .nav-link {
            color: #999;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: default;
        }

        .nav-tabs .nav-link.active {
            color: #1565C0;
            background-color: transparent;
            border-color: #1565C0;
            font-weight: 600;
        }

        .nav-tabs .nav-link:disabled {
            opacity: 0.6;
        }

        /* Mostrar/ocultar secciones del wizard */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        /* Botón de envío en submitSection */
        #submitSection {
            text-align: center;
        }

        #finalSubmitBtn {
            padding: 15px 40px;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        #finalSubmitBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
        }

        /* Botón flotante de limpiar formulario */
        #floatingClearContainer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        #clearFormBtn {
            padding: 12px 24px;
            font-size: 1rem;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4);
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        #clearFormBtn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(220, 53, 69, 0.6);
        }

        #clearFormBtn i {
            margin-right: 8px;
        }

        /* Overlay de validación con IA - OCULTO por defecto */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loader {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 400px;
        }

        .loader-spinner {
            margin-bottom: 20px;
        }
        
        td:empty {
            border: none !important;
            background: transparent !important;
        }

        .loader-spinner img {
            width: 80px;
            height: 80px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .loader-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .loader-subtext {
            font-size: 0.95rem;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include $headerFile; ?>
    <div class="container-fluid" style="width: 1500px; max-width: 100%; margin: 100px auto;">
        <h2 class="text-center mb-4">FICHA DE INSCRIPCION COMO PROVEEDOR DE ALOJAMIENTO</h2>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>


        <?php if ($esCadena && !empty($borradores_list)): ?>
            <div class="alert alert-info">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong>Borradores guardados:</strong>
                        <span class="text-muted">elige cuál continuar.</span>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="formularioIncripHotel.php?nuevo=1">
                        + Nuevo hotel
                    </a>
                </div>

                <div class="mt-2 table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th>NIT</th>
                                <th>Última actualización</th>
                                <th>Sección</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borradores_list as $b): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($b['nombre'] ?? '') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($b['nit'] ?? '') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($b['updated_at'] ?? '') ?></td>
                                    <td class="text-center"><?= (int) ($b['wizard_step'] ?? 0) ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-success"
                                            href="formularioIncripHotel.php?id=<?= (int) $b['id_hotel'] ?>">
                                            Continuar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <form id="multiStepForm" action="../controlador/registrodatoshotel.php" method="post"
            enctype="multipart/form-data" novalidate>

            <input type="hidden" name="id_hotel_borrador" id="id_hotel_borrador"
                value="<?php echo htmlspecialchars($datos_borrador['id_hotel'] ?? ''); ?>">

            <input type="hidden" name="wizard_step" id="wizard_step"
                value="<?php echo (int) ($datos_borrador['wizard_step'] ?? 0); ?>">

            <input type="hidden" name="es_borrador" id="es_borrador" value="1">

            <!-- Navegación por Tabs (solo visual - no clickeable) -->
            <ul class="nav nav-tabs mb-4" id="hotelFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" type="button" disabled>
                        1. General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contactos-tab" type="button" disabled>
                        2. Contactos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="servicios-tab" type="button" disabled>
                        3. Servicios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="habitaciones-tab" type="button" disabled>
                        4. Habitaciones y Salones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="salones-tab" type="button" disabled>
                        5. Negociación
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documentos-tab" type="button" disabled>
                        6. Documentos
                    </button>
                </li>
            </ul>

            <div id="section1" class="form-section active">
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
                                <td colspan="3"><input type="text"
                                        value="<?php echo htmlspecialchars($nombre ?? ''); ?>" name="cadena_hotelera"
                                        class="form-control" required aria-required="true"></td>
                            </tr>
                            <?php
                            // 1. ¿Es usuario CADENA?
                            $esCadena = isset($_SESSION['id_rol']) && (int) $_SESSION['id_rol'] === 7;

                            // 2. NIT base sugerido (normalmente el usuario = NIT de la cadena)
                            $nit_base_sugerido = $esCadena ? ($_SESSION['usuario'] ?? '') : '';
                            if ($esCadena && !empty($precarga_general['nit'])) {
                                $nit_base_sugerido = (string) $precarga_general['nit'];
                            }

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

                            $nit_consecutivo_precarga = trim((string) ($precarga_general['nit_consecutivo'] ?? ''));
                            if (
                                $esCadena &&
                                $nit_base_sugerido !== '' &&
                                $nit_consecutivo_precarga !== '' &&
                                strpos($nit_consecutivo_precarga, $nit_base_sugerido) === 0
                            ) {
                                $suffix = substr($nit_consecutivo_precarga, strlen($nit_base_sugerido));
                                $suffix = strtoupper(preg_replace('/[^A-Z]/', '', $suffix));
                                if ($suffix !== '') {
                                    $nit_sufijo_sugerido = $suffix;
                                }
                            } elseif ($esCadena && $nit_base_sugerido !== '') {
                                $stmt_sufijos = $conn->prepare("
                                    SELECT nit_consecutivo
                                    FROM tbl_alojamiento_general
                                    WHERE nit = ?
                                      AND nit_consecutivo IS NOT NULL
                                      AND nit_consecutivo <> ''
                                ");

                                if ($stmt_sufijos) {
                                    $stmt_sufijos->bind_param("s", $nit_base_sugerido);
                                    $stmt_sufijos->execute();
                                    $res_sufijos = $stmt_sufijos->get_result();
                                    $usados_conn = [];

                                    while ($row_sufijo = $res_sufijos->fetch_assoc()) {
                                        $nc = (string) ($row_sufijo['nit_consecutivo'] ?? '');
                                        if (strpos($nc, $nit_base_sugerido) === 0) {
                                            $suffix = substr($nc, strlen($nit_base_sugerido));
                                            $suffix = strtoupper(preg_replace('/[^A-Z]/', '', $suffix));
                                            if ($suffix !== '') {
                                                $usados_conn[$suffix] = true;
                                            }
                                        }
                                    }
                                    $stmt_sufijos->close();

                                    $candidatos = [];
                                    for ($i = 0; $i < 26; $i++) {
                                        $candidatos[] = chr(ord('A') + $i);
                                    }
                                    for ($i = 0; $i < 26; $i++) {
                                        for ($j = 0; $j < 26; $j++) {
                                            $candidatos[] = chr(ord('A') + $i) . chr(ord('A') + $j);
                                        }
                                    }

                                    foreach ($candidatos as $letra) {
                                        if (!isset($usados_conn[$letra])) {
                                            $nit_sufijo_sugerido = $letra;
                                            break;
                                        }
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td class="required-label">Nombre del Hotel:</td>
                                <td>
                                    <input value="" type="text" name="nombre" class="form-control" required>
                                </td>

                                <td class="required-label">NIT:</td>
                                <td>
                                    <?php if ($esCadena): ?>
                                        <!-- Vista especial para CADENA -->
                                        <div class="input-group">
                                            <span class="input-group-text">NIT</span>
                                            <!-- NIT numérico real -->
                                            <input type="text" class="form-control" name="nit" id="nitBase"
                                                value="<?php echo htmlspecialchars($nit_base_sugerido); ?>"
                                                placeholder="860402288" required>

                                            <span class="input-group-text">Sufijo</span>
                                            <input type="text" class="form-control text-center fw-bold sufijo-input"
                                                id="nitSufijo" maxlength="2"
                                                value="<?php echo htmlspecialchars($nit_sufijo_sugerido); ?>" required
                                                readonly>


                                        </div>

                                        <!-- NIT interno para contabilidad: 860402288A, 860402288B, etc. -->
                                        <input type="hidden" name="nit_consecutivo" id="nitConsecutivo">

                                        <small class="form-text text-muted">
                                            El sistema asigna automáticamente un consecutivo interno para este NIT de
                                            cadena.
                                        </small>

                                    <?php else: ?>
                                        <!-- Vista normal para NO cadenas -->
                                        <input type="text" name="nit" class="form-control" required>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="required-label">Razón Social:</td>
                                <td colspan="3">
                                    <input type="text" name="razon_social" id="razon_social" class="form-control"
                                        required>
                                </td>

                            </tr>
                            <tr>
                                <td class="required-label">Teléfono:</td>
                                <td colspan="3"><input type="number" name="telefono" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">Dirección del hotel:</td>
                                <td colspan="3"><input type="text" name="direccion" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">Ciudad:</td>
                                <td><input type="text" name="ciudad" class="form-control" required></td>
                                <td class="required-label">País:</td>
                                <td><input type="text" name="pais" class="form-control" required>
                                </td>
                            </tr>
                            <tr>
                                <td>Website:</td>
                                <td><input type="text" name="website" class="form-control"></td>
                                <td class="required-label">Categoría:</td>
                                <td>
                                    <select name="categoria" class="form-control" required>
                                        <option value="" selected>-----</option>
                                        <option value="3-star">3 Estrellas</option>
                                        <option value="4-star">4 Estrellas</option>
                                        <option value="5-star">5 Estrellas</option>
                                        <option value="boutique">Boutique</option>
                                        <option value="glamping">Glamping</option>
                                        <option value="luxury">Luxury</option>
                                    </select>
                                </td>
                            </tr>

                            <thead class="table-dark">
                                <tr>
                                    <th colspan="4" class="text-center">INFORMACIÓN DEL PROVEEDOR</th>
                                </tr>
                            </thead>
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="4">DATOS DE CONTACTO</th>
                                </tr>
                            </thead>

                            <tr class="separador">
                                <td>Contacto Gerencia</td>
                                <td><input type="text" name="contacto_gerencia" class="form-control"></td>
                                <td>Móvil:</td>
                                <td><input type="number" name="movil_gerencia" class="form-control"></td>
                            </tr>
                            <tr>
                                <td>E-mail:</td>
                                <td><input type="email" name="email_gerencia" class="form-control"></td>
                                <td>Teléfono:</td>
                                <td><input type="number" name="telefono_gerencia" class="form-control"></td>
                            </tr>

                            <tr class="separador">
                                <td class="required-label">Contacto Comercial</td>
                                <td><input type="text" name="contacto_comercial" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_comercial" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_comercial" class="form-control" required></td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_comercial" class="form-control" required></td>
                            </tr>
                            <tr class="separador">
                                <td class="required-label">Contacto Reservas Individuales</td>
                                <td><input type="text" name="contacto_reservas" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_reservas" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_reservas" class="form-control" required></td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_reservas" class="form-control" required></td>
                            </tr>
                            <tr class="separador">
                                <td class="required-label">Contacto Grupos</td>
                                <td><input type="text" name="contacto_grupos" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_grupos" class="form-control" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_grupos" class="form-control" required>
                                </td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_grupos" class="form-control" required></td>
                            </tr>
                            <tr class="separador">
                                <td class="required-label">Contacto Pagos (Control Administrativo)</td>
                                <td><input type="text" name="contacto_pagos" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_pagos" class="form-control" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_pagos" class="form-control" required>
                                </td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_pagos" class="form-control" required></td>
                            </tr>
                            <tr class="separador">
                                <td class="required-label">Contacto Reclamaciones (para PQR’s de pasajeros)</td>
                                <td><input type="text" name="contacto_reclamaciones" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_reclamaciones" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_reclamaciones" class="form-control" required></td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_reclamaciones" class="form-control" required>
                                </td>
                            </tr>
                            <tr class="separador">
                                <td class="required-label">Contacto Extranet (Channel Manager)</td>
                                <td><input type="text" name="contacto_extranet" class="form-control" required></td>
                                <td class="required-label">Móvil:</td>
                                <td><input type="number" name="movil_extranet" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td class="required-label">E-mail:</td>
                                <td><input type="email" name="email_extranet" class="form-control" required></td>
                                <td class="required-label">Teléfono:</td>
                                <td><input type="number" name="telefono_extranet" class="form-control" required></td>
                            </tr>

                        </tbody>
                    </table>
                    <style>
                        .separador {
                            border-top: 4px solid #333;
                        }
                    </style>
                </div>
            </div>

            <div id="section2" class="form-section">
                <h2 class="text-center mb-4">DESCRIPCION PRODUCTO</h2>

                <div class="card mb-4 black-theme-card">
                    <div class="card-header">
                        <h5 class="mb-0 required-label">Descripción del Hotel</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="descripcionProducto" class="form-label required-label">Descripción</label>
                                <textarea id="descripcionProducto" name="descripcion_producto" class="form-control"
                                    rows="4"
                                    placeholder="Favor escribir una breve descripción del hotel teniendo en cuenta: ubicación incluyendo distancia en km al aeropuerto más cercano, servicios generales del hotel, servicios generales de las habitaciones y distancias a puntos de interés del destino"
                                    required></textarea>
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
                                <select id="tarifasIncluyen" name="incluye_desayuno" class="form-select" required>
                                    <option value="0">-------</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="numeroHabitaciones" class="form-label required-label">Número total
                                    de habitaciones</label>
                                <input type="number" id="numeroHabitaciones" name="numero_habitaciones"
                                    class="form-control" required min="0">
                            </div>

                            <div class="col-md-4">
                                <label for="precioDesayuno" class="form-label">Precio del
                                    desayuno por
                                    persona (si no está incluido)</label>
                                <input type="text" id="precioDesayuno" name="precio_desayuno" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="tipoDesayuno" class="form-label required-label">Tipo de
                                    desayuno</label>
                                <select id="tipoDesayuno" name="tipo_desayuno" class="form-select" required>
                                    <option value="0">-------</option>
                                    <option value="a_la_carta">A la Carta</option>
                                    <option value="americano">Americano</option>
                                    <option value="buffet">Buffet</option>
                                    <option value="continental">Continental</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="horaCheckIn" class="form-label required-label">Hora Check In</label>
                                <input type="time" id="horaCheckIn" name="hora_check_in" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="horaCheckOut" class="form-label required-label">Hora Check
                                    Out</label>
                                <input type="time" id="horaCheckOut" name="hora_check_out" class="form-control"
                                    required>
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
                                    <input type="checkbox" id="restauranteEspecializado" name="amenidad_restaurante"
                                        value="1" class="form-check-input">

                                    <label for="restauranteEspecializado" class="form-check-label">Restaurante
                                        Especializado</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="barLounge" name="amenidad_bar_lounge" value="1"
                                        class="form-check-input">

                                    <label for="barLounge" class="form-check-label">Bar/Lounge</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="habitacionesEspeciales" name="amenidad_hab_especiales"
                                        value="1" class="form-check-input">

                                    <label for="habitacionesEspeciales" class="form-check-label">Habitaciones o pisos
                                        especiales</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="gayFriendly" name="amenidad_gay_friendly" value="1"
                                        class="form-check-input">

                                    <label for="gayFriendly" class="form-check-label">Gay Friendly</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="planesBoda" name="amenidad_planes_boda" value="1"
                                        class="form-check-input">

                                    <label for="planesBoda" class="form-check-label">Planes de Boda y Luna de
                                        Miel</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-2 required-label" style="font-weight: bold;">Régimen Alimenticio
                                    (Seleccione uno o más)</h6>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="pensioncompleta" name="regimen_alimenticio[]" value="PC"
                                        class="form-check-input">
                                    <label for="pensioncompleta" class="form-check-label">PC: Pensión Completa</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="mediapension" name="regimen_alimenticio[]" value="MP"
                                        class="form-check-input">
                                    <label for="mediapension" class="form-check-label">MP: Media
                                        Pensión</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="desayunobuffet" name="regimen_alimenticio[]" value="BB"
                                        class="form-check-input">
                                    <label for="desayunobuffet" class="form-check-label">BB: Desayuno
                                        Buffet</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="desayunoamericano" name="regimen_alimenticio[]"
                                        value="AB" class="form-check-input">
                                    <label for="desayunoamericano" class="form-check-label">AB: Desayuno
                                        americano</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="desayunoalacarta" name="regimen_alimenticio[]" value="CB"
                                        class="form-check-input">
                                    <label for="desayunoalacarta" class="form-check-label">CB: Desayuno a la
                                        Carta</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="todoIncluido" name="regimen_alimenticio[]" value="FULL"
                                        class="form-check-input">
                                    <label for="todoIncluido" class="form-check-label">FULL: Todo
                                        Incluido</label>
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
                                <select id="PetFriendly" name="pet_friendly" class="form-select" required
                                    onchange="togglePolicyDetails()">
                                    <option value="0">-------</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="politicaMascotas" class="form-label" id="policyDetailsLabel">Detalles de la
                                    política <span id="requiredIndicator"
                                        style="color: red; display: none;">*</span></label>
                                <textarea id="politicaMascotas" name="politica_mascotas" class="form-control" rows="3"
                                    placeholder="Describa la política de mascotas, si aplica"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Lógica Pet Friendly (dejada en el DOM para simplicidad)
                    function togglePolicyDetails() {
                        const petFriendly = document.getElementById('PetFriendly');
                        const policyDetails = document.getElementById('politicaMascotas');
                        const requiredIndicator = document.getElementById('requiredIndicator');
                        const label = document.getElementById('policyDetailsLabel');

                        // Si selecciona "Sí"
                        if (petFriendly.value === '1') {
                            policyDetails.required = true;
                            policyDetails.closest('.col-md-8').classList.add('required-field-container');
                            requiredIndicator.style.display = 'inline';
                        } else { // Si selecciona "No" o "-------"
                            policyDetails.required = false;
                            policyDetails.closest('.col-md-8').classList.remove('required-field-container');
                            requiredIndicator.style.display = 'none';
                        }
                    }

                    // Llamar a la función al cargar la página para inicializar el estado
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
                                    <input type="hidden" name="accesibilidad_banos" value="0">
                                    <input type="checkbox" id="banosAccesibles" name="accesibilidad_banos" value="1"
                                        class="form-check-input">
                                    <label for="banosAccesibles" class="form-check-label">Baños accesibles</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input type="hidden" name="accesibilidad_habitaciones" value="0">
                                    <input type="checkbox" id="habitacionesAccesibles" name="accesibilidad_habitaciones"
                                        value="1" class="form-check-input">
                                    <label for="habitacionesAccesibles" class="form-check-label">Habitaciones
                                        accesibles</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input type="hidden" name="accesibilidad_espacios_comunes" value="0">
                                    <input type="checkbox" id="espaciosAccesibles" name="accesibilidad_espacios_comunes"
                                        value="1" class="form-check-input">
                                    <label for="espaciosAccesibles" class="form-check-label">Espacios comunes
                                        accesibles</label>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <label for="habitacionesDiscapacidad" class="form-label">Habitaciones para
                                    discapacidad</label>
                                <input type="number" id="habitacionesDiscapacidad" name="habitaciones_discapacidad"
                                    class="form-control" min="0">
                            </div>

                            <div class="col-md-6">
                                <label for="habitacionesConnecting" class="form-label">Habitaciones Connecting</label>
                                <input type="number" id="habitacionesConnecting" name="habitaciones_connecting"
                                    class="form-control" min="0">
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
                                        class="form-check-input">
                                    <label for="familiar" class="form-check-label">Familiar</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="gayOnly" name="tipo_hotel[]" value="gay_only"
                                        class="form-check-input">
                                    <label for="gayOnly" class="form-check-label">Gay Only</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="corporativo" name="tipo_hotel[]" value="corporativo"
                                        class="form-check-input">
                                    <label for="corporativo" class="form-check-label">Corporativo</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="boutique" name="tipo_hotel[]" value="boutique"
                                        class="form-check-input">
                                    <label for="boutique" class="form-check-label">Boutique</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="soloAdultos" name="tipo_hotel[]" value="solo_adultos"
                                        class="form-check-input">
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
                                <textarea id="informacionAdicional" name="informacion_adicional" class="form-control"
                                    rows="4" placeholder="Escriba cualquier información adicional"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section3" class="form-section">
                <h2 class="text-center mb-4">SERVICIOS DE LA PROPIEDAD</h2>
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
                                <select name="parqueadero" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Minibar</td>
                            <td>
                                <select name="minibar" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Con cocina</td>
                            <td>
                                <select name="con_cocina" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Cafetera de cortesía</td>
                            <td>
                                <select name="cafetera_cortesia" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Servicio a la habitación</td>
                            <td>
                                <select name="servicio_habitacion" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Servicio a la habitación 24 hrs</td>
                            <td>
                                <select name="servicio_habitacion_24_hrs" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Recepción 24 hrs</td>
                            <td>
                                <select name="recepcion_24_hrs" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                            <td class="required-label">Transfer Aero-Htl-Aero</td>
                            <td>
                                <select name="transfer_aero_htl_aero" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Aire Acondicionado en el hotel</td>
                            <td>
                                <select name="aire_acondicionado_hotel" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Turco</td>
                            <td>
                                <select name="turco" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Servicio de Lavandería</td>
                            <td>
                                <select name="servicio_lavanderia" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                            <td class="required-label">Transfer Htl - Playa - Htl</td>
                            <td>
                                <select name="transfer_htl_playa_htl" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Lobby Lounge</td>
                            <td>
                                <select name="lobby_lounge" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Bar</td>
                            <td>
                                <select name="bar" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Guarda equipaje</td>
                            <td>
                                <select name="guarda_equipaje" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                            <td class="required-label">Asoleadoras</td>
                            <td>
                                <select name="asoleadoras" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Terraza</td>
                            <td>
                                <select name="terraza" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Bar en la piscina</td>
                            <td>
                                <select name="bar_en_la_piscina" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Servicios de Niñera (Cargo Adicional)</td>
                            <td>
                                <select name="servicio_ninera" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Muelle Privado</td>
                            <td>
                                <select name="muelle_privado" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Café-Bar</td>
                            <td>
                                <select name="cafe_bar" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Concierge</td>
                            <td>
                                <select name="concierge" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Super/Minimercado/ Tienda de regalos</td>
                            <td>
                                <select name="tienda_regalos" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Sendero Ecológico</td>
                            <td>
                                <select name="sendero_ecologico" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Discoteca</td>
                            <td>
                                <select name="discoteca" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Playa</td>
                            <td>
                                <select name="playa" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Alquiler de bicicletas</td>
                            <td>
                                <select name="alquiler_bicicletas" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Sí, con Costo</option>
                                </select>
                            </td>
                            <td class="required-label">Mini Golf</td>
                            <td>
                                <select name="mini_golf" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Café, Agua Saborizada y Aromática en la recepción</td>
                            <td>
                                <select name="bebidas_recepcion" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Piscina</td>
                            <td>
                                <select name="piscina" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Cajero Automático</td>
                            <td>
                                <select name="cajero_automatico" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Snack-bar</td>
                            <td>
                                <select name="snack_bar" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Capilla</td>
                            <td>
                                <select name="capilla" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Piscina infantil</td>
                            <td>
                                <select name="piscina_infantil" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Cambio de moneda</td>
                            <td>
                                <select name="cambio_moneda" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Salón de Fitness</td>
                            <td>
                                <select name="salon_fitness" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Club de Niños</td>
                            <td>
                                <select name="club_ninos" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Pesca</td>
                            <td>
                                <select name="pesca" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Enfermería y/o Servicio Médico</td>
                            <td>
                                <select name="servicio_medico" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Zona de Juegos Infantiles</td>
                            <td>
                                <select name="zona_juegos_infantiles" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Sala de Masajes</td>
                            <td>
                                <select name="sala_masajes" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Salón de juegos</td>
                            <td>
                                <select name="salon_juegos" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Personal Bilingüe</td>
                            <td>
                                <select name="personal_bilingue" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Sauna</td>
                            <td>
                                <select name="sauna" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Salón de belleza</td>
                            <td>
                                <select name="salon_belleza" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Casino</td>
                            <td>
                                <select name="casino" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Lobby con sala de espera</td>
                            <td>
                                <select name="lobby_sala_espera" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Spa</td>
                            <td>
                                <select name="spa" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Gimnasio</td>
                            <td>
                                <select name="gimnasio_general" class="form-select" id="gimnasio_general" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Sí, Abierto las 24 horas</option>
                                </select>
                            </td>
                            <td class="required-label">Juegos de Mesa (Ping Pong, Billar)</td>
                            <td>
                                <select name="juegos_mesa" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Ascensor</td>
                            <td>
                                <select name="ascensor" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Directo Al Piso</option>
                                    <option value="2">Interpiso</option>
                                </select>
                            </td>
                            <td class="required-label">Toallas para la playa y piscina</td>
                            <td>
                                <select name="toallas_playa_piscina" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                    <option value="3">Si, Con Costo</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label">Jacuzzi</td>
                            <td>
                                <select name="jacuzzi" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td class="required-label">Ventilador de techo</td>
                            <td>
                                <select name="ventilador_techo" class="form-select" required>
                                    <option value="0">--</option>
                                    <option value="1">Sí</option>
                                    <option value="2">No</option>
                                </select>
                            </td>
                            <td colspan="4"></td>
                        </tr>

                        <tr>
                            <td colspan="1">ALGUN OTRO SERVICIO?</td>
                            <td colspan="4">
                                <textarea id="otroservicio" name="otroservicio" class="form-control" rows="2"
                                    placeholder="Describa el servicio."></textarea>
                            </td>

                            <td class="required-label">Cuenta con agua caliente en habitaciones</td>
                            <td colspan="2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="agua_caliente_habitaciones"
                                        id="agua_caliente_si" value="si" required>
                                    <label class="form-check-label" for="agua_caliente_si">SI</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="agua_caliente_habitaciones"
                                        id="agua_caliente_no" value="no">
                                    <label class="form-check-label" for="agua_caliente_no">No</label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center align-middle section-title required-label" rowspan="2">
                                INTERNET
                            </td>
                            <td>Wifi:</td>

                            <td>
                                <select name="wifi" class="form-select" required>
                                    <option value="------">------</option>
                                    <option value="Gratis">Gratis</option>
                                    <option value="Con costo">Con costo</option>
                                    <option value="No hay internet">No hay internet</option>
                                </select>
                            </td>
                            <td class="required-label">Cable:</td>
                            <td>
                                <select name="cable" class="form-select" required>
                                    <option value="------">--------</option>
                                    <option value="Gratis">Gratis</option>
                                    <option value="Con costo">Con costo</option>
                                    <option value="No hay internet">No hay internet</option>
                                </select>
                            </td>
                            <td class="required-label">Área de cobertura del internet:</td>
                            <td colspan="2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                        id="internet_habitaciones" value="habitaciones">
                                    <label class="form-check-label" for="internet_habitaciones">Habitaciones</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                        id="internet_areas" value="areas_especificas">
                                    <label class="form-check-label" for="internet_areas">Áreas
                                        específicas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                        id="internet_publicas" value="areas_publicas">
                                    <label class="form-check-label" for="internet_publicas">Áreas
                                        Públicas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cobertura_internet[]"
                                        id="no_hay_internet" value="no_hay_internet">
                                    <label class="form-check-label" for="no_hay_internet">No hay
                                        internet</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="required-label" colspan="1">Canal dedicado:</td>
                            <td colspan="2">
                                <select name="canal_dedicado" class="form-select" required>
                                    <option value="------">------</option>
                                    <option value="Gratis">Gratis</option>
                                    <option value="Con costo">Con costo</option>
                                    <option value="No hay internet">No hay internet</option>
                                </select>
                            </td>
                            <td class="required-label" colspan="2" style="text-align: center;">Wi-Fi Zonas
                                Comunes
                            </td>
                            <td colspan="2">
                                <select name="wifi_zonas_comunes" class="form-select" required>
                                    <option value="------">-------</option>
                                    <option value="Gratis">Gratis</option>
                                    <option value="Con costo">Con costo</option>
                                    <option value="No hay internet">No hay internet</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="section4" class="form-section">
                <h2 class="text-center mb-6">SERVICIOS DE LA PROPIEDAD</h2>

                <div class="border p-4 mt-4 rounded">
                    <h3 class="text-center mb-4">Descripción de Habitaciones</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered black-theme-card habitaciones-table"
                            style="background-color: #000; color: #fff;" id="habitacionTable">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" scope="col">Tipos de habitación</th>
                                    <th rowspan="2" scope="col">Total de<br>habitaciones</th>
                                    <th colspan="3" class="text-center">DESCRIPCIÓN</th>
                                    <th rowspan="2" scope="col">Metros Cuadrados</th>
                                    <th colspan="7" class="text-center">Opciones de acomodación en
                                        camas<br><small>(Cantidad de camas)</small></th>
                                    <th rowspan="2" scope="col">Observaciones</th>
                                    <th rowspan="2" scope="col">Servicios Generales</th>
                                    <th rowspan="2" scope="col">Acción</th>
                                </tr>
                                <tr>
                                    <th scope="col">Máx. adultos</th>
                                    <th scope="col">Máx. niños</th>
                                    <th scope="col">Máx. total</th>
                                    <th scope="col">Cama sencilla</th>
                                    <th scope="col">Cama doble</th>
                                    <th scope="col">Cama queen</th>
                                    <th scope="col">Cama king</th>
                                    <th scope="col">Camas twin sencillas</th>
                                    <th scope="col">Camas twin dobles</th>
                                    <th scope="col">Camas adicionales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="sticky-col">
                                        <input type="text" class="form-control form-control-sm" placeholder="Estandar"
                                            name="tipo_habitacion[]" required aria-label="Tipo de habitación">
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="total_habitaciones[]" placeholder="1" required
                                            aria-label="Total de habitaciones"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="max_adultos[]" placeholder="1" required
                                            aria-label="Capacidad máxima de adultos">
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="max_ninos[]" placeholder="1" required
                                            aria-label="Capacidad máxima de niños">
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="max_total[]" placeholder="1" required
                                            aria-label="Capacidad máxima total"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0" name="mts2[]"
                                            placeholder="20" required aria-label="Metros cuadrados"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="cama_sencilla[]" placeholder="1" aria-label="Camas sencillas"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="cama_doble[]" aria-label="Camas dobles"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="cama_queen[]" aria-label="Camas queen"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="cama_king[]" aria-label="Camas king"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="camarote_sencillo[]" aria-label="Camarotes sencillos"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="camarote_doble[]" aria-label="Camarotes dobles"></td>
                                    <td><input type="number" class="form-control form-control-sm" min="0"
                                            name="camas_adicionales[]" aria-label="Camas adicionales"></td>
                                    <td><textarea class="form-control form-control-sm" name="observaciones[]"
                                            aria-label="Observaciones adicionales" rows="3"></textarea>
                                    </td>
                                    <td class="services-cell" data-services="" data-description="">

                                        <button type="button" class="btn btn-primary btn-sm add-service-btn">
                                            Add Servicio
                                        </button>
                                    </td>

                                    <td>
                                        <button class="btn btn-danger btn-sm" type="button"
                                            onclick="removeHabitacionRow(this)">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-primary mt-3" id="addRowBtn">Agregar Fila</button>
                    <h5>**Adicione a la tabla tantos números de filas como se requiera para describir
                        todos los tipos de categorias de habitaciones que tenga el hotel.</h5>
                </div>


                <div class="border p-4 mt-4 rounded">
                    <h3 class="text-center mb-4">Salones y Espacios para Eventos</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered black-theme-card" id="tablaSalones">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Nombre del Salón o Área</th>
                                    <th scope="col">M²</th>
                                    <th scope="col">Largo (m)</th>
                                    <th scope="col">Ancho (m)</th>
                                    <th scope="col">Alto (m)</th>
                                    <th scope="col">U / Herradura</th>
                                    <th scope="col">Aula</th>
                                    <th scope="col">Auditorio</th>
                                    <th scope="col">Banquete</th>
                                    <th scope="col">Imperial</th>
                                    <th scope="col">Cóctel</th>
                                    <th scope="col">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" class="form-control" placeholder="Nombre"
                                            name="nombre_salon[]" aria-label="Nombre del salón"></td>
                                    <td><input type="number" class="form-control" placeholder="M²" min="0"
                                            name="m2_salon[]" aria-label="Metros cuadrados"></td>
                                    <td><input type="number" class="form-control" placeholder="Largo" min="0"
                                            name="largo_salon[]" aria-label="Largo en metros"></td>
                                    <td><input type="number" class="form-control" placeholder="Ancho" min="0"
                                            name="ancho_salon[]" aria-label="Ancho en metros"></td>
                                    <td><input type="number" class="form-control" placeholder="Alto" min="0"
                                            name="alto_salon[]" aria-label="Alto en metros"></td>
                                    <td><input type="number" class="form-control" placeholder="U / Herradura" min="0"
                                            name="cap_u_herradura[]" aria-label="Capacidad U o Herradura"></td>
                                    <td><input type="number" class="form-control" placeholder="Aula" min="0"
                                            name="cap_aula[]" aria-label="Capacidad Aula"></td>
                                    <td><input type="number" class="form-control" placeholder="Auditorio" min="0"
                                            name="cap_auditorio[]" aria-label="Capacidad Auditorio"></td>
                                    <td><input type="number" class="form-control" placeholder="Banquete" min="0"
                                            name="cap_banquete[]" aria-label="Capacidad Banquete"></td>
                                    <td><input type="number" class="form-control" placeholder="Imperial" min="0"
                                            name="cap_imperial[]" aria-label="Capacidad Imperial"></td>
                                    <td><input type="number" class="form-control" placeholder="Cóctel" min="0"
                                            name="cap_coctel[]" aria-label="Capacidad Cóctel"></td>
                                    <td><button class="btn btn-danger btn-sm" type="button"
                                            onclick="removeHabitacionRow(this)">Eliminar</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mt-3" id="addRowButton">Agregar Fila</button>
                        <h5>**Adicione a la tabla tantos números de filas como se requiera para describir
                            todos los tipos de Salones y espacios para eventos.</h5>

                        <div>
                            <div class="card shadow-sm">
                                <div class="card-header table-dark">
                                    <h5 class="mb-0">Registro de Espacios para Eventos</h5>
                                </div>
                                <div class="table card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="salonesParaEventos" class="form-label fw-bold">Salones
                                                para Eventos</label>
                                            <input type="number" class="form-control" id="salonesParaEventos"
                                                name="salones_eventos_count" placeholder="Número de salones" min="0"
                                                aria-describedby="salonesHelp">
                                            <div id="salonesHelp" class="form-text">Ingrese la cantidad de
                                                salones disponibles.
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="centroNegocios" class="form-label fw-bold">Centros
                                                de Negocios</label>
                                            <input type="number" class="form-control" id="centroNegocios"
                                                name="centro_negocios_count" placeholder="Número de centros" min="0"
                                                aria-describedby="negociosHelp">
                                            <div id="negociosHelp" class="form-text">Especifique la cantidad
                                                de centros de negocios.
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="espaciosExternos" class="form-label fw-bold">Espacios
                                                Externos para Eventos</label>
                                            <input type="number" class="form-control" id="espaciosExternos"
                                                name="espacios_externos_count" placeholder="Número de espacios" min="0"
                                                aria-describedby="externosHelp">
                                            <div id="externosHelp" class="form-text">Indique la cantidad de
                                                espacios externos.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="color: white !important;">
                                <h5 class="modal-title" style="color: white !important;" id="exampleModalLabel">
                                    SERVICIOS GENERALES DE LA
                                    HABITACIÓN
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="h6">Equipamiento</legend>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="armarioVestidor"
                                                    value="Closet" name="modal_service[]">
                                                <label class="form-check-label" for="armarioVestidor">Closet</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="banoPrivado"
                                                    value="Baño Privado" name="modal_service[]">
                                                <label class="form-check-label" for="banoPrivado">Baño
                                                    Privado</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cocineta"
                                                    value="Cocineta" name="modal_service[]">
                                                <label class="form-check-label" for="cocineta">Cocineta</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="escritorioTrabajo"
                                                    value="Escritorio de Trabajo" name="modal_service[]">
                                                <label class="form-check-label" for="escritorioTrabajo">Escritorio
                                                    de Trabajo</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="balconTerraza"
                                                    value="Balcón o Terraza" name="modal_service[]">
                                                <label class="form-check-label" for="balconTerraza">Balcón o
                                                    Terraza</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="comedor"
                                                    value="Comedor" name="modal_service[]">
                                                <label class="form-check-label" for="comedor">Comedor</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="minibar"
                                                    value="Minibar" name="modal_service[]">
                                                <label class="form-check-label" for="minibar">Minibar</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="Amenidades"
                                                    value="Amenidades" name="modal_service[]">
                                                <label class="form-check-label" for="Amenidades">Amenidades</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="Sofacama"
                                                    value="Sofá Cama" name="modal_service[]">
                                                <label class="form-check-label" for="Sofacama">Sofá Cama</label>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="h6">Tecnología y Seguridad</legend>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="telefono"
                                                    value="Teléfono" name="modal_service[]">
                                                <label class="form-check-label" for="telefono">Teléfono</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cajillaSeguridad"
                                                    value="Cajilla de Seguridad" name="modal_service[]">
                                                <label class="form-check-label" for="cajillaSeguridad">Cajilla
                                                    de Seguridad</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="wifi_hab"
                                                    value="Wifi Habitación" name="modal_service[]">
                                                <label class="form-check-label" for="wifi_hab">Wifi Habitación</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="nevera"
                                                    value="Nevera" name="modal_service[]">
                                                <label class="form-check-label" for="nevera">Nevera</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="iPad" value="iPad"
                                                    name="modal_service[]">
                                                <label class="form-check-label" for="iPad">iPad</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="smartTV"
                                                    value="Smart TV" name="modal_service[]">
                                                <label class="form-check-label" for="smartTV">Smart
                                                    TV</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="baseIpodMp3"
                                                    value="Base para conectar IPOD/MP3" name="modal_service[]">
                                                <label class="form-check-label" for="baseIpodMp3">Base para
                                                    conectar IPOD/MP3</label>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="h6">Comodidades</legend>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="batasBano"
                                                    value="Batas de Baño y Pantuflas" name="modal_service[]">
                                                <label class="form-check-label" for="batasBano">Batas de
                                                    Baño y Pantuflas</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cunaBebe"
                                                    value="Cuna para bebé disponible" name="modal_service[]">
                                                <label class="form-check-label" for="cunaBebe">Cuna para
                                                    bebé disponible</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="secadorPelo"
                                                    value="Secador para pelo" name="modal_service[]">
                                                <label class="form-check-label" for="secadorPelo">Secador
                                                    para pelo</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="maquinaCafe"
                                                    value="Máquina de Café" name="modal_service[]">
                                                <label class="form-check-label" for="maquinaCafe">Máquina de
                                                    Café</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="plancha"
                                                    value="Plancha y tabla de planchar" name="modal_service[]">
                                                <label class="form-check-label" for="plancha">Plancha y
                                                    tabla de planchar</label>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-3">
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="h6">Servicios Adicionales</legend>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="servicioHabitacion"
                                                    value="Servicio a la habitación" name="modal_service[]">
                                                <label class="form-check-label" for="servicioHabitacion">Servicio a
                                                    la habitación</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="servicioDespertador"
                                                    value="Servicio Despertador" name="modal_service[]">
                                                <label class="form-check-label" for="servicioDespertador">Servicio
                                                    Despertador</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="llamadasLocales"
                                                    value="Llamadas Locales" name="modal_service[]">
                                                <label class="form-check-label" for="llamadasLocales">Llamadas
                                                    Locales</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    id="llamadasInternacionales" value="Llamadas Internacionales"
                                                    name="modal_service[]">
                                                <label class="form-check-label" for="llamadasInternacionales">Llamadas
                                                    Internacionales</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="tinaHidromasajes"
                                                    value="Tina de Hidromasajes" name="modal_service[]">
                                                <label class="form-check-label" for="tinaHidromasajes">Tina
                                                    de Hidromasajes</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    id="Aire Acondicionado_hab" value="Aire Acondicionado Habitación"
                                                    name="modal_service[]">
                                                <label class="form-check-label" for="Aire Acondicionado_hab">Aire
                                                    Acondicionado</label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="descripcion">Observaciones:</label>
                                    <textarea class="form-control" id="descripcion_modal" rows="3"
                                        placeholder="Observaciones: (relacione los servicios que no se encuentran en la lista y su costo, si aplica)"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-info" id="checkAllBtn">Chequear
                                    Todas</button>
                                <button type="button" class="btn btn-secondary" id="cancelBtn"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="adicionarBtn">Adicionar</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="section5" class="form-section">
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
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mercado_distribucion[]"
                                                id="mercado-nac" value="Nacional" required>
                                            <label class="form-check-label" for="mercado-nac">Nacional</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mercado_distribucion[]"
                                                id="mercado-int" value="Internacional">
                                            <label class="form-check-label" for="mercado-int">Internacional</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mercado_distribucion[]"
                                                id="mercado-ambos" value="Ambos">
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
                                        <input type="text" class="form-control" id="monto_visual" placeholder="$ 0">

                                        <input type="hidden" name="monto_credito" id="monto_real">
                                    </td>
                                </tr>
                                <script>
                                    const inputVisual = document.getElementById('monto_visual');
                                    const inputReal = document.getElementById('monto_real');

                                    inputVisual.addEventListener('input', (e) => {
                                        // 1. Limpiar el valor de cualquier cosa que no sea número
                                        let valor = e.target.value.replace(/\D/g, '');

                                        // 2. Guardar el valor limpio en el input oculto (lo que va a la DB)
                                        inputReal.value = valor;

                                        // 3. Formatear para la vista del usuario
                                        if (valor) {
                                            e.target.value = new Intl.NumberFormat('es-CO', {
                                                style: 'currency',
                                                currency: 'COP',
                                                minimumFractionDigits: 0
                                            }).format(valor);
                                        } else {
                                            e.target.value = '';
                                        }
                                    });
                                </script>

                                <tr>
                                    <td><strong>Tiempo de crédito aprobado:</strong></td>
                                    <td><input type="text" class="form-control" name="tiempo_credito"
                                            placeholder="Ejemplo: 5 Dias"></td>
                                </tr>
                                <tr>
                                    <td><strong>Indicar %RETEICA:</strong></td>
                                    <td><input type="text" class="form-control" name="porcentaje_reteica"
                                            placeholder="Ejemplo: 5%"></td>
                                </tr>
                                <tr>
                                    <td><strong>Indicar % RETEFUENTE:</strong></td>
                                    <td><input type="text" class="form-control" name="porcentaje_retefuente"
                                            placeholder="Ejemplo: 5%"></td>
                                </tr>
                                <tr>
                                    <td><strong>Código CIIU:</strong></td>
                                    <td>
                                        <input type="text" name="ciiu" class="form-control" placeholder="Ej: 5511"
                                            maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                            required>
                                        <div class="form-text">Código de 4 dígitos de su actividad económica.</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo de contribuyente:</strong></td>
                                    <td>
                                        <select id="tipoContribuyente" name="tipo_contribuyente" class="form-select"
                                            required>
                                            <option value="" disabled selected>-- Selecciona un tipo de contribuyente --
                                            </option>

                                            <optgroup label="Personas Naturales">
                                                <option value="PN - No responsable de IVA">Persona Natural - No
                                                    responsable de IVA</option>
                                                <option value="PN - Responsable de IVA">Persona Natural - Responsable de
                                                    IVA</option>
                                                <option value="PN - Régimen Simple (RST)">Persona Natural - Régimen
                                                    Simple (RST)</option>
                                                <option value="PN - Profesional Independiente">Persona Natural -
                                                    Profesional Independiente</option>
                                            </optgroup>

                                            <optgroup label="Personas Jurídicas">
                                                <option value="PJ - Responsable de IVA">Persona Jurídica - Responsable
                                                    de IVA</option>
                                                <option value="PJ - No responsable de IVA">Persona Jurídica - No
                                                    responsable de IVA</option>
                                                <option value="PJ - Gran Contribuyente">Gran Contribuyente</option>
                                                <option value="PJ - Autorretenedor">Autorretenedor</option>
                                                <option value="PJ - Entidad sin Ánimo de Lucro (ESAL)">Entidad sin Ánimo
                                                    de Lucro (ESAL)</option>
                                                <option value="PJ - Sociedad de Comercialización Internacional">Sociedad
                                                    de Comercialización Internacional (C.I.)</option>
                                            </optgroup>

                                            <optgroup label="Casos Especiales / Otros">
                                                <option value="Consorcio / Unión Temporal">Consorcio / Unión Temporal
                                                </option>
                                                <option value="Sucesión Ilíquida">Sucesión Ilíquida (Herencias no
                                                    repartidas)</option>
                                                <option value="Entidad de Derecho Público">Entidad de Derecho Público
                                                    (Gobierno)</option>
                                                <option value="No Residente">Persona Natural Extranjera / No Residente
                                                </option>
                                                <option value="Otro">Otro (No especificado)</option>
                                            </optgroup>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Número de cuenta bancaria:</strong></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-bank"></i> #
                                            </span>
                                            <input type="text" class="form-control" id="numero_cuenta"
                                                name="numero_cuenta" placeholder="Ej: 0123456789" pattern="\[0-9]*"
                                                inputmode="numeric"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                                        </div>
                                        <div class="form-text">Solo se permiten números, sin espacios ni guiones.</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Forma de conexión (una opción):</strong></td>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="connectionType"
                                                id="extranetRadio" value="extranet">
                                            <label class="form-check-label" for="extranetRadio">Extranet</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="connectionType"
                                                id="channelManagerRadio" value="channelManager">
                                            <label class="form-check-label" for="channelManagerRadio">Channel
                                                Manager</label>
                                        </div>
                                        <select id="channelManagerName" name="channelName" class="form-select"
                                            style="display: none;">
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
                                        <input type="text" id="otroChannelInput" name="channelName" class="form-control"
                                            placeholder="Escribe el nombre del Channel Manager"
                                            style="display: none; margin-top: 10px;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo de inventario (solo aplica para extranet):</strong></td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="allotmentCheckbox"
                                                name="allotment_selected">
                                            <label class="form-check-label" for="allotmentCheckbox">Allotment</label>
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
                                                                    <button style="padding: 5px !important; "
                                                                        type="button" class="btn btn-danger btn-sm"
                                                                        onclick="eliminarFilaAllotment(this)">Eliminar</button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipos de tarifas a conectar:</strong></td>
                                    <td>
                                        <label><input type="checkbox" id="tarifaFIT" name="tarifa_tipo[]" value="FIT">
                                            FIT</label>
                                        <label><input type="checkbox" id="tarifaDinamica" name="tarifa_tipo[]"
                                                value="Dinamicas">
                                            Dinámicas</label>
                                        <label><input type="checkbox" id="tarifaAmbas" name="tarifa_tipo[]"
                                                value="Ambos"> Ambos</label>
                                    </td>
                                </tr>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const chkFIT = document.getElementById('tarifaFIT');
                                        const chkDinamica = document.getElementById('tarifaDinamica');
                                        const chkAmbas = document.getElementById('tarifaAmbas');

                                        function setAmbas(checked) {
                                            chkFIT.checked = checked;
                                            chkDinamica.checked = checked;
                                            chkFIT.disabled = checked;
                                            chkDinamica.disabled = checked;
                                        }

                                        chkAmbas.addEventListener('change', function () {
                                            setAmbas(this.checked);
                                        });

                                        // Si el usuario recarga y "Ambos" está marcado, mantener el estado
                                        setAmbas(chkAmbas.checked);
                                    });
                                </script>
                                <tr>
                                    <td><strong>Descuento fijo (opaco) sobre tarifa Dinámica:</strong></td>
                                    <td><input type="text" class="form-control" name="descuento_dinamico"
                                            placeholder="Escribir texto..."></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <strong>Vigencia del acuerdo:</strong><br>
                                        Será de un (1) año contado a partir de la fecha de su firma, renovándose
                                        automáticamente a su vencimiento en forma sucesiva y por períodos iguales, salvo
                                        que alguna de LAS PARTES notifique por escrito a la otra con no menos de 30 días
                                        calendario de anticipación a la fecha de vencimiento del acuerdo, su voluntad
                                        de no renovar el mismo.
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
                                <tr>
                                    <td><input type="text" class="form-control" name="plan_tarifario_nombre[]"
                                            placeholder="Ejemplo: Standard Rate Refundable - Breakfast included."></td>
                                    <td><input type="text" class="form-control" name="plan_tarifario_cancelacion[]"
                                            placeholder="2 días"></td>
                                    <td><input type="text" class="form-control" name="plan_tarifario_penalidad[]"
                                            placeholder="1 noche"></td>
                                    <td><input type="text" class="form-control" name="plan_tarifario_no_show[]"
                                            placeholder="100% de la reserva"></td>
                                    <td><input type="text" class="form-control"
                                            name="plan_tarifario_salida_anticipada[]" placeholder="50% de la reserva">
                                    </td>
                                    <td><button class="btn btn-danger btn-sm" type="button"
                                            onclick="eliminarFilaTablaTarifas(this)">Eliminar</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mb-3" onclick="agregarFilaTablaTarifas()">Agregar
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
                                    <td><input type="text" class="form-control" name="politica_ninos"
                                            placeholder="(Especificar si incluye o no el desayuno)"></td>
                                </tr>
                                <tr>
                                    <td>Grupos</td>
                                    <td><input type="text" class="form-control" name="politica_grupos"
                                            placeholder="(Desde cuántas habitaciones se manejan grupos)"></td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="text-muted mt-2">* El acuerdo dinámico se habilita mínimo desde 1 año</p>
                    </div>
                </div>
            </div>


            <div id="section6" class="form-section">
                <div class="alert alert-warning">
                    <strong>Recomendaciones:</strong>
                    <p>No olvide proporcionar entre 15 a 25 fotos (de preferencia horizontales) en alta
                        resolución de la propiedad, etc.</p>
                    <p>El material que nos proporcione será usado para la promoción de los servicios...</p>
                    <p>A continuación, adjunte las fotos mediante el botón designado. Estas deben ser subidas con el
                        nombre de la propiedad y el nombre correspondiente en cada imagen. Ejemplo: (fachada, recepción,
                        restaurantes, salones, zonas comunes, habitaciones, baños, etc)"
                    </p>
                    <!-- Botón que abre el popup de fotos promocionales -->
                    <div class="mt-3">
                        <label class="form-label fw-semibold">📸 Fotos promocionales principales <span
                                class="text-danger">*</span></label>
                        <br>
                        <button type="button" class="btn btn-primary btn-sm" id="btnAbrirFotosPromo">
                            <i class="fa fa-camera me-1"></i> Subir fotos promocionales
                        </button>
                        <div id="resumenFotosPromo" class="mt-2 small text-muted"></div>
                    </div>

                    <!-- Modal con los 4 inputs individuales -->
                    <div class="modal fade" id="modalFotosPromo" tabindex="-1" aria-labelledby="modalFotosPromoLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header" style="background:#2C56E6;color:#fff;">
                                    <h5 class="modal-title" style="color: white;" id="modalFotosPromoLabel">
                                        <i class="fa fa-camera me-2"></i> 4 Fotos Promocionales Principales
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted mb-3">Estas fotos serán usadas en el PDF de promoción. Suba
                                        <strong>una foto por categoría</strong>.
                                    </p>
                                    <div class="row g-3">

                                        <!-- Fachada -->
                                        <div class="col-12 col-md-6">
                                            <div class="card h-100 border" style="border-radius:10px;">
                                                <div class="card-body">
                                                    <label class="form-label fw-semibold" for="foto_fachada">🏨
                                                        Fachada</label>
                                                    <p class="text-muted small mb-2">Vista exterior del hotel o
                                                        propiedad.</p>
                                                    <input type="file" class="form-control foto-promo-input"
                                                        id="foto_fachada" name="foto_fachada" accept="image/*" required
                                                        aria-required="true">
                                                    <div class="mt-2 text-center">
                                                        <img id="prev_foto_fachada" src="" alt=""
                                                            class="img-thumbnail d-none"
                                                            style="max-height:110px;object-fit:cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Habitaciones -->
                                        <div class="col-12 col-md-6">
                                            <div class="card h-100 border" style="border-radius:10px;">
                                                <div class="card-body">
                                                    <label class="form-label fw-semibold" for="foto_habitaciones">🛏️
                                                        Habitaciones</label>
                                                    <p class="text-muted small mb-2">Foto representativa de las
                                                        habitaciones disponibles.</p>
                                                    <input type="file" class="form-control foto-promo-input"
                                                        id="foto_habitaciones" name="foto_habitaciones" accept="image/*"
                                                        required aria-required="true">
                                                    <div class="mt-2 text-center">
                                                        <img id="prev_foto_habitaciones" src="" alt=""
                                                            class="img-thumbnail d-none"
                                                            style="max-height:110px;object-fit:cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Piscina -->
                                        <div class="col-12 col-md-6">
                                            <div class="card h-100 border" style="border-radius:10px;">
                                                <div class="card-body">
                                                    <label class="form-label fw-semibold" for="foto_piscina">🏊 Piscina
                                                        o zona recreativa</label>
                                                    <p class="text-muted small mb-2">Ejemplos: piscina, jacuzzi, spa,
                                                        gimnasio, terraza.</p>
                                                    <input type="file" class="form-control foto-promo-input"
                                                        id="foto_piscina" name="foto_piscina" accept="image/*" required
                                                        aria-required="true">
                                                    <div class="mt-2 text-center">
                                                        <img id="prev_foto_piscina" src="" alt=""
                                                            class="img-thumbnail d-none"
                                                            style="max-height:110px;object-fit:cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Zona común -->
                                        <div class="col-12 col-md-6">
                                            <div class="card h-100 border" style="border-radius:10px;">
                                                <div class="card-body">
                                                    <label class="form-label fw-semibold" for="foto_zona_comun">🛎️ Zona
                                                        común</label>
                                                    <p class="text-muted small mb-2">Ejemplos: lobby, salón de eventos,
                                                        restaurante, bar, sala de reuniones.</p>
                                                    <input type="file" class="form-control foto-promo-input"
                                                        id="foto_zona_comun" name="foto_zona_comun" accept="image/*"
                                                        required aria-required="true">
                                                    <div class="mt-2 text-center">
                                                        <img id="prev_foto_zona_comun" src="" alt=""
                                                            class="img-thumbnail d-none"
                                                            style="max-height:110px;object-fit:cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" data-bs-dismiss="modal"
                                        id="btnGuardarFotos">
                                        <i class="fa fa-check me-1"></i> Guardar selección
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resto de fotos generales -->
                    <div class="mt-3">
                        <label for="foto" class="form-label fw-semibold">📁 Fotos adicionales de la propiedad</label>
                        <p class="text-muted small mb-2">Adjunte entre 15 y 25 fotos adicionales (fachada, recepción,
                            restaurantes, salones, baños, etc.).</p>
                        <input type="file" class="form-control" id="foto" name="fotos_hotel[]" accept="image/*" multiple
                            required aria-required="true">
                    </div>

                    <script>
                        // Preview de cada foto al seleccionarla dentro del modal
                        document.querySelectorAll('.foto-promo-input').forEach(function (input) {
                            input.addEventListener('change', function () {
                                var preview = document.getElementById('prev_' + this.id);
                                if (this.files && this.files[0]) {
                                    var reader = new FileReader();
                                    reader.onload = function (e) {
                                        preview.src = e.target.result;
                                        preview.classList.remove('d-none');
                                    };
                                    reader.readAsDataURL(this.files[0]);
                                } else {
                                    preview.src = '';
                                    preview.classList.add('d-none');
                                }
                                actualizarResumenFotos();
                            });
                        });

                        // Abrir modal
                        document.getElementById('btnAbrirFotosPromo').addEventListener('click', function () {
                            var modal = new bootstrap.Modal(document.getElementById('modalFotosPromo'));
                            modal.show();
                        });

                        // Resumen debajo del botón principal al cerrar el modal
                        function actualizarResumenFotos() {
                            var nombres = {
                                foto_fachada: '🏨 Fachada',
                                foto_habitaciones: '🛏️ Habitaciones',
                                foto_piscina: '🏊 Piscina',
                                foto_zona_comun: '🛎️ Zona común'
                            };
                            var resumen = [];
                            Object.keys(nombres).forEach(function (id) {
                                var input = document.getElementById(id);
                                if (input && input.files && input.files[0]) {
                                    resumen.push('<span class="badge bg-success me-1">' + nombres[id] + ' ✔</span>');
                                } else {
                                    resumen.push('<span class="badge bg-secondary me-1">' + nombres[id] + ' —</span>');
                                }
                            });
                            document.getElementById('resumenFotosPromo').innerHTML = resumen.join('');
                        }

                        document.getElementById('modalFotosPromo').addEventListener('hidden.bs.modal', actualizarResumenFotos);
                    </script>
                </div>

                <div class="documents-section">
                    <h2 class="section-title">Carga de Documentos Anexos</h2>

                    <h3 class="category-title">Documentos Obligatorios</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <label for="rut" class="form-label">RUT Actualizado <span
                                    class="text-danger">*</span></label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['RUT'], 'rut_existente'); ?>
                            <input type="file" class="form-control" id="rut" name="rut[]" accept=".pdf" multiple
                                <?= empty(pnv_docs_validos_por_tipos($documentos_existentes, ['RUT'])) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_validos_por_tipos($documentos_existentes, ['RUT']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                            <div id="vigencia-rut" class="vigencia-indicator"></div>
                        </li>
                        <li class="mb-3">
                            <label for="registro_turismo" class="form-label">Registro Nacional de Turismo Actualizado
                                <span class="text-danger">*</span></label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['RNT', 'Registro Nacional de Turismo'], 'registro_turismo_existente'); ?>
                            <input type="file" class="form-control" id="registro_turismo" name="registro_turismo[]"
                                accept=".pdf" multiple <?= empty(pnv_docs_validos_por_tipos($documentos_existentes, ['RNT', 'Registro Nacional de Turismo'])) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_validos_por_tipos($documentos_existentes, ['RNT', 'Registro Nacional de Turismo']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                            <div id="vigencia-registro_turismo" class="vigencia-indicator"></div>
                        </li>
                        <li class="mb-3">
                            <label for="camara_comercio" class="form-label">Cámara de Comercio (no mayor a 30 días)
                                <span class="text-danger">*</span></label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Camara de Comercio', 'Cámara de Comercio'], 'camara_comercio_existente'); ?>
                            <input type="file" class="form-control" id="camara_comercio" name="camara_comercio[]"
                                accept=".pdf" multiple <?= empty(pnv_docs_validos_por_tipos($documentos_existentes, ['Camara de Comercio', 'Cámara de Comercio'])) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_validos_por_tipos($documentos_existentes, ['Camara de Comercio', 'Cámara de Comercio']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                            <div id="vigencia-camara_comercio" class="vigencia-indicator"></div>
                        </li>
                        <li class="mb-3">
                            <label for="certificacion_bancaria" class="form-label">Certificación Bancaria <span
                                    class="text-danger">*</span></label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Certificacion Bancaria', 'Certificación Bancaria'], 'certificacion_bancaria_existente'); ?>
                            <input type="file" class="form-control" id="certificacion_bancaria"
                                name="certificacion_bancaria[]" accept=".pdf" multiple
                                <?= empty(pnv_docs_validos_por_tipos($documentos_existentes, ['Certificacion Bancaria', 'Certificación Bancaria'])) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_validos_por_tipos($documentos_existentes, ['Certificacion Bancaria', 'Certificación Bancaria']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                            <div id="vigencia-certificacion_bancaria" class="vigencia-indicator"></div>
                        </li>
                    </ul>

                    <h3 class="category-title">Documentos Opcionales</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <label for="certificados_sostenibilidad" class="form-label">Certificados de
                                Sostenibilidad</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Certificados Sostenibilidad', 'Certificados de Sostenibilidad'], 'certificados_sostenibilidad_existente'); ?>
                            <input type="file" class="form-control" id="certificados_sostenibilidad"
                                name="certificados_sostenibilidad[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Certificados Sostenibilidad', 'Certificados de Sostenibilidad']))
                                    ? 'Solo archivos PDF (si aplica). Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF (si aplica)' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="certificado_bomberos" class="form-label">Certificado de Seguridad de
                                Bomberos</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Certificado Bomberos', 'Certificado de Seguridad de Bomberos'], 'certificado_bomberos_existente'); ?>
                            <input type="file" class="form-control" id="certificado_bomberos"
                                name="certificado_bomberos[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Certificado Bomberos', 'Certificado de Seguridad de Bomberos']))
                                    ? 'Solo archivos PDF (si aplica). Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF (si aplica)' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="informacion_credito" class="form-label">Información sobre Crédito Actual</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Informacion Credito', 'Información sobre Crédito Actual'], 'informacion_credito_existente'); ?>
                            <input type="file" class="form-control" id="informacion_credito"
                                name="informacion_credito[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Informacion Credito', 'Información sobre Crédito Actual']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="concepto_sanitario" class="form-label">Concepto Técnico Sanitario</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Concepto Sanitario', 'Concepto Técnico Sanitario'], 'concepto_sanitario_existente'); ?>
                            <input type="file" class="form-control" id="concepto_sanitario" name="concepto_sanitario[]"
                                accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Concepto Sanitario', 'Concepto Técnico Sanitario']))
                                    ? 'Solo archivos PDF (si aplica). Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF (si aplica)' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="mantenimiento_piscinas" class="form-label">Certificado de Mantenimiento de
                                Piscinas</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Mantenimiento Piscinas', 'Certificado de Mantenimiento de Piscinas'], 'mantenimiento_piscinas_existente'); ?>
                            <input type="file" class="form-control" id="mantenimiento_piscinas"
                                name="mantenimiento_piscinas[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Mantenimiento Piscinas', 'Certificado de Mantenimiento de Piscinas']))
                                    ? 'Solo archivos PDF (si aplica). Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF (si aplica)' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="mantenimiento_ascensores" class="form-label">Certificado de Mantenimiento de
                                Ascensores</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['Mantenimiento Ascensores', 'Certificado de Mantenimiento de Ascensores'], 'mantenimiento_ascensores_existente'); ?>
                            <input type="file" class="form-control" id="mantenimiento_ascensores"
                                name="mantenimiento_ascensores[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['Mantenimiento Ascensores', 'Certificado de Mantenimiento de Ascensores']))
                                    ? 'Solo archivos PDF (si aplica). Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF (si aplica)' ?>
                            </small>
                        </li>
                        <li class="mb-3">
                            <label for="sg_sst" class="form-label">Certificado de Implementación de SG-SST</label>
                            <?php pnv_render_docs_existentes($documentos_existentes, ['SG-SST', 'Certificado de Implementación de SG-SST'], 'sg_sst_existente'); ?>
                            <input type="file" class="form-control" id="sg_sst" name="sg_sst[]" accept=".pdf" multiple>
                            <small class="form-text text-muted">
                                <?= !empty(pnv_docs_por_tipos($documentos_existentes, ['SG-SST', 'Certificado de Implementación de SG-SST']))
                                    ? 'Solo archivos PDF. Puede agregar un nuevo PDF si desea actualizar o complementar el documento.'
                                    : 'Solo archivos PDF' ?>
                            </small>
                        </li>
                    </ul>
                    <div class="alert alert-info" style="margin-top: 15px; padding: 10px 15px; border-radius: 8px;">
                        <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Los documentos se validarán
                        automáticamente con IA cuando envíe el formulario.
                    </div>
                </div>
            </div>


            <div id="mensajeGuardado"
                style="display:none; position:fixed; bottom:20px; right:20px; background:#d4edda; color:#155724; padding:10px 20px; border:1px solid #c3e6cb; border-radius:5px; z-index:9999;">
                Se ha guardado el formulario
            </div>

            <div class="d-flex justify-content-center gap-3 mb-6">
                <button style="height: 0.5%;" type="button" class="btn btn-secondary fw-bold mb-3" id="prevBtn"
                    style="display: none;">Anterior</button>

                <button type="button" class="btn btn-primary fw-bold mb-3" id="nextBtn">Siguiente</button>

                <div class="text-center mb-4" id="submitSection" style="display: none;">
                    <button style="font-size: 1rem !important; padding: 0.62rem 1rem !important;" type="button"
                        class="btn btn-success fw-bold" id="finalSubmitBtn">
                        Enviar Formulario
                    </button>
                </div>
            </div>

            <div id="floatingClearContainer">
                <button type="button" class="btn btn-danger fw-bold shadow-lg" id="clearFormBtn">
                    <i class="fas fa-eraser"></i> Limpiar Formulario
                </button>
            </div>

            <!-- ===================== MODAL DILIGENCIADOR ===================== -->
            <div class="modal fade" id="modalDiligenciador" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" style="color: #fff">Confirmar envío</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2 text-muted">
                                Para enviar la ficha, indique quién diligenció la información.
                            </p>

                            <!-- CAMPOS OBLIGATORIOS / OPCIONALES -->
                            <div class="mb-3">
                                <label class="form-label"><strong>Nombre de quien diligencia *</strong></label>
                                <input type="text" class="form-control" id="diligencia_nombre" name="diligencia_nombre"
                                    required placeholder="Ej: Juan Pérez" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Correo</strong> (opcional)</label>
                                <input type="email" class="form-control" id="diligencia_correo" name="diligencia_correo"
                                    placeholder="Ej: reservas@hotel.com">
                            </div>

                            <div class="mb-2">
                                <label class="form-label"><strong>Cargo / Área</strong> (opcional)</label>
                                <input type="text" class="form-control" id="diligencia_cargo" name="diligencia_cargo"
                                    placeholder="Ej: Comercial / Reservas">
                            </div>

                            <small class="text-muted d-block mt-2">
                                Este dato quedará registrado como soporte del envío.
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                            <!-- ESTE BOTÓN DISPARA EL SUBMIT REAL -->
                            <button type="button" class="btn btn-success fw-bold" id="btnEnviarDesdeModal">
                                Enviar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- =============================================================== -->
        </form>
    </div>

    <script>
        window.PRECARGA_FORMULARIO = <?php echo json_encode($precarga_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>

    <script>

        // Función para renderizar las filas de Planes Tarifarios guardadas en el borrador
        function renderPlanesTarifarios(planes) {
            const tbody = document.querySelector('#tablaTarifas tbody');

            // Validamos la existencia del contenedor y que el array tenga datos
            if (!tbody) return;

            // Limpiamos el contenido previo (como la fila de ejemplo estática)
            tbody.innerHTML = '';

            if (!planes || planes.length === 0) {
                // Opcional: Si quieres que siempre haya una fila vacía si no hay datos, 
                // podrías llamar a agregarFilaTablaTarifas() aquí.
                return;
            }

            // Iteramos sobre cada plan tarifario del JSON
            planes.forEach(p => {
                const tr = document.createElement('tr');

                // Inyectamos los valores del JSON en los inputs correspondientes
                // s.nombre, p.cancelacion, p.penalidad, p.no_show, p.salida
                tr.innerHTML = `
            <td>
                <input type="text" class="form-control" name="plan_tarifario_nombre[]" 
                    value="${escapeHtml(p.nombre)}">
            </td>
            <td>
                <input type="text" class="form-control" name="plan_tarifario_cancelacion[]" 
                    value="${escapeHtml(p.cancelacion)}">
            </td>
            <td>
                <input type="text" class="form-control" name="plan_tarifario_penalidad[]" 
                    value="${escapeHtml(p.penalidad)}">
            </td>
            <td>
                <input type="text" class="form-control" name="plan_tarifario_no_show[]" 
                    value="${escapeHtml(p.no_show)}">
            </td>
            <td>
                <input type="text" class="form-control" name="plan_tarifario_salida_anticipada[]" 
                    value="${escapeHtml(p.salida)}">
            </td>
            <td>
                <button class="btn btn-danger btn-sm" type="button" 
                    onclick="eliminarFilaTablaTarifas(this)">Eliminar</button>
            </td>
        `;

                tbody.appendChild(tr);
            });
        }

        // INTEGRACIÓN EN TU LÓGICA DE CARGA:
        // Busca donde haces el success del AJAX de obtener borrador:


        // Función para mostrar el mensaje de caché
        function mostrarMensaje(mensaje) {
            const msgEl = document.getElementById('mensajeGuardado');
            if (msgEl) {
                msgEl.textContent = mensaje;
                msgEl.style.display = 'block';
                setTimeout(() => {
                    msgEl.style.display = 'none';
                }, 3000);
            }
        }

        // --- LOGICA DE CACHE ---
        function guardarCamposEnCache() {
            const datos = {};
            document.querySelectorAll('input, select, textarea').forEach(campo => {
                // Priorizamos NAME para inputs de formulario, ID para campos de control
                const key = campo.name || campo.id;
                if (!key) return;

                if (campo.type === 'radio' || campo.type === 'checkbox') {
                    if (campo.checked) {
                        if (key.endsWith('[]')) {
                            datos[key] = datos[key] || [];
                            datos[key].push(campo.value);
                        } else {
                            datos[key] = campo.value;
                        }
                    }
                } else {
                    // Aquí entran los tipo 'number', 'text', 'hidden', etc.
                    datos[key] = campo.value;
                }
            });

            localStorage.setItem('formularioCache', JSON.stringify(datos));
        }

        function recuperarCamposDesdeCache() {
            const cache = localStorage.getItem('formularioCache');
            if (!cache) return;

            try {
                const datos = JSON.parse(cache);
                for (let key in datos) {

                    if (key === 'nitSufijo' || key === 'nit_consecutivo') continue;

                    // Manejo de arrays (checkboxes)
                    if (key.endsWith('[]') && Array.isArray(datos[key])) {
                        document.querySelectorAll(`input[name="${key}"]`).forEach(campo => {
                            campo.checked = datos[key].includes(campo.value);
                            campo.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        continue;
                    }

                    // Buscar el campo por nombre exacto (incluyendo corchetes) o por ID
                    const campo = document.querySelector(`[name="${key}"]`) || document.getElementById(key);
                    if (!campo) continue;

                    if (campo.type === 'checkbox' || campo.type === 'radio') {
                        if (campo.value === datos[key]) {
                            campo.checked = true;
                        }
                    } else {
                        campo.value = datos[key];
                    }

                    // Disparamos ambos eventos para asegurar que la UI reaccione
                    campo.dispatchEvent(new Event('input', { bubbles: true }));
                    campo.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Ejecutar funciones de refresco de UI si existen
                if (typeof actualizarOpcionesTarifa === 'function') actualizarOpcionesTarifa();
                if (typeof toggleDynamicTable === 'function') toggleDynamicTable();
                if (typeof actualizarContadores === 'function') actualizarContadores(); // Por si tienes contadores de texto

            } catch (e) {
                console.error("Error recuperando desde caché:", e);
            }
        }

        /* PEGAR AQUÍ */
        function actualizarCoberturaInternet() {
            const noInternet = document.getElementById('no_hay_internet');

            const otrosChecks = [
                document.getElementById('internet_habitaciones'),
                document.getElementById('internet_areas'),
                document.getElementById('internet_publicas')
            ].filter(Boolean);

            if (!noInternet) return;

            if (noInternet.checked) {
                otrosChecks.forEach(check => {
                    check.checked = false;
                    check.disabled = true;
                });
            } else {
                otrosChecks.forEach(check => {
                    check.disabled = false;
                });
            }
        }

        // --- FIN LOGICA DE CACHE ---

        // --- LÓGICA DEL BOTÓN DE LIMPIEZA ---

        function limpiarFormularioCompleto() {
            if (confirm("¿Estás seguro que deseas limpiar todo el formulario? Se perderá el progreso guardado en el navegador.")) {

                // 1. Resetear la caché del navegador (CLAVE)
                localStorage.removeItem('formularioCache');

                // 2. Resetear todos los campos del formulario (incluye inputs, selects, textareas)
                const form = document.getElementById('multiStepForm');
                if (form) {
                    form.reset();
                }

                // 3. (Opcional) Forzar la recarga para volver a la Sección 1 y recargar la UI
                window.location.reload();

                // O si prefieres solo resetear la UI sin recargar, puedes llamar a showSection(0)
                // showSection(0); 
            }
        }



        // ...
        // --- Bloque de Inicialización principal ---
        document.addEventListener('DOMContentLoaded', () => {
            // ... (Tu lógica existente: recuperarCamposDesdeCache, showSection, setInterval, etc.) ...

            const noInternet = document.getElementById('no_hay_internet');

            const otrosChecksInternet = [
                document.getElementById('internet_habitaciones'),
                document.getElementById('internet_areas'),
                document.getElementById('internet_publicas')
            ].filter(Boolean);

            if (noInternet) {
                noInternet.addEventListener('change', actualizarCoberturaInternet);
            }

            otrosChecksInternet.forEach(check => {
                check.addEventListener('change', function () {
                    if (this.checked && noInternet) {
                        noInternet.checked = false;
                        actualizarCoberturaInternet();
                    }
                });
            });

            actualizarCoberturaInternet();

            const clearBtn = document.getElementById('clearFormBtn');

            if (clearBtn) {
                // Asignar la función de limpieza al botón
                clearBtn.addEventListener('click', limpiarFormularioCompleto);
            }

            // ... (El resto de la lógica de envío del spinner)
        });
    </script>
    <script>
        const selectChannel = document.getElementById("channelManagerName");
        const inputOtro = document.getElementById("otroChannelInput");

        selectChannel.addEventListener("change", function () {
            if (this.value === "Otro") {
                inputOtro.style.display = "block";  // Mostrar campo
            } else {
                inputOtro.style.display = "none";   // Ocultar campo
                inputOtro.value = "";               // Limpiar campo
            }
        });
    </script>


    <script>
        // Funciones de la Sección 6: Conexión y Tarifas
        function actualizarOpcionesTarifa() {
            const extranet = document.getElementById('extranetRadio')?.checked;
            const channelManager = document.getElementById('channelManagerRadio')?.checked;

            const tarifaFIT = document.getElementById('tarifaFIT');
            const tarifaDinamica = document.getElementById('tarifaDinamica');
            const tarifaAmbas = document.getElementById('tarifaAmbas');
            const channelManagerName = document.getElementById('channelManagerName');
            const allotmentCheckbox = document.getElementById('allotmentCheckbox');
            const allotmentFields = document.getElementById('allotmentFields');

            const elements = [tarifaFIT, tarifaDinamica, tarifaAmbas, channelManagerName, allotmentCheckbox, allotmentFields];
            if (elements.some(e => e === null)) {
                // console.error('Uno o más elementos de la Sección 6 no se encontraron en el DOM.');
                return;
            }

            // Lógica para "Forma de conexión"
            if (extranet) {
                // Opciones de Tarifa
                tarifaFIT.disabled = false;
                tarifaDinamica.disabled = true;
                tarifaDinamica.checked = false; // Desmarcar si se cambia a Extranet
                tarifaAmbas.disabled = true;
                tarifaAmbas.checked = false; // Desmarcar si se cambia a Extranet

                // Channel Manager Name
                channelManagerName.style.display = 'none';
                channelManagerName.disabled = true;
                channelManagerName.value = '';
                channelManagerName.removeAttribute('required');

                // Allotment
                allotmentCheckbox.closest('.form-check').style.display = 'block';
                allotmentCheckbox.disabled = false;
                allotmentFields.style.display = allotmentCheckbox.checked ? 'block' : 'none';

            } else if (channelManager) {
                // Opciones de Tarifa
                tarifaFIT.disabled = false;
                tarifaDinamica.disabled = false;
                tarifaAmbas.disabled = false;

                // Channel Manager Name
                channelManagerName.style.display = 'block';
                channelManagerName.disabled = false;
                channelManagerName.setAttribute('required', 'required');

                // Allotment
                allotmentCheckbox.closest('.form-check').style.display = 'none';
                allotmentCheckbox.disabled = true;
                allotmentCheckbox.checked = false; // Desmarcar
                allotmentFields.style.display = 'none';

            } else { // Ninguna seleccionada
                // Opciones de Tarifa
                tarifaFIT.disabled = true;
                tarifaFIT.checked = false;
                tarifaDinamica.disabled = true;
                tarifaDinamica.checked = false;
                tarifaAmbas.disabled = true;
                tarifaAmbas.checked = false;

                // Channel Manager Name
                channelManagerName.style.display = 'none';
                channelManagerName.disabled = true;
                channelManagerName.value = '';
                channelManagerName.removeAttribute('required');

                // Allotment
                allotmentCheckbox.closest('.form-check').style.display = 'none';
                allotmentCheckbox.disabled = true;
                allotmentCheckbox.checked = false;
                allotmentFields.style.display = 'none';
            }

            // Llamar a la función que maneja la visibilidad de la tabla de planes tarifarios
            toggleDynamicTable();
        }

        function toggleDynamicTable() {
            const dinamica = document.getElementById('tarifaDinamica')?.checked;
            const ambas = document.getElementById('tarifaAmbas')?.checked;
            const tablaDinamica = document.getElementById('tablaDinamica');
            if (!tablaDinamica) return;
            tablaDinamica.style.display = (dinamica || ambas) ? 'block' : 'none';
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
                <td><button class="btn btn-danger btn-sm" type="button" onclick="eliminarFilaTablaTarifas(this)">Eliminar</button></td>
            `;
        }

        function eliminarFilaTablaTarifas(boton) {
            const fila = boton.closest("tr");
            if (fila) fila.remove();
        }

    </script>

    <script>
        let currentRow = null;

        // Función para agregar fila a la tabla de habitaciones
        function addHabitacionRow() {
            try {
                const tableBody = document.querySelector('#habitacionTable tbody');
                if (!tableBody) return;

                const newRow = document.createElement('tr');
                // IMPORTANTE: Se han corregido los 'name' en este template para que sean arrays de PHP
                newRow.innerHTML = `
                    <td class="sticky-col">
                        <input type="text" class="form-control form-control-sm" placeholder="Estandar" name="tipo_habitacion[]" required aria-label="Tipo de habitación">
                    </td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="total_habitaciones[]" placeholder="1" required aria-label="Total de habitaciones"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="max_adultos[]" placeholder="1" required aria-label="Capacidad máxima de adultos"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="max_ninos[]" placeholder="1" required aria-label="Capacidad máxima de niños"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="max_total[]" placeholder="1" required aria-label="Capacidad máxima total"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="mts2[]" placeholder="20" required aria-label="Metros cuadrados"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="cama_sencilla[]" placeholder="1" aria-label="Camas sencillas"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="cama_doble[]" aria-label="Camas dobles"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="cama_queen[]" aria-label="Camas queen"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="cama_king[]" aria-label="Camas king"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="camarote_sencillo[]" aria-label="Camarotes sencillos"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="camarote_doble[]" aria-label="Camarotes dobles"></td>
                    <td><input type="number" class="form-control form-control-sm" min="0" name="camas_adicionales[]" aria-label="Camas adicionales"></td>
                    <td><textarea class="form-control form-control-sm"name="observaciones[]" aria-label="Observaciones adicionales"rows="3"></textarea></td>
                    <td class="services-cell" data-services="" data-description="">
                    
                    <button type="button" class="btn btn-primary btn-sm add-service-btn" >Add Servicio</button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeHabitacionRow(this)">Eliminar</button>

                    </td>
                `;

                tableBody.appendChild(newRow);
                renumerarServiciosHabitaciones();

                /*attachServiceButtonListener*/(newRow.querySelector('.add-service-btn'));
                // console.log('New row added with dynamic fields');
            } catch (error) {
                console.error('Error adding row:', error);
            }
        }

        // Función para agregar fila a la tabla de salones
        function addSalonRow() {
            const tbody = document.querySelector('#tablaSalones tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" class="form-control" placeholder="Nombre" name="nombre_salon[]" aria-label="Nombre del salón"></td>
                <td><input type="number" class="form-control" placeholder="M²" min="0" step="0.1" name="m2_salon[]" aria-label="Metros cuadrados"></td>
                <td><input type="number" class="form-control" placeholder="Largo" min="0" step="0.1" name="largo_salon[]" aria-label="Largo en metros"></td>
                <td><input type="number" class="form-control" placeholder="Ancho" min="0" step="0.1" name="ancho_salon[]" aria-label="Ancho en metros"></td>
                <td><input type="number" class="form-control" placeholder="Alto" min="0" step="0.1" name="alto_salon[]" aria-label="Alto en metros"></td>
                <td><input type="number" class="form-control" placeholder="U / Herradura" min="0" name="cap_u_herradura[]" aria-label="Capacidad U o Herradura"></td>
                <td><input type="number" class="form-control" placeholder="Aula" min="0" name="cap_aula[]" aria-label="Capacidad Aula"></td>
                <td><input type="number" class="form-control" placeholder="Auditorio" min="0" name="cap_auditorio[]" aria-label="Capacidad Auditorio"></td>
                <td><input type="number" class="form-control" placeholder="Banquete" min="0" name="cap_banquete[]" aria-label="Capacidad Banquete"></td>
                <td><input type="number" class="form-control" placeholder="Imperial" min="0" name="cap_imperial[]" aria-label="Capacidad Imperial"></td>
                <td><input type="number" class="form-control" placeholder="Cóctel" min="0" name="cap_coctel[]" aria-label="Capacidad Cóctel"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeHabitacionRow(this)">Eliminar</button></td>
            `;
            tbody.appendChild(newRow);
        }

        function escapeHtml(s) {
            return String(s ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderHabitacionesFromDraft(habitaciones) {
            const tbody = document.querySelector('#habitacionTable tbody');
            if (!tbody) return;

            // 1. Limpiamos el cuerpo de la tabla para evitar duplicados
            tbody.innerHTML = '';

            // 2. Iteramos sobre el array de habitaciones del JSON
            (habitaciones || []).forEach((h, idx) => {
                const tr = document.createElement('tr');

                // --- LÓGICA DE PROCESAMIENTO DE SERVICIOS (JSON anidado) ---
                let serviciosArr = [];
                let observacionesModal = '';

                // El campo servicios_gen_json viene como un string JSON en tu respuesta
                if (h.servicios_gen_json) {
                    try {
                        const parsed = (typeof h.servicios_gen_json === 'string')
                            ? JSON.parse(h.servicios_gen_json)
                            : h.servicios_gen_json;
                        serviciosArr = parsed.servicios || [];
                        observacionesModal = parsed.obs || '';
                    } catch (e) {
                        console.error("Error parseando servicios_gen_json en la fila " + idx, e);
                    }
                }

                // Preparamos el texto que se verá en la celda "Servicios Generales"
                const fullTxt = (serviciosArr.length > 0)
                    ? (serviciosArr.join(', ') + (observacionesModal ? ` [Obs: ${observacionesModal}]` : ''))
                    : (observacionesModal ? `Obs: ${observacionesModal}` : 'Sin servicios');

                // 3. Construimos el HTML de la fila inyectando los valores del JSON
                tr.innerHTML = `
            <td class="sticky-col">
                <input type="text" class="form-control form-control-sm" name="tipo_habitacion[]" required 
                    value="${escapeHtml(h.tipo_habitacion)}">
            </td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="total_habitaciones[]" required 
                value="${escapeHtml(h.total_habitaciones)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="max_adultos[]" required 
                value="${escapeHtml(h.max_adultos)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="max_ninos[]" required 
                value="${escapeHtml(h.max_ninos)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="max_total[]" required 
                value="${escapeHtml(h.max_total)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="mts2[]" required 
                value="${escapeHtml(h.mts2)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="cama_sencilla[]" 
                value="${escapeHtml(h.cama_sencilla)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="cama_doble[]" 
                value="${escapeHtml(h.cama_doble)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="cama_queen[]" 
                value="${escapeHtml(h.cama_queen)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="cama_king[]" 
                value="${escapeHtml(h.cama_king)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="camarote_sencillo[]" 
                value="${escapeHtml(h.camarote_sencillo)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="camarote_doble[]" 
                value="${escapeHtml(h.camarote_doble)}"></td>
            <td><input type="number" class="form-control form-control-sm" min="0" name="camas_adicionales[]" 
                value="${escapeHtml(h.camas_adicionales)}"></td>
            <td><textarea class="form-control form-control-sm" name="observaciones[]" rows="3">${escapeHtml(h.observaciones || '')}</textarea></td>
            <td class="services-cell" data-services="${serviciosArr.join(';')}" data-description="${escapeHtml(observacionesModal)}">
                
                <button type="button" class="btn btn-primary btn-sm add-service-btn">Add Servicio</button>
                <input type="hidden" name="habitacion_servicios[${idx}]" value='${h.servicios_gen_json || '{"servicios":[],"obs":""}'}'>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeHabitacionRow(this)">Eliminar</button>
            </td>
        `;

                tbody.appendChild(tr);
            });

            // 4. Mantenemos la integridad de los índices para los servicios
            renumerarServiciosHabitaciones();

            // 5. Si el JSON venía vacío, agregamos una fila en blanco por defecto
            if (!habitaciones || habitaciones.length === 0) {
                addHabitacionRow();
            }
        }

        function renderSalonesFromDraft(salones) {
            const tbody = document.querySelector('#tablaSalones tbody');
            if (!tbody) return;

            // 1. Limpiar el contenido previo para evitar duplicados al recargar
            tbody.innerHTML = '';

            // 2. Iterar sobre el array de salones del JSON
            (salones || []).forEach((s) => {
                const tr = document.createElement('tr');

                // 3. Construir la fila mapeando las llaves del JSON a los inputs del formulario
                // Nota: Se usa escapeHtml para asegurar que los datos no rompan el HTML
                tr.innerHTML = `
            <td>
                <input type="text" class="form-control" name="nombre_salon[]" 
                    value="${escapeHtml(s.nombre_salon)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="0.1" name="m2_salon[]" 
                    value="${escapeHtml(s.m2)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="0.1" name="largo_salon[]" 
                    value="${escapeHtml(s.largo)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="0.1" name="ancho_salon[]" 
                    value="${escapeHtml(s.ancho)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="0.1" name="alto_salon[]" 
                    value="${escapeHtml(s.alto)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_u_herradura[]" 
                    value="${escapeHtml(s.cap_u_herradura)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_aula[]" 
                    value="${escapeHtml(s.cap_aula)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_auditorio[]" 
                    value="${escapeHtml(s.cap_auditorio)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_banquete[]" 
                    value="${escapeHtml(s.cap_banquete)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_imperial[]" 
                    value="${escapeHtml(s.cap_imperial)}">
            </td>
            <td>
                <input type="number" class="form-control" min="0" name="cap_coctel[]" 
                    value="${escapeHtml(s.cap_coctel)}">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeHabitacionRow(this)">Eliminar</button>
            </td>
        `;

                tbody.appendChild(tr);
            });

            // 4. Si el borrador no tiene salones guardados, inicializar con una fila vacía
            if (!salones || salones.length === 0) {
                addSalonRow();
            }
        }

        function renumerarServiciosHabitaciones() {
            const rows = document.querySelectorAll('#habitacionTable tbody tr');
            rows.forEach((tr, idx) => {
                const serviceCell = tr.querySelector('.services-cell');
                if (!serviceCell) return;

                let hidden = serviceCell.querySelector('input[type="hidden"][name^="habitacion_servicios["]') || tr.querySelector('input[type="hidden"][name^="habitacion_servicios["]');
                if (!hidden) {
                    // Crear un hidden por defecto para que SIEMPRE se envíe al backend
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = `habitacion_servicios[${idx}]`;
                    hidden.value = JSON.stringify({ servicios: [], obs: "" });
                    serviceCell.appendChild(hidden);
                } else {
                    hidden.name = `habitacion_servicios[${idx}]`;
                }
            });
        }
        function removeHabitacionRow(btn) {
            const tr = btn.closest('tr');
            if (tr) tr.remove();
            renumerarServiciosHabitaciones();
        }
        // Manejar el botón "Adicionar" del Modal
        function handleAdicionar() {
            if (!currentRow) {
                console.error('No current row selected');
                return;
            }

            // 1. Quitar el foco del botón inmediatamente para evitar el error de "aria-hidden"
            const btnAdicionar = document.getElementById('adicionarBtn');
            if (btnAdicionar) btnAdicionar.blur();

            const checkboxes = document.querySelectorAll('#exampleModal .form-check-input:checked');
            const description = document.getElementById('descripcion_modal').value.trim();
            const services = Array.from(checkboxes).map(checkbox => checkbox.value);

            const displayServices = services.join(', ') + (description ? ` [Obs: ${description}]` : '');

            const serviceCell = currentRow.querySelector('.services-cell');


            // Ejecutamos la lógica de guardado si la celda existe
            if (serviceCell) {
                // No mostrar el listado de servicios dentro del TD.
                // Los servicios sí se guardan en data-services, data-description y el input hidden.
                const displayDiv = serviceCell.querySelector('.service-display');
                if (displayDiv) {
                    displayDiv.remove();
                }

                // Guardamos los datos en el elemento de la celda para recuperarlos al reabrir
                serviceCell.dataset.services = services.join(';');
                serviceCell.dataset.description = description;

                // Para enviar a PHP: creamos un input oculto dentro de la celda
                const idx = Array.from(document.querySelectorAll('#habitacionTable tbody tr')).indexOf(currentRow);

                let hiddenInput = serviceCell.querySelector(`input[name="habitacion_servicios[${idx}]"]`);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `habitacion_servicios[${idx}]`;
                    serviceCell.appendChild(hiddenInput);
                } else {
                    hiddenInput.name = `habitacion_servicios[${idx}]`; // por si cambió el orden
                }
                hiddenInput.value = JSON.stringify({ servicios: services, obs: description });

                renumerarServiciosHabitaciones();

            }

            // --- MANEJO DEL CIERRE DEL MODAL ---
            const modalElement = document.getElementById('exampleModal');
            if (modalElement) {
                const modalInstance =
                    bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                // ✅ el listener VA ANTES del hide
                modalElement.addEventListener('hidden.bs.modal', () => {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                }, { once: true });

                // ✅ hide SOLO UNA VEZ
                modalInstance.hide();
            }



            // Limpiar campos del modal para el próximo uso
            document.querySelectorAll('#exampleModal .form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
            const descModal = document.getElementById('descripcion_modal');
            if (descModal) descModal.value = '';

            // Resetear la fila seleccionada
            currentRow = null;




        }

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar Listeners para agregar filas
            document.getElementById('addRowBtn')?.addEventListener('click', addHabitacionRow);
            document.getElementById('addRowButton')?.addEventListener('click', addSalonRow);


            // ===== Modal Servicios (Opción A: apertura por JS, estable para filas dinámicas) =====
            const modalElement = document.getElementById('exampleModal');
            const tbodyHab = document.querySelector('#habitacionTable tbody');

            if (modalElement) {
                // Recomendación: el modal idealmente debe ser hijo directo de <body>
                if (modalElement.parentElement !== document.body) {
                    document.body.appendChild(modalElement);
                }

                // Cargar valores guardados al abrir el modal (funciona si se abre por JS o por Bootstrap)
                modalElement.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; // puede venir null si lo abrimos por JS
                    if (button) currentRow = button.closest('tr');

                    if (!currentRow) return;

                    const serviceCell = currentRow.querySelector('.services-cell');
                    const servicesString = serviceCell?.dataset.services || '';
                    const descriptionString = serviceCell?.dataset.description || '';
                    const saved = servicesString ? servicesString.split(';') : [];

                    document.querySelectorAll('#exampleModal .form-check-input').forEach(cb => {
                        cb.checked = saved.includes(cb.value);
                    });

                    const desc = document.getElementById('descripcion_modal');
                    if (desc) desc.value = descriptionString;
                });

                // Delegación: abrir modal desde cualquier botón .add-service-btn (incluye filas nuevas)
                if (tbodyHab) {
                    tbodyHab.addEventListener('click', function (e) {
                        const btn = e.target.closest('.add-service-btn');
                        if (!btn) return;

                        e.preventDefault();
                        e.stopPropagation();

                        currentRow = btn.closest('tr');
                        if (!currentRow) return;

                        // Prefill modal with existing services/obs from this row (draft or previous edits)
                        const serviceCell = currentRow.querySelector('.services-cell');
                        const selectedServices = (serviceCell?.dataset?.services || '').split(';').map(s => s.trim()).filter(Boolean);
                        const selectedObs = serviceCell?.dataset?.description || '';

                        // Reset all modal checkboxes first
                        const modalCheckboxes = Array.from(document.querySelectorAll('#exampleModal .form-check-input'));
                        modalCheckboxes.forEach(cb => { cb.checked = false; });

                        // Re-check based on row data (match by value)
                        if (selectedServices.length) {
                            const selectedSet = new Set(selectedServices);
                            modalCheckboxes.forEach(cb => {
                                if (selectedSet.has(cb.value)) cb.checked = true;
                            });
                        }

                        const descEl = document.getElementById('descripcion_modal');
                        if (descEl) descEl.value = selectedObs;

                        const instance = bootstrap.Modal.getOrCreateInstance(modalElement);
                        instance.show();
                    });
                }
            }
            // Manejar lógica del Modal de Servicios
            document.getElementById('adicionarBtn')?.addEventListener('click', handleAdicionar);

            // Manejar "Chequear Todas"
            const checkAllBtn = document.getElementById('checkAllBtn');
            if (checkAllBtn) {
                let allChecked = false;
                checkAllBtn.addEventListener('click', function () {
                    const checkboxes = document.querySelectorAll('#exampleModal .form-check-input');
                    allChecked = !allChecked;
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = allChecked;
                    });
                });
            }

            // Conexión dinámica para la Sección 6
            document.querySelectorAll('input[name="connectionType"]').forEach(input => {
                input.addEventListener('change', actualizarOpcionesTarifa);
            });
            document.getElementById('allotmentCheckbox')?.addEventListener('change', actualizarOpcionesTarifa);
            document.getElementById('tarifaFIT')?.addEventListener('change', toggleDynamicTable);
            document.getElementById('tarifaDinamica')?.addEventListener('change', toggleDynamicTable);
            document.getElementById('tarifaAmbas')?.addEventListener('change', toggleDynamicTable);

            // Secciones 7: Lógica de Certificado (se movió la función al cuerpo para la coherencia)
            window.manejarCertificado = function () {
                const seleccion = document.getElementById("certificadoSostenibilidad").value;
                const mensajeDiv = document.getElementById("mensajeCertificado");

                if (seleccion === "si") {
                    mensajeDiv.innerHTML =
                        `<p class="text-danger">⚠️ Recuerde cargar el certificado en el apartado de <strong>Documentos Opcionales</strong>.</p>`;
                } else if (seleccion === "no") {
                    mensajeDiv.innerHTML =
                        `<a href="https://forms.zohopublic.com/panamericadeviajes/form/FORMULARIOCOMPROMISOSOSTENIBILIDAD/formperma/NJSbJSOm2DCS23YXcpQ-cU5HEQAad3H0HQjHW0c5lTU" target="_blank">Complete este formulario de compromiso de sostenibilidad</a>`;
                } else {
                    mensajeDiv.innerHTML = "";
                }
            };
        });
    </script>
    <script>
        let currentSection = 0;
        const sections = document.querySelectorAll('.form-section');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitSection = document.getElementById('submitSection');

        // Array con los IDs de los tabs para actualizar visualmente
        const tabButtons = ['general-tab', 'contactos-tab', 'servicios-tab', 'habitaciones-tab', 'salones-tab', 'documentos-tab'];

        function setWizardStep(step) {
            const input = document.getElementById('wizard_step');
            if (input) input.value = String(step);
        }

        function showSection(index) {
            setWizardStep(index);

            // Ocultar todas las secciones y mostrar la actual
            sections.forEach((section, i) => {
                section.classList.toggle('active', i === index);
            });

            // Actualizar tabs visualmente
            tabButtons.forEach((tabId, i) => {
                const tabBtn = document.getElementById(tabId);
                if (tabBtn) {
                    if (i === index) {
                        tabBtn.classList.add('active');
                    } else {
                        tabBtn.classList.remove('active');
                    }
                }
            });

            // Mostrar/ocultar botones
            prevBtn.style.display = index === 0 ? 'none' : 'block';
            nextBtn.style.display = index === sections.length - 1 ? 'none' : 'block';
            submitSection.style.display = index === sections.length - 1 ? 'block' : 'none';
        }

        // Función de validación de la sección actual
        function validateCurrentSection() {
            const currentActiveSection = sections[currentSection];
            const inputs = currentActiveSection.querySelectorAll('[required], input:not([disabled])[name="gimnasio_general"]');
            let allValid = true;

            inputs.forEach(input => {
                input.classList.remove('is-invalid');
                let isValid = true;

                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (!document.querySelector(`input[name="${input.name}"]:checked`)) {
                        isValid = false;
                    }
                } else if (input.type === 'file') {
                    if (input.files.length === 0) {
                        if (input.hasAttribute('required')) {
                            if (input.id === 'signature-image' && document.getElementById('draw-signature')?.checked) {
                                isValid = true;
                            } else {
                                isValid = false;
                            }
                        }
                    }
                } else if (!input.value.trim() || input.value === '0') {
                    isValid = false;
                }

                if (input.id === 'firma_dibujada_data') {
                    if (document.getElementById('draw-signature')?.checked && !input.value.trim()) {
                        isValid = false;
                    } else if (document.getElementById('upload-image')?.checked) {
                        isValid = true;
                    }
                }

                if (!isValid) {
                    allValid = false;
                    input.classList.add('is-invalid');
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        const firstInput = document.querySelector(`input[name="${input.name}"]`);
                        firstInput.classList.add('is-invalid');
                    }
                }
            });

            if (!allValid) {
                alert('Por favor, complete todos los campos requeridos (*).');
                const firstInvalid = currentActiveSection.querySelector('.is-invalid');
                if (firstInvalid) {
                    if (firstInvalid.classList.contains('foto-promo-input')) {
                        const modalFotos = document.getElementById('modalFotosPromo');
                        if (modalFotos && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(modalFotos).show();
                        }
                    }
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return allValid;
        }

        // Event Listeners para navegación
        nextBtn.addEventListener('click', async function () {
            if (!validateCurrentSection()) return;

            if (currentSection >= sections.length - 1) return;

            try { guardarCamposEnCache(); } catch (e) { }

            const nextStep = currentSection + 1;
            setWizardStep(nextStep);

            const form = document.getElementById('multiStepForm');

            console.log("radio CM checked:", document.getElementById("channelManagerRadio")?.checked);
            console.log("radio Extranet checked:", document.getElementById("extranetRadio")?.checked);
            console.log("select disabled:", document.getElementById("channelManagerName")?.disabled);
            console.log("select value:", document.getElementById("channelManagerName")?.value);

            actualizarOpcionesTarifa();

            const fd = new FormData(form);

            const cmSelect = document.getElementById('channelManagerName');
            if (cmSelect) {
                fd.set('channelName', cmSelect.value || '');
            }

            console.log("DOM channelName:", document.querySelector('[name="channelName"]')?.value);
            console.log("FD channelName:", fd.get("channelName"));
            console.log("FD connectionType:", fd.get("connectionType"));

            form.querySelectorAll('input[type="file"]').forEach(inp => {
                fd.delete(inp.name);
            });

            fd.set('es_borrador', '1');
            fd.set('wizard_step', String(nextStep));

            const idInput = document.getElementById('id_hotel_borrador');
            if (idInput && idInput.value) {
                fd.set('id_hotel_borrador', idInput.value);
            }

            nextBtn.disabled = true;

            try {
                const resp = await fetch('../controlador/guardarBorradorAjax.php', {
                    method: 'POST',
                    body: fd
                });

                const data = await resp.json();

                if (!data.ok) {
                    alert('No se pudo guardar el borrador: ' + (data.error || 'Error desconocido'));
                    setWizardStep(currentSection);
                    return;
                }

                if (data.id_hotel && idInput) {
                    idInput.value = String(data.id_hotel);
                }

                if (data.nit_consecutivo) {
                    const nitConsecutivoEl = document.getElementById('nitConsecutivo');
                    const nitBaseEl = document.getElementById('nitBase');
                    const nitSufijoEl = document.getElementById('nitSufijo');
                    const consecutivo = String(data.nit_consecutivo).trim().toUpperCase();

                    if (nitConsecutivoEl) {
                        nitConsecutivoEl.value = consecutivo;
                    }

                    if (nitBaseEl && nitSufijoEl) {
                        const base = (nitBaseEl.value || '').trim();
                        if (base && consecutivo.indexOf(base) === 0) {
                            const sufijo = consecutivo.substring(base.length).replace(/[^A-Z]/g, '');
                            if (sufijo) {
                                nitSufijoEl.value = sufijo;
                            }
                        }
                    }
                }

                currentSection = nextStep;
                showSection(currentSection);
                window.scrollTo(0, 0);

            } catch (err) {
                alert('Error de conexión al guardar el borrador. Revisa internet e intenta de nuevo.');
                setWizardStep(currentSection);
            } finally {
                nextBtn.disabled = false;
            }
        });

        prevBtn.addEventListener('click', function () {
            if (currentSection > 0) {
                guardarCamposEnCache();
                currentSection--;
                showSection(currentSection);
                window.scrollTo(0, 0);
            }
        });


        function setValueFormularioPrecarga(name, value) {
            const safeName = (window.CSS && CSS.escape) ? CSS.escape(name) : String(name).replace(/"/g, '\"');
            const first = document.querySelector(`[name="${safeName}"]`);
            const nodes = document.querySelectorAll(`[name="${safeName}"]`);
            if (!first) return;

            const type = (first.type || '').toLowerCase();
            const tag = (first.tagName || '').toLowerCase();

            if (type === 'radio') {
                nodes.forEach(el => { el.checked = String(el.value) === String(value ?? ''); });
                nodes.forEach(el => el.dispatchEvent(new Event('change', { bubbles: true })));
                return;
            }

            if (type === 'checkbox') {
                if (nodes.length > 1) {
                    const values = Array.isArray(value) ? value.map(String) : [String(value ?? '')];
                    nodes.forEach(el => { el.checked = values.includes(String(el.value)); });
                    nodes.forEach(el => el.dispatchEvent(new Event('change', { bubbles: true })));
                    return;
                }
                first.checked = value === 1 || value === '1' || value === true || value === 'true' || value === 'si';
                first.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }

            first.value = value ?? '';
            first.dispatchEvent(new Event('input', { bubbles: true }));
            first.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function setMultiCheckboxFormularioPrecarga(name, valuesArr) {
            const safeName = (window.CSS && CSS.escape) ? CSS.escape(name) : String(name).replace(/"/g, '\"');
            const nodes = document.querySelectorAll(`[name="${safeName}"]`);
            const set = new Set((Array.isArray(valuesArr) ? valuesArr : []).map(String));
            nodes.forEach(n => {
                n.checked = set.has(String(n.value));
                n.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        function aplicarDatosPrecargaFormulario(data) {
            if (!data || !data.ok) return false;

            const idInput = document.getElementById('id_hotel_borrador');
            const gf = data.general_form || {};

            if (gf.id_hotel && idInput) idInput.value = String(gf.id_hotel);

            Object.keys(gf).forEach(k => {
                if (['tipo_hotel', 'mercado_distribucion', 'tarifa_tipo', 'planes_tarifarios', 'allotment', 'allotment_selected', 'regimen_alimenticio'].includes(k)) return;

                if (k === 'monto_credito' && gf[k] !== null && gf[k] !== '') {
                    const inputReal = document.getElementById('monto_real');
                    const inputVisual = document.getElementById('monto_visual');
                    if (inputReal) inputReal.value = gf[k];
                    if (inputVisual) {
                        inputVisual.value = gf[k];
                        inputVisual.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    return;
                }

                setValueFormularioPrecarga(k, gf[k]);
            });

            if (gf.tipo_hotel) setMultiCheckboxFormularioPrecarga('tipo_hotel[]', gf.tipo_hotel);
            if (gf.mercado_distribucion) setMultiCheckboxFormularioPrecarga('mercado_distribucion[]', gf.mercado_distribucion);
            if (gf.tarifa_tipo) setMultiCheckboxFormularioPrecarga('tarifa_tipo[]', gf.tarifa_tipo);
            if (gf.regimen_alimenticio) setMultiCheckboxFormularioPrecarga('regimen_alimenticio[]', gf.regimen_alimenticio);

            const cf = data.contactos_form || {};
            Object.keys(cf).forEach(k => setValueFormularioPrecarga(k, cf[k]));

            const sf = data.servicios_form || {};
            Object.keys(sf).forEach(k => {
                if (k === 'cobertura_internet') {
                    setMultiCheckboxFormularioPrecarga('cobertura_internet[]', sf[k]);
                } else {
                    setValueFormularioPrecarga(k, sf[k]);
                }
            });

            if (Array.isArray(data.habitaciones)) renderHabitacionesFromDraft(data.habitaciones);
            if (Array.isArray(data.salones)) renderSalonesFromDraft(data.salones);
            if (gf.planes_tarifarios) renderPlanesTarifarios(gf.planes_tarifarios);
            if (gf.allotment !== undefined && typeof renderAllotmentFromDraft === 'function') {
                renderAllotmentFromDraft(gf.allotment_selected, gf.allotment, gf.connectionType);
            }

            if (typeof actualizarCoberturaInternet === 'function') actualizarCoberturaInternet();
            if (typeof actualizarOpcionesTarifa === 'function') actualizarOpcionesTarifa();
            if (typeof toggleDynamicTable === 'function') toggleDynamicTable();
            if (typeof renumerarServiciosHabitaciones === 'function') renumerarServiciosHabitaciones();
            if (typeof togglePolicyDetails === 'function') togglePolicyDetails();

            return true;
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', async () => {

            // ====== Helpers de precarga ======
            const setValueByName = (name, value) => {
                const el = document.querySelector(`[name="${CSS.escape(name)}"]`);
                if (!el) return;

                const tag = (el.tagName || '').toLowerCase();
                const type = (el.type || '').toLowerCase();

                if (type === 'checkbox') {
                    el.checked = value === 1 || value === "1" || value === true || value === "true";
                    return;
                }

                if (type === 'radio') {
                    const opt = document.querySelector(`[name="${CSS.escape(name)}"][value="${CSS.escape(String(value))}"]`);
                    if (opt) opt.checked = true;
                    return;
                }

                if (tag === 'select') {
                    el.value = (value ?? '');
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    return;
                }

                el.value = (value ?? '');
            };

            const setMultiCheckboxByName = (name, valuesArr) => {
                const nodes = document.querySelectorAll(`[name="${CSS.escape(name)}"]`);
                if (!nodes || !nodes.length) return;
                const set = new Set((Array.isArray(valuesArr) ? valuesArr : []).map(String));
                nodes.forEach(n => {
                    n.checked = set.has(String(n.value));
                });
            };

            // ====== 1) PRIORIDAD: BORRADOR (BD) > CACHE (localStorage) ======
            const idBorradorEl = document.getElementById('id_hotel_borrador');
            const wizardStepEl = document.getElementById('wizard_step');
            const esBorradorEl = document.getElementById('es_borrador');

            const urlParams = new URLSearchParams(window.location.search);
            const idFromUrl = urlParams.get('id');

            const borradorId = (idFromUrl && idFromUrl.trim() !== '')
                ? parseInt(idFromUrl, 10)
                : (idBorradorEl && idBorradorEl.value ? parseInt(idBorradorEl.value, 10) : 0);

            const tieneBorradorBD = !!(borradorId && !Number.isNaN(borradorId));
            const precargaServidor = window.PRECARGA_FORMULARIO || null;
            const tienePrecargaServidor = !!(precargaServidor && precargaServidor.ok);

            if (tienePrecargaServidor) {
                aplicarDatosPrecargaFormulario(precargaServidor);
                const stepServidor = parseInt(String(precargaServidor.wizard_step ?? 0), 10);
                currentSection = isNaN(stepServidor) ? 0 : stepServidor;
            } else if (!tieneBorradorBD) {
                try { recuperarCamposDesdeCache(); } catch (e) { }
            }

            // ====== 1.1) CARGA DESDE BASE DE DATOS ======
            if (!tienePrecargaServidor && tieneBorradorBD) {
                try {
                    if (esBorradorEl) esBorradorEl.value = '1';
                    if (idBorradorEl) idBorradorEl.value = String(borradorId);

                    const fd = new FormData();
                    fd.append('id_hotel', String(borradorId));

                    const resp = await fetch('../controlador/obtenerBorradorAjax.php', {
                        method: 'POST',
                        body: fd
                    });

                    const data = await resp.json();

                    if (data && data.ok) {
                        // A. Prellenar campos generales
                        const gf = data.general_form || {};
                        Object.keys(gf).forEach(k => {
                            // Evitamos procesar aquí los arrays complejos que requieren lógica propia
                            if (['tipo_hotel', 'mercado_distribucion', 'tarifa_tipo', 'planes_tarifarios', 'allotment', 'allotment_selected', 'regimen_alimenticio'].includes(k)) return;

                            // Manejo de moneda (monto_credito)
                            if (k === 'monto_credito' && gf[k]) {
                                const inputReal = document.getElementById('monto_real');
                                const inputVisual = document.getElementById('monto_visual');
                                if (inputReal && inputVisual) {
                                    inputReal.value = gf[k];
                                    inputVisual.value = gf[k];
                                    inputVisual.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                return;
                            }
                            setValueByName(k, gf[k]);
                        });

                        // B. PINTAR TABLAS DINÁMICAS (Aquí es donde se insertan tus datos JSON)
                        if (data.habitaciones) renderHabitacionesFromDraft(data.habitaciones);
                        if (data.salones) renderSalonesFromDraft(data.salones);
                        if (gf.planes_tarifarios) renderPlanesTarifarios(gf.planes_tarifarios);

                        // B2. PINTAR ALLOTMENT (si existe)
                        if (gf.allotment !== undefined) {
                            renderAllotmentFromDraft(gf.allotment_selected, gf.allotment, gf.connectionType);
                        }

                        // C. Cargar Checkboxes Múltiples
                        if (gf.tipo_hotel) setMultiCheckboxByName('tipo_hotel[]', gf.tipo_hotel);
                        if (gf.mercado_distribucion) setMultiCheckboxByName('mercado_distribucion[]', gf.mercado_distribucion);
                        if (gf.tarifa_tipo) setMultiCheckboxByName('tarifa_tipo[]', gf.tarifa_tipo);
                        if (gf.regimen_alimenticio) setMultiCheckboxByName('regimen_alimenticio[]', gf.regimen_alimenticio);

                        // D. Prellenar Contactos
                        const cf = data.contactos_form || {};
                        Object.keys(cf).forEach(k => setValueByName(k, cf[k]));

                        // E. Prellenar Servicios de la Propiedad
                        const sf = data.servicios_form || {};
                        Object.keys(sf).forEach(k => {
                            if (k === 'cobertura_internet') {
                                setMultiCheckboxByName('cobertura_internet[]', sf[k]);
                            } else {
                                setValueByName(k, sf[k]);
                            }
                        });

                        actualizarCoberturaInternet();

                        // F. Actualizar visibilidad de UI según selecciones cargadas
                        actualizarOpcionesTarifa();
                        toggleDynamicTable();
                        renumerarServiciosHabitaciones();

                        // G. Sincronizar el paso del asistente
                        const stepApi = parseInt(String(data.wizard_step ?? 0), 10);
                        currentSection = isNaN(stepApi) ? 0 : stepApi;

                    } else {
                        console.error('Borrador inválido:', data?.error);
                        recuperarCamposDesdeCache();
                    }
                } catch (err) {
                    console.error('Error en fetch de borrador:', err);
                    recuperarCamposDesdeCache();
                }
            }

            // ====== 2) ARRANCAR INTERFAZ ======
            showSection(currentSection);

            // ====== 3) LÓGICA DE ENVÍO FINAL ======
            const form = document.getElementById('multiStepForm');
            const overlay = document.getElementById('overlay');
            const finalSubmitBtn = document.getElementById('finalSubmitBtn');
            const btnEnviarDesdeModal = document.getElementById('btnEnviarDesdeModal');
            const modalDiligenciador = new bootstrap.Modal(document.getElementById('modalDiligenciador'));

            finalSubmitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                // Validación antes de abrir modal
                if (validateCurrentSection()) {
                    modalDiligenciador.show();
                }
            });

            btnEnviarDesdeModal.addEventListener('click', async function () {
                const nombreDiligencia = document.getElementById('diligencia_nombre');
                if (!nombreDiligencia.value.trim()) {
                    nombreDiligencia.classList.add('is-invalid');
                    alert("Por favor, ingrese el nombre de quien diligencia.");
                    return;
                }

                modalDiligenciador.hide();

                // Si ya fue validado, enviar directamente
                if (window.formularioValidadoPorIA) {
                    overlay.style.display = 'flex';
                    overlay.setAttribute('aria-hidden', 'false');

                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';

                    const esBorradorFinal = document.getElementById('es_borrador');
                    if (esBorradorFinal) esBorradorFinal.value = '0';

                    setWizardStep(currentSection);
                    form.submit();
                    return;
                }

                // Validar con IA antes de enviar
                overlay.style.display = 'flex';
                overlay.setAttribute('aria-hidden', 'false');
                const loader = overlay.querySelector('.loader');
                if (loader) {
                    // El loader ya tiene su estilo CSS, no necesita innerHTML
                }

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Validando...';

                try {
                    // Validar todos los documentos con IA
                    const resultados = await window.validarDocumentosConIA();

                    // Verificar si hay documentos con problemas
                    const documentosProblema = [];
                    resultados.forEach(r => {
                        const estado = String(r.estado || '').toUpperCase();
                        if (
                            estado === 'EXPIRED' ||
                            estado === 'ERROR' ||
                            estado === 'SIN_ARCHIVO' ||
                            estado === 'SIN ARCHIVO' ||
                            estado === 'INVALID' ||
                            estado === 'UNKNOWN' ||
                            !r.valido
                        ) {
                            documentosProblema.push(`${r.documento}: ${r.observaciones || estado}`);
                        }
                    });
                    // Si hay documentos con problemas, no permitir envío
                    if (documentosProblema.length > 0) {
                        overlay.style.display = 'none';
                        overlay.setAttribute('aria-hidden', 'true');

                        this.disabled = false;
                        this.innerHTML = 'Enviar';

                        // Resetear solo los documentos que fallaron para que se puedan volver a subir
                        resultados.forEach(r => {
                            if (!r.valido) {
                                window.documentosValidados[r.id ||
                                    Object.keys(window.documentosValidados).find(k =>
                                        window.documentosValidados[k] === false || window.documentosValidados[k] === null
                                    )
                                ] = null;
                            }
                        });

                        alert('No se puede enviar el formulario. Los siguientes documentos tienen problemas de vigencia:\n\n' +
                            documentosProblema.join('\n') +
                            '\n\nPor favor, actualice los documentos que aparecen en rojo y vuelva a intentar.');

                        // Hacer scroll hacia la sección de documentos para que el usuario los vea
                        const seccionDocs = document.querySelector('.documents-section');
                        if (seccionDocs) {
                            seccionDocs.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }

                        return;
                    }

                    // Todos los documentos son válidos, enviar
                    window.formularioValidadoPorIA = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';

                    const esBorradorFinal = document.getElementById('es_borrador');
                    if (esBorradorFinal) esBorradorFinal.value = '0';

                    setWizardStep(currentSection);
                    form.submit();

                } catch (error) {
                    console.error("Error en validación:", error);
                    overlay.style.display = 'none';
                    this.disabled = false;
                    this.innerHTML = 'Enviar';
                    alert('Error al validar los documentos con IA. Por favor, intente nuevamente.');
                }
            });
            window.addEventListener('pageshow', function () {
                overlay.style.display = 'none';
                btnEnviarDesdeModal.disabled = false;
                btnEnviarDesdeModal.innerHTML = 'Enviar';
                const esBorradorBack = document.getElementById('es_borrador');
                if (esBorradorBack) esBorradorBack.value = '1';
            });
        });
    </script>
    <script>
        function renderAllotmentFromDraft(allotment_selected, allotment_rows, connectionType) {
            const cb = document.getElementById('allotmentCheckbox');
            const fields = document.getElementById('allotmentFields');
            const tbody = document.querySelector('#tablaAllotment tbody');
            if (!cb || !fields || !tbody) return;

            const isExtranet = (String(connectionType || '') === 'extranet');
            const selected = (String(allotment_selected) === '1');

            // Set checkbox state (solo aplica en extranet)
            cb.checked = (isExtranet && selected);

            // Limpiar filas actuales
            tbody.innerHTML = '';

            const rows = Array.isArray(allotment_rows) ? allotment_rows : [];

            // Helper: crear fila con los mismos estilos/estructura que tu función agregarFilaAllotment()
            const addRow = (tipo, num) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="text" name="allotment_tipo_habitacion[]" class="form-control form-control-sm"
                            value="${escapeHtml(tipo ?? '')}" ></td>
                    <td><input type="number" name="allotment_num_habitaciones[]" class="form-control form-control-sm"
                            value="${escapeHtml(num ?? '')}"  min="0"></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaAllotment(this)">Eliminar</button></td>
                `;
                tbody.appendChild(tr);
            };

            // Si está seleccionado y hay datos, pintarlos; si no, dejar una fila vacía por UX
            if (cb.checked && rows.length > 0) {
                rows.forEach(r => addRow(r.tipo_habitacion ?? '', r.num_habitaciones ?? ''));
            } else {
                addRow('', '');
            }
            // Disparar la lógica existente de mostrar/ocultar y habilitar/deshabilitar inputs
            try {
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (e) { /* noop */ }
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
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
                // Si solo queda una fila, en lugar de eliminarla limpiamos los campos
                tr.querySelectorAll('input').forEach(i => i.value = '');
            }
        }

        // Si ya tienes una función updateAllotmentUI(), solo añade la línea disableHidden:
        function disableHidden(container, disabled) {
            if (!container) return;
            container.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = disabled);
        }

        // Ejemplo de integración dentro de tu lógica existente:
        (function hookAllotmentVisibility() {
            const allotmentCheckbox = document.getElementById('allotmentCheckbox');
            const allotmentFields = document.getElementById('allotmentFields');
            function updateAllotmentUI() {
                const visible = allotmentCheckbox && allotmentCheckbox.checked;
                if (allotmentFields) {
                    allotmentFields.style.display = visible ? 'block' : 'none';
                    disableHidden(allotmentFields, !visible);
                }
            }
            if (allotmentCheckbox) {
                allotmentCheckbox.addEventListener('change', updateAllotmentUI);
                updateAllotmentUI(); // estado inicial
            }
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var nitBase = document.getElementById('nitBase');
            var nitSufijo = document.getElementById('nitSufijo');
            var nitConsecutivo = document.getElementById('nitConsecutivo');

            if (!nitBase || !nitSufijo || !nitConsecutivo) return;

            function actualizarNitConsecutivo() {
                var base = (nitBase.value || '').trim();
                var sufijo = (nitSufijo.value || '').trim().toUpperCase();
                nitConsecutivo.value = base + sufijo; // ej: 860402288A
            }

            var consecutivoPrecargado = (nitConsecutivo.value || '').trim().toUpperCase();
            var basePrecargada = (nitBase.value || '').trim();
            if (consecutivoPrecargado && basePrecargada && consecutivoPrecargado.indexOf(basePrecargada) === 0) {
                var sufijoPrecargado = consecutivoPrecargado
                    .substring(basePrecargada.length)
                    .replace(/[^A-Z]/g, '');
                if (sufijoPrecargado) {
                    nitSufijo.value = sufijoPrecargado;
                }
            }

            actualizarNitConsecutivo();
            nitBase.addEventListener('input', actualizarNitConsecutivo);
            nitSufijo.addEventListener('input', actualizarNitConsecutivo);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const inputRazon = document.getElementById("razon_social");
            const previewRazon = document.getElementById("razonSocialPreview");

            if (!inputRazon || !previewRazon) return;

            const actualizar = () => {
                const valor = inputRazon.value.trim();
                previewRazon.textContent = valor !== "" ? valor : "[Razón Social]";
            };

            // inicial
            actualizar();

            // tiempo real
            inputRazon.addEventListener("input", actualizar);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('multiStepForm');
            if (!form) return;

            const usuario = <?php echo json_encode($_SESSION['usuario'] ?? 'anon'); ?>;
            const storageKey = `draftHotel:${usuario}`;

            // ...resto igual
        });
    </script>

    <script>
        $(document).ready(function () {
            // Variables globales para almacenar resultados de validación
            window.documentosValidados = {
                rut: null,
                registro_turismo: null,
                camara_comercio: null,
                certificacion_bancaria: null
            };
            ['rut', 'registro_turismo', 'camara_comercio', 'certificacion_bancaria'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', function () {
                    window.documentosValidados[id] = null;
                    const indicador = document.getElementById('vigencia-' + id);
                    if (indicador) indicador.innerHTML = '';
                });
            });
            window.validacionDocumentos = {};
            window.formularioValidadoPorIA = false;

            // Función global reutilizable para validar documentos con IA
            window.validarDocumentosConIA = async function () {
                const documentosIA = [
                    { id: 'rut', nombre: 'RUT' },
                    { id: 'registro_turismo', nombre: 'RNT' },
                    { id: 'camara_comercio', nombre: 'Camara de Comercio' },
                    { id: 'certificacion_bancaria', nombre: 'Certificacion Bancaria' }
                ];

                const promesas = documentosIA.map(doc => procesarDocumento(doc));
                const resultados = await Promise.all(promesas);
                return resultados;
            };

            async function procesarDocumento(doc) {
                const input = document.getElementById(doc.id);
                const indicador = document.getElementById('vigencia-' + doc.id);
                const hoy = new Date();

                if (window.documentosValidados[doc.id] === true) {
                    return { documento: doc.nombre, id: doc.id, estado: 'VALID', valido: true };
                }

                const existePrevio = document.querySelector(`[name="${doc.id}_existente"]`)?.value === '1';
                if ((!input || !input.files || input.files.length === 0) && existePrevio) {
                    window.documentosValidados[doc.id] = true;
                    if (indicador) {
                        indicador.innerHTML = '✓ Documento vigente ya cargado previamente';
                        indicador.style.color = 'green';
                    }
                    return {
                        documento: doc.nombre, id: doc.id, estado: 'VALID', valido: true,
                        observaciones: 'Documento vigente ya cargado previamente'
                    };
                }

                if (!input || !input.files || input.files.length === 0) {
                    if (indicador) {
                        indicador.innerHTML = '✗ Falta subir documento';
                        indicador.style.color = 'red';
                    }
                    return {
                        documento: doc.nombre, id: doc.id, estado: 'SIN_ARCHIVO', valido: false,
                        observaciones: 'No se seleccionó archivo'
                    };
                }

                const file = input.files[0];
                const formData = new FormData();
                formData.append('archivo', file);
                formData.append('tipo_documento', doc.nombre);

                try {
                    const res = await fetch('validar_vigencia_openai.php', { method: 'POST', body: formData });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const data = await res.json();

                    let estado = (data.status || data.estado || '').toUpperCase();
                    let valido = false;
                    let fecha = '';
                    let observaciones = '';

                    if (doc.id === 'rut') {
                        fecha = data.generation_date || data.issue_date || '';
                        if (!fecha) {
                            estado = 'UNKNOWN'; valido = false;
                            observaciones = 'No se encontró fecha de generación del RUT.';
                        } else {
                            const dias = Math.floor((hoy - new Date(fecha + 'T00:00:00')) / 864e5);
                            if (dias > 365) {
                                estado = 'EXPIRED'; valido = false;
                                observaciones = `RUT no válido: tiene más de 1 año desde su generación (${dias} días).`;
                            } else {
                                estado = 'VALID'; valido = true;
                                observaciones = `RUT válido. Generado hace ${dias} días.`;
                            }
                        }

                    } else if (doc.id === 'certificacion_bancaria') {
                        fecha = data.issue_date || data.best_validity_date || '';
                        if (!fecha) {
                            estado = 'UNKNOWN'; valido = false;
                            observaciones = 'No se encontró fecha de expedición de la certificación bancaria.';
                        } else {
                            const diasTrans = Math.floor((hoy - new Date(fecha + 'T00:00:00')) / 864e5);
                            const diasRest = 365 - diasTrans;
                            if (diasTrans > 365) {
                                estado = 'EXPIRED'; valido = false;
                                observaciones = `Certificación bancaria vencida: tiene ${diasTrans} días (máx. 365).`;
                            } else {
                                estado = 'VALID'; valido = true;
                                observaciones = `Certificación bancaria válida. Vence en ${diasRest} días.`;
                            }
                        }

                    } else {
                        fecha = data.best_validity_date || data.expiration_date || data.valid_until || data.issue_date || '';
                        valido = estado === 'VALID';
                        observaciones = data.observations || '';
                    }

                    if (indicador) {
                        indicador.innerHTML = valido
                            ? `✓ Documento vigente${fecha ? ' hasta ' + fecha : ''}`
                            : `✗ ${observaciones || 'Documento no válido o vencido'}`;
                        indicador.style.color = valido ? 'green' : 'red';
                    }

                    window.documentosValidados[doc.id] = valido;
                    return {
                        documento: doc.nombre, id: doc.id, estado, valido, fecha_vigencia: fecha,
                        respuesta: data, observaciones
                    };

                } catch (error) {
                    console.error(`Error validando ${doc.nombre}:`, error);
                    if (indicador) {
                        indicador.innerHTML = '✗ Error validando documento';
                        indicador.style.color = 'red';
                    }
                    return {
                        documento: doc.nombre, id: doc.id, estado: 'ERROR', valido: false,
                        observaciones: 'Error al validar con IA'
                    };
                }
            }

            // Botón de validación manual (opcional, ahora valida automáticamente en submit)
            $("#btnValidarVigencias").on("click", async function () {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validando...');

                await window.validarDocumentosConIA();

                btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Validar vigencia de los archivos (opcional)');
            });

            function mostrarIndicadoresVigencia(resultados) {
                resultados.forEach(r => {
                    const container = $(`#vigencia-${r.id}`);
                    let iconClass = '';
                    let stateClass = '';
                    let icon = '';

                    switch (r.estado) {
                        case 'VALID':
                            stateClass = 'valid';
                            icon = '✓';
                            iconClass = 'text-success';
                            break;
                        case 'EXPIRED':
                            stateClass = 'expired';
                            icon = '✗';
                            iconClass = 'text-danger';
                            break;
                        case 'ABOUT TO EXPIRE':
                            stateClass = 'warning';
                            icon = '⚠';
                            iconClass = 'text-warning';
                            break;
                        case 'ERROR':
                            stateClass = 'error';
                            icon = '✗';
                            iconClass = 'text-danger';
                            break;
                        case 'SIN ARCHIVO':
                            stateClass = 'sin-archivo';
                            icon = 'ℹ';
                            iconClass = 'text-muted';
                            break;
                        default:
                            stateClass = 'sin-archivo';
                            icon = '?';
                            iconClass = 'text-muted';
                    }

                    const html = `
                <span class="vigencia-icon ${iconClass}">${icon}</span>
                <span class="vigencia-date">${r.vigencia || 'No encontrada'}</span>
                <span class="vigencia-estado">${r.estado}</span>
                ${r.observaciones ? `<div class="vigencia-obs">${r.observaciones}</div>` : ''}
            `;

                    container.html(html)
                        .removeClass('valid expired warning error sin-archivo')
                        .addClass(stateClass + ' show');
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <!-- Overlay de validación con IA -->
    <div id="overlay" class="overlay" aria-hidden="true">
        <div class="loader" role="status" aria-label="Cargando…">
            <div class="loader-spinner">
                <img src="/facturacion/img/faviconxd.png" alt="Cargando..." />
            </div>
            <div class="loader-text">Estamos validando la vigencia de sus documentos</div>
            <div class="loader-subtext">Por favor espere un momento</div>
        </div>
    </div>
</body>

</html>
