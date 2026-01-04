<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = env('APIS_NET_PE_TOKEN');
$baseUrl = env('APIS_NET_PE_URL');
$ruc = '20131312955'; // Example RUC (Sunat)

echo "Testing RUC Lookup for: $ruc\n";
echo "URL: $baseUrl\n";

try {
    $response = Http::withOptions(['verify' => false])
        ->withToken($token)
        ->get("{$baseUrl}/v1/sunat/ruc", [
            'numero' => $ruc
        ]);

    echo "Status: " . $response->status() . "\n";
    print_r($response->json());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
