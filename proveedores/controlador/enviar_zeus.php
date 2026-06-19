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

// ==================== 3. PREPARACIÓN DE VARIABLES GLOBALES ====================
$nit=trim((string)($hotel['nit'] ?? '')); 
$nit_consecutivo=trim((string)($hotel['nit_consecutivo'] ?? '')); // Contiene 860402288B si existe
$razon_social=trim((string)(($hotel['razon_social'] ?? '') ?: ($hotel['nombre'] ?? '')));
$direccion=trim((string)($hotel['direccion'] ?? ''));
$telefono=trim((string)($hotel['telefono'] ?? ''));
$ciudad_original=trim((string)($hotel['ciudad'] ?? '')); 
$nombre_tercero=$razon_social; 
$usuarioZeus='jcastro'; 

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

// --- VARIABLES CRÍTICAS DE IDENTIFICACIÓN ---
$nit_solo_digitos = preg_replace('/\D+/', '', $nit); 

// 1. IDTERCERO (NIT Puro) - Usado para la tabla TERCEROS
$idtercero = $nit_solo_digitos; 

// 2. ID CLIENTE/PROVEEDOR - Usa el consecutivo si existe, sino usa el NIT puro
$idcliente_prove_alt = $nit_consecutivo ?: $idtercero; 

// 3. DV fijo en '1'
$digito_verif = '1';
// --------------------------------------------------------

$emailDefault=trim((string)($contacto['email'] ?? ''));
if($emailDefault==='') $emailDefault='director.sistemas@panamericanaviajes.com';
$emailEsc=esc_sql($emailDefault);

// ==================== LOTES SQL REUTILIZABLES ====================

// LOTE 1: SQL del EMAIL (Completo para asegurar la validación de Factura Electrónica en el Tercero)
$emailSql =
    "CREATE TABLE #TEMPZML_SQL(TipoCorreo Varchar(50),Email Varchar(800)) " . 
    "Insert Into #TEMPZML_SQL Values('Email por Defecto','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Comprobantes','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Factura Electrónica','{$emailEsc}') " . 
    "Insert Into #TEMPZML_SQL Values('Envio de Extracto','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Pagos','{$emailEsc}') " . 
    "Select * from #TEMPZML_SQL";

