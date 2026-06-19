# 🔍 GUÍA DE DIAGNÓSTICO - APROBACIÓN NO FUNCIONA

## Problema
Al dar clic en "Aprobar", no se envía ni a Juniper ni a CRM y no aparece el debug.

---

## ✅ SOLUCIÓN IMPLEMENTADA

### Cambios realizados en `enviar_aprobacion.php`:

1. **Debug mejorado**: Ahora muestra información detallada desde el inicio
2. **Manejo de errores**: Los errores ya no se silencian, se guardan en sesión
3. **Logging**: Se registran errores en PHP error_log
4. **Validación de pasos**: Cada paso (BD, Juniper, Zoho) reporta su estado

---

## 📋 PASOS PARA DIAGNOSTICAR

### PASO 1: Ejecutar script SQL
```bash
# Desde phpMyAdmin o consola MySQL:
SOURCE z:\htdocs\Facturacion\sql\add_numero_cuenta_to_alojamiento.sql;
```

**Resultado esperado**: "✅ Columna numero_cuenta agregada exitosamente"

---

### PASO 2: Verificar configuración PHP
Abrir en navegador:
```
http://localhost/Facturacion/proveedores/controlador/test_debug.php
```

**Verificar que muestre**:
- ✅ PHP Version: 8.x
- ✅ Sesión iniciada: SÍ
- ✅ Usuario sesión: [tu_usuario]
- ✅ Rol sesión: [tu_rol]
- ✅ Conexión a BD exitosa

**Si falla alguno**: Revisar sesión o conexión DB

---

### PASO 3: Probar aprobación CON DEBUG

#### Opción A: URL directa
```
http://localhost/Facturacion/proveedores/controlador/enviar_aprobacion.php?id=123&debug=1
```
(Reemplazar `123` con un ID de hotel válido en estado PENDIENTE)

#### Opción B: Modificar botón temporalmente
En `consultaHotel.php` línea 562, cambiar:
```php
href="../controlador/enviar_aprobacion.php?id=<?php echo $datos_hotel['id_hotel']; ?>"
```
Por:
```php
href="../controlador/enviar_aprobacion.php?id=<?php echo $datos_hotel['id_hotel']; ?>&debug=1"
```

---

### PASO 4: Leer output del debug

**Si DEBUG funciona**, verás:
```
========================================
DEBUG MODE ACTIVADO
========================================
Fecha: 2026-06-01 15:30:00
ID Hotel: 123
Usuario: tu_usuario

[PASO 1] Guardando aprobación en BD...
✅ Aprobación guardada. Filas afectadas: 1

[PASO 2] Preparando envío a Juniper...
Datos a enviar a Juniper:
{
  "nit": "860402288A",
  "nombre": "Hotel Test",
  "email": "test@hotel.com",
  "cuenta_bancaria": "1234567890",
  ...
}
[JUNIPER] ✅ Enviado. Hotel ID 123 → Juniper ID: XXXXX

[PASO 3] Enviando a Zoho CRM...
[ZOHO] ✅ Resultado: Enviado correctamente

========================================
PROCESO COMPLETADO EXITOSAMENTE
========================================
Hotel ID: 123
Juniper ID: XXXXX
Zoho CRM: ✅ Enviado
Estado final: APROBADO
```

**Si falla en algún paso**: El debug mostrará exactamente dónde y por qué

---

## 🔴 ERRORES COMUNES Y SOLUCIONES

### Error 1: "No se encontró el hotel"
**Causa**: ID inválido o no existe en BD
**Solución**: Verificar que el hotel existe: `SELECT * FROM tbl_alojamiento_general WHERE id_hotel = 123`

### Error 2: "Column 'numero_cuenta' not found"
**Causa**: No se ejecutó el script SQL
**Solución**: Ejecutar `add_numero_cuenta_to_alojamiento.sql`

### Error 3: "Error al enviar a Juniper: Connection timeout"
**Causa**: Servidor Juniper no responde o credenciales incorrectas
**Solución**: Verificar conectividad a `https://www.panamericanadeviajes.net/wsExportacion/wsSuppliers.asmx`

### Error 4: "Función construirYEnviarZoho() no existe"
**Causa**: No se cargó enviar_zoho.php
**Solución**: Verificar que existe el archivo y que se incluye correctamente

### Error 5: Página en blanco (sin output)
**Causa**: Error fatal PHP no mostrado
**Solución**: 
1. Revisar `logs/log_tiquetes.txt` o logs de Apache/PHP
2. Agregar al inicio de `enviar_aprobacion.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

---

## 🧪 VERIFICACIÓN FINAL

Después de aprobar un hotel, verificar en BD:

```sql
SELECT 
    id_hotel,
    nombre,
    estado_aprobacion,
    juniper_id,
    aprobado_por,
    fecha_aprobacion
FROM tbl_alojamiento_general
WHERE id_hotel = 123;
```

**Resultado esperado**:
- estado_aprobacion = 'APROBADO'
- juniper_id = 'XXXXX' (no NULL)
- aprobado_por = 'tu_usuario'
- fecha_aprobacion = fecha actual

---

## 📞 SI SIGUE SIN FUNCIONAR

1. Ejecutar test_debug.php y enviar output
2. Acceder con `&debug=1` y copiar TODO el output
3. Revisar logs de PHP en:
   - `C:\xampp\apache\logs\error.log` (XAMPP)
   - `C:\wamp\logs\php_error.log` (WAMP)
   - `logs/log_tiquetes.txt` (proyecto)

---

## 📁 ARCHIVOS MODIFICADOS

- ✅ `proveedores/controlador/enviar_aprobacion.php` - Debugging mejorado
- ✅ `proveedores/controlador/registrodatoshotel.php` - Captura numero_cuenta
- ✅ `sql/add_numero_cuenta_to_alojamiento.sql` - Agrega columna a BD
- ✅ `proveedores/controlador/test_debug.php` - Test de configuración

---

Última actualización: 2026-06-01
