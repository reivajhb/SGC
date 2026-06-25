<?php
ob_start();
include_once "../../../config/seguridad.php";
include_once "../../../config/conexion.php";

if (ob_get_length()) {
    ob_clean();
}

$id_causacion = filter_input(INPUT_GET, 'id_causacion', FILTER_VALIDATE_INT);

if (!$id_causacion) {
    responderError(400, "Solicitud no valida.");
}

$stmt = mysqli_prepare($conn, "SELECT c.prefijo, c.numero_factura, c.ruta_pdf, p.nombre AS nombre_proveedor
                              FROM tbl_causacion c
                              JOIN tbl_proveedores p ON c.id_proveedor = p.id_proveedor
                              WHERE c.id_causacion = ?");
mysqli_stmt_bind_param($stmt, "i", $id_causacion);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$causacion = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$causacion) {
    responderError(404, "No se encontro la causacion solicitada.");
}

// RUTA DIRECTA DESDE BD (instantaneo, sin busqueda)
if (!empty($causacion['ruta_pdf']) && is_file($causacion['ruta_pdf'])) {
    $pdf = $causacion['ruta_pdf'];
} else {
    // FALLBACK: busqueda en disco para facturas sin ruta_pdf guardada
    $pdf = buscarPdfFactura(
        $causacion['nombre_proveedor'] ?? '',
        $causacion['numero_factura'] ?? ''
    );

    // Si se encuentra, actualizar la BD para la proxima vez
    if ($pdf) {
        $stmtUpdate = mysqli_prepare($conn, "UPDATE tbl_causacion SET ruta_pdf = ? WHERE id_causacion = ?");
        mysqli_stmt_bind_param($stmtUpdate, "si", $pdf, $id_causacion);
        mysqli_stmt_execute($stmtUpdate);
        mysqli_stmt_close($stmtUpdate);
    }
}

if (!$pdf) {
    responderError(404, "No se encontro PDF para la factura " . trim($causacion['numero_factura'] ?? '') . ".");
}

while (ob_get_level()) {
    ob_end_clean();
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"" . basename($pdf) . "\"");
header("Content-Length: " . filesize($pdf));
header("X-Content-Type-Options: nosniff");
readfile($pdf);
exit;

/**
 * Normaliza un string: minusculas, sin tildes, sin caracteres especiales, sin espacios extra.
 */
function normalizarTexto($texto)
{
    $texto = trim((string) $texto);
    $texto = mb_strtolower($texto, 'UTF-8');

    $desde = ['á','é','í','ó','ú','ä','ë','ï','ö','ü','à','è','ì','ò','ù','â','ê','î','ô','û','ñ','ç','Á','É','Í','Ó','Ú','Ä','Ë','Ï','Ö','Ü','À','È','Ì','Ò','Ù','Â','Ê','Î','Ô','Û','Ñ','Ç'];
    $hacia  = ['a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','n','c','a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','n','c'];

    $texto = str_replace($desde, $hacia, $texto);

    $texto = preg_replace('/[^a-z0-9\s]/u', '', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

/**
 * Busca el PDF de una factura dado el nombre del proveedor y el numero de factura.
 * Estrategia:
 *   1. Coincidencia exacta de carpeta.
 *   2. Coincidencia normalizada (sin tildes, lowercase).
 *   3. Fuzzy match por similitud (similar_text >= 80%).
 *   4. Fuzzy match por distancia Levenshtein como ultimo recurso.
 *   5. Busqueda recursiva del PDF solo por numero de factura en todo el directorio base.
 */
function buscarPdfFactura($nombreProveedor, $numeroFactura)
{
    $base = '//192.168.155.32/pdfdescom2';

    $carpetaProveedor = resolverDirectorioProveedor($base, $nombreProveedor);

    if ($carpetaProveedor) {
        $ruta = rtrim($carpetaProveedor, '/\\') . '/' . trim($numeroFactura) . '.pdf';
        if (is_file($ruta)) {
            return $ruta;
        }
    }

    $rutaRecursiva = buscarPdfRecursivo($base, trim($numeroFactura) . '.pdf');
    if ($rutaRecursiva) {
        return $rutaRecursiva;
    }

    return null;
}

/**
 * Resuelve el directorio del proveedor usando multiples estrategias de coincidencia.
 */
function resolverDirectorioProveedor($base, $nombreProveedor)
{
    $nombreProveedor = trim((string) $nombreProveedor);

    if ($nombreProveedor === '') {
        return null;
    }

    $base = rtrim($base, '/\\');

    $directorioExacto = $base . '/' . $nombreProveedor;
    if (is_dir($directorioExacto)) {
        return $directorioExacto;
    }

    if (!is_dir($base)) {
        return null;
    }

    $directorios = array_filter(scandir($base), function ($entry) use ($base) {
        return $entry !== '.' && $entry !== '..' && is_dir($base . '/' . $entry);
    });

    if (empty($directorios)) {
        return null;
    }

    $nombreNormalizado = normalizarTexto($nombreProveedor);

    foreach ($directorios as $dir) {
        if (normalizarTexto($dir) === $nombreNormalizado) {
            return $base . '/' . $dir;
        }
    }

    $mejorSimilitud  = 0;
    $mejorDirectorio = null;

    foreach ($directorios as $dir) {
        $dirNormalizado = normalizarTexto($dir);
        similar_text($nombreNormalizado, $dirNormalizado, $porcentaje);

        if ($porcentaje > $mejorSimilitud) {
            $mejorSimilitud  = $porcentaje;
            $mejorDirectorio = $base . '/' . $dir;
        }
    }

    if ($mejorSimilitud >= 80) {
        return $mejorDirectorio;
    }

    $menorDistancia  = PHP_INT_MAX;
    $dirLevenshtein  = null;

    foreach ($directorios as $dir) {
        $dirNormalizado = normalizarTexto($dir);
        $distancia = levenshtein($nombreNormalizado, $dirNormalizado);

        if ($distancia < $menorDistancia) {
            $menorDistancia  = $distancia;
            $dirLevenshtein  = $base . '/' . $dir;
        }
    }

    if ($menorDistancia <= 5) {
        return $dirLevenshtein;
    }

    return null;
}

/**
 * Busca un archivo de forma recursiva dentro de un directorio base.
 * Retorna la ruta completa al primer archivo encontrado o null si no existe.
 */
function buscarPdfRecursivo($directorio, $nombreArchivo)
{
    if (!is_dir($directorio)) {
        return null;
    }

    $entradas = scandir($directorio);

    foreach ($entradas as $entrada) {
        if ($entrada === '.' || $entrada === '..') {
            continue;
        }

        $rutaCompleta = rtrim($directorio, '/\\') . '/' . $entrada;

        if (is_file($rutaCompleta) && $entrada === $nombreArchivo) {
            return $rutaCompleta;
        }

        if (is_dir($rutaCompleta)) {
            $encontrado = buscarPdfRecursivo($rutaCompleta, $nombreArchivo);
            if ($encontrado) {
                return $encontrado;
            }
        }
    }

    return null;
}

/**
 * Responde con un error HTTP y termina la ejecucion.
 */
function responderError($codigo, $mensaje)
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code($codigo);
    header("Content-Type: text/plain; charset=utf-8");
    echo $mensaje;
    exit;
}