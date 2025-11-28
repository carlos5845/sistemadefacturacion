# Verificación de Requisitos SUNAT BETA

## Estado de Cumplimiento

### ✅ 1. Certificado Digital Válido

**Requisito:**
- Formato: .pfx / .p12 ✅
- Longitud mínima de clave: 2048 bits ✅
- Contraseña obligatoria ✅
- Contiene certificado + llave privada ✅

**Estado:** ✅ **CUMPLE**
- El sistema acepta archivos PFX/P12
- Se valida la contraseña antes de guardar
- Se extrae certificado y clave privada correctamente

**Nota:** ⚠️ Los certificados self-signed NO son aceptados por SUNAT. Se necesita certificado de pruebas válido.

---

### ❌ 2. Firma Digital XAdES-BES

**Requisito:**
- Firma XAdES-BES (NO XML-DSIG simple)
- Para facturas, boletas, notas de crédito y débito

**Estado:** ❌ **NO CUMPLE**

**Problema detectado:**
El código actual usa `XMLSecurityDSig` que genera firma XML-DSIG simple, NO XAdES-BES.

```php
// Código actual (XML-DSIG simple)
$objDSig = new XMLSecurityDSig();
$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
$objDSig->addReferenceList([$rootNode], XMLSecurityDSig::SHA256, ...);
```

**Solución requerida:**
- Implementar firma XAdES-BES con `QualifyingProperties` y `SignedProperties`
- Incluir información del certificado X509 en formato XAdES
- Agregar elementos `xades:QualifyingProperties` y `xades:SignedProperties`

---

### ✅ 3. Estructura UBL Correcta

**Requisito:**
- UBLVersionID: 2.1 ✅
- CustomizationID: 2.0 ✅
- Tipos de documento: 01, 03, 07, 08 ✅
- Series correctas: F001-F999, B001-B999, FC01, FD01, BC01, BD01 ⚠️
- Número correlativo sin saltos ⚠️

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ UBLVersionID: 2.1
- ✅ CustomizationID: 2.0 con schemeAgencyName="PE:SUNAT"
- ✅ Tipos de documento correctos

**Falta validar:**
- ⚠️ Validación de series según tipo de documento (F001-F999 para facturas, etc.)
- ⚠️ Validación de números correlativos sin saltos

---

### ⚠️ 4. Cálculos Tributarios Correctos

**Requisito:**
- IGV calculado exactamente (18% sobre valor de venta)
- Totales coincidentes (LineExtensionAmount, TaxInclusiveAmount, PayableAmount, TaxTotal)
- Redondeo a 2 decimales siempre

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ Usa `number_format(..., 2, '.', '')` para redondeo a 2 decimales
- ✅ Incluye todos los totales requeridos

**Falta verificar:**
- ⚠️ Validación de que IGV = 18% exactamente
- ⚠️ Validación de que los totales coinciden matemáticamente
- ⚠️ Validación de que TaxInclusiveAmount = LineExtensionAmount + TaxAmount

---

### ⚠️ 5. Reglas del Emisor (Empresa Beta)

**Requisito:**
- RUC: 20000000001 (para pruebas beta)
- Razón social válida
- Ubigeo válido
- Dirección válida
- Nombre comercial

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ Valida que RUC existe
- ✅ Valida que razón social existe
- ✅ Incluye ubigeo y dirección

**Falta:**
- ⚠️ Validación específica para ambiente beta (RUC debe ser x|)
- ⚠️ Validación de formato de ubigeo (6 dígitos)

---

### ⚠️ 6. Reglas del Cliente

**Requisito:**
- Facturas (01): RUC obligatorio, NO DNI
- Boletas (03): DNI permitido, consumidor final (99999999) permitido

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ Incluye datos del cliente en el XML
- ✅ Usa schemeID correcto según tipo de identidad

**Falta:**
- ⚠️ Validación que facturas requieren RUC (no DNI)
- ⚠️ Validación que boletas pueden usar DNI o consumidor final

---

### ✅ 7. Validaciones XML

**Requisito:**
- Orden correcto de nodos UBL
- Namespaces obligatorios
- UBLExtensions aunque esté vacío

**Estado:** ✅ **CUMPLE**

**Cumple:**
- ✅ Namespaces correctos incluidos
- ✅ UBLExtensions incluido (aunque vacío)
- ✅ Orden de nodos parece correcto según estructura UBL

---

### ❌ 8. Envío en ZIP

**Requisito:**
- XML debe comprimirse a ZIP antes de enviar
- Solo 1 XML dentro del ZIP
- Sin carpetas
- Sin BOM (Byte Order Mark)

