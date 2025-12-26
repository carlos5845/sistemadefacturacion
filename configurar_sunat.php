// ============================================
// SCRIPT DE CONFIGURACIÓN SUNAT
// RUC: 20557912879
// Usuario SOL: MODDATOS
// ============================================

// PASO 1: Obtener o crear empresa
$company = App\Models\Company::first();

if (!$company) {
    echo "❌ No hay empresas. Creando una nueva...\n";
    $company = App\Models\Company::create([
        'ruc' => '20557912879',
        'business_name' => 'EMPRESA DE PRUEBA SAC',
        'trade_name' => 'PRUEBA',
        'address' => 'AV. PRUEBA 123',
        'phone' => '999999999',
        'email' => 'prueba@empresa.com',
    ]);
}

// PASO 2: Configurar credenciales SUNAT
$company->ruc = '20557912879';
$company->sol_username = '20557912879MODDATOS';  // RUC + MODDATOS
$company->sol_password = encrypt('MODDATOS');    // Encriptado
$company->save();

echo "✅ Empresa configurada:\n";
echo "   ID: " . $company->id . "\n";
echo "   RUC: " . $company->ruc . "\n";
echo "   SOL User: " . $company->sol_username . "\n\n";

// PASO 3: IMPORTANTE - Configurar certificado
// ⚠️ NECESITAS COMPLETAR ESTA PARTE:
// 
// 1. Copia tu certificado .pfx a: storage/app/certificates/
// 2. Luego descomentar y ajustar estas líneas:

// $company->certificate_path = 'tu_certificado.pfx';  // 👈 Nombre del archivo
// $company->certificate_password = 'tu_password';      // 👈 Password del certificado
// $company->save();
// echo "✅ Certificado configurado\n\n";

// PASO 4: Verificar configuración
echo "📋 CONFIGURACIÓN ACTUAL:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "RUC: " . $company->ruc . "\n";
echo "Usuario SOL: " . $company->sol_username . "\n";
echo "Ambiente: " . config('sunat.environment') . "\n";
echo "URL SUNAT: " . config('sunat.urls.beta.send') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 PRÓXIMOS PASOS:\n";
echo "1. Copiar tu certificado .pfx a storage/app/certificates/\n";
echo "2. Descomentar líneas del PASO 3 y agregar nombre y password\n";
echo "3. Ejecutar este script de nuevo\n";
echo "4. Verificar que el certificado cargue correctamente\n";
echo "5. Crear un documento de prueba y enviarlo a SUNAT\n";