// LOTE 2: SQL de CUENTAS POR MONEDA (Usado para Proveedor)
$cuentasSqlProv = 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('123','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('ANT','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('COP','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('CRD','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('EFE','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('EUR','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('TCA','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('US$','','')" . 
    "Insert into #cuentas(Idmoneda,Codicta,codictaProvision) Values('VAL','','')";
    
// LOTE 3: SQL de CUENTAS POR MONEDA (Usado para Cliente)
$cuentasSqlCliente = 
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
    if($DEBUG) die("❌ Error conectando a Zeus (Global): ".$e->getMessage());
    $_SESSION['flash_error']="❌ Error conectando a Zeus.";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}

// ==================== 5. EJECUCIÓN DEL FLUJO COMPLETO (1. TERCERO -> 2. PROVEEDOR -> 3. CLIENTE) ====================
$log_steps = [];
$success = false;

try{
    
    // =======================================================
    // 1. CREAR TERCERO (SPMae_Terceros)
    // Usamos el lote SQL COMPLETO en @Email
    // =======================================================
    $log_steps[] = "INICIANDO CREACIÓN DE TERCERO (ID: {$idtercero})";
    
    $chk_tercero=$zeus->prepare("SELECT 1 FROM TERCEROS WHERE IDTERCERO = ?");
    $chk_tercero->execute([$idtercero]);
    $existe_tercero=(bool)$chk_tercero->fetchColumn();
    $op_tercero=$existe_tercero ? 'SMonedas' : 'I';

    // Parámetros Tercero fijos:
    $tipo_terce='J'; $tipo_empresa='CNT'; $tipo_identif='13'; 
    $tipo_razon_social='AN'; $segmento='06'; $div_politica='5711001'; 
    $cod_dane='11001'; $pais='169'; 

    $sqlSP_Tercero="
EXEC dbo.SpMae_Terceros
 @OP=:op, @IDTERCERO=:idtercero, @NOMBRETER=:nombreter, @DIGIVERIF=:digito_verif, @TIPOTERCE=:tipo_terce, 
 @TipoEmpresa=:tipo_empresa, @SEGMENTO=:segmento, @DIVPOLITICA=:div_politica, @CODIGODANE=:cod_dane, 
 @DIRECCION=:direccion, @CIUDAD=:ciudad, @Telefono=:telefono, @TipoIdentificacion=:tipo_identif, 
 @Nombre1='', @Nombre2='', @Apellido1='', @Apellido2='', @TipoRazonSocial=:tipo_razon_social, 
 @Usuario=:usuario_zeus, @Prefijo_NCF='', @Sexo='M', @Profesion='', @FechaNacimiento='20191231', 
 @Hobbies='', @NombreConyugue='', @FechaNacimientoConyugue='20191231', @TipoClienteFrecuenciaCompra='', 
 @EstratoSocial='', @Barrio='', @Telefono2='', @Celular='', @Fechagrabacion='20200609', 
 @Pais=:pais, @Tipo='N', @CentroCosto='', @Item='', @Deshabilitado=0, @CodigoOcupacion='', 
 @Email=:email_sql_tercero, 
 @ManejaAcuerdo=0, @FechaInicialAcuerdo='20191231', @FechaFinalAcuerdo='20191231', 
 @Escenarios_ClaseContribuyente_Iden=0, @Escenarios_CategoriaTributariaIVA_Iden=0, @Escenarios_TipoContribuyente_Iden=0, 
 @Escenarios_EsAutorretenedor_Iden=0, @Escenarios_AplicaICAT_Iden=0, @Escenarios_TipoRetencionIVA_Iden=0, 
 @AReteSunat=0, @APercepSunat=0, @BuenContriSunat=0, @TipoEmail='', @bl_CupoCreditoPorCliente=1, 
 @bl_CupoCreditoPorMoneda=0, @DiasGracia=0, @ValorCupoCredito=0, @bl_Bloqueo=0, @CrearProveedor=0, 
 @CrearCliente=0, @DiPlazo=0, @TipoBloqueo='T', @bl_FechaNacimiento=0
";

    $st=$zeus->prepare($sqlSP_Tercero);
    $st->execute([
        ':op'=>$op_tercero, ':idtercero'=>$idtercero, ':nombreter'=>$nombre_tercero, ':digito_verif'=>$digito_verif,
        ':tipo_terce'=>$tipo_terce, ':tipo_empresa'=>$tipo_empresa, ':segmento'=>$segmento, ':div_politica'=>$div_politica,
        ':cod_dane'=>$cod_dane, ':direccion'=>$direccion, ':ciudad'=>$ciudad, ':telefono'=>$telefono,
        ':tipo_identif'=>$tipo_identif, ':tipo_razon_social'=>$tipo_razon_social, ':usuario_zeus'=>$usuarioZeus, 
        ':pais'=>$pais, ':email_sql_tercero'=>$emailSql, // <-- Aquí se envía el lote SQL completo
    ]);
    
    $log_steps[] = "✅ TERCERO CREADO/ACTUALIZADO (OP: {$op_tercero}). NIT: {$idtercero} (DV: {$digito_verif})";
    
    // =======================================================
    // 2. CREAR PROVEEDOR (SpMae_Proveedores)
    // IDPROVE: Usa el consecutivo ($idcliente_prove_alt)
    // =======================================================
    $log_steps[] = "INICIANDO CREACIÓN DE PROVEEDOR (ID: {$idcliente_prove_alt})";
    
    $chk_prove=$zeus->prepare("SELECT 1 FROM PROVEEDORES WHERE IDPROVE = ?");
    $chk_prove->execute([$idcliente_prove_alt]);
    $existe_prove=(bool)$chk_prove->fetchColumn();
    $op_prove=$existe_prove ? 'SMo' : 'I';

    $sqlSP_Prove="
EXEC dbo.SpMae_Proveedores
 @Op=:op, @IDPROVE=:idprove, @IDTERCERO=:idtercero, @RAZONCIAL=:razon, @DIRECCION=:direccion, 
 @CIUDAD=:ciudad, @TELEFONO=:telefono, @FAX='', @DIRCORRES='', @EMAIL=:email_sql, @WEBSITE='', 
 @IDZONA='ZG', @DIPLAZO=0, @CUPOCRE=0, @CODICTA='2815100101', @CONTACTO='', @DIRCONTA='', 
 @EMAILCONTA='', @TELCONTA='', @CONTACTOA='', @DIRCONTAA='', @EMAILCONTAA='', @TELCONTAA='', 
 @GERENTE='', @EMAILGEREN='', @DIRGERENTE='', @TELGERENTE='', @SEGMENTO='02', @CODIGOUBICACION='ADS', 
 @DIVPOLITICA='5741206', @CODIGODANE='41206', @SERIE='', @AUTORIZACION='', @Usuario=:usuario_zeus, 
 @CodAlterno='', @Tag='', @Prefijo_NCF='', @IndEmail=0, @Cuentasxmonedas=:cuentas_sql, 
 @GrEmpresarial='', @CentroCosto='', @Pais='169', @Tipo='N', @Item='', @Deshabilitado=0, 
 @IdClaseProv=0, @TipoEmail=''
";

    $st=$zeus->prepare($sqlSP_Prove);
    $st->execute([
        ':op' => $op_prove, ':idprove' => $idcliente_prove_alt, ':idtercero' => $idtercero,
        ':razon' => $razon_social, ':direccion' => $direccion, ':ciudad' => $ciudad,
        ':telefono' => $telefono, ':email_sql' => $emailSql, ':cuentas_sql'=> $cuentasSqlProv, 
        ':usuario_zeus' => $usuarioZeus,
    ]);

    $log_steps[] = "✅ PROVEEDOR CREADO/ACTUALIZADO (OP: {$op_prove}). ID Proveedor: {$idcliente_prove_alt}";

    // =======================================================
    // 3. CREAR CLIENTE (SpMae_CLIENTES)
    // IDCLIENTE: Usa el consecutivo ($idcliente_prove_alt)
    // =======================================================
    $log_steps[] = "INICIANDO CREACIÓN DE CLIENTE (ID: {$idcliente_prove_alt})";

    $chk_cliente=$zeus->prepare("SELECT 1 FROM CLIENTES WHERE IDCLIENTE = ?");
    $chk_cliente->execute([$idcliente_prove_alt]);
    $existe_cliente=(bool)$chk_cliente->fetchColumn();
    $op_cliente=$existe_cliente ? 'SMo' : 'I';

    $codIctaCliente = '130505'; $idVende = '186'; $tipoCliente = '002';

    $sqlSP_Cliente="
EXEC dbo.SpMae_CLIENTES
 @OP=:op, @IDCLIENTE=:idcliente, @IDTERCERO=:idtercero, @RAZONCIAL=:razon, @DIRECCION=:direccion,
 @CIUDAD=:ciudad, @TELEFONO=:telefono, @FAX='', @DIRCORRES='', @EMAIL=:email_sql, @WEBSITE='',
 @IDZONA='ZG', @IDVENDE=:idvende, @DIPLAZO=0, @DiaGracia=0, @CUPOCRE=0, @CODICTA=:codicta_cliente,
 @CONTACTO='', @DIRCONTA='', @EMAILCONTA='', @TELCONTA='', @CONTACTOA='', @DIRCONTAA='',
 @EMAILCONTAA='', @TELCONTAA='', @GERENTE='', @EMAILGEREN='', @DIRGERENTE='', @TELGERENTE='',
 @SEGMENTO='06', @TAG='', @CodigoUbicacion='ADS', @TIPOCLIENTE=:tipo_cliente, @FormatoDeFactura='',
 @FormatoDePedido='', @FormatoDeRemision='', @FormatoDeFacturaRemision='', @DIVPOLITICA='5711001',
 @Codigodane='11001', @Usuario=:usuario_zeus, @CodAlterno=:cod_alterno_cliente, @BLOQUEOPORNIT=0,
 @IndNCF=0, @GrEmpresarial='', @Pais='169', @Tipo='N', @CentroCosto='', @Item='', @GenerarMora=0,
 @IntMora=0, @Deshabilitado=0, @FormaPagoCredito=1, @FormaPagoContado=1, @CuentasPorMoneda=:cuentas_sql,
 @TipoEmail='', @TipoNotificacion_FE='E', @ProveedorTecnologico='', @UsoLibre='',
 @bl_CupoCreditoPorMoneda=0, @TipoAsumeMora=0
";

    $st=$zeus->prepare($sqlSP_Cliente);
    $st->execute([
        ':op' => $op_cliente, ':idcliente' => $idcliente_prove_alt, ':idtercero' => $idtercero,
        ':razon' => $razon_social, ':direccion' => $direccion, ':ciudad' => $ciudad,
        ':telefono' => $telefono, ':email_sql' => $emailSql, ':cuentas_sql' => $cuentasSqlCliente,
        ':idvende' => $idVende, ':codicta_cliente' => $codIctaCliente, ':tipo_cliente' => $tipoCliente,
        ':usuario_zeus' => $usuarioZeus, ':cod_alterno_cliente' => $idcliente_prove_alt,
    ]);

    $log_steps[] = "✅ CLIENTE CREADO/ACTUALIZADO (OP: {$op_cliente}). ID Cliente: {$idcliente_prove_alt}";

    $success = true;

} catch (Throwable $e) {
    $log_steps[] = "❌ ERROR: ".$e->getMessage();
}

// ==================== 6. SALIDA DE DEBUG / REDIRECCIÓN ====================
if ($DEBUG) {
    echo "===== FLUJO DE CREACIÓN DE ENTIDAD ZEUS FINALIZADO =====\n";
    echo "ID Único (NIT Puro / ID TERCERO): {$idtercero}\n";
    echo "ID Cliente/Proveedor (Consecutivo): {$idcliente_prove_alt}\n";
    echo "--- LOG DE EJECUCIÓN ---\n";
    print_r($log_steps);
    
    if ($success) {
        echo "\n\n✅ FLUJO COMPLETO EXITOSO: Tercero, Proveedor y Cliente creados/actualizados.";
    } else {
        echo "\n\n🛑 FLUJO FALLIDO. Ver error en el log.";
    }
    exit();
}

if ($success) {
    $_SESSION['flash_success'] = "✅ Entidad Zeus Completa Creada (Tercero, Proveedor, Cliente): {$idtercero}";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
} else {
    $_SESSION['flash_error'] = "❌ Error al crear Entidad Zeus Completa. Último paso fallido: " . end($log_steps);
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}