**Estado:** ❌ **NO CUMPLE**

**Problema detectado:**
El código actual envía el XML directamente en Base64, NO comprimido en ZIP.

```php
// Código actual - envía XML directo
$fileContent = base64_encode($xmlSigned);
```

**Solución requerida:**
- Comprimir XML a ZIP antes de codificar en Base64
- Nombre del archivo: `{SERIE}-{NUMERO}.zip`
- Validar que no tenga BOM

---

### ⚠️ 9. Conexión SOAP Correcta

**Requisito:**
- Endpoint BETA: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService`
- Operación: `sendBill`
- Parámetros: nombre ZIP, archivo ZIP en Base64

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ Endpoint beta configurado correctamente
- ✅ Operación `sendBill` en SOAP envelope
- ✅ Parámetros `fileName` y `contentFile`

**Problemas:**
- ⚠️ Endpoint actual: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService` (tiene `-beta` extra)
- ⚠️ Envía XML directo en lugar de ZIP

**Nota:** El endpoint en el código tiene `-beta` pero según requisitos debería ser sin `-beta`.

---

### ⚠️ 10. Validación de Respuesta

**Requisito:**
- Procesar CDR ZIP de respuesta
- Validar código 0 (éxito)
- Extraer mensaje "Proceso Exitoso"

**Estado:** ⚠️ **PARCIALMENTE CUMPLE**

**Cumple:**
- ✅ Procesa respuesta SOAP
- ✅ Extrae `applicationResponse` (CDR XML)
- ✅ Extrae `statusCode` y `statusMessage`

**Falta:**
- ⚠️ Procesar CDR ZIP (actualmente solo procesa XML)
- ⚠️ Validación específica de código 0

---

## Resumen de Estado

| Requisito | Estado | Prioridad |
|-----------|--------|-----------|
| 1. Certificado Digital | ✅ Cumple | Alta |
| 2. Firma XAdES-BES | ❌ **NO CUMPLE** | **CRÍTICA** |
| 3. Estructura UBL | ⚠️ Parcial | Media |
| 4. Cálculos Tributarios | ⚠️ Parcial | Alta |
| 5. Reglas Emisor | ⚠️ Parcial | Media |
| 6. Reglas Cliente | ⚠️ Parcial | Media |
| 7. Validaciones XML | ✅ Cumple | Alta |
| 8. Envío ZIP | ❌ **NO CUMPLE** | **CRÍTICA** |
| 9. Conexión SOAP | ⚠️ Parcial | Alta |
| 10. Validación Respuesta | ⚠️ Parcial | Media |

---

## Acciones Requeridas (Prioridad)

### 🔴 CRÍTICO (Bloquea envío a SUNAT)

1. **Implementar Firma XAdES-BES**
   - Cambiar de XML-DSIG simple a XAdES-BES
   - Agregar elementos `xades:QualifyingProperties` y `xades:SignedProperties`
   - Incluir información completa del certificado X509

2. **Implementar Compresión ZIP**
   - Comprimir XML a ZIP antes de enviar
   - Nombre: `{SERIE}-{NUMERO}.zip`
   - Validar sin BOM, sin carpetas

### 🟡 ALTA PRIORIDAD

3. **Validar Cálculos Tributarios**
   - Verificar IGV = 18% exactamente
   - Validar que totales coinciden matemáticamente
   - Asegurar redondeo correcto

4. **Corregir Endpoint SOAP**
   - Verificar endpoint correcto (con o sin `-beta`)
   - Asegurar que envía ZIP en Base64

5. **Validar Reglas de Cliente**
   - Facturas: RUC obligatorio (no DNI)
   - Boletas: DNI o consumidor final permitido

### 🟢 MEDIA PRIORIDAD

6. **Validar Series y Números**
   - Validar series según tipo de documento
   - Validar números correlativos sin saltos

7. **Validar Reglas Emisor Beta**
   - Validar RUC beta si está en ambiente beta
   - Validar formato de ubigeo

8. **Procesar CDR ZIP**
   - Extraer y procesar CDR desde ZIP de respuesta

---

## Conclusión

**El sistema NO está listo para enviar a SUNAT BETA** debido a:

1. ❌ Falta implementar firma XAdES-BES (actualmente usa XML-DSIG simple)
2. ❌ Falta comprimir XML a ZIP antes de enviar

Estos dos puntos son **BLOQUEADORES CRÍTICOS** que impedirán que SUNAT acepte los documentos.

Los demás puntos son mejoras importantes pero no bloquean completamente el envío.
