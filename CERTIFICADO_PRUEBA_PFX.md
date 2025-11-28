# Certificado de Prueba PFX/P12 para SUNAT

## ⚠️ IMPORTANTE

**Este certificado es SOLO para pruebas locales de desarrollo.**

**SUNAT NO aceptará documentos firmados con un certificado auto-firmado en producción.**

Para producción, necesitas un certificado emitido por una entidad certificadora autorizada por SUNAT:
- e-Certicámara
- Camerfirma  
- Otros proveedores autorizados

## Generar Certificado de Prueba

### Opción 1: Script PHP (Recomendado)

Ejecuta el siguiente comando desde la raíz del proyecto:

```bash
php scripts/generate-sunat-test-certificate.php
```

O el script simplificado:

```bash
php scripts/create-test-cert.php
```

### Opción 2: Usando OpenSSL directamente

Si tienes OpenSSL instalado en tu sistema:

```bash
# Crear clave privada
openssl genrsa -out private_key.pem 2048

# Crear solicitud de certificado
openssl req -new -key private_key.pem -out cert.csr -subj "/C=PE/ST=Lima/L=Lima/O=Empresa de Prueba SAC/OU=Facturacion Electronica/CN=20123456789/emailAddress=test@example.com"

# Crear certificado auto-firmado (válido por 1 año)
openssl x509 -req -days 365 -in cert.csr -signkey private_key.pem -out certificate.pem

# Crear archivo PFX/P12
openssl pkcs12 -export -out test_certificate.p12 -inkey private_key.pem -in certificate.pem -passout pass:test123
```

## Configurar el Certificado en el Sistema

1. **Genera el certificado** usando uno de los métodos anteriores
2. **Ubicación del archivo**: El certificado se guardará en `storage/app/certificates/test_certificate.p12`
3. **Contraseña por defecto**: `test123`

### Pasos para configurar:

1. Ve a **Empresas** → **Editar Empresa**
2. En el campo **"Certificado Digital PFX/P12"**, haz clic en **"Elegir archivo"**
3. Selecciona el archivo: `storage/app/certificates/test_certificate.p12`
4. En el campo **"Contraseña del Certificado"**, ingresa: `test123`
5. Haz clic en **"Guardar"**

## Verificar que el Certificado Funciona

Después de configurar el certificado:

1. Crea un documento de prueba
2. Haz clic en **"Enviar a SUNAT"**
3. El sistema debería:
   - Generar el XML
   - Firmar el XML con el certificado
   - Intentar enviarlo a SUNAT

**Nota**: En modo desarrollo, si hay problemas de SSL o el certificado no es válido para SUNAT, el sistema mostrará un mensaje informativo pero permitirá continuar con las pruebas locales.

## Información del Certificado de Prueba

- **RUC**: 20123456789 (de prueba)
- **Organización**: Empresa de Prueba SAC
- **Válido por**: 365 días
- **Formato**: PFX/P12
- **Contraseña**: test123
- **Algoritmo**: SHA-256
- **Tamaño de clave**: 2048 bits

## Solución de Problemas

### Error: "No se pudo leer el certificado PFX/P12"

- Verifica que la contraseña sea correcta: `test123`
- Asegúrate de que el archivo no esté corrupto
- Intenta regenerar el certificado

### Error: "La extensión OpenSSL no está habilitada"

- En `php.ini`, asegúrate de que `extension=openssl` esté descomentado
- Reinicia el servidor web/PHP-FPM

### El certificado no firma el XML

- Verifica que el certificado se haya guardado correctamente
- Revisa los logs en `storage/logs/laravel.log`
- Asegúrate de que el certificado tenga clave privada y certificado X509

## Para Producción

Cuando estés listo para producción:

1. **Contacta una entidad certificadora autorizada**:
   - Visita: https://www.sunat.gob.pe/exportacion/factura-electronica/
   - Busca proveedores de certificados digitales autorizados

2. **Solicita un certificado digital** para facturación electrónica

3. **Configura el certificado** en el sistema usando el mismo proceso

4. **Prueba en ambiente beta de SUNAT** antes de ir a producción

## Archivos Generados

Después de ejecutar el script, encontrarás:

- `storage/app/certificates/test_certificate.p12` - Archivo PFX/P12 (para subir al sistema)
- `storage/app/certificates/test_certificate.pem` - Certificado en formato PEM
- `storage/app/certificates/test_private_key.pem` - Clave privada (mantener segura)

**Importante**: Los archivos en `storage/app/certificates/` están en `.gitignore` y no se subirán al repositorio por seguridad.

