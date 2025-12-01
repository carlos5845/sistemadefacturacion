<?php

namespace App\Services\Sunat;

use App\Models\Document;
use Illuminate\Support\Facades\Log;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMDocument;

class XmlGeneratorService
{
    /**
     * Generate XML for a document according to SUNAT UBL 2.1 format.
     */
    public function generate(Document $document): string
    {
        $document->load([
            'company',
            'customer',
            'items.product',
            'items.taxType',
            'documentType',
        ]);

        $company = $document->company;
        $customer = $document->customer;

        // Validar datos requeridos
        if (empty($company->ruc)) {
            throw new \Exception('La empresa no tiene RUC configurado.');
        }

        if (empty($company->business_name)) {
            throw new \Exception('La empresa no tiene razón social configurada.');
        }

        // Iniciar XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"';
        $xml .= ' xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"';
        $xml .= ' xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"';
        $xml .= ' xmlns:ccts="urn:un:unece:uncefact:documentation:2"';
        $xml .= ' xmlns:ds="http://www.w3.org/2000/09/xmldsig#"';
        $xml .= ' xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"';
        $xml .= ' xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"';
        $xml .= ' xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"';
        $xml .= ' xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"';
        $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n";

        // UBLExtensions (requerido por SUNAT)
        $xml .= '  <ext:UBLExtensions>' . "\n";
        $xml .= '    <ext:UBLExtension>' . "\n";
        $xml .= '      <ext:ExtensionContent/>' . "\n";
        $xml .= '    </ext:UBLExtension>' . "\n";
        $xml .= '  </ext:UBLExtensions>' . "\n";

        // cbc:UBLVersionID
        $xml .= '  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>' . "\n";

        // cbc:CustomizationID - Código de versión del formato según SUNAT
        $xml .= '  <cbc:CustomizationID schemeAgencyName="PE:SUNAT" schemeName="SUNAT:Formato de Comprobante de Pago">2.0</cbc:CustomizationID>' . "\n";

        // cbc:ID - Serie y número del documento
        $xml .= '  <cbc:ID>' . $this->escapeXml($document->series . '-' . str_pad((string) $document->number, 8, '0', STR_PAD_LEFT)) . '</cbc:ID>' . "\n";

        // cbc:IssueDate - Fecha de emisión
        $xml .= '  <cbc:IssueDate>' . $document->issue_date->format('Y-m-d') . '</cbc:IssueDate>' . "\n";

        // cbc:IssueTime - Hora de emisión (opcional pero recomendado)
        $xml .= '  <cbc:IssueTime>' . now()->format('H:i:s') . '</cbc:IssueTime>' . "\n";

        // cbc:InvoiceTypeCode - Código de tipo de documento
        // 01=Factura, 03=Boleta, 07=Nota de Crédito, 08=Nota de Débito
        $xml .= '  <cbc:InvoiceTypeCode listID="0101" listName="Tipo de Documento" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">' . $this->escapeXml($document->document_type) . '</cbc:InvoiceTypeCode>' . "\n";

        // ===== LEYENDAS =====
        // Monto en letras (Código 1000)
        // Nota: Según UBL 2.1, cbc:Note va en el encabezado, antes de DocumentCurrencyCode
        $numberToWords = new \App\Services\NumberToWordsService();
        $legendValue = $numberToWords->toWords($document->total, $document->currency);
        
        $xml .= '  <cbc:Note languageLocaleID="1000">' . $this->escapeXml($legendValue) . '</cbc:Note>' . "\n";

        // cbc:DocumentCurrencyCode - Código de moneda
        $xml .= '  <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe" listURI="urn:un:unece:uncefact:codelist:specification:5639:1988">' . $this->escapeXml($document->currency) . '</cbc:DocumentCurrencyCode>' . "\n";

        // ===== DATOS DEL EMISOR (Empresa) =====
        $xml .= '  <cac:AccountingSupplierParty>' . "\n";
        $xml .= '    <cac:Party>' . "\n";

        // RUC del emisor
        $xml .= '      <cac:PartyIdentification>' . "\n";
        $xml .= '        <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escapeXml($company->ruc) . '</cbc:ID>' . "\n";
        $xml .= '      </cac:PartyIdentification>' . "\n";

        // Nombre comercial
        if (! empty($company->trade_name)) {
            $xml .= '      <cac:PartyName>' . "\n";
            $xml .= '        <cbc:Name>' . $this->escapeXml($company->trade_name) . '</cbc:Name>' . "\n";
            $xml .= '      </cac:PartyName>' . "\n";
        }

        // Dirección del emisor
        $xml .= '      <cac:PostalAddress>' . "\n";
        $xml .= '        <cbc:ID schemeName="Ubigeos">' . $this->escapeXml($company->ubigeo ?? '000000') . '</cbc:ID>' . "\n";
        if (! empty($company->address)) {
            $xml .= '        <cbc:StreetName>' . $this->escapeXml($company->address) . '</cbc:StreetName>' . "\n";
        }
        $xml .= '        <cac:Country>' . "\n";
        $xml .= '          <cbc:IdentificationCode listID="ISO 3166-1" listName="Country" listAgencyName="United Nations Economic Commission for Europe" listURI="urn:un:unece:uncefact:codelist:specification:6639:1988">PE</cbc:IdentificationCode>' . "\n";
        $xml .= '        </cac:Country>' . "\n";
        $xml .= '      </cac:PostalAddress>' . "\n";

        // Razón social del emisor
        $xml .= '      <cac:PartyLegalEntity>' . "\n";
        $xml .= '        <cbc:RegistrationName>' . $this->escapeXml($company->business_name) . '</cbc:RegistrationName>' . "\n";
        $xml .= '      </cac:PartyLegalEntity>' . "\n";

        $xml .= '    </cac:Party>' . "\n";
        $xml .= '  </cac:AccountingSupplierParty>' . "\n";

        // ===== DATOS DEL CLIENTE (Adquiriente) =====
        if ($customer) {
            $xml .= '  <cac:AccountingCustomerParty>' . "\n";
            $xml .= '    <cac:Party>' . "\n";

            // Tipo y número de documento del cliente
            $identityTypeCode = $this->getIdentityTypeCode($customer->identity_type);
            $xml .= '      <cac:PartyIdentification>' . "\n";
            $xml .= '        <cbc:ID schemeID="' . $identityTypeCode . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escapeXml($customer->identity_number) . '</cbc:ID>' . "\n";
            $xml .= '      </cac:PartyIdentification>' . "\n";

            // Nombre del cliente
            $xml .= '      <cac:PartyLegalEntity>' . "\n";
            $xml .= '        <cbc:RegistrationName>' . $this->escapeXml($customer->name) . '</cbc:RegistrationName>' . "\n";
            $xml .= '      </cac:PartyLegalEntity>' . "\n";

            $xml .= '    </cac:Party>' . "\n";
            $xml .= '  </cac:AccountingCustomerParty>' . "\n";
        } else {
            // Cliente genérico (para boletas sin cliente)
            $xml .= '  <cac:AccountingCustomerParty>' . "\n";
            $xml .= '    <cac:Party>' . "\n";
            $xml .= '      <cac:PartyIdentification>' . "\n";
            $xml .= '        <cbc:ID schemeID="1" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">00000000</cbc:ID>' . "\n";
            $xml .= '      </cac:PartyIdentification>' . "\n";
            $xml .= '      <cac:PartyLegalEntity>' . "\n";
            $xml .= '        <cbc:RegistrationName>CLIENTE VARIOS</cbc:RegistrationName>' . "\n";
            $xml .= '      </cac:PartyLegalEntity>' . "\n";
            $xml .= '    </cac:Party>' . "\n";
            $xml .= '  </cac:AccountingCustomerParty>' . "\n";
        }

        // ===== TOTALES =====
        // Total de impuestos
        if ($document->total_igv > 0) {
            $xml .= '  <cac:TaxTotal>' . "\n";
            $xml .= '    <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total_igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
            $xml .= '    <cac:TaxSubtotal>' . "\n";
            $xml .= '      <cbc:TaxableAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total_taxed, 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
            $xml .= '      <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total_igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
            $xml .= '      <cac:TaxCategory>' . "\n";
            $xml .= '        <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID>' . "\n";
            $xml .= '        <cac:TaxScheme>' . "\n";
            $xml .= '          <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Type Identifier" schemeAgencyName="United Nations Economic Commission for Europe">1000</cbc:ID>' . "\n";
            $xml .= '          <cbc:Name>IGV</cbc:Name>' . "\n";
            $xml .= '          <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>' . "\n";
            $xml .= '        </cac:TaxScheme>' . "\n";
            $xml .= '      </cac:TaxCategory>' . "\n";
            $xml .= '    </cac:TaxSubtotal>' . "\n";
            $xml .= '  </cac:TaxTotal>' . "\n";
        }

        // Total del documento
        $xml .= '  <cac:LegalMonetaryTotal>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total_taxed, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";
        $xml .= '    <cbc:TaxInclusiveAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total, 2, '.', '') . '</cbc:TaxInclusiveAmount>' . "\n";
        $xml .= '    <cbc:PayableAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $document->total, 2, '.', '') . '</cbc:PayableAmount>' . "\n";
        $xml .= '  </cac:LegalMonetaryTotal>' . "\n";

        // ===== ITEMS DEL DOCUMENTO =====
        foreach ($document->items as $index => $item) {
            $xml .= '  <cac:InvoiceLine>' . "\n";
            $xml .= '    <cbc:ID>' . ($index + 1) . '</cbc:ID>' . "\n";

            // Cantidad
            $unitCode = $this->getUnitCode($item->product?->unit_type ?? 'NIU');
            $xml .= '    <cbc:InvoicedQuantity unitCode="' . $unitCode . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . number_format((float) $item->quantity, 2, '.', '') . '</cbc:InvoicedQuantity>' . "\n";

            // Precio unitario
            $xml .= '    <cbc:LineExtensionAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->unit_price, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";

            // Referencia de precios (Precio con IGV)
            $priceWithTax = $item->unit_price * (1 + ($item->igv > 0 ? 0.18 : 0));
            $xml .= '    <cac:PricingReference>' . "\n";
            $xml .= '      <cac:AlternativeConditionPrice>' . "\n";
            $xml .= '        <cbc:PriceAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $priceWithTax, 2, '.', '') . '</cbc:PriceAmount>' . "\n";
            $xml .= '        <cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>' . "\n";
            $xml .= '      </cac:AlternativeConditionPrice>' . "\n";
            $xml .= '    </cac:PricingReference>' . "\n";

            // Impuestos del item
            if ((float) $item->igv > 0) {
                $xml .= '    <cac:TaxTotal>' . "\n";
                $xml .= '      <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
                $xml .= '      <cac:TaxSubtotal>' . "\n";
                $xml .= '        <cbc:TaxableAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) ($item->quantity * $item->unit_price), 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
                $xml .= '        <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
                $xml .= '        <cac:TaxCategory>' . "\n";
                $xml .= '          <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->getTaxCategoryId($item->tax_type) . '</cbc:ID>' . "\n";
                // Percent (IGV 18%)
                if ($this->getTaxSchemeId($item->tax_type) === '1000') {
                    $xml .= '          <cbc:Percent>18.00</cbc:Percent>' . "\n";
                }
                // TaxExemptionReasonCode (Afectación al IGV - Catálogo 07)
                $xml .= '          <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $this->escapeXml($item->tax_type) . '</cbc:TaxExemptionReasonCode>' . "\n";
                $xml .= '          <cac:TaxScheme>' . "\n";
                $xml .= '            <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Type Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->getTaxSchemeId($item->tax_type) . '</cbc:ID>' . "\n";
                $xml .= '            <cbc:Name>' . $this->getTaxSchemeName($item->tax_type) . '</cbc:Name>' . "\n";
                $xml .= '            <cbc:TaxTypeCode>' . $this->getTaxTypeCode($item->tax_type) . '</cbc:TaxTypeCode>' . "\n";
                $xml .= '          </cac:TaxScheme>' . "\n";
                $xml .= '        </cac:TaxCategory>' . "\n";
                $xml .= '      </cac:TaxSubtotal>' . "\n";
                $xml .= '    </cac:TaxTotal>' . "\n";
            }

            // Descripción del item
            $xml .= '    <cac:Item>' . "\n";
            $xml .= '      <cbc:Description>' . $this->escapeXml($item->description) . '</cbc:Description>' . "\n";
            $xml .= '    </cac:Item>' . "\n";

            // Precio del item
            $xml .= '    <cac:Price>' . "\n";
            $xml .= '      <cbc:PriceAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->unit_price, 2, '.', '') . '</cbc:PriceAmount>' . "\n";
            $xml .= '    </cac:Price>' . "\n";

            $xml .= '  </cac:InvoiceLine>' . "\n";
        }



        $xml .= '</Invoice>';
        
        Log::info('Generated XML:', ['xml' => $xml]);

        return $xml;
    }

