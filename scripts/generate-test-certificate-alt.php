<?php

/**
 * Script alternativo para generar certificado de prueba
 * Usa OpenSSL directamente si está disponible en el sistema
 */

echo "Generando certificado de prueba...\n\n";

// Verificar si OpenSSL está disponible en el sistema
$opensslPath = null;
$possiblePaths = [
    'openssl',
    'C:\\OpenSSL-Win64\\bin\\openssl.exe',
    'C:\\OpenSSL-Win32\\bin\\openssl.exe',
];

foreach ($possiblePaths as $path) {
    $output = [];
    $returnVar = 0;
    @exec("{$path} version 2>&1", $output, $returnVar);
    if ($returnVar === 0) {
        $opensslPath = $path;
        break;
    }
}

if (!$opensslPath) {
    // Intentar con PHP OpenSSL
    echo "OpenSSL CLI no encontrado, usando PHP OpenSSL...\n";
    
    // Configuración mínima
    $config = [
        'digest_alg' => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    
    $privateKey = @openssl_pkey_new($config);
    if (!$privateKey) {
        die("Error: No se pudo generar la clave privada. Verifica que OpenSSL esté habilitado en PHP.\n");
    }
    
    $dn = [
        'countryName' => 'PE',
        'stateOrProvinceName' => 'Lima',
        'localityName' => 'Lima',
        'organizationName' => 'Empresa de Prueba SUNAT',
        'commonName' => 'test.sunat.local',
    ];
    
    $csr = @openssl_csr_new($dn, $privateKey, $config);
    if (!$csr) {
        die("Error al generar CSR\n");
    }
    
    $cert = @openssl_csr_sign($csr, null, $privateKey, 365);
    if (!$cert) {
        die("Error al firmar certificado\n");
    }
    
    openssl_x509_export($cert, $certPem);
    openssl_pkey_export($privateKey, $privateKeyPem, 'test123');
    
    $fullPem = $certPem . "\n" . $privateKeyPem;
} else {
    // Usar OpenSSL CLI
    echo "Usando OpenSSL CLI: {$opensslPath}\n";
    
    $storageDir = __DIR__ . '/../storage/app';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    
    $keyFile = $storageDir . '/temp-key.pem';
    $certFile = $storageDir . '/temp-cert.pem';
    $fullCertFile = $storageDir . '/test-certificate.pem';
    
    // Generar clave privada
    $cmd = "{$opensslPath} genrsa -aes256 -passout pass:test123 -out \"{$keyFile}\" 2048 2>&1";
    exec($cmd, $output, $returnVar);
    if ($returnVar !== 0) {
        die("Error al generar clave privada: " . implode("\n", $output) . "\n");
    }
    
    // Generar certificado
    $dn = "/C=PE/ST=Lima/L=Lima/O=Empresa de Prueba SUNAT/CN=test.sunat.local";
    $cmd = "{$opensslPath} req -new -x509 -key \"{$keyFile}\" -passin pass:test123 -days 365 -out \"{$certFile}\" -subj \"{$dn}\" 2>&1";
    exec($cmd, $output, $returnVar);
    if ($returnVar !== 0) {
        unlink($keyFile);
        die("Error al generar certificado: " . implode("\n", $output) . "\n");
    }
    
    // Convertir clave privada a formato sin contraseña para exportar
    $keyNoPassFile = $storageDir . '/temp-key-nopass.pem';
    $cmd = "{$opensslPath} rsa -in \"{$keyFile}\" -passin pass:test123 -out \"{$keyNoPassFile}\" 2>&1";
    exec($cmd, $output, $returnVar);
    
    // Leer archivos y combinar
    $certPem = file_get_contents($certFile);
    $keyPem = file_get_contents($keyNoPassFile);
    $fullPem = $certPem . "\n" . $keyPem;
    
    // Limpiar archivos temporales
    @unlink($keyFile);
    @unlink($certFile);
    @unlink($keyNoPassFile);
}

// Guardar certificado
$storageDir = __DIR__ . '/../storage/app';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

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

