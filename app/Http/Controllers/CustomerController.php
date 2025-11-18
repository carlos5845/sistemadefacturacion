<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $isSuperAdmin = $user->hasRole('super-admin');

        // Super-admin puede ver todos los clientes
        if ($isSuperAdmin) {
            $customers = Customer::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%");
                })
                ->with('company')
                ->latest()
                ->paginate(15);

            return Inertia::render('Customers/Index', [
                'customers' => $customers,
                'filters' => $request->only(['search']),
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $companyId) {
            return Inertia::render('Customers/Index', [
                'customers' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'filters' => $request->only(['search']),
                'error' => 'Debe estar asociado a una empresa para ver clientes. Crea una empresa primero.',
            ]);
        }

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            })
            ->with('company')
            ->latest()
            ->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search']),
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

        // Super-admin puede crear clientes pero necesita seleccionar empresa
        if ($isSuperAdmin && ! $companyId) {
            return Inertia::render('Customers/Create', [
                'error' => 'Como super-admin, debes seleccionar una empresa o asociarte a una para crear clientes.',
            ]);
        }

        // Usuarios normales necesitan estar asociados a una empresa
        if (! $isSuperAdmin && ! $companyId) {
            return Inertia::render('Customers/Create', [
                'error' => 'Debe estar asociado a una empresa para crear clientes. Crea una empresa primero.',
            ]);
        }

        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
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
                ->withErrors(['company_id' => 'Debe estar asociado a una empresa para crear clientes.'])
                ->withInput();
        }

        $customer = Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Cliente creado exitosamente.')
            ->with('created_customer_id', $customer->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): Response
    {
        $customer->load(['company', 'documents']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
