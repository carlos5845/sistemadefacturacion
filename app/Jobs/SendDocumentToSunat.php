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
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SunatApiService $sunatService): void
    {
        try {
            // Update document status to SENT
            $this->document->update(['status' => 'SENT']);

            // Send to SUNAT
            $sunatService->send($this->document);

            Log::info('Document sent to SUNAT successfully', [
                'document_id' => $this->document->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send document to SUNAT', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);

            // Update document status back to PENDING on failure
            $this->document->update(['status' => 'PENDING']);

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
