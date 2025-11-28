<?php

/**
 * Script para generar un certificado de prueba (self-signed) para desarrollo
 * 
 * Este certificado NO debe usarse en producción.
 * Solo es válido para pruebas locales de firma digital.
 * 
 * Uso:
 * php scripts/generate-test-certificate.php
 */

// Configuración simplificada para evitar problemas en Windows
// No usamos 'x509_extensions' ni 'config' que pueden causar problemas
$config = [
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

// Crear clave privada con manejo de errores mejorado
$privateKey = @openssl_pkey_new($config);
if (!$privateKey) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }

    $errorMsg = !empty($errors) ? implode("\n", $errors) : 'Error desconocido';
    die("Error al generar la clave privada:\n{$errorMsg}\n\nSugerencia: Verifica que la extensión OpenSSL esté habilitada en PHP.\n");
}

// Crear certificado
$dn = [
    'countryName' => 'PE',
    'stateOrProvinceName' => 'Lima',
    'localityName' => 'Lima',
    'organizationName' => 'Empresa de Prueba SUNAT',
    'organizationalUnitName' => 'Sistemas',
    'commonName' => 'test.sunat.local',
    'emailAddress' => 'test@sunat.local',
];

$csr = @openssl_csr_new($dn, $privateKey, $config);
if (!$csr) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    $errorMsg = !empty($errors) ? implode("\n", $errors) : 'Error desconocido';
    die("Error al generar el CSR:\n{$errorMsg}\n");
}

// Firmar el certificado (self-signed) - sin configuración adicional
$cert = @openssl_csr_sign($csr, null, $privateKey, 365, null, time());

if (!$cert) {
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    $errorMsg = !empty($errors) ? implode("\n", $errors) : 'Error desconocido';
    die("Error al firmar el certificado:\n{$errorMsg}\n");
}

// Exportar certificado y clave privada
openssl_x509_export($cert, $certPem);
openssl_pkey_export($privateKey, $privateKeyPem, 'test123'); // Contraseña: test123

// Combinar certificado y clave privada en formato PEM
$fullPem = $certPem . "\n" . $privateKeyPem;

// Crear directorio si no existe
$storageDir = __DIR__ . '/../storage/app';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

// Guardar en archivo
$certFile = $storageDir . '/test-certificate.pem';
file_put_contents($certFile, $fullPem);

echo "✓ Certificado de prueba generado exitosamente!\n\n";
echo "Archivo guardado en: {$certFile}\n\n";
echo "=== CERTIFICADO (PEM) ===\n";
echo $fullPem;
echo "\n\n=== INFORMACIÓN ===\n";
echo "Contraseña del certificado: test123\n";
echo "Válido por: 365 días\n";
echo "Uso: Solo para desarrollo/pruebas\n\n";
echo "=== INSTRUCCIONES ===\n";
echo "1. Copia el contenido del certificado (entre las líneas === CERTIFICADO ===)\n";
echo "2. Ve a Editar Empresa en el sistema\n";
echo "3. Pega el certificado en el campo 'Certificado Digital (PEM)'\n";
echo "4. Ingresa la contraseña: test123\n";
echo "5. Guarda los cambios\n\n";
