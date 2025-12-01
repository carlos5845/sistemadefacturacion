# Instrucciones para Generar y Usar Certificado de Prueba PFX/P12

## ⚠️ ADVERTENCIA IMPORTANTE

**Este certificado es SOLO para pruebas locales de desarrollo.**

**SUNAT NO aceptará documentos firmados con un certificado auto-firmado en producción real.**

Para producción, necesitas un certificado emitido por una entidad certificadora autorizada por SUNAT.

---

## Paso 1: Generar el Certificado

Abre una terminal en la raíz del proyecto y ejecuta:

```bash
php scripts/generate-sunat-test-certificate.php
```

O el script simplificado:

```bash
php scripts/create-test-cert.php
```

### Si el script funciona correctamente, verás:

```
=== Generador de Certificado de Prueba PFX/P12 ===

✓ Extensión OpenSSL detectada
✓ Directorio creado: storage/app/certificates
✓ Clave privada guardada
✓ Solicitud de certificado creada
✓ Certificado guardado
✓ Archivo PFX/P12 creado: storage/app/certificates/test_certificate.p12
✓ Certificado verificado correctamente

=== Información del Certificado ===
RUC (CN): 20123456789
Organización: Empresa de Prueba SAC
Válido desde: 2025-01-XX XX:XX:XX
Válido hasta: 2026-01-XX XX:XX:XX

=== Instrucciones de Uso ===
1. El archivo PFX/P12 está en: storage/app/certificates/test_certificate.p12
2. Contraseña del certificado: test123
```

---

## Paso 2: Configurar el Certificado en el Sistema

1. **Inicia sesión** en el sistema
2. Ve a **Empresas** → Selecciona tu empresa → **Editar**
3. Desplázate hasta la sección **"Certificado Digital PFX/P12"**
4. Haz clic en **"Elegir archivo"** o **"Browse"**
5. Navega a: `storage/app/certificates/test_certificate.p12`
6. Selecciona el archivo
7. En el campo **"Contraseña del Certificado"**, ingresa: `test123`
8. Haz clic en **"Guardar"**

El sistema validará automáticamente que el certificado sea válido antes de guardarlo.

---

## Paso 3: Probar la Firma de Documentos

1. **Crea un documento** de prueba (factura, boleta, etc.)
2. Ve a la página de **detalles del documento**
3. Haz clic en **"Enviar a SUNAT"**
4. El sistema debería:
   - Generar el XML UBL 2.1
   - Firmar el XML con el certificado
   - Intentar enviarlo a SUNAT

### Verificar que el XML está firmado:

1. En la página de detalles del documento, busca la sección **"XML"**
2. Haz clic en **"Ver XML Firmado"**
3. Deberías ver un elemento `<ds:Signature>` dentro del XML, lo que indica que está firmado

---

## Información del Certificado de Prueba

- **Archivo**: `storage/app/certificates/test_certificate.p12`
- **Contraseña**: `test123`
- **RUC**: 20123456789 (de prueba)
- **Organización**: Empresa de Prueba SAC
- **Válido por**: 365 días
- **Formato**: PFX/P12 (PKCS#12)
- **Algoritmo**: SHA-256
- **Tamaño de clave**: 2048 bits

---

## Solución de Problemas

### Error: "No se pudo leer el certificado PFX/P12"

**Causas posibles:**
- Contraseña incorrecta (debe ser exactamente `test123`)
- Archivo corrupto
- El certificado no se generó correctamente

**Solución:**
1. Regenera el certificado ejecutando el script nuevamente
2. Verifica que el archivo existe: `storage/app/certificates/test_certificate.p12`
3. Asegúrate de usar la contraseña correcta: `test123`

### Error: "La extensión OpenSSL no está habilitada"

**Solución:**
1. Abre tu archivo `php.ini`
2. Busca la línea: `;extension=openssl`
3. Quita el punto y coma: `extension=openssl`
4. Guarda el archivo
5. Reinicia tu servidor web (Apache/Nginx) o PHP-FPM

### El certificado no firma el XML

**Verifica:**
1. Que el certificado esté configurado en la empresa
2. Los logs en `storage/logs/laravel.log` para ver errores específicos
3. Que el certificado tenga tanto clave privada como certificado X509

---

## Para Producción Real con SUNAT

Cuando estés listo para producción:

### 1. Obtener Certificado Válido

Contacta con una entidad certificadora autorizada por SUNAT:

- **e-Certicámara**: https://www.ecerticamara.com.pe/
- **Camerfirma**: https://www.camerfirma.com/
- **Otros proveedores autorizados**: Consulta en https://www.sunat.gob.pe/

### 2. Proceso de Obtención

1. Solicita un certificado digital para facturación electrónica
2. Proporciona la documentación requerida (RUC, documentos de la empresa)
3. Recibirás el certificado en formato PFX/P12 con su contraseña
4. El certificado será válido por 1-2 años

### 3. Configurar en el Sistema

Usa el mismo proceso que con el certificado de prueba:
1. Ve a Editar Empresa
2. Sube el archivo PFX/P12 real
3. Ingresa la contraseña proporcionada por la entidad certificadora
4. Guarda los cambios

### 4. Probar en Ambiente Beta

Antes de ir a producción:
1. Configura `SUNAT_ENVIRONMENT=beta` en tu `.env`
2. Prueba enviando documentos de prueba
3. Verifica que SUNAT acepte los documentos
4. Una vez validado, cambia a `SUNAT_ENVIRONMENT=production`

---

## Archivos Generados

Después de ejecutar el script, encontrarás:

```
storage/app/certificates/
├── test_certificate.p12          ← Usar este archivo en el sistema
├── test_certificate.pem          ← Certificado en formato PEM (alternativo)
└── test_private_key.pem          ← Clave privada (mantener segura)
```

**Importante**: Los archivos en `storage/app/certificates/` están en `.gitignore` y no se subirán al repositorio por seguridad.

---

## Comandos Rápidos

```bash
# Generar certificado
php scripts/generate-sunat-test-certificate.php

# Verificar que el archivo existe
ls -lh storage/app/certificates/test_certificate.p12

# Ver información del certificado (si tienes OpenSSL instalado)
openssl pkcs12 -info -in storage/app/certificates/test_certificate.p12 -nodes
```

---

## Notas Adicionales

- El certificado de prueba es válido por **365 días**
- Puedes regenerar el certificado en cualquier momento ejecutando el script nuevamente
- El certificado de prueba **NO funcionará** con SUNAT real, solo para pruebas locales
- Para desarrollo, puedes dejar el certificado vacío y el sistema funcionará en modo simulación

---

¿Necesitas ayuda? Revisa los logs en `storage/logs/laravel.log` para más detalles sobre cualquier error.