    /**
     * Add XAdES-BES elements to signed XML according to SUNAT requirements.
     * 
     * XAdES-BES (XML Advanced Electronic Signatures - Basic Electronic Signature)
     * requires additional elements beyond standard XML-DSIG:
     * - xades:QualifyingProperties
     * - xades:SignedProperties
     * - xades:SignedSignatureProperties
     * - xades:SignedDataObjectProperties
     * 
     * @param  string  $signedXml  XML with basic XML-DSIG signature
     * @param  string  $certData  X509 certificate data (PEM format)
     * @return string XML with XAdES-BES signature
     */
    protected function addXAdESBES(string $signedXml, string $certData): string
    {
        try {
            // Cargar XML firmado
            $doc = new DOMDocument();
            $doc->loadXML($signedXml);

            // Registrar namespace XAdES
            $xadesNs = 'http://uri.etsi.org/01903/v1.3.2#';

            // Buscar el elemento Signature
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

            $signatureNodes = $xpath->query('//ds:Signature');
            if ($signatureNodes->length === 0) {
                throw new \Exception('No se encontró el elemento Signature en el XML firmado.');
            }

            $signatureNode = $signatureNodes->item(0);

            // Obtener ID del Signature si existe, o generar uno
            $signatureId = $signatureNode->hasAttribute('Id')
                ? $signatureNode->getAttribute('Id')
                : 'Signature-' . uniqid();

            if (!$signatureNode->hasAttribute('Id')) {
                $signatureNode->setAttribute('Id', $signatureId);
            }

            // Validar y limpiar el certificado antes de procesarlo
            // Asegurar que el certificado esté en formato PEM válido
            $certPEM = trim($certData);

            // Limpiar Bag Attributes si existen
            if (preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $certPEM, $matches)) {
                $certPEM = $matches[0];
            }

            // Si no tiene los headers PEM, intentar leerlo como DER y convertir
            if (!str_contains($certPEM, '-----BEGIN CERTIFICATE-----')) {
                // Intentar leer como DER y convertir a PEM
                $certResource = @openssl_x509_read($certData);
                if ($certResource === false) {
                    // Si falla, intentar como contenido binario
                    $certResource = @openssl_x509_read('data://application/x-x509-cert;base64,' . base64_encode($certData));
                }

                if ($certResource !== false) {
                    openssl_x509_export($certResource, $certPEM);
                    $certPEM = trim($certPEM);
                } else {
                    throw new \Exception('No se pudo leer el certificado X.509. El formato del certificado no es válido.');
                }
            }

            // Verificar que el certificado sea válido antes de parsearlo
            $certResource = @openssl_x509_read($certPEM);
            if ($certResource === false) {
                $errorMsg = 'No se pudo leer el certificado X.509. ';
                while (($error = openssl_error_string()) !== false) {
                    $errorMsg .= trim($error) . ' ';
                }
                throw new \Exception($errorMsg);
            }

            // Obtener información del certificado
            $certInfo = openssl_x509_parse($certResource, true);
            if ($certInfo === false) {
                $errorMsg = 'No se pudo parsear el certificado X.509. ';
                while (($error = openssl_error_string()) !== false) {
                    $errorMsg .= trim($error) . ' ';
                }
                throw new \Exception($errorMsg);
            }

            $certSerialNumber = isset($certInfo['serialNumber']) ? (string) $certInfo['serialNumber'] : '';
            $certIssuer = isset($certInfo['issuer']) && is_array($certInfo['issuer']) ? $this->formatDN($certInfo['issuer']) : '';
            $certSubject = isset($certInfo['subject']) && is_array($certInfo['subject']) ? $this->formatDN($certInfo['subject']) : '';
            $certValidFrom = isset($certInfo['validFrom_time_t']) ? date('Y-m-d\TH:i:s\Z', $certInfo['validFrom_time_t']) : '';
            $certValidTo = isset($certInfo['validTo_time_t']) ? date('Y-m-d\TH:i:s\Z', $certInfo['validTo_time_t']) : '';

            // Usar el certificado PEM limpio para el hash
            $certData = $certPEM;

            // Si no se puede obtener el issuer, usar el subject como fallback
            if (empty($certIssuer) && !empty($certSubject)) {
                $certIssuer = $certSubject;
            }

            // Si aún no hay issuer, usar un valor por defecto
            if (empty($certIssuer)) {
                $certIssuer = 'CN=Unknown';
            }

            // Crear elemento Object para XAdES
            $objectNode = $doc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Object');

            // Crear QualifyingProperties
            $qualifyingProps = $doc->createElementNS($xadesNs, 'xades:QualifyingProperties');
            $qualifyingProps->setAttribute('Target', '#' . $signatureId);

            // Crear SignedProperties
            $signedProps = $doc->createElementNS($xadesNs, 'xades:SignedProperties');
            $signedProps->setAttribute('Id', 'SignedProperties-' . uniqid());

            // Crear SignedSignatureProperties
            $signedSigProps = $doc->createElementNS($xadesNs, 'xades:SignedSignatureProperties');

            // SigningTime
            $signingTime = $doc->createElementNS($xadesNs, 'xades:SigningTime', date('Y-m-d\TH:i:s\Z'));
            $signedSigProps->appendChild($signingTime);

            // SigningCertificate
            $signingCert = $doc->createElementNS($xadesNs, 'xades:SigningCertificate');
            $cert = $doc->createElementNS($xadesNs, 'xades:Cert');

            // CertDigest
            $certDigest = $doc->createElementNS($xadesNs, 'xades:CertDigest');
            $digestMethod = $doc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
            $certDigest->appendChild($digestMethod);

            // Calcular hash del certificado
            $certHash = base64_encode(hash('sha256', base64_decode(preg_replace('/-----[^-]+-----/', '', $certData)), true));
            $digestValue = $doc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue', $certHash);
            $certDigest->appendChild($digestValue);
            $cert->appendChild($certDigest);

            // IssuerSerial
            $issuerSerial = $doc->createElementNS($xadesNs, 'xades:IssuerSerial');
            $x509IssuerName = $doc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509IssuerName', $certIssuer);
            $issuerSerial->appendChild($x509IssuerName);
            $x509SerialNumber = $doc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509SerialNumber', $certSerialNumber);
            $issuerSerial->appendChild($x509SerialNumber);
            $cert->appendChild($issuerSerial);

            $signingCert->appendChild($cert);
            $signedSigProps->appendChild($signingCert);

            // SignerRole
            $signerRole = $doc->createElementNS($xadesNs, 'xades:SignerRole');
            $claimedRoles = $doc->createElementNS($xadesNs, 'xades:ClaimedRoles');
            $claimedRole = $doc->createElementNS($xadesNs, 'xades:ClaimedRole', 'supplier');
            $claimedRoles->appendChild($claimedRole);
            $signerRole->appendChild($claimedRoles);
            $signedSigProps->appendChild($signerRole);

            $signedProps->appendChild($signedSigProps);

            // Crear SignedDataObjectProperties
            $signedDataObjProps = $doc->createElementNS($xadesNs, 'xades:SignedDataObjectProperties');

            // DataObjectFormat - Referencia al objeto firmado (Invoice)
            // Buscar la referencia en el Signature
            $referenceNodes = $xpath->query('.//ds:Reference', $signatureNode);
            $objectReference = null;
            if ($referenceNodes->length > 0) {
                $firstReference = $referenceNodes->item(0);
                $objectReference = $firstReference->hasAttribute('URI')
                    ? $firstReference->getAttribute('URI')
                    : '#Invoice';
            } else {
                $objectReference = '#Invoice';
            }

            $dataObjectFormat = $doc->createElementNS($xadesNs, 'xades:DataObjectFormat');
            $dataObjectFormat->setAttribute('ObjectReference', $objectReference);
            $mimeType = $doc->createElementNS($xadesNs, 'xades:MimeType', 'text/xml');
            $dataObjectFormat->appendChild($mimeType);
            $encoding = $doc->createElementNS($xadesNs, 'xades:Encoding', 'UTF-8');
            $dataObjectFormat->appendChild($encoding);
            $signedDataObjProps->appendChild($dataObjectFormat);

            $signedProps->appendChild($signedDataObjProps);

            $qualifyingProps->appendChild($signedProps);
            $objectNode->appendChild($qualifyingProps);

            // Agregar Object al Signature
            $signatureNode->appendChild($objectNode);

            // Agregar namespace XAdES al elemento raíz si no existe
            $rootElement = $doc->documentElement;
            if (! $rootElement->hasAttribute('xmlns:xades')) {
                $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xades', $xadesNs);
            }

            return $doc->saveXML();
        } catch (\Exception $e) {
            Log::error('Error adding XAdES-BES elements', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Si falla agregar XAdES, retornar XML con firma básica
            // Esto permite que el sistema funcione aunque no tenga XAdES completo
            Log::warning('Returning XML with basic signature (XAdES-BES addition failed)', [
                'error' => $e->getMessage(),
            ]);

            return $signedXml;
        }
    }

