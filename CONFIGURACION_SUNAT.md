# 🔐 Guía Rápida: Configurar SUNAT para Pruebas

## 📋 Requisitos Previos

Antes de empezar, necesitas obtener de SUNAT:

1. **Certificado Digital de Prueba** (.pfx o .p12)
    - Descargar desde: https://www.sunat.gob.pe
    - Sección: Facturación Electrónica → Ambiente de Pruebas

2. **Credenciales SOL de Prueba**
    - Usuario: `{TU_RUC}MODDATOS`
    - Password: Proporcionado por SUNAT

3. **RUC de Prueba**
    - SUNAT proporciona RUCs de prueba
    - Ejemplo: `20123456789`

---

## ⚡ Configuración Rápida (5 pasos)

### **Paso 1: Configurar Variables de Entorno**

Abre tu archivo `.env` y agrega/modifica:

```env
# Ambiente SUNAT
SUNAT_ENV=beta
SUNAT_LOG_LEVEL=debug

# Timeouts
SUNAT_TIMEOUT=60
SUNAT_RETRY_ATTEMPTS=3
```

### **Paso 2: Copiar Certificado**

```bash
# Crear carpeta si no existe
mkdir storage\app\certificates

# Copiar tu certificado .pfx aquí
# Ejemplo: storage\app\certificates\certificado_prueba.pfx
```

### **Paso 3: Configurar Empresa en la Base de Datos**

Opción A - **Desde Tinker (Recomendado):**

```bash
php artisan tinker
```

```php
// Obtener/crear empresa
$company = App\Models\Company::first(); // o ::create([...])

// Configurar datos SUNAT
$company->ruc = '20123456789';  // Tu RUC de prueba
$company->business_name = 'EMPRESA DE PRUEBA SAC';
$company->sol_username = '20123456789MODDATOS';  // RUC + MODDATOS
$company->sol_password = encrypt('tu_password_aqui');  // Contraseña SOL
$company->certificate_path = 'certificado_prueba.pfx';  // Nombre del archivo
$company->certificate_password = 'password_del_certificado';
$company->save();

echo "✅ Empresa configurada\n";
```

Opción B - **Desde la UI:**

1. Ir a `/companies`
2. Editar tu empresa
3. Llenar los campos de SUNAT

### **Paso 4: Verificar Configuración**

```bash
php artisan tinker
```

```php
// Test rápido
$company = App\Models\Company::first();

// Verificar datos
echo "RUC: " . $company->ruc . "\n";
echo "SOL User: " . $company->sol_username . "\n";
echo "Certificado: " . $company->certificate_path . "\n";

// Probar carga de certificado
$certService = new App\Services\CertificateService();
try {
    $certService->loadCertificate($company);
    echo "✅ Certificado válido\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

### **Paso 5: Crear y Enviar Documento de Prueba**

**Desde la UI:**

1. Iniciar cola de trabajos:

    ```bash
    php artisan queue:work
    ```

2. Abrir navegador: `http://localhost:8000/documents/create`

3. Crear factura de prueba:
    - **Tipo:** Factura (01)
    - **Serie:** F001
    - **Cliente:** Uno con RUC
    - **Items:** 1 producto/servicio
    - Precio: 100.00

4. Guardar documento

5. Click **"Enviar a SUNAT"**

6. Esperar 10-30 segundos

7. Verificar resultado:
    - ✅ Estado: ACCEPTED
    - ✅ CDR disponible
    - ✅ Sin errores

---

## 🧪 Tests de Verificación

### Test 1: Ambiente Configurado

```bash
php artisan tinker
```

```php
config('sunat.environment');  // Debe retornar: "beta"
config('sunat.urls.beta.send');  // URL de SUNAT BETA
```

### Test 2: XML Generado Correctamente

```bash
php artisan tinker
```

```php
$doc = App\Models\Document::latest()->first();
$xmlService = new App\Services\Sunat\XmlGeneratorService();
$xml = $xmlService->generate($doc);

// Verificar ProfileID correcto
str_contains($xml, '0101') ? "✅ OK" : "❌ ERROR";

// Guardar para inspección
file_put_contents('test.xml', $xml);
echo "Guardado en test.xml\n";
```

