<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogTaxType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'affects_igv',
    ];

    protected function casts(): array
    {
        return [
            'affects_igv' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tax_type', 'code');
    }

    public function documentItems(): HasMany
    {
        return $this->hasMany(DocumentItem::class, 'tax_type', 'code');
    }

    public function documentTaxes(): HasMany
    {
        return $this->hasMany(DocumentTax::class, 'tax_type', 'code');
    }
}