    /**
     * Format Distinguished Name (DN) from array to string.
     * 
     * @param  array  $dn  Distinguished Name array from openssl_x509_parse
     * @return string Formatted DN string
     */
    protected function formatDN(array $dn): string
    {
        $parts = [];
        foreach ($dn as $key => $value) {
            $parts[] = $key . '=' . $value;
        }
        return implode(', ', $parts);
    }

    /**
     * Escape XML special characters.
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get identity type code for SUNAT.
     */
    protected function getIdentityTypeCode(?string $identityType): string
    {
        return match ($identityType) {
            'DNI' => '1',
            'RUC' => '6',
            'CE' => '4',
            'PAS' => '7',
            default => '0', // Otros
        };
    }

    /**
     * Get unit code for SUNAT (UN/ECE Recommendation 20).
     */
    protected function getUnitCode(string $unitType): string
    {
        // Mapeo de códigos comunes de unidades de medida
        $unitMap = [
            'NIU' => 'NIU', // Unidad
            'KG' => 'KGM', // Kilogramo
            'M' => 'MTR', // Metro
            'L' => 'LTR', // Litro
            'M2' => 'MTK', // Metro cuadrado
            'M3' => 'MTQ', // Metro cúbico
        ];

        return $unitMap[strtoupper($unitType)] ?? 'NIU';
    }

