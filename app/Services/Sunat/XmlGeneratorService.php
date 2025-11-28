<?php

namespace App\Services\Sunat;

use App\Models\Document;
use Illuminate\Support\Facades\Log;

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

        // ===== ITEMS DEL DOCUMENTO =====
        foreach ($document->items as $index => $item) {
            $xml .= '  <cac:InvoiceLine>' . "\n";
            $xml .= '    <cbc:ID>' . ($index + 1) . '</cbc:ID>' . "\n";

            // Cantidad
            $unitCode = $this->getUnitCode($item->product?->unit_type ?? 'NIU');
            $xml .= '    <cbc:InvoicedQuantity unitCode="' . $unitCode . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe" unitCodeListName="Unit Code">' . number_format((float) $item->quantity, 2, '.', '') . '</cbc:InvoicedQuantity>' . "\n";

            // Precio unitario
            $xml .= '    <cbc:LineExtensionAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->unit_price, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";

            // Descripción del item
            $xml .= '    <cac:Item>' . "\n";
            $xml .= '      <cbc:Description>' . $this->escapeXml($item->description) . '</cbc:Description>' . "\n";
            $xml .= '    </cac:Item>' . "\n";

            // Precio del item
            $xml .= '    <cac:Price>' . "\n";
            $xml .= '      <cbc:PriceAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->unit_price, 2, '.', '') . '</cbc:PriceAmount>' . "\n";
            $xml .= '    </cac:Price>' . "\n";

            // Impuestos del item
            if ((float) $item->igv > 0) {
                $xml .= '    <cac:TaxTotal>' . "\n";
                $xml .= '      <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
                $xml .= '      <cac:TaxSubtotal>' . "\n";
                $xml .= '        <cbc:TaxableAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) ($item->quantity * $item->unit_price), 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
                $xml .= '        <cbc:TaxAmount currencyID="' . $this->escapeXml($document->currency) . '">' . number_format((float) $item->igv, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
                $xml .= '        <cac:TaxCategory>' . "\n";
                $xml .= '          <cac:TaxScheme>' . "\n";
                $xml .= '            <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Type Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->escapeXml($item->tax_type) . '</cbc:ID>' . "\n";
                $xml .= '            <cbc:Name>' . $this->getTaxTypeName($item->tax_type) . '</cbc:Name>' . "\n";
                $xml .= '            <cbc:TaxTypeCode>' . $this->escapeXml($item->tax_type) . '</cbc:TaxTypeCode>' . "\n";
                $xml .= '          </cac:TaxScheme>' . "\n";
                $xml .= '        </cac:TaxCategory>' . "\n";
                $xml .= '      </cac:TaxSubtotal>' . "\n";
                $xml .= '    </cac:TaxTotal>' . "\n";
            }

            $xml .= '  </cac:InvoiceLine>' . "\n";
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

        $xml .= '</Invoice>';

        return $xml;
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
     */
    public function sign(string $xml, string $certificate, string $password): string
    {
        // TODO: Implement XML signing with digital certificate
        // This typically requires OpenSSL and XMLSecLib
        // Reference: https://github.com/robrichards/xmlseclibs
        //
        // Para implementar la firma digital, necesitarás:
        // 1. Instalar xmlseclibs: composer require robrichards/xmlseclibs
        // 2. Cargar el certificado digital (.p12)
        // 3. Firmar el XML según especificación XMLDSig
        // 4. Insertar la firma en el XML

        Log::warning('XML signing not yet implemented. Returning unsigned XML.');

        return $xml;
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
}
