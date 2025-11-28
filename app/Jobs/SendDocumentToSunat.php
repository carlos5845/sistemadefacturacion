<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Sunat\SunatApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDocumentToSunat implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SunatApiService $sunatService): void
    {
        try {
            // Update document status to SENT
            $this->document->update(['status' => 'SENT']);

            // Send to SUNAT
            $sunatResponse = $sunatService->send($this->document);

            // Si la respuesta tiene código ERROR, actualizar el estado a REJECTED
            if ($sunatResponse->sunat_code === 'ERROR') {
                $this->document->update(['status' => 'REJECTED']);
            } elseif ($sunatResponse->sunat_code === 'PENDING') {
                // Si está pendiente (sin XML firmado), mantener en SENT
                $this->document->update(['status' => 'SENT']);
            }

            Log::info('Document sent to SUNAT successfully', [
                'document_id' => $this->document->id,
                'sunat_code' => $sunatResponse->sunat_code,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send document to SUNAT', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update document status back to PENDING on failure
            $this->document->update(['status' => 'PENDING']);

            // Crear respuesta de error
            \App\Models\SunatResponse::updateOrCreate(
                ['document_id' => $this->document->id],
                [
                    'sunat_code' => 'ERROR',
                    'sunat_message' => 'Error al enviar: ' . $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job failed to send document to SUNAT', [
            'document_id' => $this->document->id,
            'error' => $exception->getMessage(),
        ]);

        $this->document->update(['status' => 'PENDING']);
    }
}