    /**
     * Get tax type name.
     */
    protected function getTaxTypeName(string $taxType): string
    {
        return match ($taxType) {
            '10' => 'Gravado - Operación Onerosa',
            '20' => 'Exonerado - Operación Onerosa',
            '30' => 'Inafecto - Operación Onerosa',
            '40' => 'Exportación',
            default => 'Gravado',
        };
    }

    /**
     * Sign XML with digital certificate.
     *
     * @param  string  $xml  XML content to sign
     * @param  string  $certificate  Certificate content (PEM format) or path to .p12 file
     * @param  string  $password  Certificate password
     * @return string Signed XML
     */
    public function sign(string $xml, string $certificate, string $password): string
    {
        try {
            // Cargar el XML en un DOMDocument
            $doc = new DOMDocument();
            $doc->loadXML($xml);

            // Crear objeto de firma
            $objDSig = new XMLSecurityDSig();
            $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

            // Usar el documento completo como referencia para evitar agregar atributo Id al nodo raíz
            $rootNode = $doc->documentElement;
            $objDSig->addReferenceList(
                [$doc],
                XMLSecurityDSig::SHA256,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
                ['force_uri' => true, 'overwrite' => false]
            );

            // Cargar la clave privada del certificado
            // El certificado puede venir como:
            // 1. Contenido PEM directo (texto)
            // 2. Ruta a archivo .p12/.pfx en storage
            // 3. Contenido binario base64 del archivo .p12/.pfx
            $privateKey = null;
            $certData = null;

            // Si el certificado parece ser contenido PEM directo
            if (str_contains($certificate, '-----BEGIN')) {
                // Intentar extraer la clave privada del PEM
                $privateKeyResource = openssl_pkey_get_private($certificate, $password);
                if ($privateKeyResource === false) {
                    throw new \Exception('No se pudo cargar la clave privada del certificado PEM. Verifique el formato y la contraseña.');
                }
                openssl_pkey_export($privateKeyResource, $privateKey);
                openssl_x509_export(openssl_x509_read($certificate), $certData);
            } else {
                // Es un archivo PFX/P12 - puede ser ruta o contenido base64
                $pkcs12Content = null;

                // Verificar si es una ruta a archivo
                // Normalizar la ruta para Windows (convertir / a \ si es necesario)
                $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $certificate);

                if (file_exists($normalizedPath)) {
                    // Es una ruta absoluta o relativa al storage
                    $pkcs12Content = file_get_contents($normalizedPath);
                } elseif (file_exists($certificate)) {
                    // Intentar con la ruta original
                    $pkcs12Content = file_get_contents($certificate);
                } elseif (str_starts_with($certificate, storage_path('app'))) {
                    // Es una ruta dentro de storage/app
                    $normalizedStoragePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $certificate);
                    if (file_exists($normalizedStoragePath)) {
                        $pkcs12Content = file_get_contents($normalizedStoragePath);
                    } else {
                        throw new \Exception("El archivo de certificado no existe en storage: {$certificate}");
                    }
                } elseif (file_exists(storage_path('app/' . $certificate))) {
                    // Es una ruta relativa dentro de storage/app
                    $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/' . $certificate));
                    $pkcs12Content = file_get_contents($relativePath);
                } elseif (base64_decode($certificate, true) !== false && strlen($certificate) > 100) {
                    // Podría ser contenido base64 del archivo .p12
                    $decoded = base64_decode($certificate, true);
                    if ($decoded !== false && str_starts_with($decoded, "\x30\x82")) {
                        // Parece ser un archivo PKCS#12 válido (empieza con DER encoding)
                        $pkcs12Content = $decoded;
                    } else {
                        throw new \Exception('El certificado no es válido. Debe ser un archivo PFX/P12 o contenido PEM.');
                    }
                } else {
                    // Intentar como contenido binario directo
                    $pkcs12Content = $certificate;
                }

                if ($pkcs12Content === null) {
                    throw new \Exception("No se pudo leer el contenido del certificado PFX/P12.");
                }

                // Leer el archivo .p12/.pfx
                $certs = [];
                $errorMessage = '';

                if (! openssl_pkcs12_read($pkcs12Content, $certs, $password)) {
                    // Obtener el último error de OpenSSL
                    while (($error = openssl_error_string()) !== false) {
                        $errorMessage .= $error . "\n";
                    }
                    throw new \Exception('No se pudo leer el certificado PFX/P12. Verifique que la contraseña sea correcta y que el archivo esté en formato válido. Error: ' . trim($errorMessage));
                }

                if (empty($certs['pkey']) || empty($certs['cert'])) {
                    throw new \Exception('El certificado PFX/P12 no contiene la clave privada o el certificado X509 necesario.');
                }

                $privateKey = $certs['pkey'];

                // Asegurar que el certificado esté en formato PEM válido
                $certData = trim($certs['cert']);

                // Limpiar Bag Attributes si existen (común en PFX exportados)
                if (preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $certData, $matches)) {
                    $certData = $matches[0];
                }