### Test 3: Ver Logs en Tiempo Real

```bash
# Terminal 1: Cola de trabajos
php artisan queue:work

# Terminal 2: Ver logs de SUNAT
Get-Content storage\logs\sunat.log -Wait -Tail 20
```

---

## 📊 Estructura de Datos Esperada

### Tabla `companies`:

| Campo                  | Ejemplo                 | Descripción                          |
| ---------------------- | ----------------------- | ------------------------------------ |
| `ruc`                  | `20123456789`           | RUC de prueba SUNAT                  |
| `business_name`        | `EMPRESA DE PRUEBA SAC` | Razón social                         |
| `sol_username`         | `20123456789MODDATOS`   | RUC +Usuario SOL                     |
| `sol_password`         | `encrypt('password')`   | Password encriptado                  |
| `certificate_path`     | `cert_prueba.pfx`       | Archivo en storage/app/certificates/ |
| `certificate_password` | `123456`                | Password del certificado             |

---

## ⚠️ Errores Comunes y Soluciones

### Error: "Certificado inválido"

**Solución:**

```bash
# Verificar que el archivo existe
dir storage\app\certificates

# Verificar permisos
# El archivo debe ser legible por el servidor web
```

### Error: "Credenciales SOL incorrectas"

**Verificar:**

- Formato: `{RUC}{USUARIO_SOL}` (sin espacios)
- Ejemplo correcto: `20123456789MODDATOS`
- Ejemplo incorrecto: `20123456789 MODDATOS` ❌

### Error: "No se puede conectar a SUNAT"

**Posibles causas:**

1. SUNAT BETA en mantenimiento
2. Firewall bloqueando conexión
3. URL incorrecta

**Verificar:**

```bash
# Probar conexión
curl https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService
```

### Error 3205: "Debe consignar tipo de operación"

**Solución:** ✅ Ya resuelto con nuestro fix

- El ProfileID ahora es "0101" (correcto)
- El servicio `XmlGeneratorService.php` deficiente fue eliminado

---

## 📝format: Formato de Credenciales

### Usuario SOL:

```
Formato: {RUC}{USUARIO_SOL}
Ejemplo: 20123456789MODDATOS
         └─────┬─────┘└──┬───┘
              RUC      Usuario
```

### Archivo de Certificado:

```
Ubicación: storage/app/certificates/mi_cert.pfx
En BD:     certificate_path = "mi_cert.pfx"
           certificate_password = "password123"
```

---

## 🚀 Próximos Pasos

Una vez configurado:

1. **Crear documentos de prueba** con diferentes escenarios:
    - Facturas con IGV
    - Boletas sin cliente
    - Notas de crédito/débito

2. **Validar respuestas de SUNAT:**
    - CDR debe descargarse
    - Estado debe ser "ACCEPTED"
    - Hash debe coincidir

3. **Monitorear logs:**
    - No debe haber errores críticos
    - Tiempos de respuesta < 30s

4. **Cuando esté todo OK:**
    - Cambiar `SUNAT_ENV=production`
    - Usar certificado de producción
    - Configurar credenciales SOL de producción

---

## 💡 Consejos

✅ **Hacer:**

- Probar primero en BETA
- Guardar logs de todas las pruebas
- Validar XML antes de enviar
- Revisar CDR recibidos

❌ **No hacer:**

- Usar certificados de producción en BETA
- Commitear `.env` al repositorio
- Commitear certificados al repositorio
- Enviar documentos reales a BETA

---

## 📞 Ayuda

**Archivos de log:**

```bash
storage/logs/sunat.log      # Logs de SUNAT
storage/logs/laravel.log    # Logs generales
```

**Comandos útiles:**

```bash
php artisan queue:work       # Iniciar cola
php artisan queue:restart    # Reiniciar cola
php artisan tinker          # Consola interactiva
```

**URLs de SUNAT:**

- Portal: https://www.sunat.gob.pe
- SOL: https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm
- Documentación: https://cpe.sunat.gob.pe

---

**¿Listo para configurar?** Sigue los 5 pasos y estarás enviando documentos a SUNAT en minutos! 🎉
