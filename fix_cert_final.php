<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dir = storage_path('app/certificates');
$files = glob($dir . '/*.pfx');

if (empty($files)) {
    echo "❌ No hay archivos .pfx en $dir\n";
    exit(1);
}

$source = $files[0];
$dest = $dir . '/test.pfx';

if (copy($source, $dest)) {
    echo "✅ Copiado " . basename($source) . " a test.pfx\n";
    
    $company = App\Models\Company::first();
    $company->certificate = 'test.pfx';
    $company->certificate_password = '12345678'; // Asumimos pass del demo
    $company->save();
    
    echo "✅ BD Actualizada con 'test.pfx'\n";
} else {
    echo "❌ Error al copiar archivo\n";
}
