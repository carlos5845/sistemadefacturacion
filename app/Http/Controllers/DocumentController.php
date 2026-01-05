<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Jobs\SendDocumentToSunat;
use App\Models\CatalogDocumentType;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $isSuperAdmin = $user->hasRole('super-admin');

        // Super-admin puede ver todos los documentos
        if ($isSuperAdmin) {
            $documents = Document::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('series', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%");
                })
                ->when($request->document_type, function ($query, $type) {
                    $query->where('document_type', $type);
                })
                ->when($request->status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->with(['customer', 'documentType', 'company'])
                ->latest('issue_date')
                ->paginate(12);

            // Transformar documentos para incluir solo el nombre del tipo
            $documents->getCollection()->transform(function ($document) {
                if ($document->documentType) {
                    $document->document_type_name = $document->documentType->name;
                    unset($document->documentType);
                }
                return $document;
            });

            $documentTypes = CatalogDocumentType::whereIn('code', ['01', '03'])->orderBy('name')->get();

            return Inertia::render('Documents/Index', [
                'documents' => $documents,
                'documentTypes' => $documentTypes,
                'filters' => $request->only(['search', 'document_type', 'status']),
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $companyId) {
            return Inertia::render('Documents/Index', [
                'documents' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'documentTypes' => CatalogDocumentType::orderBy('name')->get(),
                'filters' => $request->only(['search', 'document_type', 'status']),
                'error' => 'Debe estar asociado a una empresa para ver documentos. Crea una empresa primero.',
            ]);
        }

        $documents = Document::query()
            ->where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('series', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%");
            })
            ->when($request->document_type, function ($query, $type) {
                $query->where('document_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->with(['customer', 'documentType'])
            ->latest('issue_date')
            ->paginate(12);

        // Transformar documentos para incluir solo el nombre del tipo
        $documents->getCollection()->transform(function ($document) {
            if ($document->documentType) {
                $document->document_type_name = $document->documentType->name;
                unset($document->documentType);
            }
            return $document;
        });

        $documentTypes = CatalogDocumentType::whereIn('code', ['01', '03'])->orderBy('name')->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
            'filters' => $request->only(['search', 'document_type', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = request()->user();
        $companyId = $user->company_id;
        $isSuperAdmin = $user->hasRole('super-admin');

        // Super-admin puede crear documentos pero necesita seleccionar empresa
        if ($isSuperAdmin && ! $companyId) {
            return Inertia::render('Documents/Create', [
                'customers' => collect([]),
                'documentTypes' => CatalogDocumentType::orderBy('name')->get(),
                'error' => 'Como super-admin, debes seleccionar una empresa o asociarte a una para crear documentos.',
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $isSuperAdmin && ! $companyId) {
            return Inertia::render('Documents/Create', [
                'customers' => collect([]),
                'documentTypes' => collect([]),
                'error' => 'Debe estar asociado a una empresa para crear documentos. Crea una empresa primero.',
            ]);
        }

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $products = \App\Models\Product::where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $documentTypes = CatalogDocumentType::whereIn('code', ['01', '03'])->orderBy('name')->get();

        return Inertia::render('Documents/Create', [
            'customers' => $customers,
            'documentTypes' => $documentTypes,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Si no viene company_id en el request, usar el del usuario
        if (! isset($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }

        // Validar que tenga company_id (excepto super-admin que puede especificarlo)
        if (! $data['company_id'] && ! $user->hasRole('super-admin')) {
            return redirect()->back()
                ->withErrors(['company_id' => 'Debe estar asociado a una empresa para crear documentos.'])
                ->withInput();
        }

        $items = $data['items'];
        unset($data['items']);

        // Convertir customer_id vacío a null
        if (isset($data['customer_id']) && $data['customer_id'] === '') {
            $data['customer_id'] = null;
        }

        $document = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $items) {
            $document = Document::create($data);

            foreach ($items as $index => $item) {
                DocumentItem::create([
                    'document_id' => $document->id,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'tax_type' => $item['tax_type'],
                    'igv' => $item['igv'] ?? 0,
                    'order' => $index + 1,
                ]);
            }

            return $document;
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Documento creado exitosamente.')
            ->with('created_document_id', $document->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document): Response
    {
        $user = $request->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $document->load([
            'company',
            'customer',
            'documentType',
            'items.product',
            'items.taxType',
            'payments',
            'taxes.taxType',
            'sunatResponse',
        ]);

        // Preparar datos para Inertia
        $documentData = $document->toArray();
        if ($document->documentType) {
            $documentData['document_type_obj'] = [
                'name' => $document->documentType->name,
            ];
        }

        // Incluir información sobre XML (sin enviar el contenido completo por defecto)
        $documentData['has_xml'] = ! empty($document->xml);
        $documentData['has_xml_signed'] = ! empty($document->xml_signed);
        $documentData['hash'] = $document->hash;

        return Inertia::render('Documents/Show', [
            'document' => $documentData,
        ]);
    }

    /**
     * Print the specified resource.
     */
    public function print(Request $request, Document $document): Response
    {
        $user = $request->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para imprimir este documento.');
        }

        $document->load([
            'company',
            'customer',
            'documentType',
            'items.product',
            'items.taxType',
            'payments',
            'taxes.taxType',
            'sunatResponse',
        ]);

        // Preparar datos para Inertia
        $documentData = $document->toArray();
        if ($document->documentType) {
            $documentData['document_type_obj'] = [
                'name' => $document->documentType->name,
            ];
        }

        return Inertia::render('Documents/Print', [
            'document' => $documentData,
        ]);
    }



    /**
     * Download XML file (original).
     */
    public function downloadXml(Document $document)
    {
        $user = request()->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para descargar este XML.');
        }

        if (empty($document->xml)) {
            abort(404, 'El documento no tiene XML generado.');
        }

        $fileName = $document->series . '-' . $document->number . '.xml';

        return response($document->xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Download signed XML file.
     */
    public function downloadXmlSigned(Document $document)
    {
        $user = request()->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para descargar este XML firmado.');
        }

        if (empty($document->xml_signed)) {
            abort(404, 'El documento no tiene XML firmado.');
        }

        $fileName = $document->series . '-' . $document->number . '-signed.xml';

        return response($document->xml_signed, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * View XML content (original).
     */
    public function viewXml(Document $document): Response
    {
        $user = request()->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para ver este XML.');
        }

        if (empty($document->xml)) {
            abort(404, 'El documento no tiene XML generado.');
        }

        return Inertia::render('Documents/XmlViewer', [
            'document' => [
                'id' => $document->id,
                'series' => $document->series,
                'number' => $document->number,
                'document_type' => $document->document_type,
            ],
            'xml' => $document->xml,
            'type' => 'original',
        ]);
    }

    /**
     * View signed XML content.
     */
    public function viewXmlSigned(Document $document): Response
    {
        $user = request()->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id && ! $user->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para ver este XML firmado.');
        }

        if (empty($document->xml_signed)) {
            abort(404, 'El documento no tiene XML firmado.');
        }

        return Inertia::render('Documents/XmlViewer', [
            'document' => [
                'id' => $document->id,
                'series' => $document->series,
                'number' => $document->number,
                'document_type' => $document->document_type,
            ],
            'xml' => $document->xml_signed,
            'type' => 'signed',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document): Response|RedirectResponse
    {
        $user = request()->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id) {
            abort(403, 'No tienes permiso para editar este documento.');
        }

        if ($document->status !== 'PENDING') {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Solo se pueden editar documentos pendientes.');
        }

        $document->load(['items', 'payments', 'taxes']);

        $customers = Customer::where('company_id', request()->user()->company_id)
            ->orderBy('name')
            ->get();

        $documentTypes = CatalogDocumentType::whereIn('code', ['01', '03'])->orderBy('name')->get();

        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'customers' => $customers,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $user = $request->user();

        // Verificar autorización
        if ($user->company_id !== $document->company_id) {
            abort(403, 'No tienes permiso para editar este documento.');
        }

        if ($document->status !== 'PENDING') {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Solo se pueden editar documentos pendientes.');
        }

        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        // Actualizar documento
        $document->update($data);

        // Eliminar items existentes y crear nuevos
        $document->items()->delete();

        foreach ($items as $index => $item) {
            DocumentItem::create([
                'document_id' => $document->id,
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
                'tax_type' => $item['tax_type'],
                'igv' => $item['igv'] ?? 0,
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Documento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document): RedirectResponse
    {
        // Permitir eliminar si está PENDING o REJECTED (o cualquier estado que no sea final exitoso)
        // Evitar eliminar si ya fue aceptado o anulado oficialmente en SUNAT
        if (in_array($document->status, ['ACCEPTED', 'CANCELED'])) {
            return redirect()->route('documents.index')
                ->with('error', 'No se pueden eliminar documentos que ya han sido aceptados o anulados.');
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }

    /**
     * Send document to SUNAT.
     */
    public function sendToSunat(Request $request, Document $document): RedirectResponse
    {
        $user = $request->user();

        // Verificar autorización manualmente
        if ($user->company_id !== $document->company_id) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'No tienes permiso para enviar este documento a SUNAT.');
        }

        if ($document->status !== 'PENDING') {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Solo se pueden enviar documentos pendientes.');
        }

        // Verificar que la empresa tenga credenciales SOL
        $company = $document->company;
        if (empty($company->user_sol) || empty($company->password_sol)) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'La empresa no tiene configuradas las credenciales SOL. Configure el Usuario SOL y Contraseña SOL en la configuración de la empresa.');
        }

        try {
            SendDocumentToSunat::dispatch($document);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Documento enviado a SUNAT. El proceso se está ejecutando en segundo plano. El estado se actualizará automáticamente cuando SUNAT responda.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error dispatching job to send document to SUNAT', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('documents.show', $document)
                ->with('error', 'Error al enviar el documento a SUNAT: ' . $e->getMessage());
        }
    }
    /**
     * Get the next series and number for a document type.
     */
    public function getNextSeriesNumber(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->company_id) {
            return response()->json(['error' => 'Usuario no asociado a una empresa'], 400);
        }

        $companyId = $user->company_id;
        $documentType = $request->input('document_type');
        $series = $request->input('series');

        if (! $documentType) {
            return response()->json(['error' => 'Tipo de documento requerido'], 400);
        }

        // Si no se proporcionó serie, buscar la última usada o sugerir una por defecto
        if (! $series) {
            $lastDocument = Document::where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->latest('id')
                ->first();

            if ($lastDocument) {
                $series = $lastDocument->series;
            } else {
                // Series por defecto según tipo
                $series = match ($documentType) {
                    '01' => 'F001', // Factura
                    '03' => 'B001', // Boleta
                    '07' => 'FC01', // Nota de Crédito (asumiendo ref a factura por defecto)
                    '08' => 'FD01', // Nota de Débito
                    default => '0001',
                };
            }
        }

        // Buscar el último número para esa serie
        $lastNumber = Document::where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->where('series', $series)
            ->max('number');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return response()->json([
            'series' => $series,
            'number' => $nextNumber,
        ]);
    }
}
