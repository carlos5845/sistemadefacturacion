<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\CatalogTaxType;
use App\Models\CatalogUnit;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $isSuperAdmin = $user->hasRole('super-admin');

        // Super-admin puede ver todos los productos
        if ($isSuperAdmin) {
            $products = Product::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->when($request->category_id, function ($query, $categoryId) {
                    $query->where('category_id', $categoryId);
                })
                ->when($request->active !== null, function ($query) use ($request) {
                    $query->where('active', $request->boolean('active'));
                })
                ->with(['category', 'unit', 'taxType', 'company'])
                ->latest()
                ->paginate(15);

            $categories = ProductCategory::orderBy('name')->get();

            return Inertia::render('Products/Index', [
                'products' => $products,
                'categories' => $categories,
                'filters' => $request->only(['search', 'category_id', 'active']),
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $companyId) {
            return Inertia::render('Products/Index', [
                'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'categories' => collect([]),
                'filters' => $request->only(['search', 'category_id', 'active']),
                'error' => 'Debe estar asociado a una empresa para ver productos. Crea una empresa primero.',
            ]);
        }

        $products = Product::query()
            ->where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->active !== null, function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->with(['category', 'unit', 'taxType'])
            ->latest()
            ->paginate(15);

        $categories = ProductCategory::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'active']),
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

        // Super-admin puede crear productos pero necesita seleccionar empresa
        if ($isSuperAdmin && ! $companyId) {
            return Inertia::render('Products/Create', [
                'categories' => collect([]),
                'units' => CatalogUnit::orderBy('name')->get(),
                'taxTypes' => CatalogTaxType::orderBy('name')->get(),
                'error' => 'Como super-admin, debes seleccionar una empresa o asociarte a una para crear productos.',
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $isSuperAdmin && ! $companyId) {
            return Inertia::render('Products/Create', [
                'categories' => collect([]),
                'units' => collect([]),
                'taxTypes' => collect([]),
                'error' => 'Debe estar asociado a una empresa para crear productos. Crea una empresa primero.',
            ]);
        }

        $categories = ProductCategory::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $units = CatalogUnit::orderBy('name')->get();
        $taxTypes = CatalogTaxType::orderBy('name')->get();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'units' => $units,
            'taxTypes' => $taxTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
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
                ->withErrors(['company_id' => 'Debe estar asociado a una empresa para crear productos.'])
                ->withInput();
        }

        $product = Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.')
            ->with('created_product_id', $product->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $product->load(['company', 'category', 'unit', 'taxType', 'inventoryStocks.warehouse']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        $categories = ProductCategory::where('company_id', request()->user()->company_id)
            ->orderBy('name')
            ->get();

        $units = CatalogUnit::orderBy('name')->get();
        $taxTypes = CatalogTaxType::orderBy('name')->get();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
            'taxTypes' => $taxTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
