# Script de Configuración SUNAT

Este script configura tu empresa con las credenciales de SUNAT para pruebas.

## Datos a Configurar

- **RUC:** 20557912879
- **Usuario SOL Completo:** 20557912879MODDATOS (RUC + MODDATOS)
- **Contraseña SOL:** MODDATOS
- **Ambiente:** BETA (pruebas)

## Paso 1: Configurar Empresa

Ejecuta en la terminal:

```bash
php artisan tinker
```

Luego copia y pega este código:

```php
// Obtener la primera empresa (o crear si no existe)
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

// Configurar credenciales SUNAT
$company->ruc = '20557912879';
$company->sol_username = '20557912879MODDATOS';  // RUC + MODDATOS
$company->sol_password = encrypt('MODDATOS');    // Encriptado
$company->save();

echo "✅ Empresa configurada:\n";
echo "   RUC: " . $company->ruc . "\n";
echo "   SOL User: " . $company->sol_username . "\n";
echo "   ID: " . $company->id . "\n";
```

## Paso 2: Configurar Certificado

**¿Dónde está tu certificado?** Asegúrate de que esté en `storage/app/certificates/`

```bash
# Crear carpeta si no existe
mkdir storage\app\certificates

# Copia tu certificado .pfx aquí
# Ejemplo: certificado_prueba.pfx
```

Luego en tinker:

```php
$company = App\Models\Company::first();

// Configurar certificado
$company->certificate_path = 'nombre_de_tu_certificado.pfx';  // 👈 Cambia esto
$company->certificate_password = 'password_del_certificado';   // 👈 Y esto
$company->save();

echo "✅ Certificado configurado\n";
```

## Paso 3: Verificar Configuración

```php
// Verificar datos
$company = App\Models\Company::first();

echo "\n📋 CONFIGURACIÓN ACTUAL:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "RUC: " . $company->ruc . "\n";
echo "Usuario SOL: " . $company->sol_username . "\n";
echo "Certificado: " . $company->certificate_path . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Probar certificado
try {
    $certService = new App\Services\CertificateService();
    $certService->loadCertificate($company);
    echo "✅ Certificado cargado correctamente\n";
} catch (Exception $e) {
    echo "❌ Error con certificado: " . $e->getMessage() . "\n";
}
```

## Paso 4: Verificar Ambiente

```php
// Verificar que estamos en ambiente BETA
echo "\n🌍 AMBIENTE:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "SUNAT ENV: " . config('sunat.environment') . "\n";
echo "URL Envío: " . config('sunat.urls.beta.send') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (config('sunat.environment') !== 'beta') {
    echo "⚠️ ADVERTENCIA: No estás en ambiente BETA\n";
}
```

## Paso 5: Generar XML de Prueba

```php
// Crear documento de prueba
$doc = App\Models\Document::factory()->create([
    'company_id' => $company->id,
    'document_type' => '01',  // Factura
    'series' => 'F001',
    'number' => 1,
]);

echo "\n📄 DOCUMENTO DE PRUEBA CREADO:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ID: " . $doc->id . "\n";
echo "Serie-Número: " . $doc->series . "-" . $doc->number . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Generar XML
$xmlService = new App\Services\Sunat\XmlGeneratorService();
$xml = $xmlService->generate($doc);

// Verificar ProfileID
if (str_contains($xml, '0101')) {
    echo "✅ ProfileID correcto (0101)\n";
} else {
    echo "❌ ProfileID incorrecto\n";
}

// Guardar para inspección
file_put_contents('test_sunat.xml', $xml);
echo "✅ XML guardado en: test_sunat.xml\n";
```

## Paso 6: Salir de Tinker

```php
exit
```

## Resumen de Comandos

```bash
# 1. Configurar empresa
php artisan tinker
# (copiar código del Paso 1)

# 2. Configurar certificado
# (copiar código del Paso 2)

# 3. Verificar todo
# (copiar código del Paso 3 y 4)

# 4. (Opcional) Generar XML de prueba
# (copiar código del Paso 5)

# 5. Salir
exit
```

---

**Siguiente:** Una vez configurado, podemos hacer el primer envío a SUNAT BETA! 🚀
