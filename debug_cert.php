<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use Illuminate\Support\Facades\Storage;

use RobRichards\XMLSecLibs\XMLSecurityKey;

$companies = Company::all();

foreach ($companies as $company) {
    echo "------------------------------------------------\n";
    echo "Company ID: " . $company->id . "\n";
    
    $certPath = $company->certificate;
    $password = $company->certificate_password;
    
    if (empty($certPath) || !file_exists($certPath)) {
        echo "Certificate file not found.\n";
        continue;
    }

    $pkcs12Content = file_get_contents($certPath);
    $certs = [];
    if (openssl_pkcs12_read($pkcs12Content, $certs, $password)) {
        echo "PKCS12 Read Success.\n";
        
        // Simular limpieza
        $certData = $certs['cert'];
        if (preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $certData, $matches)) {
            $certData = $matches[0];
        }
        
        echo "Testing XMLSecurityKey with cleaned cert...\n";
        
        try {
            // Test 1: Load as private key (what XmlGeneratorService does)
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            $objKey->loadKey($certs['pkey'], false, true);
            echo "Private Key Load Success.\n";
            
            // Test 2: Load as public key (might be happening internally)
            $objKeyPublic = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            // XMLSecurityKey expects PEM for public key
            $objKeyPublic->loadKey($certData, false, true); 
            echo "Public Key (Cert) Load Success.\n";

        } catch (\Exception $e) {
            echo "XMLSecurityKey Error: " . $e->getMessage() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n";
        }
        
    } else {
        echo "PKCS12 Read Failed.\n";
    }
}
