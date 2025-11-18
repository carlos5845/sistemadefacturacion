<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'business_name',
        'trade_name',
        'certificate',
        'certificate_password',
        'user_sol',
        'password_sol',
        'address',
        'ubigeo',
    ];

    protected function casts(): array
    {
        return [];
    }

    protected $hidden = [
        'certificate_password',
        'password_sol',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
