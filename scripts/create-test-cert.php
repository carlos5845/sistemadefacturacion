<?php
/**
 * Script simplificado para crear certificado de prueba PFX/P12
 */

$dir = __DIR__ . '/../storage/app/certificates';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$pfxFile = $dir . '/test_certificate.p12';
$password = 'test123';

$config = [
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

$key = openssl_pkey_new($config);
if (!$key) {
    die("Error: No se pudo generar la clave privada\n");
}

$dn = [
    'countryName' => 'PE',
    'stateOrProvinceName' => 'Lima',
    'localityName' => 'Lima',
    'organizationName' => 'Empresa de Prueba SAC',
    'organizationalUnitName' => 'Facturacion Electronica',
    'commonName' => '20123456789',
    'emailAddress' => 'test@example.com',
];

$csr = openssl_csr_new($dn, $key, $config);
$cert = openssl_csr_sign($csr, null, $key, 365);

$pfx = '';
if (openssl_pkcs12_export($cert, $pfx, $key, $password)) {
    file_put_contents($pfxFile, $pfx);
    echo "Certificado creado: {$pfxFile}\n";
    echo "Contraseña: {$password}\n";
    echo "Tamaño: " . filesize($pfxFile) . " bytes\n";
} else {
    die("Error al crear PFX\n");
}

