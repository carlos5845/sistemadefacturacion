<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $data = $request->validated();

        // Manejar archivo de certificado PFX/P12
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');

            // Validar que se proporcione la contraseña cuando se sube un archivo
            if (empty($data['certificate_password'])) {
                return redirect()->back()
                    ->withErrors(['certificate_password' => 'Debe proporcionar la contraseña del certificado PFX/P12.'])
                    ->withInput();
            }

            // Guardar el archivo en storage/app/certificates
            $certificatesPath = storage_path('app/certificates');
            if (! is_dir($certificatesPath)) {
                mkdir($certificatesPath, 0755, true);
            }

            // Generar nombre único para el archivo
            $fileName = 'cert_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('certificates', $fileName, 'local');

            // Guardar la ruta completa del archivo usando Storage::path() para normalizar rutas
            $data['certificate'] = Storage::disk('local')->path($filePath);

            // Validar que el certificado se pueda leer con la contraseña proporcionada
            try {
                $pkcs12 = file_get_contents($data['certificate']);
                $certs = [];
                if (! openssl_pkcs12_read($pkcs12, $certs, $data['certificate_password'])) {
                    // Eliminar el archivo si no es válido
                    @unlink($data['certificate']);
                    return redirect()->back()
                        ->withErrors(['certificate_file' => 'El certificado PFX/P12 no pudo ser leído. Verifique que la contraseña sea correcta y que el archivo sea válido.'])
                        ->withInput();
                }
            } catch (\Exception $e) {
                // Eliminar el archivo si hay error
                if (isset($data['certificate']) && file_exists($data['certificate'])) {
                    @unlink($data['certificate']);
                }
                return redirect()->back()
                    ->withErrors(['certificate_file' => 'Error al validar el certificado: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        // Eliminar certificate_file del array ya que no es un campo de la base de datos
        unset($data['certificate_file']);

        $company = Company::create($data);

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
        $company->loadCount(['users', 'customers', 'products', 'documents']);

        return Inertia::render('Companies/Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): Response
    {
        // Preparar datos de la empresa para el formulario
        // No enviar contraseñas sensibles, pero sí indicar si existen
        $companyData = $company->toArray();
        $companyData['has_password_sol'] = ! empty($company->password_sol);
        $companyData['has_certificate'] = ! empty($company->certificate);
        $companyData['has_certificate_password'] = ! empty($company->certificate_password);

        return Inertia::render('Companies/Edit', [
            'company' => $companyData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();

        // Si no se proporciona una nueva contraseña SOL, mantener la actual
        if (empty($data['password_sol'])) {
            unset($data['password_sol']);
        }

        // Manejar archivo de certificado PFX/P12
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');

            // Validar que se proporcione la contraseña cuando se sube un archivo
            if (empty($data['certificate_password'])) {
                if (empty($company->certificate_password)) {
                    return redirect()->back()
                        ->withErrors(['certificate_password' => 'Debe proporcionar la contraseña del certificado PFX/P12.'])
                        ->withInput();
                }
                // Usar la contraseña actual si no se proporciona una nueva
                $data['certificate_password'] = $company->certificate_password;
            }

            // Guardar el archivo en storage/app/certificates
            $certificatesPath = storage_path('app/certificates');
            if (! is_dir($certificatesPath)) {
                mkdir($certificatesPath, 0755, true);
            }

            // Eliminar el certificado anterior si existe
            if (! empty($company->certificate) && file_exists($company->certificate) && str_contains($company->certificate, 'certificates/')) {
                @unlink($company->certificate);
            }

            // Generar nombre único para el archivo
            $fileName = 'cert_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('certificates', $fileName, 'local');

            // Guardar la ruta completa del archivo usando Storage::path() para normalizar rutas
            $data['certificate'] = Storage::disk('local')->path($filePath);

            // Validar que el certificado se pueda leer con la contraseña proporcionada
            try {
                $pkcs12 = file_get_contents($data['certificate']);
                $certs = [];
                if (! openssl_pkcs12_read($pkcs12, $certs, $data['certificate_password'])) {
                    // Eliminar el archivo si no es válido
                    @unlink($data['certificate']);
                    return redirect()->back()
                        ->withErrors(['certificate_file' => 'El certificado PFX/P12 no pudo ser leído. Verifique que la contraseña sea correcta y que el archivo sea válido.'])
                        ->withInput();
                }
            } catch (\Exception $e) {
                // Eliminar el archivo si hay error
                if (isset($data['certificate']) && file_exists($data['certificate'])) {
                    @unlink($data['certificate']);
                }
                return redirect()->back()
                    ->withErrors(['certificate_file' => 'Error al validar el certificado: ' . $e->getMessage()])
                    ->withInput();
            }
        } else {
            // No se proporciona nuevo certificado, mantener el actual
            unset($data['certificate']);
        }

        // Si no se proporciona una nueva contraseña de certificado, mantener la actual
        if (empty($data['certificate_password'])) {
            unset($data['certificate_password']);
        }

        // Eliminar certificate_file del array ya que no es un campo de la base de datos
        unset($data['certificate_file']);

        $company->update($data);

        return redirect()->route('companies.show', $company)
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
