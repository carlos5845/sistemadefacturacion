<?php
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\DocumentController;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::first();
if (!$user) {
    die("No user found.");
}

$controller = new DocumentController();
$request = new Request();
$request->merge(['document_type' => '03']); // Boleta
$request->setUserResolver(function () use ($user) { return $user; });

$response = $controller->getNextSeriesNumber($request);
echo "Type 03 (Boleta): " . $response->getContent() . "\n";

$request->merge(['document_type' => '01']); // Factura
$response = $controller->getNextSeriesNumber($request);
echo "Type 01 (Factura): " . $response->getContent() . "\n";