                // Verificar que el certificado tenga los headers PEM
                if (!str_contains($certData, '-----BEGIN CERTIFICATE-----')) {
                    // Intentar leer y re-exportar el certificado para asegurar formato PEM válido
                    $certResource = @openssl_x509_read($certData);
                    if ($certResource === false) {
                        throw new \Exception('El certificado extraído del archivo PFX/P12 no es un certificado X.509 válido.');
                    }
                    openssl_x509_export($certResource, $certData);
                    $certData = trim($certData);
                }

                // Validar que el certificado se pueda leer correctamente
                $testRead = @openssl_x509_read($certData);
                if ($testRead === false) {
                    $errorMsg = 'El certificado extraído no se puede leer correctamente. ';
                    while (($error = openssl_error_string()) !== false) {
                        $errorMsg .= trim($error) . ' ';
                    }
                    throw new \Exception($errorMsg);
                }
                Log::info('Certificate extracted and validated successfully');
            }

            // Crear clave de seguridad
            Log::info('Loading private key into XMLSecurityKey');
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            // FIX: El tercer parámetro debe ser false porque estamos cargando una clave privada, no un certificado
            $objKey->loadKey($privateKey, false, false);

            // Agregar el certificado X509 a la firma
            Log::info('Adding 509 cert to signature');
            $objDSig->add509Cert($certData, true);

            // Insertar la firma en el XML
            Log::info('Signing XML');
            $objDSig->sign($objKey);

            // Agregar la firma al documento
            Log::info('Appending signature');
            
            // Buscar el nodo ExtensionContent para insertar la firma allí
            $extensionContentNode = $doc->getElementsByTagNameNS('urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2', 'ExtensionContent')->item(0);
            
            if ($extensionContentNode) {
                $objDSig->appendSignature($extensionContentNode);
            } else {
                // Fallback si no se encuentra (no debería pasar si el XML se generó bien)
                Log::warning('ExtensionContent node not found, appending to root node');
                $objDSig->appendSignature($rootNode);
            }

            // Obtener el XML firmado básico
            $signedXml = $doc->saveXML();

            // Verificar que la firma se agregó correctamente
            if (! str_contains($signedXml, '<ds:Signature') && ! str_contains($signedXml, '<Signature')) {
                throw new \Exception('La firma digital no se agregó correctamente al XML. El XML firmado no contiene el elemento Signature.');
            }

            // Agregar elementos XAdES-BES según requisitos de SUNAT
            Log::info('Adding XAdES-BES elements');
            $signedXml = $this->addXAdESBES($signedXml, $certData);

            Log::info('XML signed successfully with XAdES-BES', [
                'xml_length' => strlen($signedXml),
                'has_signature' => true,
                'has_xades' => str_contains($signedXml, 'xades:QualifyingProperties'),
            ]);

            return $signedXml;
        } catch (\Exception $e) {
            Log::error('Error signing XML with digital certificate', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Lanzar la excepción en lugar de retornar XML sin firmar
            // Esto permite que el código que llama maneje el error apropiadamente
            throw new \Exception('Error al firmar el XML: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate hash for XML document.
     */
    public function generateHash(string $xml): string
    {
        // SUNAT requiere hash SHA-256 del XML sin espacios ni saltos de línea
        $xmlCleaned = preg_replace('/>\s+</', '><', $xml);
        $xmlCleaned = preg_replace('/\s+/', ' ', $xmlCleaned);
        $xmlCleaned = trim($xmlCleaned);

        return hash('sha256', $xmlCleaned);
    }

    /**
     * Get Tax Scheme ID based on tax type code.
     */
    protected function getTaxSchemeId(string $taxType): string
    {
        return match ($taxType) {
            '10' => '1000', // IGV - Gravado
            '20' => '9997', // Exonerado
            '30' => '9998', // Inafecto
            '40' => '9995', // Exportación
            default => '1000',
        };
    }

    /**
     * Get Tax Scheme Name based on tax type code.
     */
    protected function getTaxSchemeName(string $taxType): string
    {
        return match ($taxType) {
            '10' => 'IGV',
            '20' => 'EXO',
            '30' => 'INA',
            '40' => 'EXP',
            default => 'IGV',
        };
    }

    /**
     * Get Tax Type Code based on tax type code.
     */
    protected function getTaxTypeCode(string $taxType): string
    {
        return match ($taxType) {
            '10' => 'VAT',
            '20' => 'VAT',
            '30' => 'FRE',
            '40' => 'FRE',
            default => 'VAT',
        };
    }

    /**
     * Get Tax Category ID based on tax type code.
     */
    protected function getTaxCategoryId(string $taxType): string
    {
        return match ($taxType) {
            '10' => 'S', // IGV - Standard
            '20' => 'E', // Exonerado - Exempt
            '30' => 'O', // Inafecto - Outside scope
            '40' => 'G', // Exportación - Free Export Item
            default => 'S',
        };
    }


}
