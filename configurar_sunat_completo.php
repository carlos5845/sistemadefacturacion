// ============================================
// CONFIGURACIÓN COMPLETA SUNAT
// RUC: 20557912879
// Usuario SOL: 20557912879MODDATOS
// Password SOL: MODDATOS
// Certificado: LLAMA-PE-CERTIFICADO-DEMO-20100066603
// ============================================

echo "🚀 INICIANDO CONFIGURACIÓN SUNAT...\n\n";

// PASO 1: Obtener o crear empresa
echo "📋 Paso 1: Configurando empresa...\n";
$company = App\Models\Company::first();

if (!$company) {
    echo "   ❌ No hay empresas. Creando una nueva...\n";
    $company = App\Models\Company::create([
        'ruc' => '20557912879',
        'business_name' => 'EMPRESA DE PRUEBA SAC',
        'trade_name' => 'PRUEBA',
        'address' => 'AV. PRUEBA 123',
        'phone' => '999999999',
        'email' => 'prueba@empresa.com',
    ]);
} else {
    echo "   ✅ Empresa encontrada (ID: {$company->id})\n";
}

// PASO 2: Configurar credenciales SUNAT
echo "\n🔐 Paso 2: Configurando credenciales SOL...\n";
$company->ruc = '20557912879';
$company->sol_username = '20557912879MODDATOS';  // RUC + MODDATOS
$company->sol_password = encrypt('MODDATOS');    // Encriptado
$company->save();
echo "   ✅ Credenciales SOL configuradas\n";

// PASO 3: Configurar certificado
echo "\n📜 Paso 3: Configurando certificado digital...\n";
$company->certificate_path = 'LLAMA-PE-CERTIFICADO-DEMO-20100066603';
$company->certificate_password = '12345678';
$company->save();
echo "   ✅ Certificado configurado\n";

// PASO 4: Verificar configuración
echo "\n✅ CONFIGURACIÓN COMPLETADA\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 Datos de Empresa:\n";
echo "   ID: " . $company->id . "\n";
echo "   RUC: " . $company->ruc . "\n";
echo "   Razón Social: " . $company->business_name . "\n";
echo "\n🔐 Credenciales SOL:\n";
echo "   Usuario: " . $company->sol_username . "\n";
echo "   Password: ******** (encriptado)\n";
echo "\n📜 Certificado Digital:\n";
echo "   Archivo: " . $company->certificate_path . "\n";
echo "   Password: ******** \n";
echo "\n🌍 Ambiente SUNAT:\n";
echo "   Ambiente: " . config('sunat.environment') . "\n";
echo "   URL Envío: " . config('sunat.urls.beta.send') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// PASO 5: Probar carga del certificado
echo "🧪 Paso 5: Probando carga del certificado...\n";
try {
    $certService = new App\Services\CertificateService();
    $certService->loadCertificate($company);
    echo "   ✅ Certificado cargado correctamente\n";
} catch (Exception $e) {
    echo "   ❌ Error al cargar certificado:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n⚠️  Verifica que el archivo existe en: storage/app/certificates/\n";
}

// PASO 6: Generar XML de prueba
echo "\n📄 Paso 6: Generando XML de prueba...\n";

// Verificar si hay un documento existente o crear uno
$testDoc = App\Models\Document::where('company_id', $company->id)
    ->where('series', 'F001')
    ->first();

if (!$testDoc) {
    echo "   Creando documento de prueba...\n";
    // Necesitamos un cliente
    $customer = App\Models\Customer::where('company_id', $company->id)->first();
    if (!$customer) {
        $customer = App\Models\Customer::create([
            'company_id' => $company->id,
            'identity_type' => 'RUC',
            'identity_number' => '20123456789',
            'name' => 'CLIENTE DE PRUEBA SAC',
            'address' => 'AV. CLIENTE 456',
        ]);
        echo "   ✅ Cliente de prueba creado\n";
    }
    
    $testDoc = App\Models\Document::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'document_type' => '01',
        'series' => 'F001',
        'number' => 1,
        'issue_date' => now(),
        'currency' => 'PEN',
        'total_taxed' => 100.00,
        'total_igv' => 18.00,
        'total' => 118.00,
        'status' => 'PENDING',
    ]);
    
    // Crear item
    App\Models\DocumentItem::create([
        'document_id' => $testDoc->id,
        'description' => 'PRODUCTO DE PRUEBA',
        'quantity' => 1,
        'unit_price' => 100.00,
        'total' => 118.00,
        'tax_type' => '10',
        'igv' => 18.00,
    ]);
    
    echo "   ✅ Documento de prueba creado (ID: {$testDoc->id})\n";
} else {
    echo "   ✅ Usando documento existente (ID: {$testDoc->id})\n";
}

// Generar XML
$xmlService = new App\Services\Sunat\XmlGeneratorService();
$xml = $xmlService->generate($testDoc);

// Verificar ProfileID
if (str_contains($xml, '0101')) {
    echo "   ✅ ProfileID correcto (0101 - Venta Interna)\n";
} else {
    echo "   ❌ ProfileID incorrecto\n";
}

// Guardar para inspección
file_put_contents('test_sunat.xml', $xml);
echo "   ✅ XML guardado en: test_sunat.xml\n";

// RESUMEN FINAL
echo "\n🎉 CONFIGURACIÓN COMPLETADA EXITOSAMENTE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n📝 PRÓXIMOS PASOS:\n";
echo "1. Salir de Tinker (exit)\n";
echo "2. Iniciar cola: php artisan queue:work\n";
echo "3. Ir a: http://localhost:8000/documents/{$testDoc->id}\n";
echo "4. Click en 'Enviar a SUNAT'\n";
echo "5. Esperar respuesta (10-30 segundos)\n";
echo "6. Verificar logs: tail -f storage/logs/sunat.log\n";
echo "\n✨ ¡Listo para enviar a SUNAT BETA!\n";
