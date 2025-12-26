<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = App\Models\Company::first();
echo "Certificado en BD: '" . $company->certificate . "'\n";

$path = storage_path('app/certificates/' . $company->certificate);
echo "Ruta esperada: " . $path . "\n";

if (file_exists($path)) {
    echo "✅ Archivo existe.\n";
} else {
    echo "❌ Archivo NO existe.\n";
}
