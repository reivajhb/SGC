<?php
include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php"; // Conexión a BD local

if (session_status() === PHP_SESSION_NONE) session_start();

// El modo debug se mantiene
$DEBUG=(isset($_GET['debug']) && $_GET['debug']==='1');
if($DEBUG){
    ini_set('display_errors','1');
    error_reporting(E_ALL);
    header('Content-Type: text/plain; charset=utf-8');
}

// 1. Conexión a BD Local
if(!isset($conn) || $conn->connect_error){
    if($DEBUG) die("❌ Error BD local: ".$conn->connect_error);
    $_SESSION['flash_error']="❌ Error de conexión BD local.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}
$conn->set_charset('utf8mb4');

$id_hotel=isset($_GET['id'])?(int)$_GET['id']:0;
if($id_hotel<=0){
    if($DEBUG) die("❌ ID hotel inválido.");
    $_SESSION['flash_error']="❌ ID hotel inválido.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

/**
 * Escapa las comillas simples para SQL Server.
 */
function esc_sql($s){
    return str_replace("'", "''", trim((string)$s));
}

// ==================== 2. DATOS LOCALES (HOTEL Y CONTACTO) ====================
try{
    $sql_hotel="SELECT g.nombre,g.nit,g.nit_consecutivo,g.razon_social,g.direccion,g.telefono,g.ciudad FROM tbl_alojamiento_general g WHERE g.id_hotel = ? LIMIT 1";
    $st=$conn->prepare($sql_hotel);
    $st->bind_param("i",$id_hotel);
    $st->execute();
    $hotel=$st->get_result()->fetch_assoc();
    $st->close();

    if(!$hotel) throw new Exception("Hotel no encontrado.");

    $sql_contacto="SELECT email FROM tbl_alojamiento_contactos WHERE id_hotel = ? AND email IS NOT NULL AND email <> '' ORDER BY id_contacto ASC LIMIT 1";
    $stc=$conn->prepare($sql_contacto);
    $stc->bind_param("i",$id_hotel);
    $stc->execute();
    $contacto=$stc->get_result()->fetch_assoc();
    $stc->close();

}catch(Throwable $e){
    if($DEBUG) die("❌ Error consultando datos locales: ".$e->getMessage());
    $_SESSION['flash_error']="❌ Error consultando datos locales.";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}

// ==================== 3. PREPARACIÓN DE VARIABLES ====================
$nit=trim((string)($hotel['nit'] ?? ''));
$nit_consecutivo=trim((string)($hotel['nit_consecutivo'] ?? ''));
$razon_social=trim((string)(($hotel['razon_social'] ?? '') ?: ($hotel['nombre'] ?? '')));
$direccion=trim((string)($hotel['direccion'] ?? ''));
$telefono=trim((string)($hotel['telefono'] ?? ''));
$ciudad_original=trim((string)($hotel['ciudad'] ?? '')); 

$ciudad=$ciudad_original;
if(empty($ciudad_original) || strtoupper($ciudad_original)=='CIUDAD' || strtoupper($ciudad_original)=='CIUDAD DE PRUEBA'){
    $ciudad='Bogota'; 
}

if($nit===''){
    if($DEBUG) die("❌ El hotel no tiene NIT.");
    $_SESSION['flash_error']="❌ El hotel no tiene NIT.";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}

$base=$nit_consecutivo ?: preg_replace('/\D+/', '', $nit);
$idcliente=substr($base,0,25); 

$idtercero='11221909';  
$idVende='186';  
$codIctaCliente='130505'; 
$tipoCliente='002'; 
$usuarioZeus='jcastro'; 
$codAlternoCliente=$idcliente; 

$emailDefault=trim((string)($contacto['email'] ?? ''));
if($emailDefault==='') $emailDefault='director.sistemas@panamericanaviajes.com';
$emailEsc=esc_sql($emailDefault);

// ==================== SQL del EMAIL ====================
// Nota: Dejé espacios después de las comas en los VALUES de SQL para legibilidad y compatibilidad con SQL.
$emailSql=
    "CREATE TABLE #TEMPZML_SQL(TipoCorreo Varchar(50),Email Varchar(800)) " . 
    "Insert Into #TEMPZML_SQL Values('Email por Defecto','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Comprobantes','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Factura Electrónica','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envio de Extracto','{$emailEsc}') " .
    "Select * from #TEMPZML_SQL";

// === SQL DE CUENTAS POR MONEDA ===
$cuentasSql= 
    "CREATE TABLE #TEMPZML_SQL(IdMoneda Char(3),NombreMoneda Varchar(1000),Codicta Char(16),CodictaRemision Char(16),CupoCredito NUMERIC(18,2)) " .
    "Insert Into #TEMPZML_SQL Values('123','pRuEBa',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('ANT','Cruce de Anticipo',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('COP','PESOS',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('CRD','Credito',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('EFE','EFECTIVO ',' ','',0) " . 
    "Insert Into #TEMPZML_SQL Values('EUR','EURO',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('TCA','Tarjeta Credito Aerolinea Satena',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('US$','DOLARES AMERICANOS',' ',' ',0) " . 
    "Insert Into #TEMPZML_SQL Values('VAL','Moneda del Modulo de Valeras ',' ',' ',0) " .
    "Select * from #TEMPZML_SQL";

// ==================== 4. CONEXIÓN ZEUS (SQL SERVER) ====================
try{
    $zeus=new PDO(
        "sqlsrv:Server=192.168.155.105;Database=ZeusContabilidad_PNV",
        "usuario_export",
        "ClaveExport2025!",
        [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES=>false,
            PDO::SQLSRV_ATTR_ENCODING=>PDO::SQLSRV_ENCODING_UTF8,
        ]
    );
}catch(Throwable $e){
    if($DEBUG) die("❌ Error conectando a Zeus: ".$e->getMessage());
    $_SESSION['flash_error']="❌ Error conectando a Zeus.";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}

// ==================== 5. EJECUCIÓN: SpMae_CLIENTES (UPSERT) ====================
try{
    
    // 5.1. Verificar si el Cliente ya existe
    $chk=$zeus->prepare("SELECT 1 FROM CLIENTES WHERE IDCLIENTE = ?");
    $chk->execute([$idcliente]);
    $existe=(bool)$chk->fetchColumn();

    // 5.2. Definir la operación ('SMo' = Modificar, 'I' = Insertar)
    $op=$existe ? 'SMo' : 'I';

    // 5.3. Construir la llamada al Stored Procedure (COMPATIBILIDAD FORZADA)
    $sqlSP="
EXEC dbo.SpMae_CLIENTES
 @OP=:op,
 @IDCLIENTE=:idcliente,
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
 @IDVENDE=:idvende,
 @DIPLAZO=0,
 @DiaGracia=0,
 @CUPOCRE=0,
 @CODICTA=:codicta_cliente,
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
 @SEGMENTO='06',
 @TAG='',
 @CodigoUbicacion='ADS',
 @TIPOCLIENTE=:tipo_cliente,
 @FormatoDeFactura='',
 @FormatoDePedido='',
 @FormatoDeRemision='',
 @FormatoDeFacturaRemision='',
 @DIVPOLITICA='5711001',
 @Codigodane='11001',
 @Usuario=:usuario_zeus,
 @CodAlterno=:cod_alterno_cliente,
 @BLOQUEOPORNIT=0,
 @IndNCF=0,
 @GrEmpresarial='',
 @Pais='169',
 @Tipo='N',
 @CentroCosto='',
 @Item='',
 @GenerarMora=0,
 @IntMora=0,
 @Deshabilitado=0, 
 @FormaPagoCredito=1,
 @FormaPagoContado=1,
 @CuentasPorMoneda=:cuentas_sql, 
 @TipoEmail='',
 @TipoNotificacion_FE='E',
 @ProveedorTecnologico='',
 @UsoLibre='',
 @bl_CupoCreditoPorMoneda=0,
 @TipoAsumeMora=0    
";

    // 5.4. Preparar y ejecutar (SINTAXIS MÍNIMA SIN ESPACIOS DE ALINEACIÓN)
    $st=$zeus->prepare($sqlSP);
    $st->execute([
        ':op'=>$op,
        ':idcliente'=>$idcliente,
        ':idtercero'=>$idtercero,
        ':razon'=>$razon_social,
        ':direccion'=>$direccion,
        ':ciudad'=>$ciudad,
        ':telefono'=>$telefono,
        ':email_sql'=>$emailSql,
        ':cuentas_sql'=>$cuentasSql,
        // Parámetros específicos del CLIENTE
        ':idvende'=>$idVende,
        ':codicta_cliente'=>$codIctaCliente,
        ':tipo_cliente'=>$tipoCliente,
        ':usuario_zeus'=>$usuarioZeus,
        ':cod_alterno_cliente'=>$codAlternoCliente,
    ]);

    // 5.5. Validación (Buscar el cliente en la tabla CLIENTES)
    $chk2=$zeus->prepare("SELECT IDCLIENTE, IDTERCERO, RAZONCIAL FROM CLIENTES WHERE IDCLIENTE = ?");
    $chk2->execute([$idcliente]);
    $cliente=$chk2->fetch(PDO::FETCH_ASSOC);

    // 5.6. Salida de Debug
    if($DEBUG){
        echo "===== RESULTADO FINAL (POST-SP) =====\n";
        echo "OP USADO: {$op}\n";
        echo "EXISTÍA: ".($existe ? "SI" : "NO")."\n\n";
        if($cliente){
            echo "✅ CLIENTE PRESENTE EN CLIENTES\n\n";
            print_r($cliente);
        }else{
            echo "⚠️ NO APARECE EN CLIENTES TRAS EJECUTAR\n";
        }
        echo "\nIDCLIENTE: {$idcliente}\n";
        echo "IDTERCERO FIJO: {$idtercero}\n";
        echo "RAZON SOCIAL: {$razon_social}\n";
        echo "SQL SP USADO:\n{$sqlSP}\n\n";
        echo "EMAIL_SQL:\n{$emailSql}\n\n";
        echo "CUENTAS_SQL:\n{$cuentasSql}\n";
        exit();
    }

    // 5.7. Redirección y mensaje de éxito
    $_SESSION['flash_success']="✅ Cliente Zeus OK ({$op}): {$idcliente} (Tercero: {$idtercero})";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();

}catch(Throwable $e){
    // 5.8. Salida de Debug en caso de error
    if($DEBUG){
        echo "❌ ERROR ZEUS\n\n";
        echo "Mensaje: ".$e->getMessage()."\n\n";
        echo "IDCLIENTE: {$idcliente}\n";
        echo "RAZON SOCIAL: {$razon_social}\n";
        echo "SQL SP USADO:\n{$sqlSP}\n\n";
        exit();
    }

    // 5.9. Redirección y mensaje de error
    $_SESSION['flash_error']="❌ Error Zeus al crear cliente: ".$e->getMessage();
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}