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

// ==================== 3. PREPARACIÓN DE VARIABLES (NIT COMPLETO EN IDTERCERO) ====================
$nit=trim((string)($hotel['nit'] ?? '')); 
$nit_consecutivo=trim((string)($hotel['nit_consecutivo'] ?? '')); 
$razon_social=trim((string)(($hotel['razon_social'] ?? '') ?: ($hotel['nombre'] ?? '')));
$direccion=trim((string)($hotel['direccion'] ?? ''));
$telefono=trim((string)($hotel['telefono'] ?? ''));
$ciudad_original=trim((string)($hotel['ciudad'] ?? '')); 
$nombre_tercero=$razon_social; 

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

// --- LÓGICA DE NIT SIN SEPARACIÓN ---
// 1. Limpiamos el NIT para obtener solo dígitos. Esto será el ID del Tercero.
$nit_solo_digitos = preg_replace('/\D+/', '', $nit); 

// 2. IDTERCERO toma el NIT completo de X dígitos. (Esto podría causar el error de longitud).
$idtercero = $nit_solo_digitos; 

// 3. El Dígito de Verificación se fija en '1' por solicitud.
$digito_verif = '1'; 
// ------------------------------------

$usuarioZeus='jcastro'; 
$emailDefault=trim((string)($contacto['email'] ?? ''));
if($emailDefault==='') $emailDefault='director.sistemas@panamericanaviajes.com';
$emailEsc=esc_sql($emailDefault);

// Variables fijas (ajustadas a la traza):
$tipo_terce='J'; // Jurídico
$tipo_empresa='CNT'; 
$tipo_identif='13'; // NIT/RUT
$tipo_razon_social='AN'; 
$segmento='06'; 
$div_politica='5711001'; 
$cod_dane='11001'; 
$pais='169'; 

// ==================== SQL del EMAIL (LOTE SQL para el Tercero) ====================
$emailSqlTercero =
    "CREATE TABLE #TEMPZML_SQL(TipoCorreo Varchar(50),Email Varchar(800)) " . 
    "Insert Into #TEMPZML_SQL Values('Email por Defecto','{$emailEsc}') " .
    "Insert Into #TEMPZML_SQL Values('Envío de Factura Electrónica','{$emailEsc}') " . 
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

