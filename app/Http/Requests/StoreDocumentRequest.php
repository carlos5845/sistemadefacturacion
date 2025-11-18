<?php

namespace App\Http\Requests;

use App\Models\CatalogDocumentType;
use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'exists:companies,id'],
            'customer_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && ! \App\Models\Customer::where('id', $value)->exists()) {
                    $fail('El cliente seleccionado no existe.');
                }
            }],
            'document_type' => ['required', 'string', 'size:2', Rule::exists(CatalogDocumentType::class, 'code')],
            'series' => ['required', 'string', 'max:4'],
            'number' => ['required', 'integer', 'min:1'],
            'issue_date' => ['required', 'date'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['PEN', 'USD'])],
            'total_taxed' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'total_igv' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'total' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'items.*.total' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'items.*.tax_type' => ['required', 'string', 'max:4'],
            'items.*.igv' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'document_type.exists' => 'El tipo de documento seleccionado no es válido.',
            'series.required' => 'La serie es obligatoria.',
            'number.required' => 'El número es obligatorio.',
            'issue_date.required' => 'La fecha de emisión es obligatoria.',
            'issue_date.date' => 'La fecha de emisión debe ser una fecha válida.',
            'currency.required' => 'La moneda es obligatoria.',
            'total.required' => 'El total es obligatorio.',
            'items.required' => 'Debe agregar al menos un item.',
            'items.min' => 'Debe agregar al menos un item.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('company_id') && $this->user()) {
            $this->merge([
                'company_id' => $this->user()->company_id,
            ]);
        }

        // Convertir customer_id vacío a null
        if ($this->has('customer_id') && $this->input('customer_id') === '') {
            $this->merge(['customer_id' => null]);
        }

        // Asegurar que los valores numéricos sean números, no strings
        if ($this->has('items')) {
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                if (isset($item['quantity'])) {
                    $items[$index]['quantity'] = (float) $item['quantity'];
                }
                if (isset($item['unit_price'])) {
                    $items[$index]['unit_price'] = (float) $item['unit_price'];
                }
                if (isset($item['total'])) {
                    $items[$index]['total'] = (float) $item['total'];
                }
                if (isset($item['igv'])) {
                    $items[$index]['igv'] = (float) $item['igv'];
                }
            }
            $this->merge(['items' => $items]);
        }

        // Convertir totales a números
        if ($this->has('total_taxed')) {
            $this->merge(['total_taxed' => (float) $this->input('total_taxed')]);
        }
        if ($this->has('total_igv')) {
            $this->merge(['total_igv' => (float) $this->input('total_igv')]);
        }
        if ($this->has('total')) {
            $this->merge(['total' => (float) $this->input('total')]);
        }
    }
}
