<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $companies = Company::query()
            ->when($request->search, function ($query, $search) {
                $query->where('business_name', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Companies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $company = Company::create($request->validated());

        // Asociar automáticamente al usuario que crea la empresa
        if (! $request->user()->company_id) {
            $request->user()->update(['company_id' => $company->id]);
        }

        return redirect()->route('companies.index')
            ->with('success', 'Empresa creada exitosamente. Tu usuario ha sido asociado automáticamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): Response
    {
        $company->load(['users', 'customers', 'products', 'documents']);

        return Inertia::render('Companies/Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): Response
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')
            ->with('success', 'Empresa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Empresa eliminada exitosamente.');
    }
}
