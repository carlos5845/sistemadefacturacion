<?php
require __DIR__ . '/vendor/autoload.php';

$path = 'storage/app/certificates/test.pfx';
$passwords = [
    '246246',
    '12345678',
    '123456',
    'facturacion',
    'password',
    'admin',
    ''
];

echo "Probando " . count($passwords) . " contraseñas para $path...\n";

if (!file_exists($path)) {
    echo "❌ Archivo no existe\n";
    exit(1);
}

$p12 = file_get_contents($path);

foreach ($passwords as $pass) {
    echo "Probando '$pass'... ";
    if (openssl_pkcs12_read($p12, $certs, $pass)) {
        echo "✅ ¡ÉXITO! La contraseña es: '$pass'\n";
        
        // Actualizar DB si encontramos la clave
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $c = App\Models\Company::first();
        $c->certificate_password = $pass;
        $c->save();
        echo "✅ Base de datos actualizada.\n";
        exit(0);
    } else {
        echo "❌ Falló\n";
    }
}

echo "\n❌ Ninguna contraseña funcionó.\n";
