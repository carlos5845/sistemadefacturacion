<?php

namespace App\Services\Sunat;

use App\Models\Company;
use App\Models\Document;
use App\Models\SunatResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunatApiService
{
    /**
     * Send document to SUNAT API.
     */
    public function send(Document $document): SunatResponse
    {
        $company = $document->company;

        // TODO: Configure SUNAT API endpoints
        // Production: https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService
        // Testing: https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService

        $endpoint = config('services.sunat.endpoint', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService');

        try {
            // TODO: Implement SOAP request to SUNAT
            // This requires building a SOAP envelope with the signed XML
            // Reference: https://cpe.sunat.gob.pe/

            $response = Http::timeout(30)
                ->withBasicAuth($company->user_sol, $company->password_sol)
                ->post($endpoint, [
                    'fileName' => $document->series.'-'.$document->number.'.xml',
                    'contentFile' => base64_encode($document->xml_signed),
                ]);

            return $this->processResponse($document, $response);

        } catch (\Exception $e) {
            Log::error('Error sending document to SUNAT', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return SunatResponse::create([
                'document_id' => $document->id,
                'sunat_code' => 'ERROR',
                'sunat_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process SUNAT API response.
     */
    protected function processResponse(Document $document, $response): SunatResponse
    {
        // TODO: Parse SOAP response and extract CDR (Constancia de Recepción)
        // The response contains:
        // - applicationResponse (CDR XML)
        // - statusCode (0 = success, others = errors)
        // - statusMessage

        $statusCode = $response->json('statusCode', 'ERROR');
        $statusMessage = $response->json('statusMessage', 'Error desconocido');
        $cdrXml = $response->json('applicationResponse', '');

        // Update document status
        $document->status = $statusCode === 0 ? 'ACCEPTED' : 'REJECTED';
        $document->save();

        return SunatResponse::create([
            'document_id' => $document->id,
            'cdr_xml' => $cdrXml,
            'cdr_zip' => null, // TODO: Extract ZIP if provided
            'sunat_code' => (string) $statusCode,
            'sunat_message' => $statusMessage,
        ]);
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
