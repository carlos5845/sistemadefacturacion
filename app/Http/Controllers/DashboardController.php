<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $companyId = $request->user()->company_id;

        if (! $companyId) {
            return Inertia::render('dashboard', [
                'stats' => [
                    'total_customers' => 0,
                    'total_products' => 0,
                    'total_documents' => 0,
                    'pending_documents' => 0,
                    'accepted_documents' => 0,
                    'rejected_documents' => 0,
                    'total_sales' => '0.00',
                ],
                'recentDocuments' => collect([]),
                'error' => 'Debe estar asociado a una empresa para ver el dashboard.',
            ]);
        }

        $stats = [
            'total_customers' => Customer::where('company_id', $companyId)->count(),
            'total_products' => Product::where('company_id', $companyId)->where('active', true)->count(),
            'total_documents' => Document::where('company_id', $companyId)->count(),
            'pending_documents' => Document::where('company_id', $companyId)->where('status', 'PENDING')->count(),
            'accepted_documents' => Document::where('company_id', $companyId)->where('status', 'ACCEPTED')->count(),
            'rejected_documents' => Document::where('company_id', $companyId)->where('status', 'REJECTED')->count(),
            'total_sales' => Document::where('company_id', $companyId)
                ->where('status', 'ACCEPTED')
                ->sum('total') ?? '0.00',
        ];

        $recentDocuments = Document::where('company_id', $companyId)
            ->with(['customer', 'documentType'])
            ->latest('issue_date')
            ->limit(10)
            ->get()
            ->map(function ($document) {
                if ($document->documentType) {
                    $document->document_type_name = $document->documentType->name;
                    unset($document->documentType);
                }
                return $document;
            });

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recentDocuments' => $recentDocuments,
        ]);
    }
}

