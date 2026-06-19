<?php
include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// El modo debug se mantiene
$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');
if ($DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
    header('Content-Type: text/plain; charset=utf-8');
}

if (!isset($conn) || $conn->connect_error) {
    if ($DEBUG) die("❌ Error BD local: " . $conn->connect_error);
    $_SESSION['flash_error'] = "❌ Error de conexión BD local.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}
$conn->set_charset('utf8mb4');

$id_hotel = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_hotel <= 0) {
    if ($DEBUG) die("❌ ID hotel inválido.");
    $_SESSION['flash_error'] = "❌ ID hotel inválido.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

function esc_sql($s) {
    return str_replace("'", "''", trim((string)$s));
}

// ==================== DATOS LOCAL ====================
try {
    $sql_hotel = "
        SELECT g.nombre, g.nit, g.nit_consecutivo, g.razon_social,
               g.direccion, g.telefono, g.ciudad
        FROM tbl_alojamiento_general g
        WHERE g.id_hotel = ?
        LIMIT 1
    ";
    $st = $conn->prepare($sql_hotel);
    $st->bind_param("i", $id_hotel);
    $st->execute();
    $hotel = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$hotel) throw new Exception("Hotel no encontrado.");

    $sql_contacto = "
        SELECT email
        FROM tbl_alojamiento_contactos
        WHERE id_hotel = ?
          AND email IS NOT NULL AND email <> ''
        ORDER BY id_contacto ASC
        LIMIT 1
    ";
    $stc = $conn->prepare($sql_contacto);
    $stc->bind_param("i", $id_hotel);
    $stc->execute();
    $contacto = $stc->get_result()->fetch_assoc();
    $stc->close();

} catch (Throwable $e) {
    if ($DEBUG) die("❌ Error consultando datos locales: " . $e->getMessage());
    $_SESSION['flash_error'] = "❌ Error consultando datos locales.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// ==================== VARIABLES ====================
$nit             = trim((string)($hotel['nit'] ?? ''));
$nit_consecutivo = trim((string)($hotel['nit_consecutivo'] ?? ''));
$razon_social    = trim((string)(($hotel['razon_social'] ?? '') ?: ($hotel['nombre'] ?? '')));
$direccion       = trim((string)($hotel['direccion'] ?? ''));
$telefono        = trim((string)($hotel['telefono'] ?? ''));
$ciudad_original = trim((string)($hotel['ciudad'] ?? '')); 

// === AJUSTE DE CIUDAD ===
$ciudad = $ciudad_original;
if (empty($ciudad_original) || strtoupper($ciudad_original) == 'CIUDAD' || strtoupper($ciudad_original) == 'CIUDAD DE PRUEBA') {
    $ciudad = 'Bogota'; 
}
// =========================

if ($nit === '') {
    if ($DEBUG) die("❌ El hotel no tiene NIT.");
    $_SESSION['flash_error'] = "❌ El hotel no tiene NIT.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

$base    = $nit_consecutivo ?: preg_replace('/\D+/', '', $nit);
$idprove = substr($base, 0, 25); // <-- ¡AJUSTADO! Ahora solo usa $base

// ✅ TERCERO FIJO DE PRODUCCIÓN
$idtercero = '11221909';

$emailDefault = trim((string)($contacto['email'] ?? ''));
if ($emailDefault === '') $emailDefault = 'director.sistemas@panamericanaviajes.com';
$emailEsc = esc_sql($emailDefault);

// =========================================================================================
// ✅ SQL del EMAIL (Versión de la traza manual: Insert Values y tabla local #)
// =========================================================================================
$emailSql =
    "CREATE TABLE #TEMPZML_SQL(TipoCorreo Varchar(50),Email Varchar(800)) " . 
    "Insert Into #TEMPZML_SQL Values('Email por Defecto','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Pagos','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Comprobantes','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Factura Electrónica','{$emailEsc}') " .
    "Select * from #TEMPZML_SQL";

// === SQL DE CUENTAS POR MONEDA (CRÍTICO PARA LA INSERCIÓN) ===
$cuentasSql = 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('123','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('ANT','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('COP','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('CRD','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('EFE','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('EUR','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('TCA','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('US$','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('VAL','','')";

// ==================== CONEXIÓN ZEUS ====================
try {
    $zeus = new PDO(
        "sqlsrv:Server=192.168.155.105;Database=ZeusContabilidad_PNV",
        "usuario_export",
        "ClaveExport2025!",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
        ]
    );
} catch (Throwable $e) {
    if ($DEBUG) die("❌ Error conectando a Zeus: " . $e->getMessage());
    $_SESSION['flash_error'] = "❌ Error conectando a Zeus.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// ==================== UPSERT: EXISTE? ====================
try {
    
    $chk = $zeus->prepare("SELECT 1 FROM PROVEEDORES WHERE IDPROVE = ?");
    $chk->execute([$idprove]);
    $existe = (bool)$chk->fetchColumn();

    $op = $existe ? 'SMo' : 'I';

    $sqlSP = "
EXEC dbo.SpMae_Proveedores
 @Op=:op,
 @IDPROVE=:idprove,
 @IDTERCERO=:idtercero,
 @RAZONCIAL=:razon,
 @DIRECCION=:direccion,
 @CIUDAD=:ciudad,
 @TELEFONO=:telefono,
 @FAX='',
 @DIRCORRES='',
 @EMAIL=:email_sql,
 @WEBSITE='',
 @IDZONA='ZG',
 @DIPLAZO=0,
 @CUPOCRE=0,
 @CODICTA='2815100101',
 @CONTACTO='',
 @DIRCONTA='',
 @EMAILCONTA='',
 @TELCONTA='',
 @CONTACTOA='',
 @DIRCONTAA='',
 @EMAILCONTAA='',
 @TELCONTAA='',
 @GERENTE='',
 @EMAILGEREN='',
 @DIRGERENTE='',
 @TELGERENTE='',
 @SEGMENTO='02',
 @CODIGOUBICACION='ADS',
 @DIVPOLITICA='5741206',
 @CODIGODANE='41206',
 @SERIE='',
 @AUTORIZACION='',
 @Usuario='jcastro',
 @CodAlterno='', /* <- CORRECCIÓN: Se pasa como cadena vacía directa, no como parámetro nombrado. */
 @Tag='',
 @Prefijo_NCF='',
 @IndEmail=0,
 @Cuentasxmonedas=:cuentas_sql,
 @GrEmpresarial='',
 @CentroCosto='',
 @Pais='169',
 @Tipo='N',
 @Item='',
 @Deshabilitado=0, 
 @IdClaseProv=0, /* <- CORRECCIÓN: Valor de la traza manual */
 @TipoEmail=''
";

    $st = $zeus->prepare($sqlSP);
    $st->execute([
        ':op'         => $op,
        ':idprove'    => $idprove,
        ':idtercero'  => $idtercero,
        ':razon'      => $razon_social,
        ':direccion'  => $direccion,
        ':ciudad'     => $ciudad,
        ':telefono'   => $telefono,
        ':email_sql'  => $emailSql,
        ':cuentas_sql'=> $cuentasSql, 
        // Se omite :codalterno de execute()
    ]);

    // Validación real
    $chk2 = $zeus->prepare("SELECT IDPROVE, IDTERCERO, RAZONCIAL FROM PROVEEDORES WHERE IDPROVE = ?");
    $chk2->execute([$idprove]);
    $prov = $chk2->fetch(PDO::FETCH_ASSOC);

    if ($DEBUG) {
        echo "===== RESULTADO FINAL (POST-SP) =====\n";
        echo "OP USADO: {$op}\n";
        echo "EXISTÍA: " . ($existe ? "SI" : "NO") . "\n\n";
        if ($prov) {
            echo "✅ PROVEEDOR PRESENTE EN PROVEEDORES\n\n";
            print_r($prov);
        } else {
            echo "⚠️ NO APARECE EN PROVEEDORES TRAS EJECUTAR\n";
        }
        echo "\nIDPROVE: {$idprove}\n";
        echo "IDTERCERO FIJO: {$idtercero}\n";
        echo "EMAIL_DEFAULT: {$emailDefault}\n\n";
        echo "EMAIL_SQL:\n{$emailSql}\n";
        echo "CUENTAS_SQL:\n{$cuentasSql}\n";
        exit();
    }

    $_SESSION['flash_success'] = "✅ Zeus OK ({$op}): {$idprove} (Tercero: {$idtercero})";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();

} catch (Throwable $e) {
    if ($DEBUG) {
        echo "❌ ERROR ZEUS\n\n";
        echo "Mensaje: " . $e->getMessage() . "\n\n";
        echo "IDPROVE: {$idprove}\n";
        echo "IDTERCERO FIJO: {$idtercero}\n";
        echo "EMAIL_DEFAULT: {$emailDefault}\n\n";
        echo "EMAIL_SQL:\n{$emailSql}\n";
        echo "CUENTAS_SQL:\n{$cuentasSql}\n";
        exit();
    }

    $_SESSION['flash_error'] = "❌ Error Zeus: " . $e->getMessage();
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}