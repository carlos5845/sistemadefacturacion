<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'ruc' => ['required', 'string', 'size:11', 'regex:/^[0-9]{11}$/', Rule::unique(Company::class)->ignore($this->route('company'))],
            'business_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'certificate' => ['nullable', 'string'],
            'certificate_password' => ['nullable', 'string', 'max:255'],
            'user_sol' => ['nullable', 'string', 'max:50'],
            'password_sol' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'ubigeo' => ['nullable', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
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
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.size' => 'El RUC debe tener 11 dígitos.',
            'ruc.regex' => 'El RUC debe contener solo números.',
            'ruc.unique' => 'Este RUC ya está registrado.',
            'business_name.required' => 'La razón social es obligatoria.',
            'ubigeo.size' => 'El ubigeo debe tener 6 dígitos.',
            'ubigeo.regex' => 'El ubigeo debe contener solo números.',
        ];
    }
}
