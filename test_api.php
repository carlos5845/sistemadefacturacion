<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = env('APIS_NET_PE_TOKEN');
$baseUrl = env('APIS_NET_PE_URL', 'https://api.decolecta.com');
$dni = '74777394'; // The DNI from the user's log

echo "Testing DNI Lookup for: $dni\n";
echo "Token: " . substr($token, 0, 5) . "...\n";
echo "URL: $baseUrl\n";

try {
    $response = Http::withOptions(['verify' => false])
        ->withToken($token)
        ->get("{$baseUrl}/v1/reniec/dni", [
            'numero' => $dni
        ]);

    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
