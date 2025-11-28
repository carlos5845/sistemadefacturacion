<?php

namespace App\Http\Requests;

use App\Models\CatalogDocumentType;
use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
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
        $document = $this->route('document');
        $documentType = $document ? $document->document_type : null;

        return [
            'customer_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($documentType) {
                    if ($value !== null && $value !== '' && ! \App\Models\Customer::where('id', $value)->exists()) {
                        $fail('El cliente seleccionado no existe.');
                    }

                    // Validar que el cliente cumpla con las reglas según tipo de documento
                    if ($value && $documentType) {
                        $customer = \App\Models\Customer::find($value);
                        if ($customer) {
                            // Facturas (01) requieren RUC (schemeID=6)
                            if ($documentType === '01' && $customer->identity_type !== '6') {
                                $fail('Las facturas requieren un cliente con RUC. El cliente seleccionado no tiene RUC.');
                            }
                        }
                    } elseif ($documentType === '01') {
                        // Facturas siempre requieren cliente con RUC
                        $fail('Las facturas requieren un cliente con RUC.');
                    }
                },
            ],
            'issue_date' => ['required', 'date'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['PEN', 'USD'])],
            'total_taxed' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'total_igv' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                function ($attribute, $value, $fail) {
                    // Validar que IGV sea exactamente 18% del total_taxed
                    $totalTaxed = (float) $this->input('total_taxed', 0);
                    $expectedIgv = round($totalTaxed * 0.18, 2);
                    $actualIgv = (float) $value;

                    // Comparar con tolerancia de 0.01 para evitar problemas de redondeo
                    if (abs($actualIgv - $expectedIgv) > 0.01) {
                        $fail("El IGV debe ser exactamente el 18% del valor de venta. Valor esperado: {$expectedIgv}, valor ingresado: {$actualIgv}.");
                    }
                },
            ],
            'total' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                function ($attribute, $value, $fail) {
                    // Validar que total = total_taxed + total_igv
                    $totalTaxed = (float) $this->input('total_taxed', 0);
                    $totalIgv = (float) $this->input('total_igv', 0);
                    $expectedTotal = round($totalTaxed + $totalIgv, 2);
                    $actualTotal = (float) $value;

                    // Comparar con tolerancia de 0.01 para evitar problemas de redondeo
                    if (abs($actualTotal - $expectedTotal) > 0.01) {
                        $fail("El total debe ser igual a la suma del valor de venta más el IGV. Valor esperado: {$expectedTotal}, valor ingresado: {$actualTotal}.");
                    }
                },
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $items, $fail) {
                    // Validar cálculos de items
                    $totalTaxedCalculated = 0;
                    $totalIgvCalculated = 0;

                    foreach ($items as $index => $item) {
                        $quantity = (float) ($item['quantity'] ?? 0);
                        $unitPrice = (float) ($item['unit_price'] ?? 0);
                        $itemTotal = (float) ($item['total'] ?? 0);
                        $itemIgv = (float) ($item['igv'] ?? 0);
                        $taxType = $item['tax_type'] ?? '';

                        // Calcular subtotal sin IGV
                        $subtotal = round($quantity * $unitPrice, 2);

                        // Si el item tiene IGV (tax_type = 10), el total debe incluir IGV
                        if ($taxType === '10') {
                            // Total esperado = subtotal + IGV
                            $expectedItemIgv = round($subtotal * 0.18, 2);
                            $expectedItemTotal = round($subtotal + $expectedItemIgv, 2);

                            // Validar que el IGV sea correcto
                            if (abs($itemIgv - $expectedItemIgv) > 0.01) {
                                $fail("El IGV del item #{$index} debe ser exactamente el 18% del subtotal (cantidad × precio unitario). Valor esperado: {$expectedItemIgv}, valor ingresado: {$itemIgv}.");
                            }

                            // Validar que el total incluya el IGV
                            if (abs($itemTotal - $expectedItemTotal) > 0.01) {
                                $fail("El total del item #{$index} debe ser igual al subtotal más el IGV. Subtotal: {$subtotal}, IGV: {$expectedItemIgv}, Total esperado: {$expectedItemTotal}, valor ingresado: {$itemTotal}.");
                            }

                            // Acumular para totales del documento (subtotal sin IGV y IGV por separado)
                            $totalTaxedCalculated += $subtotal;
                            $totalIgvCalculated += $expectedItemIgv;
                        } else {
                            // Si no tiene IGV, el total debe ser igual al subtotal
                            if (abs($itemTotal - $subtotal) > 0.01) {
                                $fail("El total del item #{$index} debe ser igual a cantidad × precio unitario (sin IGV). Valor esperado: {$subtotal}, valor ingresado: {$itemTotal}.");
                            }

                            // Validar que no tenga IGV
                            if ($itemIgv > 0.01) {
                                $fail("El item #{$index} no debe tener IGV porque su tipo de impuesto no es gravado.");
                            }

                            // No acumular en totales gravados si no tiene IGV
                        }
                    }

                    // Validar que los totales de items coincidan con los totales del documento
                    $totalTaxed = (float) $this->input('total_taxed', 0);
                    $totalIgv = (float) $this->input('total_igv', 0);

                    if (abs($totalTaxedCalculated - $totalTaxed) > 0.01) {
                        $fail("El total gravado calculado de los items ({$totalTaxedCalculated}) no coincide con el total gravado del documento ({$totalTaxed}).");
                    }

                    if (abs($totalIgvCalculated - $totalIgv) > 0.01) {
                        $fail("El total IGV calculado de los items ({$totalIgvCalculated}) no coincide con el total IGV del documento ({$totalIgv}).");
                    }
                },
            ],
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
