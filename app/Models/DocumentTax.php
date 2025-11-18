<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'tax_type',
        'tax_base',
        'tax_rate',
        'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_base' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function taxType(): BelongsTo
    {
        return $this->belongsTo(CatalogTaxType::class, 'tax_type', 'code');
    }
}
