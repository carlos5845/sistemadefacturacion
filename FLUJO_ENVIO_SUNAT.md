# Flujo Completo de Envío a SUNAT

## 📋 Resumen del Proceso

Cuando un usuario hace clic en el botón **"Enviar a SUNAT"** en la vista de detalle de un documento, se ejecuta el siguiente flujo:

---

## 🔄 Flujo Paso a Paso

### 1. **Inicio del Proceso** (Frontend)
- **Ubicación**: `resources/js/pages/Documents/Show.tsx`
- **Acción**: Usuario hace clic en el botón "Enviar a SUNAT"
- **Validación**: Se muestra un `confirm()` para confirmar la acción
- **Petición**: Se envía una petición POST a `/documents/{id}/send-to-sunat`

### 2. **Controlador - Verificación Inicial** (Backend)
- **Ubicación**: `app/Http/Controllers/DocumentController.php`
- **Método**: `sendToSunat(Document $document)`
- **Validaciones**:
  - ✅ Verifica autorización mediante `DocumentPolicy@sendToSunat`
  - ✅ Verifica que el documento esté en estado `PENDING`
- **Acción**: Despacha el job `SendDocumentToSunat` a la cola de trabajos
- **Respuesta**: Redirige a la vista del documento con mensaje de éxito

### 3. **Job Asíncrono - Procesamiento** (Cola de Trabajos)
- **Ubicación**: `app/Jobs/SendDocumentToSunat.php`
- **Proceso**:
  1. **Cambia estado** del documento a `SENT` (enviado)
  2. **Llama al servicio** `SunatApiService@send($document)`
  3. **Manejo de errores**:
     - Si hay error → vuelve el estado a `PENDING`
     - Registra logs de error
     - Lanza excepción para reintento

### 4. **Generación de XML** (Servicio)
- **Ubicación**: `app/Services/Sunat/XmlGeneratorService.php`
- **Estado**: ⚠️ **PENDIENTE DE IMPLEMENTACIÓN COMPLETA**
- **Proceso esperado**:
  1. Genera XML según formato UBL 2.1 de SUNAT
  2. Incluye datos del emisor (empresa)
  3. Incluye datos del cliente
  4. Incluye items con impuestos
  5. Genera hash SHA-256 del XML
  6. Firma XML con certificado digital de la empresa
  7. Almacena XML original y XML firmado en el documento

### 5. **Envío a SUNAT** (Servicio API)
- **Ubicación**: `app/Services/Sunat/SunatApiService.php`
- **Estado**: ⚠️ **PENDIENTE DE IMPLEMENTACIÓN COMPLETA**
- **Proceso esperado**:
  1. Construye petición SOAP a SUNAT
  2. Usa credenciales SOL (Usuario SOL y Contraseña SOL de la empresa)
  3. Envía XML firmado codificado en Base64
  4. Endpoints:
     - **Producción**: `https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService`
     - **Pruebas**: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService`
  5. Espera respuesta de SUNAT (timeout: 30 segundos)

### 6. **Procesamiento de Respuesta** (Servicio API)
- **Proceso**:
  1. Parsea respuesta SOAP de SUNAT
  2. Extrae CDR (Constancia de Recepción):
     - `cdr_zip`: Archivo ZIP con el CDR
     - `cdr_xml`: XML del CDR extraído
     - `sunat_code`: Código de respuesta SUNAT
     - `sunat_message`: Mensaje de SUNAT
  3. Almacena respuesta en tabla `sunat_responses`
  4. Actualiza estado del documento:
     - Si código es éxito (0) → `ACCEPTED`
     - Si código es error → `REJECTED`

### 7. **Visualización del Resultado** (Frontend)
- **Ubicación**: `resources/js/pages/Documents/Show.tsx`
- **Información mostrada**:
  - Estado actualizado del documento (ACCEPTED/REJECTED)
  - Código de respuesta SUNAT
  - Mensaje de SUNAT
  - CDR (si está disponible)

---

## 📊 Estados del Documento

| Estado | Descripción | Acciones Disponibles |
|--------|-------------|---------------------|
| `PENDING` | Documento creado, pendiente de envío | ✅ Editar<br>✅ Eliminar<br>✅ Enviar a SUNAT |
| `SENT` | Enviado a SUNAT, procesándose | ⏳ Esperando respuesta |
| `ACCEPTED` | Aceptado por SUNAT | ✅ Ver CDR<br>✅ Descargar XML |
| `REJECTED` | Rechazado por SUNAT | ⚠️ Ver motivo de rechazo<br>✅ Corregir y reenviar |
| `CANCELED` | Anulado manualmente | ❌ Sin acciones disponibles |

---

## 🔐 Seguridad y Autorización

### Política de Autorización
- **Ubicación**: `app/Policies/DocumentPolicy.php`
- **Método**: `sendToSunat(User $user, Document $document)`
- **Validaciones**:
  - Usuario debe pertenecer a la misma empresa que el documento
  - Documento debe estar en estado `PENDING`

### Credenciales SOL
- Se obtienen de la tabla `companies`:
  - `user_sol`: Usuario SOL de la empresa
  - `password_sol`: Contraseña SOL de la empresa
- Se usan para autenticación básica HTTP en la petición SOAP

---

## ⚠️ Pendientes de Implementación

### 1. Generación Completa de XML UBL 2.1
- **Estado**: Parcialmente implementado
- **Falta**: Estructura completa según especificación SUNAT
- **Referencia**: https://cpe.sunat.gob.pe/

### 2. Firma Digital del XML
- **Estado**: No implementado
- **Requisitos**:
  - Certificado digital de la empresa
  - Librería XMLSecLibs o similar
  - OpenSSL para firma

### 3. Comunicación SOAP con SUNAT
- **Estado**: Parcialmente implementado
- **Falta**: Construcción correcta del sobre SOAP
- **Referencia**: Documentación oficial de SUNAT

### 4. Procesamiento Completo del CDR
- **Estado**: Parcialmente implementado
- **Falta**: Extracción y parseo completo del CDR desde la respuesta SOAP

---

## 📝 Ejemplo de Flujo Completo

```
Usuario → Clic en "Enviar a SUNAT"
    ↓
DocumentController@sendToSunat
    ↓
Validaciones (autorización, estado PENDING)
    ↓
Despacha Job: SendDocumentToSunat
    ↓
Job cambia estado a SENT
    ↓
SunatApiService@send()
    ↓
XmlGeneratorService@generate() → Genera XML UBL 2.1
    ↓
XmlGeneratorService@sign() → Firma XML con certificado
    ↓
SunatApiService → Envía SOAP a SUNAT
    ↓
SUNAT procesa y responde
    ↓
SunatApiService@processResponse() → Procesa respuesta
    ↓
Actualiza estado (ACCEPTED/REJECTED)
    ↓
Almacena CDR en sunat_responses
    ↓
Usuario ve resultado en la vista del documento
```

---

## 🛠️ Configuración Necesaria

### Variables de Entorno
```env
QUEUE_CONNECTION=database  # o redis, sqs, etc.
```

### Ejecutar Cola de Trabajos
```bash
php artisan queue:work
```

### Configuración de SUNAT
```php
// config/services.php
'sunat' => [
    'endpoint' => env('SUNAT_ENDPOINT', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService'),
],
```

---

## 📚 Referencias

- [Documentación SUNAT CPE](https://cpe.sunat.gob.pe/)
- [Especificación UBL 2.1](https://www.oasis-open.org/standards#ublv2.1)
- [Laravel Queues](https://laravel.com/docs/queues)

