<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDocumentSeries implements ValidationRule
{
    protected $documentType;

    public function __construct($documentType)
    {
        $this->documentType = $documentType;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $series = strtoupper(trim($value));

        if ($this->documentType === '01') { // Factura
            if (!preg_match('/^F\d{3}$/', $series)) {
                $fail('La serie para facturas debe tener el formato F001-F999.');
            }
        } elseif ($this->documentType === '03') { // Boleta
            if (!preg_match('/^B\d{3}$/', $series)) {
                $fail('La serie para boletas debe tener el formato B001-B999.');
            }
        } elseif ($this->documentType === '07' || $this->documentType === '08') { // Notas
            if (!preg_match('/^(F|B)[CD]\d{2}$/', $series)) {
                $fail('La serie para notas debe tener formato válido (ej. FC01, BC01).');
            }
        }
    }
}
