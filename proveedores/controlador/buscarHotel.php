<?php
// Seguridad y conexión
include_once '../../facturacion/config/seguridad.php';
include_once '../../facturacion/config/conexion.php';

// Verificar sesión
$nitCadena = $_SESSION['usuario'] ?? null;
$idRol     = $_SESSION['id_rol'] ?? null;

if (!$nitCadena || (int)$idRol !== 7) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    header('Location: ../vista/listadoHotelesCadena.php');
    exit;
}

// Buscar hoteles de la cadena que coincidan con el nombre buscado
$busqueda = '%' . $q . '%';
$stmt = $conn->prepare("
    SELECT h.id_hotel, h.nombre
    FROM tbl_alojamiento_general h
    LEFT JOIN tbl_usuarios u ON u.id_usuario = h.id_usuario_creacion
    WHERE (h.usuario_creacion = ? OR u.usuario = ?)
      AND COALESCE(h.estado_registro, 'FINALIZADO') = 'FINALIZADO'
      AND (h.nombre LIKE ? OR h.nit LIKE ?)
    ORDER BY h.id_hotel DESC
    LIMIT 10
");
$stmt->bind_param("ssss", $nitCadena, $nitCadena, $busqueda, $busqueda);
$stmt->execute();
$res     = $stmt->get_result();
$hoteles = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (count($hoteles) === 1) {
    // Exactamente uno: ir directo al detalle
    header('Location: ../vista/consultaHotel.php?id=' . (int)$hoteles[0]['id_hotel']);
    exit;
} elseif (count($hoteles) > 1) {
    // Varios resultados: ir al listado con el filtro aplicado
    header('Location: ../vista/listadoHotelesCadena.php?q=' . urlencode($q));
    exit;
} else {
    // Ninguno encontrado
    header('Location: ../vista/listadoHotelesCadena.php?notfound=' . urlencode($q));
    exit;
}
