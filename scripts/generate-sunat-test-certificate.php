<?php

// CONFIGURACIÓN DE OPENSSL PARA WINDOWS
// Intentar detectar la configuración de OpenSSL automáticamente
$possiblePaths = [
    'C:/xampp/php/extras/openssl/openssl.cnf',
    'C:/php/extras/ssl/openssl.cnf',
    'C:/Program Files/Git/usr/ssl/openssl.cnf',
    'C:/Program Files/Git/mingw64/ssl/openssl.cnf',
    dirname(PHP_BINARY) . '/extras/openssl/openssl.cnf',
    dirname(PHP_BINARY) . '/ssl/openssl.cnf',
];

$opensslConfPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $opensslConfPath = $path;
        break;
    }
}

if ($opensslConfPath) {
    putenv("OPENSSL_CONF=" . str_replace('\\', '/', $opensslConfPath));
    echo "ℹ️  Usando configuración OpenSSL: {$opensslConfPath}\n\n";
} else {
    // Si no se encuentra el archivo, intentar sin configuración explícita
    echo "⚠️  Archivo openssl.cnf no encontrado, intentando sin configuración explícita...\n\n";
}

/**
 * Script para generar un certificado de prueba PFX/P12 para desarrollo
 * 
 * IMPORTANTE: Este certificado NO será aceptado por SUNAT en producción.
 * Solo es útil para pruebas locales de la funcionalidad de firma XML.
 * 
 * Para obtener un certificado válido para SUNAT:
 * 1. Contacte con una entidad certificadora autorizada por SUNAT:
 *    - e-Certicámara
 *    - Camerfirma
 *    - Otros proveedores autorizados
 * 2. Solicite un certificado digital para facturación electrónica
 * 3. Configure el certificado en el sistema
 */

$certificatesDir = __DIR__ . '/../storage/app/certificates';
$privateKeyFile = $certificatesDir . '/test_private_key.pem';
$certificateFile = $certificatesDir . '/test_certificate.pem';
$pfxFile = $certificatesDir . '/test_certificate.p12';
$password = 'test123'; // Contraseña de prueba

// Crear directorio si no existe
if (!is_dir($certificatesDir)) {
    mkdir($certificatesDir, 0755, true);
    echo "✓ Directorio creado: {$certificatesDir}\n";
}

echo "=== Generador de Certificado de Prueba PFX/P12 ===\n\n";
echo "Este script genera un certificado auto-firmado para pruebas locales.\n";
echo "⚠️  ADVERTENCIA: Este certificado NO será aceptado por SUNAT en producción.\n\n";

// Verificar extensión OpenSSL
if (!extension_loaded('openssl')) {
    die("❌ Error: La extensión OpenSSL de PHP no está habilitada.\n");
}

echo "✓ Extensión OpenSSL detectada\n";

// Configuración para el certificado
$config = [
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'encrypt_key' => false,
    'config' => $opensslConfPath ?: null, // Usar el archivo de configuración si existe
];

// Generar clave privada
echo "\n1. Generando clave privada...\n";
$privateKeyResource = openssl_pkey_new($config);

if ($privateKeyResource === false) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    die("❌ Error al generar la clave privada:\n" . implode("\n", $errors) . "\n");
}

// Exportar clave privada
if (!openssl_pkey_export($privateKeyResource, $privateKey, null, $config)) {
    die("❌ Error al exportar la clave privada\n");
}

file_put_contents($privateKeyFile, $privateKey);
echo "✓ Clave privada guardada: {$privateKeyFile}\n";

// Crear solicitud de certificado (CSR)
echo "\n2. Creando solicitud de certificado (CSR)...\n";
$dn = [
    'countryName' => 'PE',
    'stateOrProvinceName' => 'Lima',
    'localityName' => 'Lima',
    'organizationName' => 'Empresa de Prueba SAC',
    'organizationalUnitName' => 'Facturación Electrónica',
    'commonName' => '20123456789', // RUC de prueba
    'emailAddress' => 'test@example.com',
];

$csrResource = openssl_csr_new($dn, $privateKeyResource, $config);

if ($csrResource === false) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    die("❌ Error al crear la solicitud de certificado:\n" . implode("\n", $errors) . "\n");
}

echo "✓ Solicitud de certificado creada\n";

// Generar certificado auto-firmado (válido por 1 año)
echo "\n3. Generando certificado auto-firmado...\n";
$certificateResource = openssl_csr_sign(
    $csrResource,
    null, // Sin certificado CA (auto-firmado)
    $privateKeyResource,
    365, // Válido por 1 año
    $config,
    0 // Número de serie
);

if ($certificateResource === false) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    die("❌ Error al firmar el certificado:\n" . implode("\n", $errors) . "\n");
}

// Exportar certificado
if (!openssl_x509_export($certificateResource, $certificate)) {
    die("❌ Error al exportar el certificado\n");
}

file_put_contents($certificateFile, $certificate);
echo "✓ Certificado guardado: {$certificateFile}\n";

// Crear archivo PFX/P12
echo "\n4. Creando archivo PFX/P12...\n";
$pfxData = '';
if (!openssl_pkcs12_export(
    $certificateResource,
    $pfxData,
    $privateKeyResource,
    $password
)) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    die("❌ Error al crear el archivo PFX/P12:\n" . implode("\n", $errors) . "\n");
}

file_put_contents($pfxFile, $pfxData);
echo "✓ Archivo PFX/P12 creado: {$pfxFile}\n";

// Verificar que el certificado se puede leer
echo "\n5. Verificando certificado PFX/P12...\n";
$certs = [];
if (openssl_pkcs12_read($pfxData, $certs, $password)) {
    echo "✓ Certificado verificado correctamente\n";
    echo "  - Clave privada: " . (isset($certs['pkey']) ? '✓' : '✗') . "\n";
    echo "  - Certificado X509: " . (isset($certs['cert']) ? '✓' : '✗') . "\n";
} else {
    echo "⚠️  Advertencia: No se pudo verificar el certificado\n";
}

// Obtener información del certificado
$certInfo = openssl_x509_parse($certificateResource);
echo "\n=== Información del Certificado ===\n";
echo "RUC (CN): " . ($certInfo['subject']['CN'] ?? 'N/A') . "\n";
echo "Organización: " . ($certInfo['subject']['O'] ?? 'N/A') . "\n";
echo "Válido desde: " . date('Y-m-d H:i:s', $certInfo['validFrom_time_t']) . "\n";
echo "Válido hasta: " . date('Y-m-d H:i:s', $certInfo['validTo_time_t']) . "\n";

echo "\n=== Instrucciones de Uso ===\n";
echo "1. El archivo PFX/P12 está en: {$pfxFile}\n";
echo "2. Contraseña del certificado: {$password}\n";
echo "3. Para usar en el sistema:\n";
echo "   - Vaya a Editar Empresa\n";
echo "   - Suba el archivo: {$pfxFile}\n";
echo "   - Ingrese la contraseña: {$password}\n";
echo "   - Guarde los cambios\n";
echo "\n";
echo "⚠️  IMPORTANTE:\n";
echo "- Este certificado es solo para pruebas locales\n";
echo "- SUNAT NO aceptará documentos firmados con este certificado\n";
echo "- Para producción, necesita un certificado de una entidad autorizada\n";
echo "\n";
echo "✓ Certificado de prueba generado exitosamente\n";
