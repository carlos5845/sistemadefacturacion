# Certificado de Prueba para Desarrollo

## ⚠️ IMPORTANTE
Este certificado es **SOLO PARA DESARROLLO/PRUEBAS**. NO debe usarse en producción.

## Generar Certificado de Prueba

Ejecuta el siguiente comando en la terminal:

```bash
php scripts/generate-test-certificate.php
```

O usa este comando directo:

```bash
php artisan tinker --execute="
\$config = ['digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
\$privateKey = openssl_pkey_new(\$config);
\$dn = ['countryName' => 'PE', 'stateOrProvinceName' => 'Lima', 'localityName' => 'Lima', 'organizationName' => 'Empresa de Prueba SUNAT', 'commonName' => 'test.sunat.local'];
\$csr = openssl_csr_new(\$dn, \$privateKey, \$config);
\$cert = openssl_csr_sign(\$csr, null, \$privateKey, 365, \$config, time());
openssl_x509_export(\$cert, \$certPem);
openssl_pkey_export(\$privateKey, \$privateKeyPem, 'test123');
\$fullPem = \$certPem . PHP_EOL . \$privateKeyPem;
file_put_contents(storage_path('app/test-certificate.pem'), \$fullPem);
echo 'Certificado generado en: ' . storage_path('app/test-certificate.pem');
"
```

## Información del Certificado de Prueba

- **Contraseña**: `test123`
- **Válido por**: 365 días
- **Uso**: Solo para desarrollo/pruebas locales
- **Formato**: PEM (incluye certificado y clave privada)

## Cómo Usar el Certificado

1. **Genera el certificado** usando uno de los comandos anteriores
2. **Lee el archivo** generado en `storage/app/test-certificate.pem`
3. **Copia todo el contenido** del archivo (incluyendo las líneas `-----BEGIN CERTIFICATE-----` y `-----END PRIVATE KEY-----`)
4. **Ve a Editar Empresa** en el sistema
5. **Pega el certificado** en el campo "Certificado Digital (PEM)"
6. **Ingresa la contraseña**: `test123`
7. **Guarda los cambios**

## Configurar Credenciales SOL

Para pruebas con SUNAT, también necesitas configurar:

- **Usuario SOL**: Tu usuario SOL de SUNAT (formato: `MODDATOS` o similar)
- **Contraseña SOL**: Tu contraseña SOL de SUNAT

**Nota**: Las credenciales SOL reales solo funcionan con el ambiente de producción de SUNAT. Para desarrollo, puedes usar valores de prueba.

## Verificar que el Certificado Funciona

Después de configurar el certificado:

1. Crea un documento
2. Haz clic en "Enviar a SUNAT"
3. El sistema debería:
   - Generar el XML
   - Firmar el XML con el certificado
   - Intentar enviarlo a SUNAT

Si hay errores, revisa los logs en `storage/logs/laravel.log`.


