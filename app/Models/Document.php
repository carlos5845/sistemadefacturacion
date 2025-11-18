<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'document_type',
        'series',
        'number',
        'issue_date',
        'currency',
        'total_taxed',
        'total_igv',
        'total',
        'xml',
        'xml_signed',
        'hash',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'total_taxed' => 'decimal:2',
            'total_igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CatalogDocumentType::class, 'document_type', 'code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DocumentPayment::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(DocumentTax::class);
    }

    public function sunatResponse(): HasOne
    {
        return $this->hasOne(SunatResponse::class);
    }
}
