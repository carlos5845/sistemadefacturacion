<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$taxes = App\Models\CatalogTaxType::all();
if ($taxes->isEmpty()) {
    echo "No hay tipos de impuestos en catalogo.\n";
    // Si está vacío, quizás necesitamos correr los seeders o insertar uno manual
    try {
        App\Models\CatalogTaxType::create(['code'=>'1000', 'name'=>'IGV', 'un_ece_code'=>'VAT']);
        echo "Creado IGV 1000\n";
    } catch (\Exception $e) { echo "Error creando: ".$e->getMessage(); }
} else {
    foreach ($taxes as $t) {
        echo "Code: " . $t->code . " - Name: " . $t->name . "\n";
    }
}
