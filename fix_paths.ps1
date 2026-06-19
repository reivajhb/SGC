$projectDir = "facturacion\project"
$count = 0

# Profundidad 1: un nivel bajo project/
$depth1 = @("clienteHAA","facturasCorp","facturasRecepcion","hotel","tiquetes","transporte","usuarios")

# Profundidad 2: dos niveles bajo project/
$depth2 = @(
    "contabilidad\causacion",
    "contabilidad\proveedores",
    "contabilidad\reportes",
    "contabilidad\retenciones",
    "contabilidad\trm",
    "proveedores\proveedoresAdmin",
    "proveedores\proveedoresInternacionales",
    "proveedores\proveedoresPrepago",
    "proveedores\proveedoresTur"
)

# Profundidad 3: tres niveles bajo project/
$depth3 = @("proveedores\proveedoresTur\update")

# --- DEPTH 1: config = ../../config/  |  root = ../../../ ---
foreach ($folder in $depth1) {
    Get-ChildItem "$projectDir\$folder" -Filter "*.php" | ForEach-Object {
        $content  = Get-Content $_.FullName -Raw -Encoding UTF8
        $original = $content

        $content = $content.Replace('"../config/',     '"../../config/')
        $content = $content.Replace("'../config/",     "'../../config/")
        $content = $content.Replace("'../../PHPMailer/",   "'../../../PHPMailer/")
        $content = $content.Replace('"../../PHPMailer/',   '"../../../PHPMailer/')
        $content = $content.Replace("'../../google-api",   "'../../../google-api")
        $content = $content.Replace('"../../google-api',   '"../../../google-api')
        $content = $content.Replace('"principal.php"',     '"../../views/principal.php"')

        if ($content -ne $original) {
            Set-Content $_.FullName $content -NoNewline -Encoding UTF8
            $count++
            Write-Host "OK d1: $folder\$($_.Name)"
        }
    }
}

# --- DEPTH 2: config = ../../../config/  |  root = ../../../../ ---
foreach ($folder in $depth2) {
    Get-ChildItem "$projectDir\$folder" -Filter "*.php" | ForEach-Object {
        $content  = Get-Content $_.FullName -Raw -Encoding UTF8
        $original = $content

        $content = $content.Replace('"../config/',      '"../../../config/')
        $content = $content.Replace("'../config/",      "'../../../config/")
        $content = $content.Replace("'../../PHPMailer/",    "'../../../../PHPMailer/")
        $content = $content.Replace('"../../PHPMailer/',    '"../../../../PHPMailer/')
        $content = $content.Replace("'../../google-api",    "'../../../../google-api")
        $content = $content.Replace('"../../google-api',    '"../../../../google-api')
        $content = $content.Replace('"principal.php"',      '"../../../views/principal.php"')

        if ($content -ne $original) {
            Set-Content $_.FullName $content -NoNewline -Encoding UTF8
            $count++
            Write-Host "OK d2: $folder\$($_.Name)"
        }
    }
}

# --- DEPTH 3: config = ../../../../config/  |  root = ../../../../../ ---
foreach ($folder in $depth3) {
    Get-ChildItem "$projectDir\$folder" -Filter "*.php" | ForEach-Object {
        $content  = Get-Content $_.FullName -Raw -Encoding UTF8
        $original = $content

        # Este archivo tiene rutas bare "../seguridad.php" y "../conexion.php"
        $content = $content.Replace('"../seguridad.php"', '"../../../../config/seguridad.php"')
        $content = $content.Replace('"../conexion.php"',  '"../../../../config/conexion.php"')
        $content = $content.Replace('"../config/',        '"../../../../config/')
        $content = $content.Replace("'../config/",        "'../../../../config/")
        $content = $content.Replace('"principal.php"',    '"../../../../views/principal.php"')

        if ($content -ne $original) {
            Set-Content $_.FullName $content -NoNewline -Encoding UTF8
            $count++
            Write-Host "OK d3: $folder\$($_.Name)"
        }
    }
}

Write-Host ""
Write-Host "=== Total archivos actualizados: $count ==="
