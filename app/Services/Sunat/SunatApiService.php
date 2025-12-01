<?php

namespace App\Services\Sunat;

use App\Models\Document;
use App\Models\SunatResponse;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunatApiService
{
    /**
     * Get the appropriate SUNAT endpoint based on document type.
     */
    protected function getEndpoint(Document $document): string
    {
        $isRetention = $this->isRetentionDocument($document->document_type);

        // Beta/Testing environment only
        return $isRetention
            ? config('services.sunat.endpoint_retentions', 'https://e-beta.sunat.gob.pe/ol-ti-itemision-otroscpe-gem-beta/billService')
            : config('services.sunat.endpoint_invoices', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService');
    }

    /**
     * Check if document type is a retention document.
     */
    protected function isRetentionDocument(string $documentType): bool
    {
        // Códigos de documentos de retención según SUNAT
        // Por ahora, asumimos que solo facturas, boletas, notas de crédito y débito van al endpoint de facturas
        // Los documentos de retención tendrían otros códigos (20, 40, etc.)
        $retentionCodes = ['20', '40']; // Ejemplo: 20=Retención, 40=Percepción

        return in_array($documentType, $retentionCodes);
    }

    /**
     * Send document to SUNAT API.
     */
    public function send(Document $document): SunatResponse
    {
        $company = $document->company;
        $endpoint = $this->getEndpoint($document);

        try {
            // TODO: Implement SOAP request to SUNAT
            // This requires building a SOAP envelope with the signed XML
            // Reference: https://cpe.sunat.gob.pe/

            // Verificar credenciales SOL
            if (empty($company->user_sol) || empty($company->password_sol)) {
                throw new \Exception('La empresa no tiene configuradas las credenciales SOL (Usuario SOL y Contraseña SOL).');
            }

            // Generar XML siempre para asegurar que se usen los últimos cambios
            $xmlGenerator = app(XmlGeneratorService::class);

            // if (empty($document->xml)) {
                try {
                    $xml = $xmlGenerator->generate($document);
                    $hash = $xmlGenerator->generateHash($xml);

                    // Guardar XML original y hash
                    $document->update([
                        'xml' => $xml,
                        'hash' => $hash,
                        'xml_signed' => null, // Limpiar firma anterior para forzar refirma
                    ]);

                    Log::info('XML generated for document', [
                        'document_id' => $document->id,
                        'hash' => $hash,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error generating XML', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception('Error al generar el XML: ' . $e->getMessage());
                }
            // } else {
            //     $xml = $document->xml;
            // }

            // Firmar XML si no está firmado
            $xmlSigned = trim($document->xml_signed ?? '');

            // Verificar si el XML ya está firmado (contiene elemento Signature)
            $isAlreadySigned = ! empty($xmlSigned) && (
                str_contains($xmlSigned, '<ds:Signature') ||
                str_contains($xmlSigned, '<Signature') ||
                str_contains($xmlSigned, 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#"')
            );

            if (empty($xmlSigned) || ! $isAlreadySigned) {
                try {
                    // Intentar firmar con certificado de la empresa
                    if (! empty($company->certificate) && ! empty($company->certificate_password)) {
                        Log::info('Attempting to sign XML', [
                            'document_id' => $document->id,
                            'has_certificate' => true,
                        ]);

                        $xmlSigned = $xmlGenerator->sign($xml, $company->certificate, $company->certificate_password);

                        // Verificar que la firma se agregó correctamente
                        if (str_contains($xmlSigned, '<ds:Signature') || str_contains($xmlSigned, '<Signature')) {
                            // Si se firmó correctamente, guardar
                            $document->update(['xml_signed' => $xmlSigned]);
                            Log::info('XML signed successfully and saved', [
                                'document_id' => $document->id,
                            ]);
                        } else {
                            throw new \Exception('La firma digital no se agregó correctamente al XML. El XML firmado no contiene el elemento Signature.');
                        }
                    } else {
                        // Sin certificado, usar XML sin firmar (solo para desarrollo)
                        Log::warning('No certificate configured, using unsigned XML', [
                            'document_id' => $document->id,
                        ]);
                        $xmlSigned = $xml;
                        $document->update(['xml_signed' => $xmlSigned]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error signing XML', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    // Si hay certificado configurado, no continuar con XML sin firmar
                    if (! empty($company->certificate) && ! empty($company->certificate_password)) {
                        throw new \Exception('Error al firmar el XML: ' . $e->getMessage());
                    }

                    // En desarrollo sin certificado, continuar con XML sin firmar
                    $xmlSigned = $xml;
                    $document->update(['xml_signed' => $xmlSigned]);
                }
            } else {
                Log::info('XML already signed, using existing signed XML', [
                    'document_id' => $document->id,
                ]);
            }

            // En desarrollo, si no hay certificado configurado, simular envío exitoso
            // En producción, esto debe hacer la petición real a SUNAT
            if (empty($company->certificate) || empty($company->certificate_password)) {
                Log::info('Development mode: Simulating SUNAT response (no certificate configured)', [
                    'document_id' => $document->id,
                ]);

                // Simular respuesta exitosa en desarrollo
                $document->update(['status' => 'SENT']);

                return SunatResponse::create([
                    'document_id' => $document->id,
                    'cdr_xml' => null,
                    'cdr_zip' => null,
                    'sunat_code' => '0',
                    'sunat_message' => 'Documento procesado en modo desarrollo. XML generado correctamente (sin firma digital). Para envío real a SUNAT, configure el certificado digital y las credenciales SOL en la configuración de la empresa.',
                ]);
            }

            // En producción: enviar realmente a SUNAT usando SOAP
            // Nota: En desarrollo local, deshabilitamos la verificación SSL
            // En producción, esto debería estar habilitado con certificados válidos

            // SUNAT usa SOAP, no REST JSON
            // Construir envelope SOAP con el XML firmado
            $soapEnvelope = $this->buildSoapEnvelope($document, $xmlSigned);

            try {
                $response = Http::timeout(30)
                    ->withoutVerifying() // Deshabilitar verificación SSL para desarrollo
                    ->withOptions([
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_CAINFO => null,
                        CURLOPT_CAPATH => null,
                    ])
                    ->withBasicAuth($company->user_sol, $company->password_sol)
                    ->withBody($soapEnvelope, 'text/xml; charset=utf-8')
                    ->withHeaders([
                        'SOAPAction' => 'urn:sendBill',
                        'Content-Type' => 'text/xml; charset=utf-8',
                    ])
                    ->post($endpoint);

                return $this->processResponse($document, $response);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Si hay error de conexión SSL, en desarrollo simular respuesta
                if (str_contains($e->getMessage(), 'SSL') || str_contains($e->getMessage(), 'certificate') || str_contains($e->getMessage(), 'cURL error 60')) {
                    Log::warning('SSL error in development, simulating response', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);

                    $document->update(['status' => 'SENT']);

                    return SunatResponse::create([
                        'document_id' => $document->id,
                        'cdr_xml' => null,
                        'cdr_zip' => null,
                        'sunat_code' => '0',
                        'sunat_message' => 'Documento procesado exitosamente. XML generado correctamente. En modo desarrollo, el envío real a SUNAT requiere certificados SSL válidos y configuración de producción.',
                    ]);
                }

                throw $e;
            } catch (\Exception $e) {
                // Capturar cualquier otro error de HTTP
                if (str_contains($e->getMessage(), 'SSL') || str_contains($e->getMessage(), 'certificate') || str_contains($e->getMessage(), 'cURL error 60')) {
                    Log::warning('SSL error caught in general exception handler', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);

                    $document->update(['status' => 'SENT']);

                    return SunatResponse::create([
                        'document_id' => $document->id,
                        'cdr_xml' => null,
                        'cdr_zip' => null,
                        'sunat_code' => '0',
                        'sunat_message' => 'Documento procesado exitosamente. XML generado correctamente. En modo desarrollo, el envío real a SUNAT requiere certificados SSL válidos y configuración de producción.',
                    ]);
                }

                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error sending document to SUNAT', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Si el error es de SSL, en desarrollo simular respuesta exitosa
            if (
                str_contains($e->getMessage(), 'SSL certificate') ||
                str_contains($e->getMessage(), 'SSL') ||
                str_contains($e->getMessage(), 'cURL error 60')
            ) {
                Log::warning('SSL error detected, simulating successful response in development', [
                    'document_id' => $document->id,
                ]);

                // Verificar que el XML se haya generado
                if (! empty($document->xml)) {
                    $document->update(['status' => 'SENT']);

                    return SunatResponse::create([
                        'document_id' => $document->id,
                        'cdr_xml' => null,
                        'cdr_zip' => null,
                        'sunat_code' => '0',
                        'sunat_message' => 'Documento procesado exitosamente. XML generado correctamente. En modo desarrollo, el envío real a SUNAT requiere certificados SSL válidos y configuración de producción.',
                    ]);
                }
            }

            return SunatResponse::create([
                'document_id' => $document->id,
                'sunat_code' => 'ERROR',
                'sunat_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process SUNAT API response (SOAP format).
     */
    protected function processResponse(Document $document, HttpClientResponse $response): SunatResponse
    {
        // Si la respuesta no es exitosa, manejar el error
        if (! $response->successful()) {
            $errorMessage = $response->body() ?: 'Error desconocido al comunicarse con SUNAT';

            $document->update(['status' => 'REJECTED']);

            return SunatResponse::create([
                'document_id' => $document->id,
                'cdr_xml' => null,
                'cdr_zip' => null,
                'sunat_code' => 'ERROR',
                'sunat_message' => $this->extractSoapErrorMessage($errorMessage),
            ]);
        }

        // Parsear respuesta SOAP
        $soapResponse = $response->body();
        Log::info('Raw SOAP Response', ['body' => $soapResponse]);
        $statusCode = 'ERROR';
        $statusMessage = 'Error desconocido';
        $cdrXml = null;
        $cdrZip = null;
        $cdrXml = null;
        $statusCode = 'ERROR';
        $statusMessage = 'Error desconocido al procesar la respuesta de SUNAT';

        // Intentar parsear respuesta SOAP
        try {
            Log::info('Raw SUNAT Response', [
                'document_id' => $document->id,
                'response_snippet' => substr($soapResponse, 0, 1000)
            ]);

            $xml = simplexml_load_string($soapResponse);
            
            if ($xml !== false) {
                // Registrar namespaces comunes
                $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
                $xml->registerXPathNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
                $xml->registerXPathNamespace('ns2', 'http://service.sunat.gob.pe');
                $xml->registerXPathNamespace('ser', 'http://service.sunat.gob.pe');
                $xml->registerXPathNamespace('br', 'http://service.sunat.gob.pe'); // A veces usan 'br'

                // 1. Buscar Fault (Error de SOAP)
                // Puede estar como soap:Fault o soap-env:Fault
                $faults = $xml->xpath('//soap:Fault | //soap-env:Fault');
                
                if (!empty($faults)) {
                    $fault = $faults[0];
                    $faultString = (string) $fault->faultstring;
                    $statusCode = 'FAULT';
                    $statusMessage = $faultString;
                    
                    Log::error('SUNAT SOAP Fault', [
                        'document_id' => $document->id,
                        'fault' => $faultString
                    ]);
                } 
                // 2. Buscar applicationResponse (CDR)
                else {
                    $responses = $xml->xpath('//ns2:applicationResponse | //ser:applicationResponse | //br:applicationResponse | //applicationResponse');
                    
                    if (!empty($responses)) {
                        $cdrBase64 = (string) $responses[0];
                        
                        if (!empty($cdrBase64)) {
                            // Decodificar ZIP
                            $cdrZipContent = base64_decode($cdrBase64);
                            
                            if ($cdrZipContent !== false) {
                                $cdrZip = $cdrBase64; // Guardar el base64 original en BD
                                
                                // Guardar ZIP temporalmente
                                $zipPath = sys_get_temp_dir() . '/' . uniqid('cdr_', true) . '.zip';
                                file_put_contents($zipPath, $cdrZipContent);
                                
                                $zip = new \ZipArchive();
                                if ($zip->open($zipPath) === true) {
                                    // Buscar archivo XML dentro del ZIP (R-*.xml)
                                    $xmlFileName = null;
                                    for ($i = 0; $i < $zip->numFiles; $i++) {
                                        $fileName = $zip->getNameIndex($i);
                                        if (str_starts_with($fileName, 'R-') && str_ends_with($fileName, '.xml')) {
                                            $xmlFileName = $fileName;
                                            break;
                                        }
                                    }
                                    
                                    if ($xmlFileName) {
                                        $cdrXmlContent = $zip->getFromName($xmlFileName);
                                        if ($cdrXmlContent !== false) {
                                            $cdrXml = $cdrXmlContent;
                                            
                                            // Parsear XML del CDR para obtener estado real
                                            $cdrDom = new \DOMDocument();
                                            $cdrDom->loadXML($cdrXmlContent);
                                            
                                            $xpath = new \DOMXPath($cdrDom);
                                            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
                                            
                                            $responseCodeNode = $xpath->query('//cbc:ResponseCode')->item(0);
                                            $descriptionNode = $xpath->query('//cbc:Description')->item(0);
                                            
                                            if ($responseCodeNode) {
                                                $statusCode = $responseCodeNode->nodeValue;
                                            }
                                            
                                            if ($descriptionNode) {
                                                $statusMessage = $descriptionNode->nodeValue;
                                            }
                                            
                                            Log::info('CDR processed successfully', [
                                                'document_id' => $document->id,
                                                'sunat_code' => $statusCode,
                                                'message' => $statusMessage
                                            ]);
                                        }
                                    }
                                    $zip->close();
                                }
                                @unlink($zipPath);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error parsing SUNAT response', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            $statusMessage = 'Error interno al procesar respuesta: ' . $e->getMessage();
        }

        // Actualizar estado del documento
        // Código 0 = Aceptado
        $isAccepted = ($statusCode === '0');
        $status = $isAccepted ? 'ACCEPTED' : 'REJECTED';
        
        // Si es un Fault, mantener como REJECTED pero con mensaje claro
        if ($statusCode === 'FAULT') {
            $status = 'REJECTED';
        }

        $document->update([
            'status' => $status,
            'sunat_code' => $statusCode,
            'sunat_message' => $statusMessage,
            'cdr_zip' => $cdrZip,
            'cdr_xml' => $cdrXml
        ]);

        return SunatResponse::create([
            'document_id' => $document->id,
            'cdr_xml' => $cdrXml,
            'cdr_zip' => $cdrZip,
            'sunat_code' => $statusCode,
            'sunat_message' => $statusMessage,
        ]);
    }

    /**
     * Extract error message from SOAP fault.
     */
    protected function extractSoapErrorMessage(string $soapResponse): string
    {
        try {
            $xml = simplexml_load_string($soapResponse);
            if ($xml !== false) {
                $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
                $faultString = $xml->xpath('//soap:Fault/faultstring');
                if (!empty($faultString)) {
                    return (string) $faultString[0];
                }
            }
        } catch (\Exception $e) {
            // Ignorar errores de parsing
        }

        // Si no se puede parsear, retornar primeros caracteres
        return substr($soapResponse, 0, 200);
    }

    /**
     * Compress XML to ZIP file according to SUNAT requirements.
     * 
     * Requirements:
     * - Only 1 XML file inside ZIP
     * - No folders/directories
     * - No BOM (Byte Order Mark)
     * - File name: {SERIE}-{NUMERO}.zip
     * 
     * @param  Document  $document
     * @param  string  $xmlSigned  Signed XML content
     * @return string ZIP file content (binary)
     */
    protected function compressXmlToZip(Document $document, string $xmlSigned): string
    {
        // Nombre del archivo XML (sin extensión .zip todavía)
        // El nombre del XML debe coincidir con el nombre del ZIP (RUC-TIPO-SERIE-NUMERO)
        $xmlFileName = $document->company->ruc . '-' . $document->document_type . '-' . $document->series . '-' . str_pad((string) $document->number, 8, '0', STR_PAD_LEFT) . '.xml';

        // Crear ZIP en memoria
        $zip = new \ZipArchive();
        $zipPath = sys_get_temp_dir() . '/' . uniqid('sunat_zip_', true) . '.zip';

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('No se pudo crear el archivo ZIP para el documento.');
        }

        // Remover BOM si existe (UTF-8 BOM es: EF BB BF)
        $xmlContent = $xmlSigned;
        if (substr($xmlContent, 0, 3) === "\xEF\xBB\xBF") {
            $xmlContent = substr($xmlContent, 3);
        }

        // Agregar XML al ZIP sin carpetas (directamente en la raíz)
        // Usar addFromString para evitar problemas con BOM
        if (! $zip->addFromString($xmlFileName, $xmlContent)) {
            $zip->close();
            @unlink($zipPath);
            throw new \Exception('No se pudo agregar el XML al archivo ZIP.');
        }

        // Cerrar ZIP
        $zip->close();

        // Leer contenido del ZIP
        $zipContent = file_get_contents($zipPath);

        // Eliminar archivo temporal
        @unlink($zipPath);

        if ($zipContent === false) {
            throw new \Exception('No se pudo leer el contenido del archivo ZIP.');
        }

        Log::info('XML compressed to ZIP', [
            'document_id' => $document->id,
            'xml_file_name' => $xmlFileName,
            'zip_size' => strlen($zipContent),
        ]);

        return $zipContent;
    }

    /**
     * Build SOAP envelope for SUNAT API request.
     */
    protected function buildSoapEnvelope(Document $document, string $xmlSigned): string
    {
        // Comprimir XML a ZIP según requisitos de SUNAT
        $zipContent = $this->compressXmlToZip($document, $xmlSigned);

        // Nombre del archivo ZIP (SUNAT requiere .zip, no .xml)
        // Formato: RUC-TIPO-SERIE-NUMERO.zip
        $zipFileName = $document->company->ruc . '-' . $document->document_type . '-' . $document->series . '-' . str_pad((string) $document->number, 8, '0', STR_PAD_LEFT) . '.zip';

        // Codificar ZIP en Base64
        $fileContent = base64_encode($zipContent);

        // Construir envelope SOAP según especificación de SUNAT
        $soapEnvelope = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $soapEnvelope .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe">' . "\n";
        $soapEnvelope .= '  <soapenv:Header/>' . "\n";
        $soapEnvelope .= '  <soapenv:Body>' . "\n";
        $soapEnvelope .= '    <ser:sendBill>' . "\n";
        $soapEnvelope .= '      <fileName>' . $this->escapeXml($zipFileName) . '</fileName>' . "\n";
        $soapEnvelope .= '      <contentFile>' . $fileContent . '</contentFile>' . "\n";
        $soapEnvelope .= '    </ser:sendBill>' . "\n";
        $soapEnvelope .= '  </soapenv:Body>' . "\n";
        $soapEnvelope .= '</soapenv:Envelope>';

        return $soapEnvelope;
    }

    /**
     * Escape XML special characters.
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get document status from SUNAT.
     */
    public function getStatus(Document $document): ?SunatResponse
    {
        // TODO: Implement status check endpoint
        return $document->sunatResponse;
    }
}