// ==================== 5. EJECUCIÓN: SpMae_Terceros (UPSERT) ====================
try{
    
    // 5.1. Verificar si el Tercero ya existe
    $chk=$zeus->prepare("SELECT 1 FROM TERCEROS WHERE IDTERCERO = ?");
    $chk->execute([$idtercero]);
    $existe=(bool)$chk->fetchColumn();

    // 5.2. Definir la operación ('SMonedas' = Modificar, 'I' = Insertar)
    $op=$existe ? 'SMonedas' : 'I';

    // 5.3. Construir la llamada al Stored Procedure SpMae_Terceros
    $sqlSP="
EXEC dbo.SpMae_Terceros
 @OP=:op,
 @IDTERCERO=:idtercero,
 @NOMBRETER=:nombreter,
 @DIGIVERIF=:digito_verif,
 @TIPOTERCE=:tipo_terce,
 @TipoEmpresa=:tipo_empresa,
 @SEGMENTO=:segmento,
 @DIVPOLITICA=:div_politica,
 @CODIGODANE=:cod_dane,
 @DIRECCION=:direccion,
 @CIUDAD=:ciudad,
 @Telefono=:telefono,
 @TipoIdentificacion=:tipo_identif,
 @Nombre1='',
 @Nombre2='',
 @Apellido1='',
 @Apellido2='',
 @TipoRazonSocial=:tipo_razon_social,
 @Usuario=:usuario_zeus,
 @Prefijo_NCF='',
 @Sexo='M',
 @Profesion='',
 @FechaNacimiento='20191231',
 @Hobbies='',
 @NombreConyugue='',
 @FechaNacimientoConyugue='20191231',
 @TipoClienteFrecuenciaCompra='',
 @EstratoSocial='',
 @Barrio='',
 @Telefono2='',
 @Celular='',
 @Fechagrabacion='20200609',
 @Pais=:pais,
 @Tipo='N',
 @CentroCosto='',
 @Item='',
 @Deshabilitado=0,
 @CodigoOcupacion='',
 @Email=:email_sql_tercero,
 @ManejaAcuerdo=0,
 @FechaInicialAcuerdo='20191231',
 @FechaFinalAcuerdo='20191231',
 @Escenarios_ClaseContribuyente_Iden=0,
 @Escenarios_CategoriaTributariaIVA_Iden=0,
 @Escenarios_TipoContribuyente_Iden=0,
 @Escenarios_EsAutorretenedor_Iden=0,
 @Escenarios_AplicaICAT_Iden=0,
 @Escenarios_TipoRetencionIVA_Iden=0,
 @AReteSunat=0,
 @APercepSunat=0,
 @BuenContriSunat=0,
 @TipoEmail='',
 @bl_CupoCreditoPorCliente=1,
 @bl_CupoCreditoPorMoneda=0,
 @DiasGracia=0,
 @ValorCupoCredito=0,
 @bl_Bloqueo=0,
 @CrearProveedor=0,
 @CrearCliente=0,
 @DiPlazo=0,
 @TipoBloqueo='T', 
 @bl_FechaNacimiento=0
";

    // 5.4. Preparar y ejecutar
    $st=$zeus->prepare($sqlSP);
    $st->execute([
        ':op'=>$op,
        ':idtercero'=>$idtercero,
        ':nombreter'=>$nombre_tercero,
        ':digito_verif'=>$digito_verif,
        ':tipo_terce'=>$tipo_terce,
        ':tipo_empresa'=>$tipo_empresa,
        ':segmento'=>$segmento,
        ':div_politica'=>$div_politica,
        ':cod_dane'=>$cod_dane,
        ':direccion'=>$direccion,
        ':ciudad'=>$ciudad,
        ':telefono'=>$telefono,
        ':tipo_identif'=>$tipo_identif,
        ':tipo_razon_social'=>$tipo_razon_social,
        ':usuario_zeus'=>$usuarioZeus, 
        ':pais'=>$pais,
        ':email_sql_tercero'=>$emailSqlTercero, 
    ]);

    // 5.5. Validación real
    $chk2=$zeus->prepare("SELECT IDTERCERO, NOMBRETER FROM TERCEROS WHERE IDTERCERO = ?");
    $chk2->execute([$idtercero]);
    $tercero=$chk2->fetch(PDO::FETCH_ASSOC);

    // 5.6. Salida de Debug
    if($DEBUG){
        echo "===== RESULTADO FINAL (POST-SP) =====\n";
        echo "OP USADO: {$op}\n";
        echo "ID TERCERO (NIT COMPLETO) ENVIADO: {$idtercero}\n";
        echo "DÍGITO DE VERIFICACIÓN FIJO ENVIADO: {$digito_verif}\n\n";
        if($tercero){
            echo "✅ TERCERO PRESENTE EN TERCEROS\n\n";
            print_r($tercero);
        }else{
            echo "⚠️ NO APARECE EN TERCEROS TRAS EJECUTAR\n";
        }
        echo "EMAIL SQL TERCERO:\n{$emailSqlTercero}\n\n";
        exit();
    }

    // 5.7. Redirección y mensaje de éxito
    $_SESSION['flash_success']="✅ Tercero Zeus OK ({$op}): {$idtercero}";
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();

}catch(Throwable $e){
    // 5.8. Salida de Debug en caso de error
    if($DEBUG){
        echo "❌ ERROR ZEUS\n\n";
        echo "Mensaje: ".$e->getMessage()."\n\n";
        echo "IDTERCERO: {$idtercero}\n";
        echo "DIGIVERIF: {$digito_verif}\n";
        echo "SQL SP USADO:\n{$sqlSP}\n\n";
        exit();
    }

    // 5.9. Redirección y mensaje de error
    $_SESSION['flash_error']="❌ Error Zeus al crear tercero: ".$e->getMessage();
    header("Location: ../vista/consultaHotel.php?id=".$id_hotel);
    exit();
}