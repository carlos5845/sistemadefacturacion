<?php

echo "🚀 CONFIGURANDO SUNAT...\n\n";

// Obtener o crear empresa
$company = App\Models\Company::first();

if (!$company) {
    echo "📋 Creando nueva empresa...\n";
    $company = App\Models\Company::create([
        'ruc' => '20557912879',
        'business_name' => 'EMPRESA DE PRUEBA SAC',
        'trade_name' => 'PRUEBA',
        'address' => 'AV. PRUEBA 123',
        'phone' => '999999999',
        'email' => 'prueba@empresa.com',
    ]);
} else {
    echo "📋 Usando empresa existente (ID: {$company->id})\n";
}

// Configurar credenciales SUNAT
$company->ruc = '20557912879';
$company->sol_username = '20557912879MODDATOS';
$company->sol_password = encrypt('MODDATOS');
$company->certificate_path = 'LLAMA-PE-CERTIFICADO-DEMO-20100066603.pfx';
$company->certificate_password = '12345678';
$company->save();

echo "\n✅ CONFIGURACIÓN COMPLETADA\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Empresa ID: " . $company->id . "\n";
echo "RUC: " . $company->ruc . "\n";
echo "SOL User: " . $company->sol_username . "\n";
echo "Certificado: " . $company->certificate_path . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Verificar certificado
echo "\n🧪 Verificando certificado...\n";
try {
    $certService = new App\Services\CertificateService();
    $certService->loadCertificate($company);
    echo "✅ Certificado válido\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✨ Listo!\n";
