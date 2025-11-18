<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_type', 'code');
    }
}
