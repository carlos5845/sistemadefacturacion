<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        $companyId = $this->user()->company_id ?? $this->input('company_id');

        return [
            'company_id' => ['nullable', 'exists:companies,id'],
            'identity_type' => ['required', 'string', Rule::in(['DNI', 'RUC', 'CE', 'PAS'])],
            'identity_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique(Customer::class)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId)
                        ->where('identity_type', $this->input('identity_type'));
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
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
            'identity_type.required' => 'El tipo de documento es obligatorio.',
            'identity_type.in' => 'El tipo de documento debe ser DNI, RUC, CE o PAS.',
            'identity_number.required' => 'El número de documento es obligatorio.',
            'identity_number.unique' => 'Este cliente ya está registrado.',
            'name.required' => 'El nombre es obligatorio.',
            'email.email' => 'El email debe ser válido.',
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
