<?php

namespace App\Http\Requests;

use App\Models\CatalogTaxType;
use App\Models\CatalogUnit;
use App\Models\ProductCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'unit_type' => ['required', 'string', 'max:4', Rule::exists(CatalogUnit::class, 'code')],
            'sale_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'tax_type' => ['required', 'string', 'max:4', Rule::exists(CatalogTaxType::class, 'code')],
            'has_igv' => ['boolean'],
            'active' => ['boolean'],
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
            'name.required' => 'El nombre del producto es obligatorio.',
            'unit_type.required' => 'La unidad de medida es obligatoria.',
            'unit_type.exists' => 'La unidad de medida seleccionada no es válida.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser un número.',
            'sale_price.min' => 'El precio de venta debe ser mayor o igual a 0.',
            'tax_type.required' => 'El tipo de impuesto es obligatorio.',
            'tax_type.exists' => 'El tipo de impuesto seleccionado no es válido.',
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
    }
}
